<?php
/**
 * Gutenberg / Block Editor Integration, Editor Styles, and Block Patterns
 *
 * @package Quterma
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register Gutenberg Block Patterns.
 */
function quterma_register_block_patterns() {
    if (!function_exists('register_block_pattern')) {
        return;
    }

    register_block_pattern_category('quterma', array(
        'label' => __('Кутерьма — Редакторские блоки', 'quterma'),
    ));

    // 1. Article Lede (Лид статьи)
    register_block_pattern(
        'quterma/article-lede',
        array(
            'title'       => __('Лид статьи (вводный абзац)', 'quterma'),
            'description' => __('Крупный вводный абзац перед основным текстом материала', 'quterma'),
            'categories'  => array('quterma'),
            'content'     => '<!-- wp:paragraph {"className":"article-lede"} -->' .
                             '<p class="article-lede">Лид материала — краткое, но самостоятельное изложение сути в двух-трёх предложениях, которое даёт понять, стоит ли читать дальше, ещё до того, как читатель погрузится в текст целиком.</p>' .
                             '<!-- /wp:paragraph -->',
        )
    );

    // 2. Accent Blockquote (Цитата с зеленой акцентной чертой)
    register_block_pattern(
        'quterma/quote-accent',
        array(
            'title'       => __('Цитата с акцентом', 'quterma'),
            'description' => __('Редакционная цитата или прямая речь спикера', 'quterma'),
            'categories'  => array('quterma'),
            'content'     => '<!-- wp:quote -->' .
                             '<blockquote class="wp-block-quote"><p>Прямая речь или цитата — здесь выделяется акцентной полосой и курсивным серифом, чтобы визуально отличаться от основного повествования.</p><cite>— Имя спикера, должность</cite></blockquote>' .
                             '<!-- /wp:quote -->',
        )
    );

    // 3. Comparison Table (Таблица данных)
    register_block_pattern(
        'quterma/comparison-table',
        array(
            'title'       => __('Сравнительная таблица', 'quterma'),
            'description' => __('Стилизованная таблица для сравнительных данных', 'quterma'),
            'categories'  => array('quterma'),
            'content'     => '<!-- wp:table -->' .
                             '<figure class="wp-block-table"><table>' .
                             '<thead><tr><th>Параметр</th><th>Значение А</th><th>Значение Б</th></tr></thead>' .
                             '<tbody>' .
                             '<tr><td>Первый показатель</td><td>Данные</td><td>Данные</td></tr>' .
                             '<tr><td>Второй показатель</td><td>Данные</td><td>Данные</td></tr>' .
                             '<tr><td>Третий показатель</td><td>Данные</td><td>Данные</td></tr>' .
                             '</tbody></table></figure>' .
                             '<!-- /wp:table -->',
        )
    );

    // 4. Info Cards (Карточки принципов/информации)
    register_block_pattern(
        'quterma/info-cards',
        array(
            'title'       => __('Сетка инфо-карточек (3 колонки)', 'quterma'),
            'description' => __('Блок принципов редакции или преимуществ', 'quterma'),
            'categories'  => array('quterma'),
            'content'     => '<div class="info-grid">' .
                             '<div class="info-card"><div class="info-card-title">Независимость</div><div class="info-card-text">Редакционные решения принимаются редакцией — реклама никогда не влияет на содержание материалов.</div></div>' .
                             '<div class="info-card"><div class="info-card-title">Открытость</div><div class="info-card-text">Мы указываем источники, признаём ошибки и отвечаем на вопросы читателей напрямую.</div></div>' .
                             '<div class="info-card"><div class="info-card-title">Люди прежде всего</div><div class="info-card-text">За каждой новостью — конкретные люди. Мы стараемся не терять это из виду ни в одном материале.</div></div>' .
                             '</div>',
        )
    );

    // 5. Stat Row (Строка статистики с цифрами)
    register_block_pattern(
        'quterma/stat-counter',
        array(
            'title'       => __('Строка статистики (цифры)', 'quterma'),
            'description' => __('Строка ключевых показателей для партнерских и информационных страниц', 'quterma'),
            'categories'  => array('quterma'),
            'content'     => '<div class="stat-row">' .
                             '<div><div class="stat-num">120K</div><div class="stat-label">Читателей в месяц</div></div>' .
                             '<div><div class="stat-num">45K</div><div class="stat-label">Подписчиков в Telegram</div></div>' .
                             '<div><div class="stat-num">350+</div><div class="stat-label">Опубликованных историй</div></div>' .
                             '<div><div class="stat-num">100%</div><div class="stat-label">Локальный фокус</div></div>' .
                             '</div>',
        )
    );
}
add_action('init', 'quterma_register_block_patterns');
