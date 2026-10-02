<?php
/**
 * Enqueue scripts, styles, and fonts
 *
 * @package Quterma
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Enqueue scripts and styles.
 */
function quterma_scripts() {
    $theme_dir = get_template_directory();
    $theme_uri = get_template_directory_uri();
    $version   = defined('QUTERMA_VERSION') ? QUTERMA_VERSION : '1.0.0';

    // 1. Fonts loading:
    // Check if local fonts exist in assets/fonts/
    $has_local_fonts = file_exists($theme_dir . '/assets/fonts/unbounded-v12-cyrillic_cyrillic-ext_latin_latin-ext-regular.woff2')
                    || file_exists($theme_dir . '/assets/fonts/manrope-v20-cyrillic_cyrillic-ext_latin_latin-ext-regular.woff2')
                    || file_exists($theme_dir . '/assets/fonts/unbounded.woff2');

    if ($has_local_fonts) {
        wp_enqueue_style(
            'quterma-local-fonts',
            $theme_uri . '/assets/css/local-fonts.css',
            array(),
            $version
        );
    } else {
        // High-performance CDN fallback with preconnect
        wp_enqueue_style(
            'quterma-font-unbounded',
            'https://cdn.jsdelivr.net/npm/@fontsource/unbounded@5.1.1/index.min.css',
            array(),
            '5.1.1'
        );
        wp_enqueue_style(
            'quterma-font-unbounded-600',
            'https://cdn.jsdelivr.net/npm/@fontsource/unbounded@5.1.1/600.min.css',
            array(),
            '5.1.1'
        );
        wp_enqueue_style(
            'quterma-font-unbounded-700',
            'https://cdn.jsdelivr.net/npm/@fontsource/unbounded@5.1.1/700.min.css',
            array(),
            '5.1.1'
        );
        wp_enqueue_style(
            'quterma-font-manrope',
            'https://cdn.jsdelivr.net/npm/@fontsource/manrope@5.1.1/index.min.css',
            array(),
            '5.1.1'
        );
        wp_enqueue_style(
            'quterma-font-manrope-600',
            'https://cdn.jsdelivr.net/npm/@fontsource/manrope@5.1.1/600.min.css',
            array(),
            '5.1.1'
        );
        wp_enqueue_style(
            'quterma-font-manrope-700',
            'https://cdn.jsdelivr.net/npm/@fontsource/manrope@5.1.1/700.min.css',
            array(),
            '5.1.1'
        );
        wp_enqueue_style(
            'quterma-font-pt-serif',
            'https://cdn.jsdelivr.net/npm/@fontsource/pt-serif@5.0.8/index.min.css',
            array(),
            '5.0.8'
        );
        wp_enqueue_style(
            'quterma-font-pt-serif-700',
            'https://cdn.jsdelivr.net/npm/@fontsource/pt-serif@5.0.8/700.min.css',
            array(),
            '5.0.8'
        );
    }

    // 2. Main parent theme stylesheet
    $css_file    = $theme_dir . '/style.css';
    $css_version = file_exists($css_file) ? filemtime($css_file) : $version;
    wp_enqueue_style(
        'quterma-style',
        $theme_uri . '/style.css',
        array(),
        $css_version
    );

    // If child theme is active, enqueue its stylesheet with parent as dependency
    if (is_child_theme()) {
        wp_enqueue_style(
            'quterma-child-style',
            get_stylesheet_uri(),
            array('quterma-style'),
            $version
        );
    }

    // 3. Main theme JavaScript
    $js_file    = $theme_dir . '/assets/js/theme.js';
    $js_version = file_exists($js_file) ? filemtime($js_file) : $version;
    wp_enqueue_script(
        'quterma-script',
        $theme_uri . '/assets/js/theme.js',
        array(),
        $js_version,
        true // in footer
    );

    // 3.1 Design System scripts (dynamic rubrics sorting, filter sliders, etc.)
    $ds_file = $theme_dir . '/design-system.js';
    if (file_exists($ds_file)) {
        wp_enqueue_script(
            'quterma-design-system',
            $theme_uri . '/design-system.js',
            array('quterma-script'),
            filemtime($ds_file),
            true
        );
    }
}
add_action('wp_enqueue_scripts', 'quterma_scripts');

/**
 * Preload critical hero LCP image on single posts.
 * Hooked directly to wp_head with priority 2 to ensure it executes reliably.
 */
function quterma_preload_hero_image() {
    if (is_singular('post') && has_post_thumbnail()) {
        $post_id   = get_the_ID();
        $thumb_id  = get_post_thumbnail_id($post_id);
        $img_src   = wp_get_attachment_image_url($thumb_id, 'quterma-hero');
        $img_srcset = wp_get_attachment_image_srcset($thumb_id, 'quterma-hero');
        $img_sizes = wp_get_attachment_image_sizes($thumb_id, 'quterma-hero');

        if ($img_src) {
            echo '<link rel="preload" as="image" href="' . esc_url($img_src) . '"';
            if ($img_srcset) {
                echo ' imagesrcset="' . esc_attr($img_srcset) . '"';
            }
            if ($img_sizes) {
                echo ' imagesizes="' . esc_attr($img_sizes) . '"';
            }
            echo ' fetchpriority="high">' . "\n";
        }
    }
}
add_action('wp_head', 'quterma_preload_hero_image', 2);

/**
 * Add preconnect for Google Fonts / CDN
 */
function quterma_resource_hints($urls, $relation_type) {
    if ('preconnect' === $relation_type) {
        $urls[] = array(
            'href' => 'https://cdn.jsdelivr.net',
            'crossorigin' => 'anonymous',
        );
    }
    return $urls;
}
add_filter('wp_resource_hints', 'quterma_resource_hints', 10, 2);

/**
 * Add 'js' class to html element immediately to prevent FOUC / flash of animations
 */
function quterma_html_js_class() {
    echo "<script>document.documentElement.classList.add('js')</script>\n";
}
add_action('wp_head', 'quterma_html_js_class', 0);
