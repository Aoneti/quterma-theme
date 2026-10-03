<?php
/**
 * Helper functions: Date formatting, author initials, SVG icons, and Nav Walker
 *
 * @package Quterma
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Russian plural forms helper.
 *
 * @param int $n
 * @param array $forms Array of 3 forms: ['день', 'дня', 'дней']
 * @return string
 */
function quterma_plural($n, $forms) {
    $n = abs((int) $n);
    $mod10  = $n % 10;
    $mod100 = $n % 100;
    if ($mod100 >= 11 && $mod100 <= 19) {
        return $forms[2];
    }
    if ($mod10 === 1) {
        return $forms[0];
    }
    if ($mod10 >= 2 && $mod10 <= 4) {
        return $forms[1];
    }
    return $forms[2];
}

/**
 * Format post date in Russian human-readable format.
 * Examples: "Сегодня, 14:20", "Вчера", "3 дня назад", "14 июля 2026"
 *
 * @param int|WP_Post|null $post
 * @param bool $with_time
 * @return string
 */
function quterma_format_date($post = null, $with_time = false) {
    $post_dt = get_post_datetime($post);
    if (!$post_dt) {
        return '';
    }
    $now_dt = current_datetime();
    $diff_seconds = $now_dt->getTimestamp() - $post_dt->getTimestamp();
    $diff_days = (int) floor($diff_seconds / DAY_IN_SECONDS);

    $time_str = wp_date('H:i', $post_dt->getTimestamp(), wp_timezone());

    // Same calendar day
    if ($post_dt->format('Y-m-d') === $now_dt->format('Y-m-d')) {
        return $with_time ? sprintf(__('Сегодня, %s', 'quterma'), $time_str) : __('Сегодня', 'quterma');
    }

    // Yesterday
    $yesterday_dt = $now_dt->modify('-1 day');
    if ($post_dt->format('Y-m-d') === $yesterday_dt->format('Y-m-d')) {
        return $with_time ? sprintf(__('Вчера, %s', 'quterma'), $time_str) : __('Вчера', 'quterma');
    }

    // 2-6 days ago (with correct Russian plurals: 2 дня назад, 5 дней назад)
    if ($diff_days >= 2 && $diff_days <= 6) {
        $day_word = quterma_plural($diff_days, array('день', 'дня', 'дней'));
        return sprintf(__('%d %s назад', 'quterma'), $diff_days, $day_word);
    }

    // Default formatted Russian date
    if ($now_dt->format('Y') === $post_dt->format('Y')) {
        return wp_date('j F', $post_dt->getTimestamp(), wp_timezone());
    }

    return wp_date('j F Y', $post_dt->getTimestamp(), wp_timezone());
}

/**
 * Output <time datetime="..."> tag with formatted Russian date
 */
function quterma_time_tag($post = null, $with_time = false, $class = '') {
    $post_dt = get_post_datetime($post);
    if (!$post_dt) {
        return '';
    }
    $iso = $post_dt->format('c');
    $label = quterma_format_date($post, $with_time);
    $class_attr = $class ? ' class="' . esc_attr($class) . '"' : '';
    return '<time datetime="' . esc_attr($iso) . '"' . $class_attr . '>' . esc_html($label) . '</time>';
}

/**
 * Canonical registry of Yaroslavl region cities for gastroguide and events
 *
 * @return array
 */
if (!function_exists('quterma_get_cities')) {
    /**
     * Canonical registry of Yaroslavl region cities for gastroguide and events
     *
     * @return array
     */
    function quterma_get_cities() {
        return array(
            'yaroslavl'    => __('Ярославль', 'quterma'),
            'rybinsk'      => __('Рыбинск', 'quterma'),
            'rostov'       => __('Ростов Великий', 'quterma'),
            'pereslavl'    => __('Переславль-Залесский', 'quterma'),
            'tutaev'       => __('Тутаев', 'quterma'),
            'uglich'       => __('Углич', 'quterma'),
            'gavrilov-yam' => __('Гаврилов-Ям', 'quterma'),
            'danilov'      => __('Данилов', 'quterma'),
            'lyubim'       => __('Любим', 'quterma'),
            'myshkin'      => __('Мышкин', 'quterma'),
            'poshekhonye'  => __('Пошехонье', 'quterma'),
            'breytovo'     => __('Брейтово', 'quterma'),
        );
    }
}

if (!function_exists('quterma_get_city_name')) {
    /**
     * Get human-readable Russian city name by slug
     *
     * @param string $slug
     * @return string
     */
    function quterma_get_city_name($slug) {
        $cities = quterma_get_cities();
        return isset($cities[$slug]) ? $cities[$slug] : __('Ярославль', 'quterma');
    }
}

/**
 * Get primary editorial category for a post.
 * Excludes technical and placement service categories like 'carousel', 'lenta', etc.
 * Supports Yoast SEO and Rank Math primary category if configured.
 *
 * @param int|WP_Post|null $post
 * @return WP_Term|null
 */
function quterma_get_primary_category($post = null) {
    $post = get_post($post);
    if (!$post) {
        return null;
    }

    // 1. Check Yoast SEO primary category
    $yoast_primary_id = get_post_meta($post->ID, '_yoast_wpseo_primary_category', true);
    if ($yoast_primary_id) {
        $term = get_term($yoast_primary_id, 'category');
        if ($term && !is_wp_error($term)) {
            return $term;
        }
    }

    // 2. Check Rank Math primary term
    $rm_primary_id = get_post_meta($post->ID, 'rank_math_primary_category', true);
    if ($rm_primary_id) {
        $term = get_term($rm_primary_id, 'category');
        if ($term && !is_wp_error($term)) {
            return $term;
        }
    }

    $cats = get_the_category($post->ID);
    if (empty($cats)) {
        return null;
    }

    // Exclude placement/service categories
    $service_slugs = array('carousel', 'karusel', 'lenta', 'feed', 'featured', 'specials', 'popular');
    $editorial_cats = array();
    foreach ($cats as $cat) {
        if (!in_array(strtolower($cat->slug), $service_slugs, true)) {
            $editorial_cats[] = $cat;
        }
    }

    if (empty($editorial_cats)) {
        return $cats[0];
    }

    // Sort by taxonomy depth (deepest child category first)
    usort($editorial_cats, function ($a, $b) {
        $depth_a = count(get_ancestors($a->term_id, 'category'));
        $depth_b = count(get_ancestors($b->term_id, 'category'));
        if ($depth_a !== $depth_b) {
            return $depth_b - $depth_a; // deepest first
        }
        return 0;
    });

    return $editorial_cats[0];
}

/**
 * Get cached URL for a page by slug without running raw SQL queries on every hit.
 *
 * @param string $slug Page path / slug.
 * @param string|null $fallback Custom fallback URL if page does not exist.
 * @return string
 */
function quterma_get_page_url($slug, $fallback = null) {
    $clean_slug = sanitize_title($slug);
    if ($fallback === null) {
        $fallback = home_url('/' . $clean_slug . '/');
    }

    $cache_key = 'quterma_purl_' . $clean_slug;
    $cached_url = get_transient($cache_key);
    if (false !== $cached_url && !empty($cached_url)) {
        return $cached_url;
    }

    // 1. Check if a WordPress Page exists with this slug
    $page = get_page_by_path($slug);
    if ($page) {
        $url = get_permalink($page);
        set_transient($cache_key, $url, DAY_IN_SECONDS);
        return $url;
    }

    // 2. Check if a WordPress Category exists with this slug (e.g. culture, people, history, news)
    $cat = get_category_by_slug($slug);
    if ($cat) {
        $url = get_category_link($cat);
        set_transient($cache_key, $url, DAY_IN_SECONDS);
        return $url;
    }

    return $fallback;
}

// Bust page URL transients when a page or category is created, updated or deleted
add_action('save_post_page', function ($post_id, $post) {
    if ($post && !empty($post->post_name)) {
        delete_transient('quterma_purl_' . sanitize_title($post->post_name));
    }
}, 10, 2);
add_action('saved_term', function ($term_id, $tt_id, $taxonomy) {
    if ($taxonomy === 'category') {
        $term = get_term($term_id, 'category');
        if ($term && !is_wp_error($term)) {
            delete_transient('quterma_purl_' . sanitize_title($term->slug));
        }
    }
}, 10, 3);

/**
 * Get dynamic URL for posts archive (Все новости / Лента)
 * Checks Category 'news', page_for_posts, Page 'news', or fallback to /category/news/
 */
function quterma_get_news_url() {
    $cat = get_category_by_slug('news');
    if ($cat) {
        return get_category_link($cat);
    }
    $page_for_posts = get_option('page_for_posts');
    if ($page_for_posts) {
        $p = get_post($page_for_posts);
        if ($p && $p->post_status === 'publish') {
            return get_permalink($page_for_posts);
        }
    }
    $page = get_page_by_path('news');
    if ($page && $page->post_status === 'publish') {
        return get_permalink($page);
    }
    return home_url('/category/news/');
}

/**
 * Get canonical URL for a WordPress Category
 */
function quterma_get_category_url($slug, $fallback = '') {
    $cat = get_category_by_slug($slug);
    if ($cat) {
        return get_category_link($cat);
    }
    return !empty($fallback) ? $fallback : home_url('/category/' . sanitize_title($slug) . '/');
}

/**
 * Get author initials for avatar circle.
 * Example: "Алексей Смирнов" -> "АС"
 *
 * @param int|WP_User|null $user
 * @return string
 */
function quterma_get_author_initials($user = null) {
    if (!$user) {
        $user_id = get_the_author_meta('ID');
    } elseif (is_numeric($user)) {
        $user_id = $user;
    } else {
        $user_id = $user->ID;
    }

    $first_name = get_the_author_meta('first_name', $user_id);
    $last_name  = get_the_author_meta('last_name', $user_id);

    if (!empty($first_name) && !empty($last_name)) {
        return mb_substr($first_name, 0, 1) . mb_substr($last_name, 0, 1);
    }

    $display_name = get_the_author_meta('display_name', $user_id);
    if (!empty($display_name)) {
        $parts = explode(' ', trim($display_name));
        if (count($parts) >= 2) {
            return mb_substr($parts[0], 0, 1) . mb_substr($parts[1], 0, 1);
        }
        return mb_substr($display_name, 0, 2);
    }

    return 'К';
}

/**
 * Get category CSS token and label for card badges.
 *
 * @param int|WP_Post|null $post
 * @return array array('slug' => 'city', 'name' => 'Город')
 */
function quterma_get_post_category_info($post = null) {
    $cat = quterma_get_primary_category($post);
    if (!$cat) {
        return array('slug' => 'city', 'name' => __('Город', 'quterma'), 'link' => home_url('/category/city/'));
    }

    $slug = $cat->slug;

    // Map Russian and English category slugs to canonical English slugs
    $slug_map = array(
        'gorod'            => 'city',
        'city'             => 'city',
        'город'            => 'city',
        'kultura'          => 'culture',
        'culture'          => 'culture',
        'культура'         => 'culture',
        'iskusstvo'        => 'art',
        'art'              => 'art',
        'искусство'        => 'art',
        'lyudi'            => 'people',
        'people'           => 'people',
        'люди'             => 'people',
        'istoriya'         => 'history',
        'history'          => 'history',
        'история'          => 'history',
        'interview'        => 'interview',
        'интервью'         => 'interview',
        'kino-i-muzyka'    => 'cinema-music',
        'cinema-music'     => 'cinema-music',
        'blagoustrojstvo'  => 'improvement',
        'благоустройство'  => 'improvement',
        'nasledie'         => 'heritage',
        'наследие'         => 'heritage',
        'obshhestvo'       => 'society',
        'общество'         => 'society',
        'ekologiya'        => 'ecology',
        'экология'         => 'ecology',
        'sport'            => 'sport',
        'спорт'            => 'sport',
    );

    $normalized_slug = isset($slug_map[$slug]) ? $slug_map[$slug] : $slug;

    return array(
        'slug' => $normalized_slug,
        'name' => $cat->name,
        'link' => get_category_link($cat),
    );
}

/**
 * Render SVG placeholder image matching prototype
 *
 * @param int $width
 * @param int $height
 * @return string
 */
function quterma_placeholder_img($width = 38, $height = 38) {
    return '<div class="ph-img"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round" width="' . (int) $width . '" height="' . (int) $height . '"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="9" cy="9" r="1.7"/><path d="M21 15l-4.5-4.5a1.5 1.5 0 0 0-2.12 0L4 21"/></svg></div>';
}

/**
 * Custom Nav Walker for Desktop header navigation (.nav-pill)
 */
class Quterma_Nav_Walker extends Walker_Nav_Menu {
    public function start_lvl(&$output, $depth = 0, $args = null) {}
    public function end_lvl(&$output, $depth = 0, $args = null) {}

    public function start_el(&$output, $item, $depth = 0, $args = null, $id = 0) {
        $classes = empty($item->classes) ? array() : (array) $item->classes;
        $is_active = in_array('current-menu-item', $classes) || in_array('current_page_item', $classes);

        $class_names = 'nav-pill';
        if ($is_active) {
            $class_names .= ' active';
        }

        $attributes  = !empty($item->url) ? ' href="' . esc_url($item->url) . '"' : '';
        $attributes .= ' class="' . esc_attr($class_names) . '"';
        if ($is_active) {
            $attributes .= ' aria-current="page"';
        }

        $output .= '<a' . $attributes . '>';
        $output .= apply_filters('the_title', $item->title, $item->ID);
        $output .= '</a>';
    }

    public function end_el(&$output, $item, $depth = 0, $args = null) {
        // No closing li tag needed
    }
}

/**
 * Custom Nav Walker for Mobile navigation (.mob-nav-item)
 */
class Quterma_Mobile_Nav_Walker extends Walker_Nav_Menu {
    public function start_lvl(&$output, $depth = 0, $args = null) {}
    public function end_lvl(&$output, $depth = 0, $args = null) {}

    public function start_el(&$output, $item, $depth = 0, $args = null, $id = 0) {
        $classes = empty($item->classes) ? array() : (array) $item->classes;
        $is_active = in_array('current-menu-item', $classes) || in_array('current_page_item', $classes);

        $class_names = 'mob-nav-item';
        if ($is_active) {
            $class_names .= ' active';
        }

        $attributes  = !empty($item->url) ? ' href="' . esc_url($item->url) . '"' : '';
        $attributes .= ' class="' . esc_attr($class_names) . '"';
        if ($is_active) {
            $attributes .= ' aria-current="page"';
        }

        $output .= '<a' . $attributes . '>';
        $output .= apply_filters('the_title', $item->title, $item->ID);
        $output .= '</a>';
    }

    public function end_el(&$output, $item, $depth = 0, $args = null) {
        // No closing li tag needed
    }
}

/**
 * Custom Nav Walker for Footer navigation links (.foot-link)
 */
class Quterma_Footer_Nav_Walker extends Walker_Nav_Menu {
    public function start_lvl(&$output, $depth = 0, $args = null) {}
    public function end_lvl(&$output, $depth = 0, $args = null) {}

    public function start_el(&$output, $item, $depth = 0, $args = null, $id = 0) {
        $attributes  = !empty($item->url) ? ' href="' . esc_url($item->url) . '"' : '';
        $attributes .= ' class="foot-link"';
        $output .= '<a' . $attributes . '>';
        $output .= apply_filters('the_title', $item->title, $item->ID);
        $output .= '</a>';
    }

    public function end_el(&$output, $item, $depth = 0, $args = null) {}
}

/**
 * Default desktop navigation fallback matching prototype
 */
function quterma_default_desktop_nav() {
    $links = array(
        array('title' => __('Главная', 'quterma'), 'url' => home_url('/')),
        array('title' => __('Все новости', 'quterma'), 'url' => quterma_get_news_url()),
        array('title' => __('Город', 'quterma'), 'url' => quterma_get_category_url('city', home_url('/category/city/'))),
        array('title' => __('Культура', 'quterma'), 'url' => quterma_get_category_url('culture', home_url('/category/culture/'))),
        array('title' => __('Искусство', 'quterma'), 'url' => quterma_get_category_url('art', home_url('/category/art/'))),
        array('title' => __('Люди', 'quterma'), 'url' => quterma_get_category_url('people', home_url('/category/people/'))),
        array('title' => __('История', 'quterma'), 'url' => quterma_get_category_url('history', home_url('/category/history/'))),
        array('title' => __('Гастрогид', 'quterma'), 'url' => quterma_get_page_url('gastroguide', home_url('/gastroguide/'))),
        array('title' => __('События', 'quterma'), 'url' => quterma_get_page_url('events', home_url('/events/'))),
    );

    $current_url = home_url(add_query_arg(array(), $GLOBALS['wp']->request));

    foreach ($links as $link) {
        $is_active = (trailingslashit($current_url) === trailingslashit($link['url']));
        if (is_front_page() && $link['title'] === __('Главная', 'quterma')) {
            $is_active = true;
        }
        $class = 'nav-pill' . ($is_active ? ' active' : '');
        $current_attr = $is_active ? ' aria-current="page"' : '';
        echo '<a href="' . esc_url($link['url']) . '" class="' . esc_attr($class) . '"' . $current_attr . '>' . esc_html($link['title']) . '</a>';
    }
}

/**
 * Default mobile navigation fallback matching prototype
 */
function quterma_default_mobile_nav() {
    $links = array(
        array('title' => __('Главная', 'quterma'), 'url' => home_url('/')),
        array('title' => __('Все новости', 'quterma'), 'url' => quterma_get_news_url()),
        array('title' => __('Город', 'quterma'), 'url' => quterma_get_category_url('city', home_url('/category/city/'))),
        array('title' => __('Культура', 'quterma'), 'url' => quterma_get_category_url('culture', home_url('/category/culture/'))),
        array('title' => __('Искусство', 'quterma'), 'url' => quterma_get_category_url('art', home_url('/category/art/'))),
        array('title' => __('Люди', 'quterma'), 'url' => quterma_get_category_url('people', home_url('/category/people/'))),
        array('title' => __('История', 'quterma'), 'url' => quterma_get_category_url('history', home_url('/category/history/'))),
        array('title' => __('Гастрогид', 'quterma'), 'url' => quterma_get_page_url('gastroguide', home_url('/gastroguide/'))),
        array('title' => __('События', 'quterma'), 'url' => quterma_get_page_url('events', home_url('/events/'))),
    );

    $current_url = home_url(add_query_arg(array(), $GLOBALS['wp']->request));

    foreach ($links as $link) {
        $is_active = (trailingslashit($current_url) === trailingslashit($link['url']));
        if (is_front_page() && $link['title'] === __('Главная', 'quterma')) {
            $is_active = true;
        }
        $class = 'mob-nav-item' . ($is_active ? ' active' : '');
        $current_attr = $is_active ? ' aria-current="page"' : '';
        echo '<a href="' . esc_url($link['url']) . '" class="' . esc_attr($class) . '"' . $current_attr . '>' . esc_html($link['title']) . '</a>';
    }
}
