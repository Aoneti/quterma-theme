<?php
/**
 * Helper functions: Date formatting, reading time, author initials, SVG icons, and Nav Walker
 *
 * @package Quterma
 */

if (!defined('ABSPATH')) {
    exit;
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
    $post_time = get_post_time('U', true, $post);
    $now       = current_time('timestamp');
    $diff_days = (int) floor(($now - $post_time) / DAY_IN_SECONDS);

    $time_str = date_i18n('H:i', $post_time);

    // Same calendar day
    if (date_i18n('Y-m-d', $post_time) === date_i18n('Y-m-d', $now)) {
        return $with_time ? sprintf(__('Сегодня, %s', 'quterma'), $time_str) : __('Сегодня', 'quterma');
    }

    // Yesterday
    $yesterday = strtotime('-1 day', $now);
    if (date_i18n('Y-m-d', $post_time) === date_i18n('Y-m-d', $yesterday)) {
        return $with_time ? sprintf(__('Вчера, %s', 'quterma'), $time_str) : __('Вчера', 'quterma');
    }

    // 2-6 days ago
    if ($diff_days >= 2 && $diff_days <= 6) {
        return sprintf(_n('%d день назад', '%d дней назад', $diff_days, 'quterma'), $diff_days);
    }

    // Default formatted Russian date
    $current_year = date_i18n('Y', $now);
    $post_year    = date_i18n('Y', $post_time);

    if ($current_year === $post_year) {
        return date_i18n('j F', $post_time);
    }

    return date_i18n('j F Y', $post_time);
}

/**
 * Calculate estimated reading time in Russian.
 * Example: "6 мин. чтения"
 *
 * @param int|WP_Post|null $post
 * @return string
 */
function quterma_get_reading_time($post = null) {
    $post = get_post($post);
    if (!$post) {
        return '3 мин. чтения';
    }

    $words = str_word_count(wp_strip_all_tags($post->post_content));
    $minutes = max(1, (int) ceil($words / 180));

    return sprintf(__('%d мин. чтения', 'quterma'), $minutes);
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
    $cats = get_the_category($post);
    if (empty($cats)) {
        return array('slug' => 'city', 'name' => __('Город', 'quterma'));
    }

    $cat = $cats[0];
    $slug = $cat->slug;

    // Map Russian and English category slugs to filter slugs
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
    public function start_el(&$output, $item, $depth = 0, $args = null, $id = 0) {
        $classes = empty($item->classes) ? array() : (array) $item->classes;
        $is_active = in_array('current-menu-item', $classes) || in_array('current_page_item', $classes);

        $class_names = 'nav-pill';
        if ($is_active) {
            $class_names .= ' active';
        }

        $attributes  = !empty($item->url) ? ' href="' . esc_url($item->url) . '"' : '';
        $attributes .= ' class="' . esc_attr($class_names) . '"';

        $output .= '<a' . $attributes . '>';
        $output .= apply_filters('the_title', $item->title, $item->ID);
        $output .= '</a>';
    }

    public function end_el(&$output, $item, $depth = 0, $args = null) {
        // No closing li tag needed because markup is direct <a> tags in <nav class="nav-list">
    }
}

/**
 * Custom Nav Walker for Mobile navigation (.mob-nav-item)
 */
class Quterma_Mobile_Nav_Walker extends Walker_Nav_Menu {
    public function start_el(&$output, $item, $depth = 0, $args = null, $id = 0) {
        $classes = empty($item->classes) ? array() : (array) $item->classes;
        $is_active = in_array('current-menu-item', $classes) || in_array('current_page_item', $classes);

        $class_names = 'mob-nav-item';
        if ($is_active) {
            $class_names .= ' active';
        }

        $attributes  = !empty($item->url) ? ' href="' . esc_url($item->url) . '"' : '';
        $attributes .= ' class="' . esc_attr($class_names) . '"';

        $output .= '<a' . $attributes . '>';
        $output .= apply_filters('the_title', $item->title, $item->ID);
        $output .= '</a>';
    }

    public function end_el(&$output, $item, $depth = 0, $args = null) {
        // No closing li tag needed
    }
}

/**
 * Default desktop navigation fallback matching prototype
 */
function quterma_default_desktop_nav() {
    $links = array(
        array('title' => __('Главная', 'quterma'), 'url' => home_url('/')),
        array('title' => __('Все новости', 'quterma'), 'url' => home_url('/feed/')),
        array('title' => __('Город', 'quterma'), 'url' => home_url('/category/city/')),
        array('title' => __('Культура', 'quterma'), 'url' => home_url('/category/culture/')),
        array('title' => __('Искусство', 'quterma'), 'url' => home_url('/category/art/')),
        array('title' => __('Интервью', 'quterma'), 'url' => home_url('/interview/')),
        array('title' => __('Люди', 'quterma'), 'url' => home_url('/category/people/')),
        array('title' => __('История', 'quterma'), 'url' => home_url('/category/history/')),
        array('title' => __('Гастрогид', 'quterma'), 'url' => home_url('/gastroguide/')),
        array('title' => __('События', 'quterma'), 'url' => home_url('/events/')),
    );

    $current_url = home_url(add_query_arg(array(), $GLOBALS['wp']->request));

    foreach ($links as $link) {
        $is_active = (trailingslashit($current_url) === trailingslashit($link['url']));
        if (is_front_page() && $link['title'] === __('Главная', 'quterma')) {
            $is_active = true;
        }
        $class = 'nav-pill' . ($is_active ? ' active' : '');
        echo '<a href="' . esc_url($link['url']) . '" class="' . esc_attr($class) . '">' . esc_html($link['title']) . '</a>';
    }
}

/**
 * Default mobile navigation fallback matching prototype
 */
function quterma_default_mobile_nav() {
    $links = array(
        array('title' => __('Главная', 'quterma'), 'url' => home_url('/')),
        array('title' => __('Все новости', 'quterma'), 'url' => home_url('/feed/')),
        array('title' => __('Город', 'quterma'), 'url' => home_url('/category/city/')),
        array('title' => __('Культура', 'quterma'), 'url' => home_url('/category/culture/')),
        array('title' => __('Искусство', 'quterma'), 'url' => home_url('/category/art/')),
        array('title' => __('Интервью', 'quterma'), 'url' => home_url('/interview/')),
        array('title' => __('Люди', 'quterma'), 'url' => home_url('/category/people/')),
        array('title' => __('История', 'quterma'), 'url' => home_url('/category/history/')),
        array('title' => __('Гастрогид', 'quterma'), 'url' => home_url('/gastroguide/')),
        array('title' => __('События', 'quterma'), 'url' => home_url('/events/')),
    );

    $current_url = home_url(add_query_arg(array(), $GLOBALS['wp']->request));

    foreach ($links as $link) {
        $is_active = (trailingslashit($current_url) === trailingslashit($link['url']));
        if (is_front_page() && $link['title'] === __('Главная', 'quterma')) {
            $is_active = true;
        }
        $class = 'mob-nav-item' . ($is_active ? ' active' : '');
        echo '<a href="' . esc_url($link['url']) . '" class="' . esc_attr($class) . '">' . esc_html($link['title']) . '</a>';
    }
}

