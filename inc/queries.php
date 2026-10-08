<?php
/**
 * Query optimizations, helper query functions, and category-driven blocks
 *
 * Implements:
 * - Deterministic, exact category and tag binding (zero fuzzy substring collisions)
 * - Single-pass in-memory and transient cached taxonomy lookup map (quterma_get_taxonomy_lookup_map)
 * - Optional Customizer bindings (theme_mod) for each homepage section
 * - Protection against cascading empty blocks
 * - Query optimization via pre_get_posts
 *
 * @package Quterma
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Retrieve cached map of categories and tags indexed by exact slug and exact lowercase name.
 * Loaded once per request (or cached in transient), eliminating repetitive full taxonomy queries.
 *
 * @return array{categories: array<string, int>, tags: array<string, int>}
 */
function quterma_get_taxonomy_lookup_map() {
    static $memory_cache = null;
    if ($memory_cache !== null) {
        return $memory_cache;
    }

    $cached = get_transient('quterma_tax_lookup_map');
    if (false !== $cached && is_array($cached)) {
        $memory_cache = $cached;
        return $memory_cache;
    }

    $map = array(
        'categories' => array(), // slug/name => term_id
        'tags'       => array(), // slug/name => term_id
    );

    $cats = get_categories(array('hide_empty' => false));
    if (!empty($cats) && !is_wp_error($cats)) {
        foreach ($cats as $cat) {
            $cat_id = (int) $cat->term_id;
            $slug   = sanitize_title($cat->slug);
            $name   = function_exists('quterma_strtolower') ? quterma_strtolower(trim($cat->name)) : (function_exists('mb_strtolower') ? mb_strtolower(trim($cat->name), 'UTF-8') : strtolower(trim($cat->name)));
            $map['categories'][$slug] = $cat_id;
            $map['categories'][$name] = $cat_id;
        }
    }

    $tags = get_terms(array('taxonomy' => 'post_tag', 'hide_empty' => false));
    if (!empty($tags) && !is_wp_error($tags)) {
        foreach ($tags as $tag) {
            $tag_id = (int) $tag->term_id;
            $slug   = sanitize_title($tag->slug);
            $name   = function_exists('quterma_strtolower') ? quterma_strtolower(trim($tag->name)) : (function_exists('mb_strtolower') ? mb_strtolower(trim($tag->name), 'UTF-8') : strtolower(trim($tag->name)));
            $map['tags'][$slug] = $tag_id;
            $map['tags'][$name] = $tag_id;
        }
    }

    set_transient('quterma_tax_lookup_map', $map, 12 * HOUR_IN_SECONDS);
    $memory_cache = $map;
    return $memory_cache;
}

// Invalidate taxonomy lookup cache on term modifications
add_action('created_term', function () { delete_transient('quterma_tax_lookup_map'); });
add_action('edited_term',  function () { delete_transient('quterma_tax_lookup_map'); });
add_action('delete_term',  function () { delete_transient('quterma_tax_lookup_map'); });

/**
 * Find matching category term IDs by explicit keywords or slugs.
 * Uses strict exact matching (slug or exact normalized name), NO fuzzy substring matching.
 *
 * @param array|string $keywords
 * @return int[] Array of term IDs
 */
function quterma_get_category_ids_by_terms($keywords) {
    if (!is_array($keywords)) {
        $keywords = explode(',', (string) $keywords);
    }

    $map = quterma_get_taxonomy_lookup_map();
    $matched = array();

    foreach ($keywords as $kw) {
        $kw = trim($kw);
        if ($kw === '') {
            continue;
        }

        // Direct numeric ID support
        if (is_numeric($kw)) {
            $term_id = (int) $kw;
            if (term_exists($term_id, 'category')) {
                $matched[] = $term_id;
                continue;
            }
        }

        $clean_slug = sanitize_title($kw);
        $clean_name = function_exists('quterma_strtolower') ? quterma_strtolower($kw) : (function_exists('mb_strtolower') ? mb_strtolower($kw, 'UTF-8') : strtolower($kw));

        if (isset($map['categories'][$clean_slug])) {
            $matched[] = $map['categories'][$clean_slug];
        } elseif (isset($map['categories'][$clean_name])) {
            $matched[] = $map['categories'][$clean_name];
        }
    }

    return array_values(array_unique(array_filter($matched)));
}

/**
 * Find matching tag term IDs by explicit keywords or slugs.
 * Uses strict exact matching (slug or exact normalized name), NO fuzzy substring matching.
 *
 * @param array|string $keywords
 * @return int[] Array of tag IDs
 */
function quterma_get_tag_ids_by_terms($keywords) {
    if (!is_array($keywords)) {
        $keywords = explode(',', (string) $keywords);
    }

    $map = quterma_get_taxonomy_lookup_map();
    $matched = array();

    foreach ($keywords as $kw) {
        $kw = trim($kw);
        if ($kw === '') {
            continue;
        }

        // Direct numeric ID support
        if (is_numeric($kw)) {
            $term_id = (int) $kw;
            if (term_exists($term_id, 'post_tag')) {
                $matched[] = $term_id;
                continue;
            }
        }

        $clean_slug = sanitize_title($kw);
        $clean_name = function_exists('quterma_strtolower') ? quterma_strtolower($kw) : (function_exists('mb_strtolower') ? mb_strtolower($kw, 'UTF-8') : strtolower($kw));

        if (isset($map['tags'][$clean_slug])) {
            $matched[] = $map['tags'][$clean_slug];
        } elseif (isset($map['tags'][$clean_name])) {
            $matched[] = $map['tags'][$clean_name];
        }
    }

    return array_values(array_unique(array_filter($matched)));
}

/**
 * Helper to build WP_Query arguments matching categories or tags deterministically.
 *
 * @param array|string $keywords
 * @param int $limit
 * @param array $exclude_ids
 * @return array
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
        $args['post__not_in'] = array_map('intval', (array) $exclude_ids);
    }

    return $args;
}

/**
 * Get hero carousel posts.
 * Category: customizable via Customizer or default 'carousel' / 'karusel'.
 * Fallback: latest published posts.
 *
 * @param int $limit Number of slides (default 6).
 * @return WP_Query
 */
function quterma_get_carousel_posts($limit = 6) {
    $custom_cat = function_exists('quterma_get_setting') ? quterma_get_setting('quterma_home_carousel_cat', 'carousel') : get_theme_mod('quterma_home_carousel_cat', 'carousel');
    $keywords   = array_filter(array($custom_cat, 'carousel', 'karusel', 'карусель'));

    $args = quterma_build_term_query_args($keywords, $limit);

    if (!empty($args['tax_query'])) {
        $query = new WP_Query($args);
        if ($query->have_posts()) {
            return $query;
        }
    }

    // Fallback: latest published posts
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
 * Returns latest published posts across all categories.
 *
 * @param int $limit Number of posts (default 8).
 * @param array $exclude_ids Array of IDs to exclude.
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
        $args['post__not_in'] = array_map('intval', (array) $exclude_ids);
    }

    $query = new WP_Query($args);

    if (!$query->have_posts() && !empty($exclude_ids)) {
        unset($args['post__not_in']);
        $query = new WP_Query($args);
    }

    return $query;
}

/**
 * Get "Культурный слой" posts.
 * Deterministic binding to 'culture' / 'kulturnyj-sloj' (or Customizer setting).
 *
 * @param int $limit Number of posts (default 3).
 * @param array $exclude_ids Array of IDs to exclude.
 * @return WP_Query
 */
function quterma_get_culture_layer_posts($limit = 3, $exclude_ids = array()) {
    $custom_cat = function_exists('quterma_get_setting') ? quterma_get_setting('quterma_home_culture_cat', 'culture') : get_theme_mod('quterma_home_culture_cat', 'culture');
    $keywords   = array_filter(array($custom_cat, 'culture', 'kulturnyj-sloj', 'kultura', 'культурный слой'));

    $args = quterma_build_term_query_args($keywords, $limit, $exclude_ids);

    if (!empty($args['tax_query'])) {
        $query = new WP_Query($args);
        if (!$query->have_posts() && !empty($exclude_ids)) {
            unset($args['post__not_in']);
            $query = new WP_Query($args);
        }
        if ($query->have_posts()) {
            return $query;
        }
    }

    // Fallback: direct query by category_name
    $fallback_args = array(
        'post_type'           => 'post',
        'post_status'         => 'publish',
        'posts_per_page'      => $limit,
        'category_name'       => 'culture,kulturnyj-sloj,kultura',
        'ignore_sticky_posts' => true,
        'no_found_rows'       => true,
    );
    return new WP_Query($fallback_args);
}

/**
 * Get "Лонгриды и спецпроекты" posts.
 * Deterministic binding to 'specials' / 'longreads' (or Customizer setting).
 *
 * @param int $limit Number of posts (default 3).
 * @param array $exclude_ids Array of IDs to exclude.
 * @return WP_Query
 */
function quterma_get_specials_posts($limit = 3, $exclude_ids = array()) {
    $custom_cat = function_exists('quterma_get_setting') ? quterma_get_setting('quterma_home_specials_cat', 'specials') : get_theme_mod('quterma_home_specials_cat', 'specials');
    $keywords   = array_filter(array($custom_cat, 'specials', 'longreads', 'spetsproekty', 'longridy', 'лонгриды и спецпроекты'));

    $args = quterma_build_term_query_args($keywords, $limit, $exclude_ids);

    if (!empty($args['tax_query'])) {
        $query = new WP_Query($args);
        if (!$query->have_posts() && !empty($exclude_ids)) {
            unset($args['post__not_in']);
            $query = new WP_Query($args);
        }
        if ($query->have_posts()) {
            return $query;
        }
    }

    $fallback_args = array(
        'post_type'           => 'post',
        'post_status'         => 'publish',
        'posts_per_page'      => $limit,
        'category_name'       => 'specials,longreads,spetsproekty,longridy',
        'ignore_sticky_posts' => true,
        'no_found_rows'       => true,
    );
    return new WP_Query($fallback_args);
}

/**
 * Get "Кино и музыка" posts.
 * Deterministic binding to 'cinema-music' (or Customizer setting).
 *
 * @param int $limit Number of posts (default 4).
 * @param array $exclude_ids Array of IDs to exclude.
 * @return WP_Query
 */
function quterma_get_cinema_music_posts($limit = 4, $exclude_ids = array()) {
    $custom_cat = function_exists('quterma_get_setting') ? quterma_get_setting('quterma_home_cinema_music_cat', 'cinema-music') : get_theme_mod('quterma_home_cinema_music_cat', 'cinema-music');
    $keywords   = array_filter(array($custom_cat, 'cinema-music', 'kino-i-muzyka', 'кино и музыка'));

    $args = quterma_build_term_query_args($keywords, $limit, $exclude_ids);

    if (!empty($args['tax_query'])) {
        $query = new WP_Query($args);
        if (!$query->have_posts() && !empty($exclude_ids)) {
            unset($args['post__not_in']);
            $query = new WP_Query($args);
        }
        if ($query->have_posts()) {
            return $query;
        }
    }

    $fallback_args = array(
        'post_type'           => 'post',
        'post_status'         => 'publish',
        'posts_per_page'      => $limit,
        'category_name'       => 'cinema-music,kino-i-muzyka,kino,muzyka',
        'ignore_sticky_posts' => true,
        'no_found_rows'       => true,
    );
    return new WP_Query($fallback_args);
}

/**
 * Get "Интервью" posts.
 * Deterministic binding to 'interview' (or Customizer setting).
 *
 * @param int $limit Number of posts (default 3).
 * @param array $exclude_ids Array of IDs to exclude.
 * @return WP_Query
 */
function quterma_get_interview_posts($limit = 3, $exclude_ids = array()) {
    $custom_cat = function_exists('quterma_get_setting') ? quterma_get_setting('quterma_home_interview_cat', 'interview') : get_theme_mod('quterma_home_interview_cat', 'interview');
    $keywords   = array_filter(array($custom_cat, 'interview', 'intervyu', 'интервью'));

    $args = quterma_build_term_query_args($keywords, $limit, $exclude_ids);

    if (!empty($args['tax_query'])) {
        $query = new WP_Query($args);
        if (!$query->have_posts() && !empty($exclude_ids)) {
            unset($args['post__not_in']);
            $query = new WP_Query($args);
        }
        if ($query->have_posts()) {
            return $query;
        }
    }

    // Check for _iv_person meta
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
        $meta_args['post__not_in'] = array_map('intval', (array) $exclude_ids);
    }
    $query = new WP_Query($meta_args);
    if ($query->have_posts()) {
        return $query;
    }

    $fallback_args = array(
        'post_type'           => 'post',
        'post_status'         => 'publish',
        'posts_per_page'      => $limit,
        'category_name'       => 'interview,intervyu',
        'ignore_sticky_posts' => true,
        'no_found_rows'       => true,
    );
    return new WP_Query($fallback_args);
}

/**
 * Optimize main query via pre_get_posts.
 * Explicitly separates search, editorial archives, and taxonomy archives.
 */
function quterma_pre_get_posts($query) {
    if (is_admin() || !$query->is_main_query()) {
        return;
    }

    // 1. Search includes articles, static pages (sections), and venues
    if ($query->is_search()) {
        $query->set('post_type', array('post', 'page', 'quterma_venue'));
        $query->set('posts_per_page', 10);
    }

    // 2. Explicit pagination for editorial category and tag archives
    if ($query->is_category() || $query->is_tag()) {
        $query->set('posts_per_page', 12);
    } elseif ($query->is_tax('quterma_city')) {
        $query->set('posts_per_page', 12);
    }
}
add_action('pre_get_posts', 'quterma_pre_get_posts');
