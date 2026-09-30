<?php
/**
 * Query optimizations, helper query functions, and category-driven blocks
 *
 * @package Quterma
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get hero carousel posts.
 * Category: 'Карусель' (slug 'karusel' or 'carousel').
 * Fallback: latest sticky or published posts if category is empty.
 *
 * @param int $limit Number of slides (default 6).
 * @return WP_Query
 */
function quterma_get_carousel_posts($limit = 6) {
    $transient_key = 'quterma_query_carousel_' . $limit;
    $post_ids = get_transient($transient_key);

    if (false === $post_ids) {
        // Look for category 'karusel' or 'carousel'
        $args = array(
            'post_type'           => 'post',
            'post_status'         => 'publish',
            'posts_per_page'      => $limit,
            'category_name'       => 'karusel,carousel,карусель',
            'orderby'             => 'date',
            'order'               => 'DESC',
            'no_found_rows'       => true,
            'fields'              => 'ids',
        );

        $query = new WP_Query($args);

        // Fallback: If no posts in carousel category, get latest featured or published posts
        if (!$query->have_posts()) {
            $fallback_args = array(
                'post_type'           => 'post',
                'post_status'         => 'publish',
                'posts_per_page'      => $limit,
                'orderby'             => 'date',
                'order'               => 'DESC',
                'no_found_rows'       => true,
                'fields'              => 'ids',
            );
            $query = new WP_Query($fallback_args);
        }

        $post_ids = $query->posts;
        set_transient($transient_key, $post_ids, 15 * MINUTE_IN_SECONDS);
    }

    if (empty($post_ids)) {
        return new WP_Query();
    }

    return new WP_Query(array(
        'post_type'      => 'post',
        'post__in'       => $post_ids,
        'orderby'        => 'post__in',
        'posts_per_page' => count($post_ids),
        'no_found_rows'  => true,
    ));
}

/**
 * Get "Лента сегодня" posts.
 * Filters by category 'Лента' (or all published posts).
 *
 * @param int $limit Number of posts (default 8).
 * @param array $exclude_ids Array of IDs to exclude (e.g. from carousel).
 * @return WP_Query
 */
function quterma_get_feed_today_posts($limit = 8, $exclude_ids = array()) {
    $args = array(
        'post_type'           => 'post',
        'post_status'         => 'publish',
        'posts_per_page'      => $limit,
        'post__not_in'        => $exclude_ids,
        'orderby'             => 'date',
        'order'               => 'DESC',
        'no_found_rows'       => false,
    );

    // If 'lenta' category exists, query it or allow fallback to all
    $term = get_term_by('slug', 'lenta', 'category');
    if (!$term) {
        $term = get_term_by('slug', 'лента', 'category');
    }
    if ($term) {
        $args['cat'] = $term->term_id;
    }

    return new WP_Query($args);
}

/**
 * Get "Культурный слой" posts.
 * Category: 'Культурный слой' ('kulturnyj-sloj', 'culture-layer').
 *
 * @param int $limit Number of posts (default 3).
 * @param array $exclude_ids Array of IDs to exclude.
 * @return WP_Query
 */
function quterma_get_culture_layer_posts($limit = 3, $exclude_ids = array()) {
    $args = array(
        'post_type'           => 'post',
        'post_status'         => 'publish',
        'posts_per_page'      => $limit,
        'post__not_in'        => $exclude_ids,
        'category_name'       => 'kulturnyj-sloj,culture-layer,kultura,kulturnyj-sloi',
        'orderby'             => 'date',
        'order'               => 'DESC',
        'no_found_rows'       => true,
    );

    $query = new WP_Query($args);

    // Fallback: search by category 'Культура' if 'Культурный слой' is empty
    if (!$query->have_posts()) {
        $args['category_name'] = 'kultura,culture';
        $query = new WP_Query($args);
    }

    return $query;
}

/**
 * Get "Лонгриды и спецпроекты" posts.
 * Categories: 'Лонгриды' ('longridy', 'longreads') and 'Спецпроекты' ('spetsproekty', 'specials').
 *
 * @param int $limit Number of posts (default 4).
 * @param array $exclude_ids Array of IDs to exclude.
 * @return WP_Query
 */
function quterma_get_specials_posts($limit = 4, $exclude_ids = array()) {
    $args = array(
        'post_type'           => 'post',
        'post_status'         => 'publish',
        'posts_per_page'      => $limit,
        'post__not_in'        => $exclude_ids,
        'category_name'       => 'longridy,spetsproekty,longreads,specials',
        'orderby'             => 'date',
        'order'               => 'DESC',
        'no_found_rows'       => true,
    );

    $query = new WP_Query($args);

    // Fallback: If no posts with those specific categories, fetch oldest/longest or latest posts
    if (!$query->have_posts()) {
        unset($args['category_name']);
        $query = new WP_Query($args);
    }

    return $query;
}

/**
 * Get "Кино и музыка" posts.
 * Categories: 'Кино и музыка' ('kino-i-muzyka', 'cinema-music', 'kino', 'muzyka').
 *
 * @param int $limit Number of posts (default 4).
 * @param array $exclude_ids Array of IDs to exclude.
 * @return WP_Query
 */
function quterma_get_cinema_music_posts($limit = 4, $exclude_ids = array()) {
    $args = array(
        'post_type'           => 'post',
        'post_status'         => 'publish',
        'posts_per_page'      => $limit,
        'post__not_in'        => $exclude_ids,
        'category_name'       => 'kino-i-muzyka,cinema-music,kino,muzyka,iskusstvo,art',
        'orderby'             => 'date',
        'order'               => 'DESC',
        'no_found_rows'       => true,
    );

    $query = new WP_Query($args);

    if (!$query->have_posts()) {
        unset($args['category_name']);
        $query = new WP_Query($args);
    }

    return $query;
}

/**
 * Get "Интервью" posts.
 * Category: 'Интервью' ('interview', 'interviews', 'intervyu').
 *
 * @param int $limit Number of posts (default 4).
 * @param array $exclude_ids Array of IDs to exclude.
 * @return WP_Query
 */
function quterma_get_interview_posts($limit = 4, $exclude_ids = array()) {
    $args = array(
        'post_type'           => 'post',
        'post_status'         => 'publish',
        'posts_per_page'      => $limit,
        'post__not_in'        => $exclude_ids,
        'category_name'       => 'interview,interviews,intervyu',
        'orderby'             => 'date',
        'order'               => 'DESC',
        'no_found_rows'       => true,
    );

    $query = new WP_Query($args);

    if (!$query->have_posts()) {
        unset($args['category_name']);
        $query = new WP_Query($args);
    }

    return $query;
}
 *
 * @param string|int $category_slug Category slug or ID.
 * @param int $limit Posts limit.
 * @param array $exclude_ids IDs to exclude.
 * @return WP_Query
 */
function quterma_get_category_posts($category_slug, $limit = 6, $exclude_ids = array()) {
    $args = array(
        'post_type'           => 'post',
        'post_status'         => 'publish',
        'posts_per_page'      => $limit,
        'post__not_in'        => $exclude_ids,
        'orderby'             => 'date',
        'order'               => 'DESC',
        'no_found_rows'       => true,
    );

    if (is_numeric($category_slug)) {
        $args['cat'] = (int) $category_slug;
    } else {
        $args['category_name'] = sanitize_title($category_slug);
    }

    return new WP_Query($args);
}

/**
 * Optimize main query via pre_get_posts.
 */
function quterma_pre_get_posts($query) {
    if (is_admin() || !$query->is_main_query()) {
        return;
    }

    // Search query optimizations
    if ($query->is_search()) {
        $query->set('post_type', array('post', 'quterma_venue'));
        $query->set('posts_per_page', 10);
    }

    // Category / Tag archives
    if ($query->is_category() || $query->is_tag() || $query->is_archive()) {
        $query->set('posts_per_page', 12);
    }
}
add_action('pre_get_posts', 'quterma_pre_get_posts');

/**
 * Invalidate query transients on post updates.
 */
function quterma_flush_queries_cache() {
    delete_transient('quterma_query_carousel_6');
    delete_transient('quterma_query_carousel_8');
}
add_action('save_post', 'quterma_flush_queries_cache');
add_action('deleted_post', 'quterma_flush_queries_cache');
