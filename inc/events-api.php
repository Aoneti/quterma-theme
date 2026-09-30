<?php
/**
 * Integration with API «Культура.РФ» for Events Poster (Афиша событий)
 *
 * @package Quterma
 */

if (!defined('ABSPATH')) {
    exit;
}

const QUTERMA_CULTURE_API_ENDPOINT = 'https://all.culture.ru/api/2.3/events';
const QUTERMA_EVENTS_TRANSIENT_PREFIX = 'quterma_events_api_';

/**
 * Fetch events from API «Культура.РФ» with caching and fallback.
 *
 * @param array $args Filter parameters (city, date, limit, offset).
 * @return array Normalized list of events.
 */
function quterma_get_culture_events($args = array()) {
    $city   = isset($args['city']) ? sanitize_text_field($args['city']) : 'all';
    $when   = isset($args['when']) ? sanitize_text_field($args['when']) : 'all';
    $limit  = isset($args['limit']) ? min((int) $args['limit'], 50) : 24;
    $offset = isset($args['offset']) ? (int) $args['offset'] : 0;

    $cache_key = QUTERMA_EVENTS_TRANSIENT_PREFIX . md5($city . '_' . $when . '_' . $limit . '_' . $offset);
    $cached = get_transient($cache_key);

    if (false !== $cached && is_array($cached)) {
        return $cached;
    }

    // Build API query parameters
    $api_key = get_theme_mod('quterma_culture_api_key', '');
    $api_url = QUTERMA_CULTURE_API_ENDPOINT;

    $query_params = array(
        'limit'  => $limit,
        'offset' => $offset,
        'sort'   => '-start',
        'status' => 'accepted',
    );

    // City parameter mapping for Yaroslavl Region
    $city_names = array(
        'yaroslavl'    => 'Ярославль',
        'rybinsk'      => 'Рыбинск',
        'rostov'       => 'Ростов',
        'pereslavl'    => 'Переславль',
        'tutaev'       => 'Тутаев',
        'uglich'       => 'Углич',
        'gavrilov-yam' => 'Гаврилов-Ям',
        'danilov'      => 'Данилов',
        'lyubim'       => 'Любим',
        'myshkin'      => 'Мышкин',
        'poshekhonye'  => 'Пошехонье',
        'breytovo'     => 'Брейтово',
    );

    if ($city !== 'all' && isset($city_names[$city])) {
        $query_params['query'] = $city_names[$city];
    } else {
        $query_params['query'] = 'Ярославская область';
    }

    // Date range calculation
    $now = current_time('timestamp');
    if ($when === 'today') {
        $start_day = strtotime('today midnight', $now);
        $end_day   = strtotime('tomorrow midnight', $now) - 1;
        $query_params['start'] = $start_day * 1000;
        $query_params['end']   = $end_day * 1000;
    } elseif ($when === 'tomorrow') {
        $start_day = strtotime('tomorrow midnight', $now);
        $end_day   = strtotime('+2 days midnight', $now) - 1;
        $query_params['start'] = $start_day * 1000;
        $query_params['end']   = $end_day * 1000;
    } elseif ($when === 'week') {
        $start_day = strtotime('today midnight', $now);
        $end_day   = strtotime('+7 days midnight', $now);
        $query_params['start'] = $start_day * 1000;
        $query_params['end']   = $end_day * 1000;
    } elseif ($when === 'month') {
        $start_day = strtotime('today midnight', $now);
        $end_day   = strtotime('+30 days midnight', $now);
        $query_params['start'] = $start_day * 1000;
        $query_params['end']   = $end_day * 1000;
    }

    $request_url = add_query_arg($query_params, $api_url);

    $headers = array('Accept' => 'application/json');
    if (!empty($api_key)) {
        $headers['X-API-KEY'] = $api_key;
    }

    $response = wp_remote_get($request_url, array(
        'timeout'   => 7,
        'sslverify' => true,
        'headers'   => $headers,
    ));

    $events = array();

    if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (!empty($data['events']) && is_array($data['events'])) {
            foreach ($data['events'] as $item) {
                $events[] = quterma_normalize_culture_event($item);
            }
        }
    }

    // If API returned empty results or failed, return editorial fallback events
    if (empty($events)) {
        $events = quterma_get_fallback_events($city, $when);
    }

    // Cache results for 2 hours (7200 seconds)
    set_transient($cache_key, $events, 2 * HOUR_IN_SECONDS);

    return $events;
}

/**
 * Normalize an event item from API «Культура.РФ»
 */
function quterma_normalize_culture_event($item) {
    $timestamp = !empty($item['start']) ? (int) ($item['start'] / 1000) : time();
    $day   = date_i18n('j', $timestamp);
    $month = date_i18n('M', $timestamp); // 'июля', etc. in Russian locale
    $time  = date_i18n('H:i', $timestamp);

    // Determine place and city
    $place_name = '';
    $city_name  = 'Ярославль';
    $city_slug  = 'yaroslavl';

    if (!empty($item['places'][0])) {
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

    return array(
        'id'          => !empty($item['_id']) ? $item['_id'] : uniqid(),
        'name'        => !empty($item['name']) ? $item['name'] : '',
        'type'        => $category_name,
        'city_name'   => $city_name,
        'city_slug'   => quterma_slugify_city($city_name),
        'day'         => $day,
        'month'       => $month,
        'time'        => $time,
        'place'       => $place_name,
        'image'       => $image_url,
        'when_slug'   => quterma_classify_when($timestamp),
        'url'         => !empty($item['externalUrl']) ? $item['externalUrl'] : '#',
    );
}

/**
 * Helper to classify timestamp into when filter slug ('today', 'tomorrow', 'week', 'month')
 */
function quterma_classify_when($ts) {
    $today_start    = strtotime('today midnight');
    $today_end      = strtotime('tomorrow midnight') - 1;
    $tomorrow_end   = strtotime('+2 days midnight') - 1;
    $week_end       = strtotime('+7 days midnight');

    if ($ts >= $today_start && $ts <= $today_end) {
        return 'today';
    } elseif ($ts > $today_end && $ts <= $tomorrow_end) {
        return 'tomorrow';
    } elseif ($ts <= $week_end) {
        return 'week';
    }
    return 'month';
}

/**
 * Helper to slugify city name
 */
function quterma_slugify_city($city_name) {
    $map = array(
        'Ярославль'            => 'yaroslavl',
        'Рыбинск'              => 'rybinsk',
        'Ростов'               => 'rostov',
        'Ростов Великий'       => 'rostov',
        'Переславль-Залесский' => 'pereslavl',
        'Тутаев'               => 'tutaev',
        'Углич'                => 'uglich',
        'Гаврилов-Ям'          => 'gavrilov-yam',
        'Данилов'              => 'danilov',
        'Любим'                => 'lyubim',
        'Мышкин'               => 'myshkin',
        'Пошехонье'            => 'poshekhonye',
        'Брейтово'             => 'breytovo',
    );
    foreach ($map as $ru => $slug) {
        if (mb_stripos($city_name, $ru) !== false) {
            return $slug;
        }
    }
    return 'yaroslavl';
}

/**
 * Fallback events dataset matching prototype if API is unreachable.
 */
function quterma_get_fallback_events($city = 'all', $when = 'all') {
    $all_events = array(
        array(
            'id'        => 'fb-1',
            'name'      => 'Открытый концерт городского оркестра на набережной',
            'type'      => 'Концерт',
            'city_name' => 'Ярославль',
            'city_slug' => 'yaroslavl',
            'day'       => '14',
            'month'     => 'июля',
            'time'      => '19:00',
            'place'     => 'Волжская набережная',
            'image'     => '',
            'when_slug' => 'today',
            'url'       => '#',
        ),
        array(
            'id'        => 'fb-2',
            'name'      => 'Открытие выставки молодых художников',
            'type'      => 'Выставка',
            'city_name' => 'Ярославль',
            'city_slug' => 'yaroslavl',
            'day'       => '15',
            'month'     => 'июля',
            'time'      => '18:30',
            'place'     => 'Музей театра и кино',
            'image'     => '',
            'when_slug' => 'tomorrow',
            'url'       => '#',
        ),
        array(
            'id'        => 'fb-3',
            'name'      => 'Иммерсивный спектакль в дворянской усадьбе',
            'type'      => 'Спектакль',
            'city_name' => 'Тутаев',
            'city_slug' => 'tutaev',
            'day'       => '18',
            'month'     => 'июля',
            'time'      => '17:00',
            'place'     => 'Дворянская усадьба, Тутаев',
            'image'     => '',
            'when_slug' => 'week',
            'url'       => '#',
        ),
        array(
            'id'        => 'fb-4',
            'name'      => 'Фестиваль колокольного звона у стен кремля',
            'type'      => 'Фестиваль',
            'city_name' => 'Ростов Великий',
            'city_slug' => 'rostov',
            'day'       => '19',
            'month'     => 'июля',
            'time'      => '12:00',
            'place'     => 'Ростовский кремль',
            'image'     => '',
            'when_slug' => 'week',
            'url'       => '#',
        ),
        array(
            'id'        => 'fb-5',
            'name'      => 'Мастер-класс по сыроварению для всей семьи',
            'type'      => 'Мастер-класс',
            'city_name' => 'Углич',
            'city_slug' => 'uglich',
            'day'       => '2',
            'month'     => 'авг',
            'time'      => '11:00',
            'place'     => 'Сыроварня, Углич',
            'image'     => '',
            'when_slug' => 'month',
            'url'       => '#',
        ),
        array(
            'id'        => 'fb-6',
            'name'      => 'Ремесленная ярмарка на главной площади',
            'type'      => 'Ярмарка',
            'city_name' => 'Мышкин',
            'city_slug' => 'myshkin',
            'day'       => '9',
            'month'     => 'авг',
            'time'      => '10:00',
            'place'     => 'Никольская площадь',
            'image'     => '',
            'when_slug' => 'month',
            'url'       => '#',
        ),
    );

    $filtered = array();
    foreach ($all_events as $ev) {
        $match_city = ($city === 'all' || $ev['city_slug'] === $city);
        $match_when = ($when === 'all' || $ev['when_slug'] === $when);
        if ($match_city && $match_when) {
            $filtered[] = $ev;
        }
    }

    return !empty($filtered) ? $filtered : $all_events;
}

/**
 * AJAX handler for live filtering of events
 */
function quterma_ajax_get_events() {
    check_ajax_referer('quterma_nonce', 'nonce');

    $city = isset($_POST['city']) ? sanitize_text_field($_POST['city']) : 'all';
    $when = isset($_POST['when']) ? sanitize_text_field($_POST['when']) : 'all';

    $events = quterma_get_culture_events(array(
        'city' => $city,
        'when' => $when,
    ));

    ob_start();
    if (!empty($events)) {
        foreach ($events as $event) {
            set_query_var('event_item', $event);
            get_template_part('template-parts/content', 'event');
        }
    }
    $html = ob_get_clean();

    wp_send_json_success(array(
        'count' => count($events),
        'html'  => $html,
    ));
}
add_action('wp_ajax_quterma_get_events', 'quterma_ajax_get_events');
add_action('wp_ajax_nopriv_quterma_get_events', 'quterma_ajax_get_events');
