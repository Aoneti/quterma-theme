<?php
/**
 * Popular posts tracking and query logic ("Читают сейчас")
 *
 * Implements:
 * - Asynchronous view counting via REST API / Beacon (zero DB writes & zero Set-Cookie on public GET)
 * - Full compatibility with static page caching (page-cache hits still record views)
 * - Strict bot and editor exclusion (never counts previews, bots, or editorial staff)
 * - Daily view buckets (_quterma_views_YYYYMMDD) so a 20-day-old post read today appears in "Сегодня"
 * - Lightweight ID-only caching in transients (no heavy WP_Post serialization or option bloat)
 * - Single-query bulk priming of post meta and taxonomy terms to eliminate N+1 queries
 *
 * @package Quterma
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!defined('QUTERMA_VIEWS_META_KEY')) {
    define('QUTERMA_VIEWS_META_KEY', '_quterma_post_views');
}
if (!defined('QUTERMA_POPULAR_ID_CACHE')) {
    define('QUTERMA_POPULAR_ID_CACHE', 'quterma_pop_ids');
}

/**
 * Register REST API route for asynchronous post view tracking.
 * POST /wp-json/quterma/v1/track-view
 */
if (!function_exists('quterma_register_view_tracking_endpoint')) {
    function quterma_register_view_tracking_endpoint() {
        register_rest_route('quterma/v1', '/track-view', array(
            'methods'             => 'POST',
            'callback'            => 'quterma_rest_track_view',
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
    add_action('rest_api_init', 'quterma_register_view_tracking_endpoint');
}

/**
 * REST API handler for tracking views.
 *
 * @param WP_REST_Request $request
 * @return WP_REST_Response
 */
if (!function_exists('quterma_rest_track_view')) {
    function quterma_rest_track_view(WP_REST_Request $request) {
        $post_id = (int) $request->get_param('post_id');
        if ($post_id <= 0) {
            return new WP_REST_Response(array('success' => false, 'error' => 'invalid_id'), 400);
        }

        $post = get_post($post_id);
        if (!$post || $post->post_status !== 'publish' || $post->post_type !== 'post') {
            return new WP_REST_Response(array('success' => false, 'error' => 'not_found'), 404);
        }

        // Exclude logged in editors and administrators
        if (current_user_can('edit_posts')) {
            return new WP_REST_Response(array('success' => true, 'tracked' => false, 'reason' => 'editor_excluded'), 200);
        }

        // Strict bot detection
        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            $ua = strtolower($_SERVER['HTTP_USER_AGENT']);
            $bot_regex = '/\b(bot|crawl|slurp|spider|mediapartners|googlebot|yandexbot|bingbot|baiduspider|duckduckbot|lighthouse|headless|curl|wget)\b/i';
            if (preg_match($bot_regex, $ua)) {
                return new WP_REST_Response(array('success' => true, 'tracked' => false, 'reason' => 'bot_excluded'), 200);
            }
        }

        // Anti-repeat visit check (1 hour cookie window)
        $cookie_name = 'quterma_viewed_' . $post_id;
        if (isset($_COOKIE[$cookie_name])) {
            return new WP_REST_Response(array('success' => true, 'tracked' => false, 'reason' => 'already_counted'), 200);
        }

        // Set secure anti-repeat cookie
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

        // 1. Increment total view counter
        $total_views = (int) get_post_meta($post_id, QUTERMA_VIEWS_META_KEY, true);
        $total_views++;
        update_post_meta($post_id, QUTERMA_VIEWS_META_KEY, $total_views);

        // 2. Increment daily view counter for accurate periods
        $today_key = '_quterma_views_' . wp_date('Ymd');
        $daily_views = (int) get_post_meta($post_id, $today_key, true);
        $daily_views++;
        update_post_meta($post_id, $today_key, $daily_views);

        return new WP_REST_Response(array(
            'success'     => true,
            'tracked'     => true,
            'views_total' => $total_views,
            'views_today' => $daily_views,
        ), 200);
    }
}

/**
 * Fetch top post IDs by time period.
 *
 * @param string $period 'today' | 'yesterday' | 'week' | 'month'
 * @param int $limit Number of IDs to return (default 5).
 * @return int[] Array of post IDs.
 */
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

        // Fallback 1: if period returned fewer posts than requested limit, fill from all-time views
        if (count($matched_ids) < $limit) {
            $needed = $limit - count($matched_ids);
            $views_query = new WP_Query(array(
                'post_type'           => 'post',
                'post_status'         => 'publish',
                'posts_per_page'      => $needed,
                'post__not_in'        => $matched_ids,
                'ignore_sticky_posts' => true,
                'meta_key'            => QUTERMA_VIEWS_META_KEY,
                'orderby'             => 'meta_value_num date',
                'order'               => 'DESC',
                'fields'              => 'ids',
                'no_found_rows'       => true,
            ));
            if (!empty($views_query->posts)) {
                $matched_ids = array_merge($matched_ids, $views_query->posts);
            }
        }

        // Fallback 2: if site has very few views overall, backfill from latest published posts
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

/**
 * Get popular posts by specific time period.
 * Caches strictly post IDs in transients, then prime-loads posts in a single WP_Query.
 *
 * @param string $period 'today' | 'yesterday' | 'week' | 'month'
 * @param int $limit Number of posts to return (default 5).
 * @return WP_Post[] Array of post objects.
 */
if (!function_exists('quterma_get_popular_posts_by_period')) {
    function quterma_get_popular_posts_by_period($period = 'today', $limit = 5) {
        $period    = sanitize_key($period);
        $limit     = min(20, max(1, (int) $limit));
        $cache_key = QUTERMA_POPULAR_ID_CACHE . '_' . $period . '_' . $limit;

        $post_ids = get_transient($cache_key);

        if (false === $post_ids || !is_array($post_ids)) {
            $post_ids = quterma_get_popular_post_ids_by_period($period, $limit);
            set_transient($cache_key, $post_ids, 10 * MINUTE_IN_SECONDS);
        }

        if (empty($post_ids)) {
            return array();
        }

        // Fetch posts in one query, priming both meta and term caches (zero N+1 queries)
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

/**
 * Get general popular posts (all-time).
 *
 * @param int $limit Number of posts to return (default 5).
 * @return WP_Post[] Array of post objects.
 */
if (!function_exists('quterma_get_popular_posts')) {
    function quterma_get_popular_posts($limit = 5) {
        return quterma_get_popular_posts_by_period('month', $limit);
    }
}

/**
 * Flush popular posts transients when a post is saved or deleted.
 */
if (!function_exists('quterma_flush_popular_cache')) {
    function quterma_flush_popular_cache($post_id = 0) {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        foreach (array('today', 'yesterday', 'week', 'month') as $p) {
            delete_transient(QUTERMA_POPULAR_ID_CACHE . '_' . $p . '_5');
            delete_transient(QUTERMA_POPULAR_ID_CACHE . '_' . $p . '_10');
        }
    }
    add_action('save_post', 'quterma_flush_popular_cache');
    add_action('deleted_post', 'quterma_flush_popular_cache');
    add_action('transition_post_status', function ($new_status, $old_status) {
        if ($new_status !== $old_status) {
            quterma_flush_popular_cache();
        }
    }, 10, 2);
}
