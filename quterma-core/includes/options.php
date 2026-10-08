<?php
/**
 * Options API management and theme_mods migration helper
 *
 * @package QutermaCore
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('quterma_get_setting')) {
    /**
     * Retrieve setting from Options API with fallback to theme_mod.
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

if (!function_exists('quterma_migrate_theme_mods_to_options')) {
    /**
     * Migrate legacy theme_mods to Options API upon plugin activation.
     */
    function quterma_migrate_theme_mods_to_options() {
        $keys = array(
            'quterma_telegram',
            'quterma_vk',
            'quterma_email',
            'quterma_footer_about',
            'quterma_marquee_text',
            'quterma_ym_id',
            'quterma_ym_webvisor',
            'quterma_ga4_id',
            'quterma_yandex_verification',
            'quterma_google_verification',
            'quterma_liveinternet_enabled',
            'quterma_culture_api_url',
            'quterma_home_carousel_cat',
            'quterma_home_culture_cat',
            'quterma_home_specials_cat',
            'quterma_home_cinema_music_cat',
            'quterma_home_interview_cat',
        );

        foreach ($keys as $k) {
            $existing_opt = get_option($k, null);
            if ($existing_opt === null) {
                $mod_val = get_theme_mod($k, null);
                if ($mod_val !== null) {
                    update_option($k, $mod_val);
                }
            }
        }

        // Special handling for API key: ensure autoload is false
        $api_key = get_option('quterma_culture_api_key', null);
        if ($api_key === null) {
            $mod_key = get_theme_mod('quterma_culture_api_key', '');
            if (!empty($mod_key)) {
                update_option('quterma_culture_api_key', $mod_key, false);
            }
        }
    }
}
