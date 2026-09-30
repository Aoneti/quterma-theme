<?php
/**
 * Theme Customizer Settings and Controls
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

    // Section 1: Контакты и социальные сети
    $wp_customize->add_section('quterma_contacts_section', array(
        'title'    => __('Контакты и соцсети', 'quterma'),
        'panel'    => 'quterma_panel',
        'priority' => 10,
    ));

    $wp_customize->add_setting('quterma_telegram', array(
        'default'           => 'https://t.me/kuterma',
        'sanitize_callback' => 'esc_url_raw',
    ));
    $wp_customize->add_control('quterma_telegram', array(
        'label'    => __('Telegram канал', 'quterma'),
        'section'  => 'quterma_contacts_section',
        'type'     => 'url',
    ));

    $wp_customize->add_setting('quterma_vk', array(
        'default'           => 'https://vk.com/kuterma',
        'sanitize_callback' => 'esc_url_raw',
    ));
    $wp_customize->add_control('quterma_vk', array(
        'label'    => __('Сообщество ВКонтакте', 'quterma'),
        'section'  => 'quterma_contacts_section',
        'type'     => 'url',
    ));

    $wp_customize->add_setting('quterma_email', array(
        'default'           => 'quterma@yandex.ru',
        'sanitize_callback' => 'sanitize_email',
    ));
    $wp_customize->add_control('quterma_email', array(
        'label'    => __('Email редакции', 'quterma'),
        'section'  => 'quterma_contacts_section',
        'type'     => 'email',
    ));

    $wp_customize->add_setting('quterma_footer_about', array(
        'default'           => 'Независимое городское издание о Ярославле. С 2026 года.',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('quterma_footer_about', array(
        'label'    => __('Текст о редакции в подвале', 'quterma'),
        'section'  => 'quterma_contacts_section',
        'type'     => 'textarea',
    ));

    $wp_customize->add_setting('quterma_marquee_text', array(
        'default'           => 'Что происходит?',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('quterma_marquee_text', array(
        'label'    => __('Текст бегущей строки в подвале', 'quterma'),
        'section'  => 'quterma_contacts_section',
        'type'     => 'text',
    ));

    // Section 2: Аналитика и вебмастера
    $wp_customize->add_section('quterma_analytics_section', array(
        'title'    => __('Аналитика и вебмастера', 'quterma'),
        'panel'    => 'quterma_panel',
        'priority' => 20,
    ));

    $wp_customize->add_setting('quterma_ym_id', array(
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
        'default'           => true,
        'sanitize_callback' => 'wp_validate_boolean',
    ));
    $wp_customize->add_control('quterma_ym_webvisor', array(
        'label'   => __('Включить Яндекс Вебвизор', 'quterma'),
        'section' => 'quterma_analytics_section',
        'type'    => 'checkbox',
    ));

    $wp_customize->add_setting('quterma_ga4_id', array(
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
        'default'           => false,
        'sanitize_callback' => 'wp_validate_boolean',
    ));
    $wp_customize->add_control('quterma_liveinternet_enabled', array(
        'label'   => __('Включить счётчик LiveInternet', 'quterma'),
        'section' => 'quterma_analytics_section',
        'type'    => 'checkbox',
    ));

    // Section 3: Настройки API «Культура.РФ»
    $wp_customize->add_section('quterma_culture_api_section', array(
        'title'    => __('API «Культура.РФ» (События)', 'quterma'),
        'panel'    => 'quterma_panel',
        'priority' => 30,
    ));

    $wp_customize->add_setting('quterma_culture_api_key', array(
        'default'           => '',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('quterma_culture_api_key', array(
        'label'       => __('API-ключ ЕИПСК / Культура.РФ (опционально)', 'quterma'),
        'description' => __('Если у вас есть персональный ключ PRO.Культура.РФ / ЕИПСК, укажите его здесь.', 'quterma'),
        'section'     => 'quterma_culture_api_section',
        'type'        => 'text',
    ));
}
add_action('customize_register', 'quterma_customize_register');
