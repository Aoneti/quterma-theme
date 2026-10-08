<?php
/**
 * Plugin Name: Quterma Core
 * Plugin URI: https://quterma.ru/
 * Description: Domain business logic, custom post types (Gastroguide CPT quterma_venue), Culture.ru Events API, specialized RSS feeds (Dzen, Rambler), Schema.org JSON-LD SEO, and view analytics for Quterma.
 * Version: 1.0.0
 * Author: Quterma Editorial Team
 * Author URI: https://quterma.ru/
 * License: GPL-2.0+
 * Text Domain: quterma-core
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!defined('QUTERMA_CORE_VERSION')) {
    define('QUTERMA_CORE_VERSION', '1.0.0');
}
if (!defined('QUTERMA_CORE_ACTIVE')) {
    define('QUTERMA_CORE_ACTIVE', true);
}
if (!defined('QUTERMA_CORE_DIR')) {
    define('QUTERMA_CORE_DIR', plugin_dir_path(__FILE__));
}
if (!defined('QUTERMA_CORE_URI')) {
    define('QUTERMA_CORE_URI', plugin_dir_url(__FILE__));
}

// 1. Options API and settings helper
require_once QUTERMA_CORE_DIR . 'includes/options.php';

// 2. Custom Post Type: Gastroguide (quterma_venue) & City taxonomy
require_once QUTERMA_CORE_DIR . 'includes/cpt-venue.php';

// 3. Culture.ru Events API integration & background cron
require_once QUTERMA_CORE_DIR . 'includes/events-api.php';

// 4. Specialized RSS feeds (Dzen, Dzen News, Rambler News)
require_once QUTERMA_CORE_DIR . 'includes/rss-feeds.php';

// 5. SEO, OpenGraph, and Schema.org JSON-LD
require_once QUTERMA_CORE_DIR . 'includes/seo-json-ld.php';

// 6. Popular posts ("Читают сейчас") & async view tracking
require_once QUTERMA_CORE_DIR . 'includes/popular-views.php';

// 7. Webmaster verification & analytics scripts
require_once QUTERMA_CORE_DIR . 'includes/analytics.php';

/**
 * Activation callback: registers CPT, migrates settings, sets cron, and flushes rewrite rules.
 */
if (!function_exists('quterma_core_activate')) {
    function quterma_core_activate() {
        if (!extension_loaded('mbstring')) {
            deactivate_plugins(plugin_basename(__FILE__));
            wp_die(esc_html__('Невозможно активировать плагин «Quterma Core»: требуется включенное расширение PHP mbstring для работы с кириллицей.', 'quterma-core'));
        }

        // 1. Ensure CPT and Taxonomies are registered immediately before flushing rules
        quterma_register_gastroguide_cpt();
        flush_rewrite_rules();

        // 2. Schedule events cron if not already scheduled
        if (!wp_next_scheduled('quterma_hourly_events_cron')) {
            wp_schedule_event(time(), 'hourly', 'quterma_hourly_events_cron');
        }

        // 3. Migrate legacy theme_mods to Options API
        quterma_migrate_theme_mods_to_options();
    }
}
register_activation_hook(__FILE__, 'quterma_core_activate');

if (!extension_loaded('mbstring')) {
    add_action('admin_notices', function () {
        echo '<div class="notice notice-error"><p>' . esc_html__('Плагин «Quterma Core» требует включенного расширения PHP mbstring для корректной работы с кириллицей.', 'quterma-core') . '</p></div>';
    });
}

/**
 * Deactivation callback: cleans up cron and flushes rewrite rules.
 */
if (!function_exists('quterma_core_deactivate')) {
    function quterma_core_deactivate() {
        flush_rewrite_rules();
        $timestamp = wp_next_scheduled('quterma_hourly_events_cron');
        if ($timestamp) {
            wp_unschedule_event($timestamp, 'quterma_hourly_events_cron');
        }
    }
}
register_deactivation_hook(__FILE__, 'quterma_core_deactivate');
