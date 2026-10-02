<?php
/**
 * Theme setup and basic configuration
 *
 * @package Quterma
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Sets up theme defaults and registers support for various WordPress features.
 */
function quterma_setup() {
    // Make theme available for translation.
    load_theme_textdomain('quterma', get_template_directory() . '/languages');

    // Add default posts and comments RSS feed links to head.
    add_theme_support('automatic-feed-links');

    // Let WordPress manage the document title.
    add_theme_support('title-tag');

    // Enable support for Post Thumbnails on posts and pages.
    add_theme_support('post-thumbnails');

    // Switch default core markup for search form, gallery, and caption to output valid HTML5.
    add_theme_support('html5', array(
        'search-form',
        'gallery',
        'caption',
        'style',
        'script',
        'navigation-widgets',
    ));

    // Support Gutenberg features
    add_theme_support('responsive-embeds');
    add_theme_support('align-wide');
    add_theme_support('editor-styles');
    add_editor_style('assets/css/editor-style.css');

    // Custom background & logo (if needed by admin)
    add_theme_support('custom-logo', array(
        'height'      => 40,
        'width'       => 200,
        'flex-height' => true,
        'flex-width'  => true,
    ));

    // Register navigation menus
    register_nav_menus(array(
        'primary' => __('Главное меню (Шапка)', 'quterma'),
        'mobile'  => __('Мобильное меню', 'quterma'),
        'footer'  => __('Меню подвала', 'quterma'),
    ));

    // Set content width for responsive media
    if (!isset($GLOBALS['content_width'])) {
        $GLOBALS['content_width'] = 960;
    }
}
add_action('after_setup_theme', 'quterma_setup');

/**
 * COMPLETELY DISABLE COMMENTS IN WORDPRESS
 * Per requirements:
 * "Комментариев на сайте НЕТ и не будет.
 * Функционал комментариев необходимо полностью отключить на уровне темы."
 */

// 1. Close comments on the front-end
add_filter('comments_open', '__return_false', 20, 2);
add_filter('pings_open', '__return_false', 20, 2);

// 2. Hide existing comments
add_filter('comments_array', '__return_empty_array', 10, 2);

// 3. Remove comments page in admin menu
add_action('admin_menu', function () {
    remove_menu_page('edit-comments.php');
});

// 4. Redirect any user trying to access comments page in admin
add_action('admin_init', function () {
    global $pagenow;
    if ($pagenow === 'edit-comments.php') {
        wp_safe_redirect(admin_url());
        exit;
    }

    // Remove comments metabox from post types
    $post_types = get_post_types();
    foreach ($post_types as $post_type) {
        if (post_type_supports($post_type, 'comments')) {
            remove_post_type_support($post_type, 'comments');
            remove_post_type_support($post_type, 'trackbacks');
        }
    }
});

// 5. Remove comments-related items from admin bar
add_action('wp_before_admin_bar_render', function () {
    global $wp_admin_bar;
    if ($wp_admin_bar) {
        $wp_admin_bar->remove_menu('comments');
    }
});

// 6. Disable comments feed across all feed formats
$quterma_disable_comments_feed = function ($is_comment_feed = false) {
    if ($is_comment_feed || (function_exists('is_comment_feed') && is_comment_feed())) {
        wp_die(__('Комментарии на сайте отключены.', 'quterma'), '', array('response' => 403));
    }
};
add_action('do_feed_rss2', $quterma_disable_comments_feed, 1, 1);
add_action('do_feed_atom', $quterma_disable_comments_feed, 1, 1);
add_action('do_feed_rss',  $quterma_disable_comments_feed, 1, 1);
add_action('do_feed_rdf',  $quterma_disable_comments_feed, 1, 1);

// 7. Flush rewrite rules upon theme activation to register CPTs, taxonomies, and custom RSS feeds
add_action('after_switch_theme', 'flush_rewrite_rules');
