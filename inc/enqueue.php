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
        $local_fonts_file = $theme_dir . '/assets/css/local-fonts.css';
        $fonts_version    = file_exists($local_fonts_file) ? (string) filemtime($local_fonts_file) : $version;
        wp_enqueue_style(
            'quterma-local-fonts',
            $theme_uri . '/assets/css/local-fonts.css',
            array(),
            $fonts_version
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
        $child_css_file = get_stylesheet_directory() . '/style.css';
        $child_version  = file_exists($child_css_file) ? (string) filemtime($child_css_file) : $version;
        wp_enqueue_style(
            'quterma-child-style',
            get_stylesheet_uri(),
            array('quterma-style'),
            $child_version
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

    // 3.1 Localize script for REST API, dynamic pagination, load more, and asynchronous view tracking
    $feed_per_page = 6;
    wp_localize_script(
        'quterma-script',
        'qutermaSettings',
        array(
            'restUrl'      => esc_url_raw(rest_url()),
            'postsRestUrl' => esc_url_raw(rest_url('wp/v2/posts')),
            'trackViewUrl' => esc_url_raw(rest_url('quterma/v1/track-view')),
            'postId'       => is_singular('post') ? get_the_ID() : 0,
            'perPage'      => $feed_per_page,
            'nonce'        => wp_create_nonce('wp_rest'),
        )
    );
}
add_action('wp_enqueue_scripts', 'quterma_scripts');

/**
 * Preload critical hero LCP image on single posts.
 * Hooked directly to wp_head with priority 2 to ensure it executes reliably.
 */
function quterma_preload_hero_image() {
    if (is_singular('post') && has_post_thumbnail()) {
        $post_id    = get_the_ID();
        $thumb_id   = get_post_thumbnail_id($post_id);
        $img_src    = wp_get_attachment_image_url($thumb_id, 'quterma-hero');
        $img_srcset = wp_get_attachment_image_srcset($thumb_id, 'quterma-hero');
        $img_sizes  = function_exists('quterma_get_hero_image_sizes') ? quterma_get_hero_image_sizes() : '(max-width: 768px) 100vw, 860px';

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
 * Add preconnect for Google Fonts / CDN only when fallback is actually active
 */
function quterma_resource_hints($urls, $relation_type) {
    if ('preconnect' === $relation_type) {
        $theme_dir = get_template_directory();
        $has_local_fonts = file_exists($theme_dir . '/assets/fonts/unbounded-v12-cyrillic_cyrillic-ext_latin_latin-ext-regular.woff2')
                        || file_exists($theme_dir . '/assets/fonts/manrope-v20-cyrillic_cyrillic-ext_latin_latin-ext-regular.woff2')
                        || file_exists($theme_dir . '/assets/fonts/unbounded.woff2');

        if (!$has_local_fonts) {
            $urls[] = array(
                'href' => 'https://cdn.jsdelivr.net',
                'crossorigin' => 'anonymous',
            );
        }
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

/**
 * Preload critical WOFF2 local fonts to eliminate FOUT (Flash of Unstyled Text).
 * High-priority link rel=preload starts font download simultaneously with HTML parsing,
 * ensuring custom web fonts are cached and ready before first paint.
 * Restricted to the 2 critical fonts for the initial screen: Manrope Regular and Unbounded Bold.
 */
function quterma_preload_critical_fonts() {
    $theme_dir = get_template_directory();
    $theme_uri = get_template_directory_uri();

    $critical_fonts = array(
        '/assets/fonts/manrope-v20-cyrillic_cyrillic-ext_latin_latin-ext-regular.woff2',
        '/assets/fonts/unbounded-v12-cyrillic_cyrillic-ext_latin_latin-ext-700.woff2',
    );

    foreach ($critical_fonts as $font_rel) {
        if (file_exists($theme_dir . $font_rel)) {
            echo '<link rel="preload" href="' . esc_url($theme_uri . $font_rel) . '" as="font" type="font/woff2" crossorigin="anonymous">' . "\n";
        }
    }
}
add_action('wp_head', 'quterma_preload_critical_fonts', 1);
