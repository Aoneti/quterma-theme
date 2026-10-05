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
 * Normalize string for bulletproof Russian and Latin matching:
 * lowercase UTF-8, replace 'ё' with 'е', remove spaces, hyphens and punctuation.
 *
 * @param string $str
 * @return string
 */
function quterma_normalize_term_str($str) {
    if (empty($str)) {
        return '';
    }
    $str = urldecode((string) $str);
    if (function_exists('mb_strtolower')) {
        $str = mb_strtolower($str, 'UTF-8');
    } else {
        $str = strtolower($str);
    }
    // Standardize Russian letters
    $str = str_replace('ё', 'е', $str);
    // Strip everything except letters and digits
    $str = preg_replace('/[^a-z0-9а-я]/u', '', $str);
    return trim($str);
}

/**
 * Find matching category term IDs by keywords (supports Russian names, slugs, and Cyrillic slugs)
 *
 * @param array|string $keywords
 * @return array Array of term IDs
 */
function quterma_get_category_ids_by_terms($keywords) {
    if (!is_array($keywords)) {
        $keywords = explode(',', $keywords);
    }

    $all_cats = get_categories(array('hide_empty' => false));
    if (empty($all_cats) || is_wp_error($all_cats)) {
        $all_cats = get_terms(array(
            'taxonomy'   => 'category',
            'hide_empty' => false,
        ));
    }
    if (empty($all_cats) || is_wp_error($all_cats)) {
        return array();
    }

    $matched_ids = array();
    $norm_kws = array();
    foreach ($keywords as $kw) {
        $n = quterma_normalize_term_str($kw);
        if (!empty($n)) {
            $norm_kws[] = $n;
        }
    }

    foreach ($all_cats as $cat) {
        $n_name = quterma_normalize_term_str($cat->name);
        $n_slug = quterma_normalize_term_str($cat->slug);

        foreach ($norm_kws as $n_kw) {
            if ($n_name === $n_kw ||
                $n_slug === $n_kw ||
                (!empty($n_name) && strpos($n_name, $n_kw) !== false) ||
                (!empty($n_slug) && strpos($n_slug, $n_kw) !== false) ||
                (!empty($n_name) && strpos($n_kw, $n_name) !== false)) {
                $matched_ids[] = (int) $cat->term_id;
                break;
            }
        }
    }
    return array_unique($matched_ids);
}

/**
 * Find matching tag term IDs by keywords (supports Russian names, slugs, and Cyrillic slugs)
 *
 * @param array|string $keywords
 * @return array Array of tag IDs
 */
function quterma_get_tag_ids_by_terms($keywords) {
    if (!is_array($keywords)) {
        $keywords = explode(',', $keywords);
    }
    $all_tags = get_terms(array(
        'taxonomy'   => 'post_tag',
        'hide_empty' => false,
    ));
    if (empty($all_tags) || is_wp_error($all_tags)) {
        return array();
    }

    $matched_ids = array();
    $norm_kws = array();
    foreach ($keywords as $kw) {
        $n = quterma_normalize_term_str($kw);
        if (!empty($n)) {
            $norm_kws[] = $n;
        }
    }

    foreach ($all_tags as $tag) {
        $n_name = quterma_normalize_term_str($tag->name);
        $n_slug = quterma_normalize_term_str($tag->slug);

        foreach ($norm_kws as $n_kw) {
            if ($n_name === $n_kw ||
                $n_slug === $n_kw ||
                (!empty($n_name) && strpos($n_name, $n_kw) !== false) ||
                (!empty($n_slug) && strpos($n_slug, $n_kw) !== false) ||
                (!empty($n_name) && strpos($n_kw, $n_name) !== false)) {
                $matched_ids[] = (int) $tag->term_id;
                break;
            }
        }
    }
    return array_unique($matched_ids);
}

/**
 * Helper to build WP_Query arguments matching categories or tags
 */
function quterma_build_term_query_args($keywords, $limit, $exclude_ids = array()) {
    $cat_ids = quterma_get_category_ids_by_terms($keywords);
    $tag_ids = quterma_get_tag_ids_by_terms($keywords);

    $args = array(
        'post_type'           => 'post',
        'post_status'         => 'publish',
        'posts_per_page'      => $limit,
        'orderby'             => 'date',
        'order'               => 'DESC',
        'ignore_sticky_posts' => true,
        'no_found_rows'       => true,
    );

    $tax_query = array('relation' => 'OR');
    if (!empty($cat_ids)) {
        $tax_query[] = array(
            'taxonomy'         => 'category',
            'field'            => 'term_id',
            'terms'            => $cat_ids,
            'include_children' => true,
        );
    }
    if (!empty($tag_ids)) {
        $tax_query[] = array(
            'taxonomy' => 'post_tag',
            'field'    => 'term_id',
            'terms'    => $tag_ids,
        );
    }

    if (count($tax_query) > 1) {
        $args['tax_query'] = $tax_query;
    }

    if (!empty($exclude_ids)) {
        $args['post__not_in'] = $exclude_ids;
    }

    return $args;
}

/**
 * Get hero carousel posts.
 * Category: 'Карусель' (slug 'karusel' or 'carousel').
 * Fallback: latest published posts if carousel category is empty.
 *
 * @param int $limit Number of slides (default 6).
 * @return WP_Query
 */
function quterma_get_carousel_posts($limit = 6) {
    // 1. Look for explicit carousel category/tag
    $keywords = array('карусель', 'karusel', 'carousel', 'слайдер', 'slider');
    $args = quterma_build_term_query_args($keywords, $limit);

    if (!empty($args['tax_query'])) {
        $query = new WP_Query($args);
        if ($query->have_posts()) {
            return $query;
        }
    }

    // 2. Fallback: latest published posts
    $fallback_args = array(
        'post_type'           => 'post',
        'post_status'         => 'publish',
        'posts_per_page'      => $limit,
        'orderby'             => 'date',
        'order'               => 'DESC',
        'ignore_sticky_posts' => true,
        'no_found_rows'       => true,
    );
    return new WP_Query($fallback_args);
}

/**
 * Get feed posts for homepage block "Все новости" / "Лента".
 * Returns latest published posts across all categories without category filtering.
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
        'orderby'             => 'date',
        'order'               => 'DESC',
        'ignore_sticky_posts' => true,
        'no_found_rows'       => false,
    );

    if (!empty($exclude_ids)) {
        $args['post__not_in'] = (array) $exclude_ids;
    }

    $query = new WP_Query($args);

    // If exclusions emptied the feed, query without exclusions
    if (!$query->have_posts() && !empty($exclude_ids)) {
        unset($args['post__not_in']);
        $query = new WP_Query($args);
    }

    return $query;
}

/**
 * Get "Культурный слой" posts.
 * Categories/Tags: 'Культурный слой', 'Культура' ('kulturnyj-sloj', 'kultura', 'culture').
 *
 * @param int $limit Number of posts (default 3).
 * @param array $exclude_ids Array of IDs to exclude.
 * @return WP_Query
 */
function quterma_get_culture_layer_posts($limit = 3, $exclude_ids = array()) {
    $keywords = array(
        'культурный слой', 'культурный-слой', 'культура', 'culture', 'culture-layer',
        'kulturnyj-sloj', 'kulturnyy-sloy', 'kulturny-sloy', 'kultura', 'культурный'
    );

    $args = quterma_build_term_query_args($keywords, $limit, $exclude_ids);

    // If categories/tags were identified
    if (!empty($args['tax_query'])) {
        $query = new WP_Query($args);
        // Guarantee section appears: if exclusions left it empty, show without exclusions!
        if (!$query->have_posts() && !empty($exclude_ids)) {
            unset($args['post__not_in']);
            $query = new WP_Query($args);
        }
        return $query;
    }

    // Direct fallback by common category names if term cache hasn't updated
    $fallback_args = array(
        'post_type'           => 'post',
        'post_status'         => 'publish',
        'posts_per_page'      => $limit,
        'category_name'       => 'kulturnyj-sloj,culture-layer,kultura,culture',
        'ignore_sticky_posts' => true,
        'no_found_rows'       => true,
    );
    return new WP_Query($fallback_args);
}

/**
 * Get "Лонгриды и спецпроекты" posts.
 * Categories/Tags: 'Лонгриды', 'Спецпроекты', 'Лонгриды и спецпроекты'.
 *
 * @param int $limit Number of posts (default 3).
 * @param array $exclude_ids Array of IDs to exclude.
 * @return WP_Query
 */
function quterma_get_specials_posts($limit = 3, $exclude_ids = array()) {
    $keywords = array(
        'лонгриды и спецпроекты', 'спецпроекты и лонгриды', 'лонгриды', 'спецпроекты',
        'лонгрид', 'спецпроект', 'longreads', 'specials', 'special', 'longread',
        'longridy', 'spetsproekty', 'specproekty', 'longridy-i-spetsproekty'
    );

    $args = quterma_build_term_query_args($keywords, $limit, $exclude_ids);

    if (!empty($args['tax_query'])) {
        $query = new WP_Query($args);
        if (!$query->have_posts() && !empty($exclude_ids)) {
            unset($args['post__not_in']);
            $query = new WP_Query($args);
        }
        return $query;
    }

    $fallback_args = array(
        'post_type'           => 'post',
        'post_status'         => 'publish',
        'posts_per_page'      => $limit,
        'category_name'       => 'longridy,spetsproekty,longreads,specials',
        'ignore_sticky_posts' => true,
        'no_found_rows'       => true,
    );
    return new WP_Query($fallback_args);
}

/**
 * Get "Кино и музыка" posts.
 * Categories/Tags: 'Кино и музыка', 'Кино', 'Музыка'.
 *
 * @param int $limit Number of posts (default 4).
 * @param array $exclude_ids Array of IDs to exclude.
 * @return WP_Query
 */
function quterma_get_cinema_music_posts($limit = 4, $exclude_ids = array()) {
    $keywords = array(
        'кино и музыка', 'кино-и-музыка', 'кино', 'музыка', 'cinema-music',
        'kino-i-muzyka', 'kino', 'muzyka', 'cinema', 'music'
    );

    $args = quterma_build_term_query_args($keywords, $limit, $exclude_ids);

    if (!empty($args['tax_query'])) {
        $query = new WP_Query($args);
        if (!$query->have_posts() && !empty($exclude_ids)) {
            unset($args['post__not_in']);
            $query = new WP_Query($args);
        }
        return $query;
    }

    $fallback_args = array(
        'post_type'           => 'post',
        'post_status'         => 'publish',
        'posts_per_page'      => $limit,
        'category_name'       => 'kino-i-muzyka,cinema-music,kino,muzyka',
        'ignore_sticky_posts' => true,
        'no_found_rows'       => true,
    );
    return new WP_Query($fallback_args);
}

/**
 * Get "Интервью" posts.
 * Category/Tag: 'Интервью' ('interview', 'interviews', 'intervyu', 'интервью', 'диалог').
 *
 * @param int $limit Number of posts (default 3).
 * @param array $exclude_ids Array of IDs to exclude.
 * @return WP_Query
 */
function quterma_get_interview_posts($limit = 3, $exclude_ids = array()) {
    $keywords = array(
        'интервью', 'interview', 'interviews', 'intervyu', 'диалог', 'беседа'
    );

    $args = quterma_build_term_query_args($keywords, $limit, $exclude_ids);

    if (!empty($args['tax_query'])) {
        $query = new WP_Query($args);
        if (!$query->have_posts() && !empty($exclude_ids)) {
            unset($args['post__not_in']);
            $query = new WP_Query($args);
        }
        return $query;
    }

    // Also check for meta _iv_person if category isn't set yet
    $meta_args = array(
        'post_type'           => 'post',
        'post_status'         => 'publish',
        'posts_per_page'      => $limit,
        'meta_key'            => '_iv_person',
        'meta_compare'        => 'EXISTS',
        'ignore_sticky_posts' => true,
        'no_found_rows'       => true,
    );
    if (!empty($exclude_ids)) {
        $meta_args['post__not_in'] = $exclude_ids;
    }
    $query = new WP_Query($meta_args);
    if ($query->have_posts()) {
        return $query;
    }

    $fallback_args = array(
        'post_type'           => 'post',
        'post_status'         => 'publish',
        'posts_per_page'      => $limit,
        'category_name'       => 'interview,interviews,intervyu',
        'ignore_sticky_posts' => true,
        'no_found_rows'       => true,
    );
    return new WP_Query($fallback_args);
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
