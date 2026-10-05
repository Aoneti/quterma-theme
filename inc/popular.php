<?php
/**
 * Popular posts tracking and query logic ("Читают сейчас")
 *
 * @package Quterma
 */

if (!defined('ABSPATH')) {
    exit;
}

const QUTERMA_VIEWS_META_KEY = '_quterma_post_views';
const QUTERMA_POPULAR_TRANSIENT = 'quterma_popular_posts_cache';

/**
 * Track post view count cleanly.
 * Excludes bots, admin previews, AJAX, REST API, and duplicate visits within 1 hour.
 */
function quterma_track_post_views() {
    if (!is_singular('post')) {
        return;
    }

    if (is_admin() || is_preview() || wp_doing_ajax() || wp_doing_cron()) {
        return;
    }

    // Ignore bot user agents
    if (isset($_SERVER['HTTP_USER_AGENT'])) {
        $user_agent = strtolower($_SERVER['HTTP_USER_AGENT']);
        $bot_signatures = array('bot', 'crawl', 'slurp', 'spider', 'mediapartners', 'yandex', 'google', 'bing', 'yahoo');
        foreach ($bot_signatures as $bot) {
            if (strpos($user_agent, $bot) !== false) {
                return;
            }
        }
    }

    $post_id = get_the_ID();
    if (!$post_id) {
        return;
    }

    // Anti-inflation: prevent incrementing on rapid page reloads by same user
    $cookie_name = 'quterma_viewed_' . $post_id;
    if (isset($_COOKIE[$cookie_name])) {
        return;
    }

    // Set cookie for 1 hour to prevent view spam
    if (!headers_sent()) {
        setcookie($cookie_name, '1', time() + 3600, COOKIEPATH ? COOKIEPATH : '/', COOKIE_DOMAIN);
    }

    // Increment views meta
    $views = (int) get_post_meta($post_id, QUTERMA_VIEWS_META_KEY, true);
    $views++;
    update_post_meta($post_id, QUTERMA_VIEWS_META_KEY, $views);
}
add_action('wp', 'quterma_track_post_views');

/**
 * Get popular posts with progressive date expansion.
 *
 * Logic:
 * 1. Look for posts in the last 3 days sorted by views.
 * 2. If count < $limit, expand window to 7 days, 14 days, then 30 days.
 * 3. Results are cached in a transient to minimize database load.
 *
 * @param int $limit Number of posts to return (default 5).
 * @return WP_Post[] Array of post objects.
 */
function quterma_get_popular_posts($limit = 5) {
    $cache_key = QUTERMA_POPULAR_TRANSIENT . '_' . $limit;
    $popular_posts = get_transient($cache_key);

    if (false !== $popular_posts && is_array($popular_posts)) {
        return $popular_posts;
    }

    $windows = array('3 days ago', '7 days ago', '14 days ago', '30 days ago', '1 year ago');
    $results = array();

    foreach ($windows as $window) {
        $args = array(
            'post_type'           => 'post',
            'post_status'         => 'publish',
            'posts_per_page'      => $limit,
            'ignore_sticky_posts' => true,
            'meta_key'            => QUTERMA_VIEWS_META_KEY,
            'orderby'             => 'meta_value_num date',
            'order'               => 'DESC',
            'date_query'          => array(
                array(
                    'after'     => $window,
                    'inclusive' => true,
                ),
            ),
            'no_found_rows'       => true,
            'update_post_term_cache' => false,
        );

        $query = new WP_Query($args);
        if ($query->have_posts() && count($query->posts) >= $limit) {
            $results = $query->posts;
            break;
        } elseif ($query->have_posts()) {
            // Keep the best partial set found
            $results = $query->posts;
        }
    }

    // Fallback if no posts have meta yet (new installation): get latest posts
    if (empty($results) || count($results) < $limit) {
        $needed = $limit - count($results);
        $exclude_ids = wp_list_pluck($results, 'ID');
        $fallback_args = array(
            'post_type'           => 'post',
            'post_status'         => 'publish',
            'posts_per_page'      => $needed,
            'post__not_in'        => $exclude_ids,
            'ignore_sticky_posts' => true,
            'orderby'             => 'date',
            'order'               => 'DESC',
            'no_found_rows'       => true,
        );
        $fallback_query = new WP_Query($fallback_args);
        if ($fallback_query->have_posts()) {
            $results = array_merge($results, $fallback_query->posts);
        }
    }

    // Cache results for 15 minutes (900 seconds)
    set_transient($cache_key, $results, 15 * MINUTE_IN_SECONDS);

    return $results;
}

/**
 * Get popular posts by specific time period.
 *
 * @param string $period 'today' | 'yesterday' | 'week' | 'month'
 * @param int $limit Number of posts to return (default 5).
 * @return WP_Post[] Array of post objects.
 */
function quterma_get_popular_posts_by_period($period = 'today', $limit = 5) {
    $cache_key = 'quterma_pop_' . sanitize_key($period) . '_' . (int) $limit;
    $results   = get_transient($cache_key);

    if (false !== $results && is_array($results)) {
        return $results;
    }

    $date_query = array();
    switch ($period) {
        case 'yesterday':
            $date_query = array(
                array(
                    'after'     => '4 days ago',
                    'inclusive' => true,
                ),
            );
            break;
        case 'week':
            $date_query = array(
                array(
                    'after'     => '7 days ago',
                    'inclusive' => true,
                ),
            );
            break;
        case 'month':
            $date_query = array(
                array(
                    'after'     => '30 days ago',
                    'inclusive' => true,
                ),
            );
            break;
        case 'today':
        default:
            // "Сегодня" includes articles from the last 3 days (today and yesterday)
            // ranked by popularity and view count
            $date_query = array(
                array(
                    'after'     => '3 days ago',
                    'inclusive' => true,
                ),
            );
            break;
    }

    // 1. Try to find posts with view counts in the selected period (sorted by views first)
    $args = array(
        'post_type'              => 'post',
        'post_status'            => 'publish',
        'posts_per_page'         => $limit,
        'ignore_sticky_posts'    => true,
        'meta_key'               => QUTERMA_VIEWS_META_KEY,
        'orderby'                => 'meta_value_num date',
        'order'                  => 'DESC',
        'date_query'             => $date_query,
        'no_found_rows'          => true,
        'update_post_term_cache' => false,
    );
    $query = new WP_Query($args);
    $results = $query->posts;

    // 2. If view count query didn't return enough posts within window, expand window by views
    if (empty($results) || count($results) < $limit) {
        $needed = $limit - count($results);
        $exclude_ids = wp_list_pluck($results, 'ID');
        $wider_args = array(
            'post_type'              => 'post',
            'post_status'            => 'publish',
            'posts_per_page'         => $needed,
            'post__not_in'           => $exclude_ids,
            'ignore_sticky_posts'    => true,
            'meta_key'               => QUTERMA_VIEWS_META_KEY,
            'orderby'                => 'meta_value_num date',
            'order'                  => 'DESC',
            'date_query'             => array(
                array(
                    'after'     => '30 days ago',
                    'inclusive' => true,
                ),
            ),
            'no_found_rows'          => true,
            'update_post_term_cache' => false,
        );
        $wider_query = new WP_Query($wider_args);
        if ($wider_query->have_posts()) {
            $results = array_merge($results, $wider_query->posts);
        }
    }

    // 3. Fallback: fill remaining slots with recent published posts
    if (empty($results) || count($results) < $limit) {
        $needed = $limit - count($results);
        $exclude_ids = wp_list_pluck($results, 'ID');
        $fallback_args = array(
            'post_type'              => 'post',
            'post_status'            => 'publish',
            'posts_per_page'         => $needed,
            'post__not_in'           => $exclude_ids,
            'ignore_sticky_posts'    => true,
            'orderby'                => 'date',
            'order'                  => 'DESC',
            'no_found_rows'          => true,
            'update_post_term_cache' => false,
        );
        $fallback_query = new WP_Query($fallback_args);
        if ($fallback_query->have_posts()) {
            $results = array_merge($results, $fallback_query->posts);
        }
    }

    // Cache results for 10 minutes
    set_transient($cache_key, $results, 10 * MINUTE_IN_SECONDS);

    return $results;
}

/**
 * Flush popular posts transients when a post is saved or deleted.
 */
function quterma_flush_popular_cache($post_id) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    delete_transient(QUTERMA_POPULAR_TRANSIENT . '_5');
    delete_transient(QUTERMA_POPULAR_TRANSIENT . '_10');
    foreach (array('today', 'yesterday', 'week', 'month') as $p) {
        delete_transient('quterma_pop_' . $p . '_5');
        delete_transient('quterma_pop_' . $p . '_10');
    }
}
add_action('save_post', 'quterma_flush_popular_cache');
add_action('deleted_post', 'quterma_flush_popular_cache');
