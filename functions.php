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

// 4. Helper utilities (Russian dates, reading time, SVG placeholders, walkers)
require_once QUTERMA_DIR . '/inc/helpers.php';

// 5. Query optimizations, transients, and category blocks
require_once QUTERMA_DIR . '/inc/queries.php';

// 6. Popular posts logic ("Читают сейчас")
require_once QUTERMA_DIR . '/inc/popular.php';

// 7. Gastroguide (Гастрогид) - Custom Post Type, Taxonomies, and Meta Fields
require_once QUTERMA_DIR . '/inc/gastroguide.php';

// 8. Events poster (Афиша событий) - API «Культура.РФ» integration
require_once QUTERMA_DIR . '/inc/events-api.php';

// 9. Gutenberg / Block Editor styles and Block Patterns
require_once QUTERMA_DIR . '/inc/editor.php';

// 10. Specialized RSS feeds (Dzen, Dzen News, Rambler News)
require_once QUTERMA_DIR . '/inc/rss.php';

// 11. SEO, OpenGraph, and Schema.org structured data (NewsArticle)
require_once QUTERMA_DIR . '/inc/seo.php';

// 12. Analytics scripts and Webmaster verification
require_once QUTERMA_DIR . '/inc/analytics.php';

// 13. WordPress Customizer settings and controls
require_once QUTERMA_DIR . '/inc/customizer.php';
