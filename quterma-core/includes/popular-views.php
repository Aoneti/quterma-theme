<?php
/**
 * Popular posts tracking and query logic ("Читают сейчас")
 *
 * @package QutermaCore
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!defined('QUTERMA_CORE_VIEWS_META_KEY')) {
    define('QUTERMA_CORE_VIEWS_META_KEY', '_quterma_post_views');
}
if (!defined('QUTERMA_CORE_POPULAR_ID_CACHE')) {
    define('QUTERMA_CORE_POPULAR_ID_CACHE', 'quterma_pop_ids');
}

if (!function_exists('quterma_core_register_view_tracking_endpoint')) {
    function quterma_core_register_view_tracking_endpoint() {
        register_rest_route('quterma/v1', '/track-view', array(
            'methods'             => 'POST',
            'callback'            => 'quterma_core_rest_track_view',
            'permission_callback' => '__return_true',
            'args'                => array(
                'post_id' => array(
                    'required'          => true,
                    'validate_callback' => function ($param) {
                        return is_numeric($param) && (int) $param > 0;
                    },
                    'sanitize_callback' => 'absint',
                ),
            ),
        ));
    }
    add_action('rest_api_init', 'quterma_core_register_view_tracking_endpoint');
}

if (!function_exists('quterma_core_rest_track_view')) {
    function quterma_core_rest_track_view(WP_REST_Request $request) {
    $post_id = (int) $request->get_param('post_id');
    if ($post_id <= 0) {
        return new WP_REST_Response(array('success' => false, 'error' => 'invalid_id'), 400);
    }

    $post = get_post($post_id);
    if (!$post || $post->post_status !== 'publish' || $post->post_type !== 'post') {
        return new WP_REST_Response(array('success' => false, 'error' => 'not_found'), 404);
    }

    if (current_user_can('edit_posts')) {
        return new WP_REST_Response(array('success' => true, 'tracked' => false, 'reason' => 'editor_excluded'), 200);
    }

    if (isset($_SERVER['HTTP_USER_AGENT'])) {
        $ua = strtolower($_SERVER['HTTP_USER_AGENT']);
        $bot_regex = '/\b(bot|crawl|slurp|spider|mediapartners|googlebot|yandexbot|bingbot|baiduspider|duckduckbot|lighthouse|headless|curl|wget)\b/i';
        if (preg_match($bot_regex, $ua)) {
            return new WP_REST_Response(array('success' => true, 'tracked' => false, 'reason' => 'bot_excluded'), 200);
        }
    }

    $cookie_name = 'quterma_viewed_' . $post_id;
    if (isset($_COOKIE[$cookie_name])) {
        return new WP_REST_Response(array('success' => true, 'tracked' => false, 'reason' => 'already_counted'), 200);
    }

    if (!headers_sent()) {
        $cookie_path = COOKIEPATH ? COOKIEPATH : '/';
        $cookie_domain = COOKIE_DOMAIN ? COOKIE_DOMAIN : '';
        setcookie($cookie_name, '1', array(
            'expires'  => time() + 3600,
            'path'     => $cookie_path,
            'domain'   => $cookie_domain,
            'secure'   => is_ssl(),
            'httponly' => true,
            'samesite' => 'Lax',
        ));
    }

    $total_views = (int) get_post_meta($post_id, QUTERMA_CORE_VIEWS_META_KEY, true);
    $total_views++;
    update_post_meta($post_id, QUTERMA_CORE_VIEWS_META_KEY, $total_views);

    $today_key = '_quterma_views_' . wp_date('Ymd');
    $daily_views = (int) get_post_meta($post_id, $today_key, true);
    $daily_views++;
    update_post_meta($post_id, $today_key, $daily_views);

    return new WP_REST_Response(array('success' => true, 'tracked' => true), 200);
}
}

if (!function_exists('quterma_get_popular_post_ids_by_period')) {
    function quterma_get_popular_post_ids_by_period($period = 'today', $limit = 5) {
        global $wpdb;
        $limit = min(20, max(1, (int) $limit));
        $tz    = wp_timezone();
        $now   = new DateTimeImmutable('now', $tz);

        $matched_ids = array();

        if ($period === 'today') {
            $today_key = '_quterma_views_' . $now->format('Ymd');
            $query = new WP_Query(array(
                'post_type'           => 'post',
                'post_status'         => 'publish',
                'posts_per_page'      => $limit,
                'ignore_sticky_posts' => true,
                'meta_key'            => $today_key,
                'orderby'             => 'meta_value_num date',
                'order'               => 'DESC',
                'fields'              => 'ids',
                'no_found_rows'       => true,
            ));
            $matched_ids = $query->posts;
        } elseif ($period === 'yesterday') {
            $yesterday_key = '_quterma_views_' . $now->modify('-1 day')->format('Ymd');
            $query = new WP_Query(array(
                'post_type'           => 'post',
                'post_status'         => 'publish',
                'posts_per_page'      => $limit,
                'ignore_sticky_posts' => true,
                'meta_key'            => $yesterday_key,
                'orderby'             => 'meta_value_num date',
                'order'               => 'DESC',
                'fields'              => 'ids',
                'no_found_rows'       => true,
            ));
            $matched_ids = $query->posts;
        } elseif ($period === 'week' || $period === 'month') {
            $days = ($period === 'week') ? 7 : 30;
            $bucket_keys = array();
            for ($i = 0; $i < $days; $i++) {
                $bucket_keys[] = '_quterma_views_' . $now->modify("-{$i} days")->format('Ymd');
            }

            if (!empty($bucket_keys) && $wpdb && isset($wpdb->postmeta)) {
                $placeholders = implode(',', array_fill(0, count($bucket_keys), '%s'));
                $sql = $wpdb->prepare(
                    "SELECT post_id, SUM(CAST(meta_value AS UNSIGNED)) as total_bucket_views
                     FROM {$wpdb->postmeta}
                     WHERE meta_key IN ($placeholders)
                     GROUP BY post_id
                     ORDER BY total_bucket_views DESC
                     LIMIT %d",
                    array_merge($bucket_keys, array($limit))
                );
                $results = $wpdb->get_results($sql);
                if (!empty($results)) {
                    foreach ($results as $row) {
                        $matched_ids[] = (int) $row->post_id;
                    }
                }
            }
        }

        if (count($matched_ids) < $limit) {
            $needed = $limit - count($matched_ids);
            $views_query = new WP_Query(array(
                'post_type'           => 'post',
                'post_status'         => 'publish',
                'posts_per_page'      => $needed,
                'post__not_in'        => $matched_ids,
                'ignore_sticky_posts' => true,
                'meta_key'            => QUTERMA_CORE_VIEWS_META_KEY,
                'orderby'             => 'meta_value_num date',
                'order'               => 'DESC',
                'fields'              => 'ids',
                'no_found_rows'       => true,
            ));
            if (!empty($views_query->posts)) {
                $matched_ids = array_merge($matched_ids, $views_query->posts);
            }
        }

        if (count($matched_ids) < $limit) {
            $needed = $limit - count($matched_ids);
            $latest_query = new WP_Query(array(
                'post_type'           => 'post',
                'post_status'         => 'publish',
                'posts_per_page'      => $needed,
                'post__not_in'        => $matched_ids,
                'ignore_sticky_posts' => true,
                'orderby'             => 'date',
                'order'               => 'DESC',
                'fields'              => 'ids',
                'no_found_rows'       => true,
            ));
            if (!empty($latest_query->posts)) {
                $matched_ids = array_merge($matched_ids, $latest_query->posts);
            }
        }

        return array_map('intval', array_slice($matched_ids, 0, $limit));
    }
}

if (!function_exists('quterma_get_popular_posts_by_period')) {
    function quterma_get_popular_posts_by_period($period = 'today', $limit = 5) {
        $period    = sanitize_key($period);
        $limit     = min(20, max(1, (int) $limit));
        $cache_key = QUTERMA_CORE_POPULAR_ID_CACHE . '_' . $period . '_' . $limit;

        $post_ids = get_transient($cache_key);

        if (false === $post_ids || !is_array($post_ids)) {
            $post_ids = quterma_get_popular_post_ids_by_period($period, $limit);
            set_transient($cache_key, $post_ids, 10 * MINUTE_IN_SECONDS);
        }

        if (empty($post_ids)) {
            return array();
        }

        $posts_query = new WP_Query(array(
            'post_type'              => 'post',
            'post_status'            => 'publish',
            'post__in'               => $post_ids,
            'orderby'                => 'post__in',
            'posts_per_page'         => count($post_ids),
            'ignore_sticky_posts'    => true,
            'no_found_rows'          => true,
            'update_post_meta_cache' => true,
            'update_post_term_cache' => true,
        ));

        return $posts_query->posts;
    }
}

if (!function_exists('quterma_get_popular_posts')) {
    function quterma_get_popular_posts($limit = 5) {
        return quterma_get_popular_posts_by_period('month', $limit);
    }
}

if (!function_exists('quterma_core_flush_popular_cache')) {
    function quterma_core_flush_popular_cache($post_id = 0) {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        foreach (array('today', 'yesterday', 'week', 'month') as $p) {
            delete_transient(QUTERMA_CORE_POPULAR_ID_CACHE . '_' . $p . '_5');
            delete_transient(QUTERMA_CORE_POPULAR_ID_CACHE . '_' . $p . '_10');
        }
    }
    add_action('save_post', 'quterma_core_flush_popular_cache');
    add_action('deleted_post', 'quterma_core_flush_popular_cache');
    add_action('transition_post_status', function ($new_status, $old_status) {
        if ($new_status !== $old_status) {
            quterma_core_flush_popular_cache();
        }
    }, 10, 2);
}
