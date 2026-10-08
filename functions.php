<?php
/**
 * Quterma Theme functions and definitions
 *
 * @package Quterma
 */

if (!defined('ABSPATH')) {
    exit;
}

define('QUTERMA_VERSION', '1.0.0');
define('QUTERMA_DIR', get_template_directory());
define('QUTERMA_URI', get_template_directory_uri());

// 1. Core theme setup and comment disabling
require_once QUTERMA_DIR . '/inc/setup.php';

// 2. Asset management (CSS, JS, Fonts, Preload)
require_once QUTERMA_DIR . '/inc/enqueue.php';

// 3. Image sizes, WebP support, and responsive image filters
require_once QUTERMA_DIR . '/inc/images.php';

// 4. Helper utilities (Russian dates, SVG placeholders, walkers)
require_once QUTERMA_DIR . '/inc/helpers.php';

// 5. Query optimizations, transients, and category blocks
require_once QUTERMA_DIR . '/inc/queries.php';

// Check if Quterma Core domain plugin is active or being activated
$has_core_plugin = defined('QUTERMA_CORE_ACTIVE');

if (!$has_core_plugin) {
    // 1. Detect if quterma-core is already listed in active_plugins
    $active_plugins = (array) get_option('active_plugins', array());
    foreach ($active_plugins as $active_plugin) {
        if (strpos((string) $active_plugin, 'quterma-core') !== false) {
            $has_core_plugin = true;
            break;
        }
    }
}

if (!$has_core_plugin && is_admin()) {
    // 2. Detect if plugin is currently being activated in wp-admin/plugins.php
    if (isset($_REQUEST['plugin']) && strpos((string) $_REQUEST['plugin'], 'quterma-core') !== false) {
        $has_core_plugin = true;
    } elseif (isset($_POST['checked']) && is_array($_POST['checked'])) {
        foreach ($_POST['checked'] as $checked_plugin) {
            if (strpos((string) $checked_plugin, 'quterma-core') !== false) {
                $has_core_plugin = true;
                break;
            }
        }
    }
}

// 6. Popular posts logic ("Читают сейчас")
if (!$has_core_plugin) {
    require_once QUTERMA_DIR . '/inc/popular.php';
}

// 7. Gastroguide (Гастрогид) - Custom Post Type, Taxonomies, and Meta Fields
if (!$has_core_plugin) {
    require_once QUTERMA_DIR . '/inc/gastroguide.php';
}

// 8. Events poster (Афиша событий) - API «Культура.РФ» integration
if (!$has_core_plugin) {
    require_once QUTERMA_DIR . '/inc/events-api.php';
}

// 9. Gutenberg / Block Editor styles and Block Patterns
require_once QUTERMA_DIR . '/inc/editor.php';

// 10. Specialized RSS feeds (Dzen, Dzen News, Rambler News)
if (!$has_core_plugin) {
    require_once QUTERMA_DIR . '/inc/rss.php';
}

// 11. SEO, OpenGraph, and Schema.org structured data (NewsArticle)
if (!$has_core_plugin) {
    require_once QUTERMA_DIR . '/inc/seo.php';
}

// 12. Analytics scripts and Webmaster verification
if (!$has_core_plugin) {
    require_once QUTERMA_DIR . '/inc/analytics.php';
}

// 13. WordPress Customizer settings and controls
require_once QUTERMA_DIR . '/inc/customizer.php';
