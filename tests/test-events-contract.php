<?php
/**
 * Test script for Culture.ru API contract and event normalization
 */

// Mock WordPress functions if running standalone
if (!defined('ABSPATH')) {
    define('ABSPATH', __DIR__ . '/../');
}

function wp_timezone() {
    return new DateTimeZone('Europe/Moscow');
}

function wp_date($format, $timestamp = null, $timezone = null) {
    if ($timestamp === null) $timestamp = time();
    if ($timezone === null) $timezone = wp_timezone();
    $dt = new DateTimeImmutable('@' . $timestamp);
    $dt = $dt->setTimezone($timezone);
    return $dt->format($format);
}

function __($text, $domain = 'default') {
    return $text;
}

require_once __DIR__ . '/../inc/events-api.php';

$fixture_json = file_get_contents(__DIR__ . '/events-fixture.json');
$fixture_data = json_decode($fixture_json, true);

assert(!empty($fixture_data['events']), 'Fixture events should not be empty');
assert(count($fixture_data['events']) === 3, 'Fixture must contain 3 events');

$normalized = array();
foreach ($fixture_data['events'] as $raw) {
    $ev = quterma_normalize_culture_event($raw);
    assert($ev !== null, 'Normalized event should not be null');
    assert(!empty($ev['name']), 'Event name must not be empty');
    assert(!empty($ev['city_name']), 'City name must not be empty');
    assert(!empty($ev['city_slug']), 'City slug must not be empty');
    assert(!empty($ev['when_tokens']), 'When tokens must not be empty');
    assert(!empty($ev['start_ts']), 'Start timestamp must not be empty');
    assert(is_numeric($ev['start_ts']), 'Start timestamp must be numeric');
    $normalized[] = $ev;
}

// Check Rybinsk event
$rybinsk_event = $normalized[1];
assert($rybinsk_event['city_slug'] === 'rybinsk', 'Second event must have city_slug "rybinsk"');
assert($rybinsk_event['type'] === 'Спектакль', 'Second event category must be "Спектакль"');

// Check Rostov event
$rostov_event = $normalized[2];
assert($rostov_event['city_slug'] === 'rostov', 'Third event must have city_slug "rostov"');

// Check sorting
usort($normalized, function ($a, $b) {
    return $a['start_ts'] - $b['start_ts'];
});
assert($normalized[0]['start_ts'] <= $normalized[1]['start_ts'], 'Events must sort in ascending chronological order');

echo "All Culture.ru API contract and normalization tests passed successfully!\n";
