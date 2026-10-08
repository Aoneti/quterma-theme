<?php
/**
 * Helper functions: Date formatting, author initials, SVG icons, and Nav Walker
 *
 * @package Quterma
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('quterma_get_setting')) {
    /**
     * Retrieve theme/plugin setting from Options API with seamless fallback to theme_mod.
     * Ensures settings remain intact when switching themes or using child themes.
     *
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    function quterma_get_setting($name, $default = '') {
        $val = get_option($name, null);
        if ($val !== null && $val !== '') {
            return $val;
        }
        return get_theme_mod($name, $default);
    }
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
    $same_year = ($now_dt->format('Y') === $post_dt->format('Y'));

    if ($with_time) {
        return $same_year
            ? wp_date('j F, H:i', $post_dt->getTimestamp(), wp_timezone())
            : wp_date('j F Y, H:i', $post_dt->getTimestamp(), wp_timezone());
    }

    return $same_year
        ? wp_date('j F', $post_dt->getTimestamp(), wp_timezone())
        : wp_date('j F Y', $post_dt->getTimestamp(), wp_timezone());
}

/**
 * Calculate estimated reading time for longreads and articles
 *
 * @param WP_Post|int|null $post
 * @return string
 */
function quterma_reading_time($post = null) {
    $post_obj = get_post($post);
    if (!$post_obj) {
        return '5 минут чтения';
    }
    $content = strip_tags($post_obj->post_content);
    $word_count = count(preg_split('/\s+/u', trim($content), -1, PREG_SPLIT_NO_EMPTY));
    if ($word_count < 100) {
        $minutes = 3;
    } else {
        $minutes = max(2, (int) round($word_count / 180));
    }
    $word = quterma_plural($minutes, array('минута', 'минуты', 'минут'));
    return sprintf('%d %s чтения', $minutes, $word);
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

if (!function_exists('quterma_get_venue_price_tiers')) {
    /**
     * Canonical registry of venue price tiers (Single source of truth for admin, README, and public templates)
     *
     * @return array
     */
    function quterma_get_venue_price_tiers() {
        return array(
            '₽'   => array(
                'symbol' => '₽',
                'range'  => __('до 700 ₽', 'quterma'),
                'desc'   => __('до 700 ₽ · демократично', 'quterma'),
                'admin'  => __('демократично, до 700 ₽', 'quterma'),
            ),
            '₽₽'  => array(
                'symbol' => '₽₽',
                'range'  => __('700–1500 ₽', 'quterma'),
                'desc'   => __('700–1500 ₽ · средний чек', 'quterma'),
                'admin'  => __('средний чек, 700–1500 ₽', 'quterma'),
            ),
            '₽₽₽' => array(
                'symbol' => '₽₽₽',
                'range'  => __('от 1500 ₽', 'quterma'),
                'desc'   => __('от 1500 ₽ · выше среднего', 'quterma'),
                'admin'  => __('высокий чек, от 1500 ₽', 'quterma'),
            ),
        );
    }
}

if (!function_exists('quterma_get_venue_price_desc')) {
    /**
     * Get venue price description by price symbol
     *
     * @param string $price
     * @return string
     */
    function quterma_get_venue_price_desc($price) {
        $tiers = quterma_get_venue_price_tiers();
        return isset($tiers[$price]) ? $tiers[$price]['desc'] : '';
    }
}

/**
 * Check if a category term is a technical/placement service category (e.g. "Все новости", "Карусель", "Лента")
 *
 * @param WP_Term|int $cat
 * @return bool
 */
function quterma_is_technical_category($cat) {
    if (is_numeric($cat)) {
        $cat = get_term($cat, 'category');
    }
    if (!$cat || is_wp_error($cat)) {
        return true;
    }

    // Technical slugs
    $service_slugs = array(
        'carousel', 'karusel', 'lenta', 'feed', 'featured', 'specials', 'popular',
        'news', 'all-news', 'vse-novosti', 'vsenovosti', 'novosti',
    );
    $slug_clean = strtolower(trim($cat->slug));
    if (in_array($slug_clean, $service_slugs, true)) {
        return true;
    }

    // Decoded cyrillic slugs
    $decoded_slug = strtolower(trim(urldecode($cat->slug)));
    if (in_array($decoded_slug, array('карусель', 'лента', 'новости', 'все-новости', 'всеновости', 'все новости', 'лента-новостей'), true)) {
        return true;
    }

    // Normalized Russian term names
    $name_clean = function_exists('mb_strtolower') ? mb_strtolower(trim($cat->name), 'UTF-8') : strtolower(trim($cat->name));
    $tech_names = array('все новости', 'новости', 'лента', 'карусель', 'спецпроекты', 'популярное', 'все-новости', 'лента новостей');
    if (in_array($name_clean, $tech_names, true)) {
        return true;
    }

    // Alphanumeric normalized check (strips punctuation and spaces)
    $normalized_name = preg_replace('/[^a-z0-9а-я]/ui', '', str_replace('ё', 'е', $name_clean));
    $tech_normalized = array('всеновости', 'новости', 'лента', 'карусель', 'спецпроекты', 'популярное', 'allnews', 'vsenovosti');
    if (in_array($normalized_name, $tech_normalized, true)) {
        return true;
    }

    return false;
}

/**
 * Get primary editorial category for a post.
 * Excludes technical and placement service categories like 'carousel', 'lenta', and 'all-news' ('Все новости').
 * Supports Yoast SEO and Rank Math primary category if configured (ignoring technical categories).
 *
 * @param int|WP_Post|null $post
 * @return WP_Term|null
 */
function quterma_get_primary_category($post = null) {
    $post = get_post($post);
    if (!$post) {
        return null;
    }

    // 1. Check Yoast SEO primary category (must not be technical)
    $yoast_primary_id = get_post_meta($post->ID, '_yoast_wpseo_primary_category', true);
    if ($yoast_primary_id) {
        $term = get_term($yoast_primary_id, 'category');
        if ($term && !is_wp_error($term) && !quterma_is_technical_category($term)) {
            return $term;
        }
    }

    // 2. Check Rank Math primary term (must not be technical)
    $rm_primary_id = get_post_meta($post->ID, 'rank_math_primary_category', true);
    if ($rm_primary_id) {
        $term = get_term($rm_primary_id, 'category');
        if ($term && !is_wp_error($term) && !quterma_is_technical_category($term)) {
            return $term;
        }
    }

    $cats = get_the_category($post->ID);
    if (empty($cats)) {
        return null;
    }

    // Exclude placement/service and technical categories ("Все новости", "Карусель", etc.)
    $editorial_cats = array();
    foreach ($cats as $cat) {
        if (!quterma_is_technical_category($cat)) {
            $editorial_cats[] = $cat;
        }
    }

    // If post has no editorial categories, return null (never force "Все новости")
    if (empty($editorial_cats)) {
        return null;
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
 * Safe multibyte string helper functions with fallback if ext-mbstring is absent
 */
function quterma_strtolower($str) {
    return function_exists('mb_strtolower') ? mb_strtolower($str, 'UTF-8') : strtolower($str);
}

function quterma_strpos($haystack, $needle, $offset = 0) {
    return function_exists('mb_strpos') ? mb_strpos($haystack, $needle, $offset, 'UTF-8') : strpos($haystack, $needle, $offset);
}

function quterma_stripos($haystack, $needle, $offset = 0) {
    return function_exists('mb_stripos') ? mb_stripos($haystack, $needle, $offset, 'UTF-8') : stripos($haystack, $needle, $offset);
}

/**
 * Flush cached page and section URLs
 */
function quterma_flush_page_urls_cache() {
    delete_option('quterma_page_urls');
}
add_action('save_post', 'quterma_flush_page_urls_cache');
add_action('deleted_post', 'quterma_flush_page_urls_cache');
add_action('trashed_post', 'quterma_flush_page_urls_cache');
add_action('untrashed_post', 'quterma_flush_page_urls_cache');
add_action('post_updated', 'quterma_flush_page_urls_cache');
add_action('saved_term', 'quterma_flush_page_urls_cache');
add_action('delete_term', 'quterma_flush_page_urls_cache');

/**
 * Get cached URL for a page or section by slug or template.
 * Uses in-memory runtime memoization and a single autoloaded WP option ('quterma_page_urls').
 * Negative lookups (fallbacks) are stored in the autoload array to eliminate repeating queries.
 *
 * @param string $slug Page path / slug / template identifier.
 * @param string|null $fallback Custom fallback URL if page does not exist.
 * @return string
 */
function quterma_get_page_url($slug, $fallback = null) {
    static $memo = array();

    $clean_slug = sanitize_title($slug);
    if (empty($clean_slug)) {
        return home_url('/');
    }

    $default_fallback = ($fallback !== null) ? $fallback : home_url('/' . $clean_slug . '/');

    // 1. Check runtime memory memoization
    if (isset($memo[$clean_slug])) {
        return $memo[$clean_slug];
    }

    // 2. Check autoloaded option array (1 read for all site sections, autoloaded with WP)
    $cached_urls = get_option('quterma_page_urls');
    if (!is_array($cached_urls)) {
        $cached_urls = array();
    }

    if (isset($cached_urls[$clean_slug])) {
        $memo[$clean_slug] = $cached_urls[$clean_slug];
        return $cached_urls[$clean_slug];
    }

    // 3. Resolve URL dynamically without hard dependency on slug
    $resolved_url = '';

    // 3a. Template-first lookup: if page template matches, find page regardless of editor-changed slug
    $template_file = 'page-' . $clean_slug . '.php';
    $pages_by_template = get_posts(array(
        'post_type'      => 'page',
        'post_status'    => 'publish',
        'posts_per_page' => 1,
        'meta_key'       => '_wp_page_template',
        'meta_value'     => $template_file,
        'no_found_rows'  => true,
    ));
    if (!empty($pages_by_template)) {
        $resolved_url = get_permalink($pages_by_template[0]);
    }

    // 3b. Slug lookup
    if (empty($resolved_url)) {
        $page = get_page_by_path($clean_slug);
        if ($page && $page->post_status === 'publish') {
            $resolved_url = get_permalink($page);
        }
    }

    // 3c. Category lookup (e.g. culture, people, history, art)
    if (empty($resolved_url)) {
        $cat = get_category_by_slug($clean_slug);
        if ($cat) {
            $resolved_url = get_category_link($cat);
        }
    }

    // 3d. Fallback if not found (Negative result)
    if (empty($resolved_url)) {
        $resolved_url = $default_fallback;
    }

    // 4. Save into autoload array and memoize (caches negative results as well)
    $cached_urls[$clean_slug] = $resolved_url;
    update_option('quterma_page_urls', $cached_urls, true);
    $memo[$clean_slug] = $resolved_url;

    return $resolved_url;
}

/**
 * Get dynamic URL for posts archive (Все новости / Лента)
 * Primary source: standard WordPress Page for Posts (get_option('page_for_posts')).
 * Does NOT use category 'news' — "Все новости" is a posts page aggregator, not a rubric.
 *
 * @return string
 */
function quterma_get_news_url() {
    // 1. Check standard WordPress Page for Posts setting
    $page_for_posts = (int) get_option('page_for_posts');
    if ($page_for_posts > 0) {
        $page_obj = get_post($page_for_posts);
        if ($page_obj && $page_obj->post_status === 'publish') {
            return get_permalink($page_for_posts);
        }
    }

    // 2. Fallback: check if a published Page with slug 'news', 'all-news', or 'vse-novosti' exists
    $candidates = array('news', 'all-news', 'vse-novosti');
    foreach ($candidates as $slug) {
        $page = get_page_by_path($slug);
        if ($page && $page->post_status === 'publish') {
            return get_permalink($page);
        }
    }

    // 3. Fallback: clean URL to /news/ on current site
    return home_url('/news/');
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

    $news_url    = quterma_get_news_url();
    $current_url = home_url(add_query_arg(array(), $GLOBALS['wp']->request));

    foreach ($links as $link) {
        $is_active = (trailingslashit($current_url) === trailingslashit($link['url']));
        if (is_front_page() && $link['title'] === __('Главная', 'quterma')) {
            $is_active = true;
        } elseif (is_home() && $link['url'] === $news_url) {
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

    $news_url    = quterma_get_news_url();
    $current_url = home_url(add_query_arg(array(), $GLOBALS['wp']->request));

    foreach ($links as $link) {
        $is_active = (trailingslashit($current_url) === trailingslashit($link['url']));
        if (is_front_page() && $link['title'] === __('Главная', 'quterma')) {
            $is_active = true;
        } elseif (is_home() && $link['url'] === $news_url) {
            $is_active = true;
        }
        $class = 'mob-nav-item' . ($is_active ? ' active' : '');
        $current_attr = $is_active ? ' aria-current="page"' : '';
        echo '<a href="' . esc_url($link['url']) . '" class="' . esc_attr($class) . '"' . $current_attr . '>' . esc_html($link['title']) . '</a>';
    }
}

/**
 * Safely highlight search terms in text without breaking HTML entities
 * Matches against raw text prior to HTML escaping so queries like "amp" or "nbsp" never corrupt entities.
 *
 * @param string $text
 * @param string $query
 * @return string
 */
function quterma_highlight($text, $query) {
    if (empty($text)) {
        return '';
    }
    if (empty($query)) {
        return esc_html($text);
    }

    $trimmed_query = trim($query);
    $words = array_filter(preg_split('/\s+/u', $trimmed_query));
    if (empty($words)) {
        return esc_html($text);
    }

    $patterns = array_map(function ($w) {
        return preg_quote($w, '/');
    }, $words);
    $pattern = '/(' . implode('|', $patterns) . ')/iu';

    // Split raw text into matching terms and non-matching delimiters
    $parts = preg_split($pattern, $text, -1, PREG_SPLIT_DELIM_CAPTURE);
    if ($parts === false || count($parts) <= 1) {
        if (preg_match($pattern, $text)) {
            return '<mark class="srch-hl">' . esc_html($text) . '</mark>';
        }
        return esc_html($text);
    }

    $result = '';
    foreach ($parts as $part) {
        if ($part === '') {
            continue;
        }
        if (preg_match($pattern, $part)) {
            $result .= '<mark class="srch-hl">' . esc_html($part) . '</mark>';
        } else {
            $result .= esc_html($part);
        }
    }
    return $result;
}

/**
 * Russian Typographer helper to eliminate hanging prepositions and orphan words (WCAG / Editorial rule)
 *
 * @param string $text
 * @return string
 */
function quterma_typograf($text) {
    if (empty($text) || !is_string($text)) {
        return $text;
    }

    // 1. Single and two-letter prepositions and conjunctions bound to following word
    $short_words = 'в|во|и|на|с|со|по|к|ко|о|об|обо|от|ото|из|изо|за|до|у|около|не|ни|но|да|или|как|так|что|где|для|при|без|под|подо|над|надо|про|через';
    $pattern1 = '/(?<=\s|^)(' . $short_words . ')\s+/iu';
    $text = preg_replace($pattern1, '$1&nbsp;', $text);

    // 2. Bound particles to preceding word
    $pattern2 = '/\s+(ли|ль|же|ж|бы|б)([\s\.,!?;:\)»]|$)/iu';
    $text = preg_replace($pattern2, '&nbsp;$1$2', $text);

    // 3. Em-dash with non-breaking space before
    $text = preg_replace('/\s+([—–])\s+/u', '&nbsp;$1 ', $text);

    // 4. Numbers followed by abbreviations / units (e.g. 2026 год, 5 минут, 10 км)
    $text = preg_replace('/(\d+)\s+(год|года|году|годом|годах|г\.|г|мин|минут|минуты|руб|рублей|р\.|км|м|чел)\b/iu', '$1&nbsp;$2', $text);

    return $text;
}
// Note: quterma_typograf() is intentionally NOT hooked globally to the_title or the_excerpt
// to avoid leaking &nbsp; into <title>, alt, aria-label, JSON-LD, and menu walkers.

/**
 * Display typographed title in content templates without polluting global the_title filter.
 *
 * @param int|WP_Post $post
 * @return void
 */
function quterma_the_title_typograf($post = 0) {
    echo quterma_typograf(get_the_title($post));
}

/**
 * Display typographed excerpt in content templates without polluting global the_excerpt filter.
 *
 * @param int|WP_Post $post
 * @return void
 */
function quterma_the_excerpt_typograf($post = 0) {
    echo quterma_typograf(get_the_excerpt($post));
}

