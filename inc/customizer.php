<?php
/**
 * Theme Customizer Settings and Controls
 *
 * Implements:
 * - Persistent storage via Options API (type => option) so settings never vanish on theme switch
 * - Secure API key handling (stored with autoload=false, masked in UI)
 * - Dedicated control for Culture.ru API endpoint URL
 * - Explicit deterministic bindings for homepage blocks
 *
 * @package Quterma
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register Customizer options.
 */
function quterma_customize_register($wp_customize) {
    // 1. Panel: Настройки «Кутерьма»
    $wp_customize->add_panel('quterma_panel', array(
        'title'       => __('Настройки темы «Кутерьма»', 'quterma'),
        'description' => __('Управление контактами, соцсетями, аналитикой и блоками сайта', 'quterma'),
        'priority'    => 20,
    ));

    // Section 1: Контакты и социальные сети (хранятся в Options API)
    $wp_customize->add_section('quterma_contacts_section', array(
        'title'    => __('Контакты и соцсети', 'quterma'),
        'panel'    => 'quterma_panel',
        'priority' => 10,
    ));

    $wp_customize->add_setting('quterma_telegram', array(
        'type'              => 'option',
        'default'           => 'https://t.me/kuterma',
        'sanitize_callback' => 'esc_url_raw',
    ));
    $wp_customize->add_control('quterma_telegram', array(
        'label'    => __('Telegram канал', 'quterma'),
        'section'  => 'quterma_contacts_section',
        'type'     => 'url',
    ));

    $wp_customize->add_setting('quterma_vk', array(
        'type'              => 'option',
        'default'           => 'https://vk.com/kuterma',
        'sanitize_callback' => 'esc_url_raw',
    ));
    $wp_customize->add_control('quterma_vk', array(
        'label'    => __('Сообщество ВКонтакте', 'quterma'),
        'section'  => 'quterma_contacts_section',
        'type'     => 'url',
    ));

    $wp_customize->add_setting('quterma_dzen', array(
        'type'              => 'option',
        'default'           => 'https://dzen.ru/quterma',
        'sanitize_callback' => 'esc_url_raw',
    ));
    $wp_customize->add_control('quterma_dzen', array(
        'label'    => __('Канал в Дзене', 'quterma'),
        'section'  => 'quterma_contacts_section',
        'type'     => 'url',
    ));

    $wp_customize->add_setting('quterma_email', array(
        'type'              => 'option',
        'default'           => 'redaktsiya@quterma.ru',
        'sanitize_callback' => 'sanitize_email',
    ));
    $wp_customize->add_control('quterma_email', array(
        'label'    => __('Email редакции', 'quterma'),
        'section'  => 'quterma_contacts_section',
        'type'     => 'email',
    ));

    $wp_customize->add_setting('quterma_ad_email', array(
        'type'              => 'option',
        'default'           => 'reklama@quterma.ru',
        'sanitize_callback' => 'sanitize_email',
    ));
    $wp_customize->add_control('quterma_ad_email', array(
        'label'    => __('Email рекламного отдела', 'quterma'),
        'section'  => 'quterma_contacts_section',
        'type'     => 'email',
    ));

    $wp_customize->add_setting('quterma_footer_about', array(
        'type'              => 'option',
        'default'           => 'Независимое городское издание о Ярославле. С 2026 года.',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('quterma_footer_about', array(
        'label'    => __('Текст о редакции в подвале', 'quterma'),
        'section'  => 'quterma_contacts_section',
        'type'     => 'textarea',
    ));

    $wp_customize->add_setting('quterma_marquee_text', array(
        'type'              => 'option',
        'default'           => 'Что происходит?',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('quterma_marquee_text', array(
        'label'    => __('Текст бегущей строки в подвале', 'quterma'),
        'section'  => 'quterma_contacts_section',
        'type'     => 'text',
    ));

    // Section 2: Аналитика и вебмастера (хранятся в Options API)
    $wp_customize->add_section('quterma_analytics_section', array(
        'title'    => __('Аналитика и вебмастера', 'quterma'),
        'panel'    => 'quterma_panel',
        'priority' => 20,
    ));

    $wp_customize->add_setting('quterma_ym_id', array(
        'type'              => 'option',
        'default'           => '',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('quterma_ym_id', array(
        'label'       => __('Номер счётчика Яндекс Метрики', 'quterma'),
        'description' => __('Например: 98765432. Без лишних символов.', 'quterma'),
        'section'     => 'quterma_analytics_section',
        'type'        => 'text',
    ));

    $wp_customize->add_setting('quterma_ym_webvisor', array(
        'type'              => 'option',
        'default'           => true,
        'sanitize_callback' => 'wp_validate_boolean',
    ));
    $wp_customize->add_control('quterma_ym_webvisor', array(
        'label'   => __('Включить Яндекс Вебвизор', 'quterma'),
        'section' => 'quterma_analytics_section',
        'type'    => 'checkbox',
    ));

    $wp_customize->add_setting('quterma_ga4_id', array(
        'type'              => 'option',
        'default'           => '',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('quterma_ga4_id', array(
        'label'       => __('Google Analytics 4 (Measurement ID)', 'quterma'),
        'description' => __('Например: G-XXXXXXXXXX', 'quterma'),
        'section'     => 'quterma_analytics_section',
        'type'        => 'text',
    ));

    $wp_customize->add_setting('quterma_yandex_verification', array(
        'type'              => 'option',
        'default'           => '',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('quterma_yandex_verification', array(
        'label'       => __('Код подтверждения Яндекс Вебмастер', 'quterma'),
        'description' => __('Только код из метатега content="..."', 'quterma'),
        'section'     => 'quterma_analytics_section',
        'type'        => 'text',
    ));

    $wp_customize->add_setting('quterma_google_verification', array(
        'type'              => 'option',
        'default'           => '',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('quterma_google_verification', array(
        'label'       => __('Код подтверждения Google Search Console', 'quterma'),
        'description' => __('Только код из метатега content="..."', 'quterma'),
        'section'     => 'quterma_analytics_section',
        'type'        => 'text',
    ));

    $wp_customize->add_setting('quterma_liveinternet_enabled', array(
        'type'              => 'option',
        'default'           => false,
        'sanitize_callback' => 'wp_validate_boolean',
    ));
    $wp_customize->add_control('quterma_liveinternet_enabled', array(
        'label'   => __('Включить счётчик LiveInternet', 'quterma'),
        'section' => 'quterma_analytics_section',
        'type'    => 'checkbox',
    ));

    // Section 3: Настройки API «Культура.РФ» (Options API + autoload=false)
    $last_sync    = (int) get_option('quterma_culture_events_last_sync', 0);
    $last_error   = get_option('quterma_culture_events_last_error', '');
    $events_data  = get_option('quterma_culture_events_data', array());
    $events_count = is_array($events_data) ? count($events_data) : 0;

    $sync_status_text = $last_sync > 0
        ? sprintf(__('Последняя успешная синхронизация: %s (событий в кэше: %d)', 'quterma'), wp_date('d.m.Y H:i:s', $last_sync), $events_count)
        : __('Синхронизация ещё не выполнялась (кэш пуст).', 'quterma');

    if (!empty($last_error)) {
        $sync_status_text .= ' ' . sprintf(__('Последняя ошибка: %s', 'quterma'), esc_html($last_error));
    }

    $wp_customize->add_section('quterma_culture_api_section', array(
        'title'       => __('API «Культура.РФ» (События)', 'quterma'),
        'description' => $sync_status_text,
        'panel'       => 'quterma_panel',
        'priority'    => 30,
    ));

    // API Key: Sanitized and saved with autoload=false, masked in UI
    $existing_key = get_option('quterma_culture_api_key', '');
    $has_key = !empty($existing_key);

    $wp_customize->add_setting('quterma_culture_api_key', array(
        'type'              => 'option',
        'default'           => '',
        'sanitize_callback' => function ($val) {
            $sanitized = sanitize_text_field($val);
            if (!empty($sanitized)) {
                // Ensure saved with autoload=false
                update_option('quterma_culture_api_key', $sanitized, false);
            }
            return $sanitized;
        },
    ));
    $wp_customize->add_control('quterma_culture_api_key', array(
        'label'       => __('API-ключ ЕИПСК / Культура.РФ', 'quterma'),
        'description' => $has_key
            ? __('API-ключ надёжно сохранён в базе данных (скрыт из соображений безопасности). Введите новый, если хотите его изменить.', 'quterma')
            : __('Если у вас есть ключ PRO.Культура.РФ / ЕИПСК, укажите его здесь.', 'quterma'),
        'input_attrs' => array(
            'placeholder'  => $has_key ? '••••••••••••••••••••••••' : '',
            'autocomplete' => 'new-password',
        ),
        'section'     => 'quterma_culture_api_section',
        'type'        => 'password',
    ));

    $wp_customize->add_setting('quterma_culture_api_url', array(
        'type'              => 'option',
        'default'           => 'https://opendata.mkrf.ru/v2/events/',
        'sanitize_callback' => 'esc_url_raw',
    ));
    $wp_customize->add_control('quterma_culture_api_url', array(
        'label'       => __('URL конечной точки API событий', 'quterma'),
        'description' => __('По умолчанию: https://opendata.mkrf.ru/v2/events/', 'quterma'),
        'section'     => 'quterma_culture_api_section',
        'type'        => 'url',
    ));

    // Section 4: Рубрики блоков главной страницы (детерминированная привязка в Options API)
    $wp_customize->add_section('quterma_home_blocks_section', array(
        'title'       => __('Рубрики блоков главной', 'quterma'),
        'description' => __('Явная детерминированная привязка категорий к тематическим блокам главной страницы вместо нечёткого поиска.', 'quterma'),
        'panel'       => 'quterma_panel',
        'priority'    => 40,
    ));

    $blocks_map = array(
        'quterma_home_carousel_cat'     => array('label' => __('Рубрика для карусели (slug или ID)', 'quterma'), 'default' => 'carousel'),
        'quterma_home_culture_cat'      => array('label' => __('Рубрика «Культурный слой»', 'quterma'), 'default' => 'culture'),
        'quterma_home_specials_cat'     => array('label' => __('Рубрика «Лонгриды и спецпроекты»', 'quterma'), 'default' => 'specials'),
        'quterma_home_cinema_music_cat' => array('label' => __('Рубрика «Кино и музыка»', 'quterma'), 'default' => 'cinema-music'),
        'quterma_home_interview_cat'    => array('label' => __('Рубрика «Интервью»', 'quterma'), 'default' => 'interview'),
    );

    foreach ($blocks_map as $setting_id => $block_info) {
        $wp_customize->add_setting($setting_id, array(
            'type'              => 'option',
            'default'           => $block_info['default'],
            'sanitize_callback' => 'sanitize_text_field',
        ));
        $wp_customize->add_control($setting_id, array(
            'label'   => $block_info['label'],
            'section' => 'quterma_home_blocks_section',
            'type'    => 'text',
        ));
    }
}
add_action('customize_register', 'quterma_customize_register');
