<?php
/**
 * Integration with API «Культура.РФ» for cultural events poster (Афиша)
 *
 * Implements:
 * - Automated background preloading via WP-Cron (every hour)
 * - ASC sorting by start date with lower bound >= today 00:00:00
 * - Anti-stampede lock (mutex transient) & stale-while-revalidate pattern
 * - Strict normalization, timezone awareness (wp_timezone), and date token taxonomy
 * - Fast local option storage for instant page loads (no 7s render blocks)
 *
 * @package Quterma
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('quterma_setup_events_cron')) {
    /**
     * Register hourly WP-Cron schedule and action for events synchronization
     */
    function quterma_setup_events_cron() {
        if (!wp_next_scheduled('quterma_hourly_events_cron')) {
            wp_schedule_event(time(), 'hourly', 'quterma_hourly_events_cron');
        }
    }
    add_action('after_switch_theme', 'quterma_setup_events_cron');
    add_action('init', 'quterma_setup_events_cron');
}

if (!function_exists('quterma_clear_events_cron')) {
    // Unschedule on theme switch
    function quterma_clear_events_cron() {
        $timestamp = wp_next_scheduled('quterma_hourly_events_cron');
        if ($timestamp) {
            wp_unschedule_event($timestamp, 'quterma_hourly_events_cron');
        }
    }
    add_action('switch_theme', 'quterma_clear_events_cron');
}

// Hook cron action
if (!has_action('quterma_hourly_events_cron', 'quterma_sync_culture_events')) {
    add_action('quterma_hourly_events_cron', 'quterma_sync_culture_events');
}

if (!function_exists('quterma_sync_culture_events')) {
    /**
     * Synchronize cultural events from API «Культура.РФ» in background into wp_options
     *
     * @param bool $force Force sync ignoring lock
     * @return array Normalized events array
     */
    function quterma_sync_culture_events($force = false) {
    // Prevent thundering herd with a transient lock (5 minute backoff on error/running)
    if (!$force && get_transient('quterma_events_sync_lock')) {
        return (array) get_option('quterma_culture_events_data', array());
    }
    set_transient('quterma_events_sync_lock', 1, 300);
    update_option('quterma_culture_events_last_attempt', time(), false);

    // Support offline fixture testing
    if (defined('QUTERMA_USE_FIXTURE_EVENTS') && QUTERMA_USE_FIXTURE_EVENTS) {
        $fixture_file = get_template_directory() . '/tests/events-fixture.json';
        if (file_exists($fixture_file)) {
            $data = json_decode(file_get_contents($fixture_file), true);
            if (!empty($data['events']) && is_array($data['events'])) {
                $normalized = array();
                foreach ($data['events'] as $item) {
                    if (!is_array($item)) continue;
                    $ev = quterma_normalize_culture_event($item);
                    if ($ev) {
                        $normalized[] = $ev;
                    }
                }
                update_option('quterma_culture_events_data', $normalized, false);
                update_option('quterma_culture_events_last_sync', time(), false);
                update_option('quterma_culture_events_last_error', '', false);
                delete_transient('quterma_events_sync_lock');
                return $normalized;
            }
        }
    }

    $api_url = function_exists('quterma_get_setting') ? quterma_get_setting('quterma_culture_api_url', 'https://opendata.mkrf.ru/v2/events/') : get_theme_mod('quterma_culture_api_url', 'https://opendata.mkrf.ru/v2/events/');
    $api_key = function_exists('quterma_get_setting') ? quterma_get_setting('quterma_culture_api_key', '') : get_theme_mod('quterma_culture_api_key', '');

    $tz = wp_timezone();
    $today_dt = new DateTimeImmutable('today', $tz);
    $start_ms = $today_dt->getTimestamp() * 1000;

    $headers = array('Accept' => 'application/json');
    if (!empty($api_key)) {
        $headers['X-API-KEY'] = $api_key;
    }

    $all_normalized = array();
    $limit_per_page = 100;
    $max_events     = 300;
    $offset         = 0;
    $has_more       = true;

    while ($has_more && count($all_normalized) < $max_events) {
        $query_params = array(
            'query'  => 'Ярославская область',
            'status' => 'accepted',
            'start'  => $start_ms, // Lower bound: today 00:00:00 MSK, strictly no past events
            'sort'   => 'start',    // ASC: closest upcoming events first
            'limit'  => $limit_per_page,
            'offset' => $offset,
        );

        $request_url = add_query_arg($query_params, $api_url);

        $response = wp_remote_get($request_url, array(
            'timeout'   => 8,
            'sslverify' => true,
            'headers'   => $headers,
        ));

        if (is_wp_error($response)) {
            $err_msg = $response->get_error_message();
            error_log('[Quterma Events API] Request error: ' . $err_msg);
            update_option('quterma_culture_events_last_error', $err_msg, false);
            break;
        }

        $code = wp_remote_retrieve_response_code($response);
        if ($code !== 200) {
            $err_msg = 'HTTP ' . $code;
            error_log('[Quterma Events API] HTTP ' . $code . ' returned from ' . $request_url);
            update_option('quterma_culture_events_last_error', $err_msg, false);
            break;
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (!isset($data['events']) || !is_array($data['events'])) {
            $err_msg = 'Invalid response schema: missing events array';
            error_log('[Quterma Events API] ' . $err_msg);
            update_option('quterma_culture_events_last_error', $err_msg, false);
            break;
        }

        if (empty($data['events'])) {
            break; // No more events available
        }

        foreach ($data['events'] as $item) {
            if (!is_array($item)) {
                continue;
            }
            $ev = quterma_normalize_culture_event($item);
            if ($ev) {
                $all_normalized[] = $ev;
            }
        }

        if (count($data['events']) < $limit_per_page) {
            $has_more = false;
        } else {
            $offset += $limit_per_page;
        }
    }

    if (!empty($all_normalized)) {
        // Sort by start timestamp ASC to ensure chronological order
        usort($all_normalized, function ($a, $b) {
            return $a['start_ts'] - $b['start_ts'];
        });

        update_option('quterma_culture_events_data', $all_normalized, false);
        update_option('quterma_culture_events_last_sync', time(), false);
        update_option('quterma_culture_events_last_error', '', false);
        delete_transient('quterma_events_sync_lock');
        return $all_normalized;
    }

    // Ensure option is initialized to array even on API failure
    $cached = get_option('quterma_culture_events_data', null);
    if ($cached === null) {
        update_option('quterma_culture_events_data', array(), false);
    }

    // If API failed or was empty, preserve previously stored events (stale-while-revalidate)
    return (array) get_option('quterma_culture_events_data', array());
}
}

if (!function_exists('quterma_get_culture_events')) {
/**
 * Retrieve cultural events.
 * NEVER blocks visitor requests with synchronous external HTTP requests.
 * Computes when_tokens dynamically on read so dates never get stale after midnight.
 *
 * @param array $args
 * @return array
 */
function quterma_get_culture_events($args = array()) {
    $defaults = array(
        'city'  => 'all',
        'when'  => 'all',
        'limit' => 100,
    );
    $args = wp_parse_args($args, $defaults);

    $city  = sanitize_text_field($args['city']);
    $when  = sanitize_text_field($args['when']);
    $limit = min(100, max(1, (int) $args['limit']));

    $events    = get_option('quterma_culture_events_data', null);
    $last_sync = (int) get_option('quterma_culture_events_last_sync', 0);

    // Initial fill if never synced or stale (> 1 hour): trigger background cron, NEVER block current request
    if ($events === null || (time() - $last_sync) > HOUR_IN_SECONDS) {
        if ($events === null) {
            update_option('quterma_culture_events_data', array(), false);
            $events = array();
        }
        if (!get_transient('quterma_events_sync_lock')) {
            wp_schedule_single_event(time(), 'quterma_hourly_events_cron');
        }
    }

    if (!is_array($events)) {
        $events = array();
    }

    $tz          = wp_timezone();
    $today_dt    = new DateTimeImmutable('today', $tz);
    $today_start = $today_dt->getTimestamp();

    // Filter events: strictly >= today, dynamically recalculating tokens on read
    $filtered = array();
    foreach ($events as $ev) {
        // Strip past events
        if (!isset($ev['start_ts']) || $ev['start_ts'] < $today_start) {
            continue;
        }

        // Dynamically compute date tokens on read so they remain 100% accurate past midnight
        $tokens = quterma_classify_when_tokens($ev['start_ts']);
        $ev['when_tokens'] = $tokens;
        $ev['when_slug']   = quterma_classify_when_primary($ev['start_ts']);

        // City filter
        if ($city !== 'all' && isset($ev['city_slug']) && $ev['city_slug'] !== $city) {
            continue;
        }

        // Date filter
        if ($when !== 'all') {
            $token_list = explode(' ', $tokens);
            if (!in_array($when, $token_list, true)) {
                continue;
            }
        }

        $filtered[] = $ev;
        if (count($filtered) >= $limit) {
            break;
        }
    }

    return $filtered;
}
}

if (!function_exists('quterma_normalize_culture_event')) {
/**
 * Normalize an event item from API «Культура.РФ».
 * Strictly validates dates: discards events without a valid start timestamp.
 *
 * @param array $item
 * @return array|null
 */
function quterma_normalize_culture_event($item) {
    if (empty($item) || !is_array($item)) {
        return null;
    }

    // Strict start timestamp parsing: support epoch-ms, epoch-s, or ISO date string
    if (empty($item['start'])) {
        return null; // Events without a start date must be discarded
    }

    $raw_start = $item['start'];
    $timestamp = 0;
    if (is_numeric($raw_start)) {
        $num = (float) $raw_start;
        $timestamp = ($num > 100000000000) ? (int) round($num / 1000) : (int) $num;
    } elseif (is_string($raw_start)) {
        $parsed = strtotime($raw_start);
        if ($parsed !== false && $parsed > 0) {
            $timestamp = (int) $parsed;
        }
    }

    if ($timestamp <= 0) {
        return null; // Invalid date format, discard
    }

    $tz = wp_timezone();
    $day   = wp_date('j', $timestamp, $tz);
    $month = wp_date('F', $timestamp, $tz);
    $time  = wp_date('H:i', $timestamp, $tz);
    $iso   = wp_date('c', $timestamp, $tz);

    // Determine place and city
    $place_name = '';
    $city_name  = 'Ярославль';

    if (!empty($item['places'][0]) && is_array($item['places'][0])) {
        $p = $item['places'][0];
        $place_name = !empty($p['name']) ? $p['name'] : '';
        if (!empty($p['locale']['name'])) {
            $city_name = $p['locale']['name'];
        } elseif (!empty($p['address']['city'])) {
            $city_name = $p['address']['city'];
        }
    }

    // Category / Type
    $category_name = !empty($item['category']['name']) ? $item['category']['name'] : __('Событие', 'quterma');

    // Image
    $image_url = '';
    if (!empty($item['image']['url'])) {
        $image_url = $item['image']['url'];
    }

    $tokens = quterma_classify_when_tokens($timestamp);

    // Event ID
    $event_id = !empty($item['_id']) ? (string) $item['_id'] : (!empty($item['id']) ? (string) $item['id'] : uniqid('ev_', true));

    // External URL
    $ext_url = !empty($item['externalUrl']) ? $item['externalUrl'] : (!empty($item['url']) ? $item['url'] : '#');

    return array(
        'id'          => $event_id,
        'name'        => !empty($item['name']) ? $item['name'] : '',
        'type'        => $category_name,
        'city_name'   => $city_name,
        'city_slug'   => quterma_slugify_city($city_name),
        'day'         => $day,
        'month'       => $month,
        'time'        => $time,
        'iso'         => $iso,
        'start_ts'    => $timestamp,
        'place'       => $place_name,
        'image'       => $image_url,
        'when_tokens' => $tokens,
        'when_slug'   => quterma_classify_when_primary($timestamp),
        'url'         => $ext_url,
    );
}
}

if (!function_exists('quterma_classify_when_tokens')) {
/**
 * Classify timestamp into token string for hierarchical filtering.
 * E.g. an event today matches "today week month".
 *
 * @param int $ts
 * @return string
 */
function quterma_classify_when_tokens($ts) {
    $tz           = wp_timezone();
    $today_dt     = new DateTimeImmutable('today', $tz);
    $today_start  = $today_dt->getTimestamp();
    $today_end    = $today_dt->modify('+1 day')->getTimestamp() - 1;
    $tomorrow_end = $today_dt->modify('+2 days')->getTimestamp() - 1;
    $week_end     = $today_dt->modify('+7 days')->getTimestamp();
    $month_end    = $today_dt->modify('+30 days')->getTimestamp();

    $tokens = array();

    if ($ts >= $today_start && $ts <= $today_end) {
        $tokens[] = 'today';
        $tokens[] = 'week';
        $tokens[] = 'month';
    } elseif ($ts > $today_end && $ts <= $tomorrow_end) {
        $tokens[] = 'tomorrow';
        $tokens[] = 'week';
        $tokens[] = 'month';
    } elseif ($ts <= $week_end) {
        $tokens[] = 'week';
        $tokens[] = 'month';
    } elseif ($ts <= $month_end) {
        $tokens[] = 'month';
    } else {
        $tokens[] = 'future';
    }

    return implode(' ', $tokens);
}
}

if (!function_exists('quterma_classify_when_primary')) {
/**
 * Primary slug representation of when an event takes place
 *
 * @param int $ts
 * @return string
 */
function quterma_classify_when_primary($ts) {
    $tz           = wp_timezone();
    $today_dt     = new DateTimeImmutable('today', $tz);
    $today_start  = $today_dt->getTimestamp();
    $today_end    = $today_dt->modify('+1 day')->getTimestamp() - 1;
    $tomorrow_end = $today_dt->modify('+2 days')->getTimestamp() - 1;
    $week_end     = $today_dt->modify('+7 days')->getTimestamp();

    if ($ts >= $today_start && $ts <= $today_end) {
        return 'today';
    } elseif ($ts > $today_end && $ts <= $tomorrow_end) {
        return 'tomorrow';
    } elseif ($ts <= $week_end) {
        return 'week';
    }
    return 'month';
}
}

if (!function_exists('quterma_slugify_city')) {
/**
 * Helper to slugify city name
 *
 * @param string $city_name
 * @return string
 */
function quterma_slugify_city($city_name) {
    $map = array(
        'Ярославль'            => 'yaroslavl',
        'Рыбинск'              => 'rybinsk',
        'Ростов'               => 'rostov',
        'Ростов Великий'       => 'rostov',
        'Переславль-Залесский' => 'pereslavl',
        'Переславль'           => 'pereslavl',
        'Тутаев'               => 'tutaev',
        'Углич'                => 'uglich',
        'Гаврилов-Ям'          => 'gavrilov-yam',
        'Данилов'              => 'danilov',
        'Любим'                => 'lyubim',
        'Мышкин'               => 'myshkin',
        'Пошехонье'            => 'poshekhonye',
        'Брейтово'             => 'breytovo',
    );
    $stripos_fn = function_exists('quterma_stripos') ? 'quterma_stripos' : (function_exists('mb_stripos') ? 'mb_stripos' : 'stripos');
    foreach ($map as $ru => $slug) {
        if ($stripos_fn($city_name, $ru) !== false) {
            return $slug;
        }
    }
    return 'other';
}
}
