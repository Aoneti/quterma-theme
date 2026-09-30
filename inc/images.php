<?php
/**
 * Image handling, custom thumbnail sizes, WebP support, and LCP optimizations
 *
 * @package Quterma
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register custom image sizes.
 */
function quterma_image_sizes() {
    // 1. Single article hero & carousel slide (16:9)
    add_image_size('quterma-hero', 1200, 675, true);

    // 2. Featured card in feed (21:10)
    add_image_size('quterma-featured', 900, 428, true);

    // 3. Regular news card in feed (3:2)
    add_image_size('quterma-card', 480, 320, true);

    // 4. Feature card (Culture .cc-card, Venue card, Event card) (4:3)
    add_image_size('quterma-card-4x3', 480, 360, true);

    // 5. Tile grid large (Culture / History / People)
    add_image_size('quterma-tile-large', 800, 600, true);

    // 6. Tile grid standard
    add_image_size('quterma-tile', 400, 300, true);

    // 7. Author avatar
    add_image_size('quterma-avatar', 84, 84, true);
}
add_action('after_setup_theme', 'quterma_image_sizes');

/**
 * Register custom sizes in editor image size selector.
 */
function quterma_custom_image_sizes_names($sizes) {
    return array_merge($sizes, array(
        'quterma-hero'     => __('Широкий баннер (16:9)', 'quterma'),
        'quterma-featured' => __('Главная новость (21:10)', 'quterma'),
        'quterma-card'     => __('Карточка новости (3:2)', 'quterma'),
        'quterma-card-4x3' => __('Карточка 4:3 (Культура/Заведение)', 'quterma'),
    ));
}
add_filter('image_size_names_choose', 'quterma_custom_image_sizes_names');

/**
 * Optimize image attributes for performance (LCP vs Lazy Load).
 * - Disable lazy loading on the hero image of single posts
 * - Ensure other images receive loading="lazy" and decoding="async"
 */
function quterma_post_thumbnail_html($html, $post_id, $post_thumbnail_id, $size, $attr) {
    // If it's a single post and rendering the hero image, set loading eager and fetchpriority high
    if (is_singular('post') && $size === 'quterma-hero') {
        $html = str_replace('loading="lazy"', 'loading="eager"', $html);
        if (strpos($html, 'fetchpriority') === false) {
            $html = str_replace('<img ', '<img fetchpriority="high" ', $html);
        }
    } else {
        if (strpos($html, 'decoding=') === false) {
            $html = str_replace('<img ', '<img decoding="async" ', $html);
        }
    }
    return $html;
}
add_filter('post_thumbnail_html', 'quterma_post_thumbnail_html', 10, 5);

/**
 * Filter responsive image sizes attribute for better browser asset selection.
 */
function quterma_calculate_image_sizes($sizes, $size, $image_src, $image_meta, $attachment_id) {
    if ($size === 'quterma-hero') {
        return '(max-width: 768px) 100vw, (max-width: 1240px) 960px, 1200px';
    }
    if ($size === 'quterma-featured') {
        return '(max-width: 768px) 100vw, (max-width: 1024px) 720px, 860px';
    }
    if ($size === 'quterma-card') {
        return '(max-width: 480px) 100vw, (max-width: 768px) 150px, 240px';
    }
    if ($size === 'quterma-card-4x3') {
        return '(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 380px';
    }
    return $sizes;
}
add_filter('wp_calculate_image_sizes', 'quterma_calculate_image_sizes', 10, 5);

/**
 * Support WebP and AVIF generation if supported by server (GD / Imagick)
 */
function quterma_image_editor_output_format($formats) {
    $formats['image/jpeg'] = 'image/webp';
    $formats['image/png']  = 'image/webp';
    return $formats;
}
// Only enable WebP auto-conversion if GD or Imagick supports webp
if (function_exists('imagick_supports_format') || function_exists('imagewebp')) {
    add_filter('image_editor_output_format', 'quterma_image_editor_output_format');
}

/**
 * Allow SVG upload for admin/editors for editorial brand icons
 */
function quterma_mime_types($mimes) {
    $mimes['svg']  = 'image/svg+xml';
    $mimes['svgz'] = 'image/svg+xml';
    $mimes['webp'] = 'image/webp';
    return $mimes;
}
add_filter('upload_mimes', 'quterma_mime_types');
