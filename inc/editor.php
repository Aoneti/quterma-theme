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
                             '<div><div class="stat-num">—</div><div class="stat-label">Показатель 1</div></div>' .
                             '<div><div class="stat-num">—</div><div class="stat-label">Показатель 2</div></div>' .
                             '<div><div class="stat-num">—</div><div class="stat-label">Показатель 3</div></div>' .
                             '<div><div class="stat-num">—</div><div class="stat-label">Показатель 4</div></div>' .
                             '</div>',
        )
    );

    // 6. Interview Dialogue (Реплики диалога интервью: вопрос и ответ)
    register_block_pattern(
        'quterma/interview-dialog',
        array(
            'title'       => __('Диалог интервью (Вопрос и Ответ)', 'quterma'),
            'description' => __('Оформленные реплики редакции и собеседника в интервью', 'quterma'),
            'categories'  => array('quterma'),
            'content'     => '<div class="interview-dialog">' .
                             '<div class="dialog-turn turn-q"><strong class="dialog-speaker">— Редакция:</strong> Как начинался проект и с какими главными сложностями пришлось столкнуться?</div>' .
                             '<div class="dialog-turn turn-a"><strong class="dialog-speaker">— Герой интервью:</strong> Самым важным было сохранить историческую ткань и аутентичные детали здания. Мы провели в архивах не один месяц.</div>' .
                             '</div>',
        )
    );

    // 7. Photo Story with Caption (Фотоистория с подписью)
    register_block_pattern(
        'quterma/photo-story',
        array(
            'title'       => __('Фотоистория с акцентной подписью', 'quterma'),
            'description' => __('Широкая фотография с подписью автора для репортажей и историй', 'quterma'),
            'categories'  => array('quterma'),
            'content'     => '<figure class="wp-block-image alignwide photo-story-figure">' .
                             '<img src="" alt="Фотография репортажа" loading="lazy" />' .
                             '<figcaption>Исторический фасад после реставрации. Автор снимка: редакция издания</figcaption>' .
                             '</figure>',
        )
    );
}
add_action('init', 'quterma_register_block_patterns');

/**
 * Register post and page metadata for Gutenberg / REST API and classic editor
 */
function quterma_register_editorial_meta() {
    // 1. Interview Hero Name (_iv_person) on posts and pages
    foreach (array('post', 'page') as $ptype) {
        register_post_meta($ptype, '_iv_person', array(
            'show_in_rest'      => true,
            'single'            => true,
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'auth_callback'     => function () {
                return current_user_can('edit_posts');
            },
        ));

        // 2. Interview Hero Role / Occupation (_iv_role) on posts and pages
        register_post_meta($ptype, '_iv_role', array(
            'show_in_rest'      => true,
            'single'            => true,
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'auth_callback'     => function () {
                return current_user_can('edit_posts');
            },
        ));
    }

    // 3. Page Subtitle (_page_subtitle) on pages
    register_post_meta('page', '_page_subtitle', array(
        'show_in_rest'      => true,
        'single'             => true,
        'type'               => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'auth_callback'     => function () {
            return current_user_can('edit_pages');
        },
    ));
}
add_action('init', 'quterma_register_editorial_meta');

/**
 * Add editorial meta boxes in WP Admin
 */
function quterma_add_editorial_meta_boxes() {
    // Interview fields on posts and pages
    foreach (array('post', 'page') as $screen) {
        add_meta_box(
            'quterma_interview_meta_box',
            __('Параметры интервью (герой и должность)', 'quterma'),
            'quterma_render_interview_meta_box',
            $screen,
            'normal',
            'high'
        );
    }

    // Subtitle field on pages
    add_meta_box(
        'quterma_page_meta_box',
        __('Подзаголовок страницы', 'quterma'),
        'quterma_render_page_meta_box',
        'page',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'quterma_add_editorial_meta_boxes');

/**
 * Render interview meta box
 */
function quterma_render_interview_meta_box($post) {
    wp_nonce_field('quterma_interview_meta_nonce_action', 'quterma_interview_meta_nonce');
    $person = get_post_meta($post->ID, '_iv_person', true);
    $role   = get_post_meta($post->ID, '_iv_role', true);
    ?>
    <p class="description" style="margin-bottom:12px">
        <?php esc_html_e('Заполняется для материалов рубрики «Интервью». Если поля оставлены пустыми, в качестве имени героя автоматически используется заголовок статьи.', 'quterma'); ?>
    </p>
    <p>
        <label for="quterma_iv_person"><strong><?php esc_html_e('Имя и фамилия героя:', 'quterma'); ?></strong></label><br>
        <input type="text" id="quterma_iv_person" name="_iv_person" value="<?php echo esc_attr($person); ?>" style="width:100%;max-width:500px;" placeholder="<?php esc_attr_e('Например: Андрей Данилов', 'quterma'); ?>">
    </p>
    <p>
        <label for="quterma_iv_role"><strong><?php esc_html_e('Род занятий / должность / регалии:', 'quterma'); ?></strong></label><br>
        <input type="text" id="quterma_iv_role" name="_iv_role" value="<?php echo esc_attr($role); ?>" style="width:100%;max-width:500px;" placeholder="<?php esc_attr_e('Например: архитектор-реставратор, краевед', 'quterma'); ?>">
    </p>
    <?php
}

/**
 * Render page subtitle meta box
 */
function quterma_render_page_meta_box($post) {
    wp_nonce_field('quterma_page_meta_nonce_action', 'quterma_page_meta_nonce');
    $subtitle = get_post_meta($post->ID, '_page_subtitle', true);
    ?>
    <p class="description" style="margin-bottom:12px">
        <?php esc_html_e('Подзаголовок выводится крупным шрифтом под заголовком страницы (также можно использовать стандартную цитату страницы / excerpt).', 'quterma'); ?>
    </p>
    <p>
        <input type="text" id="quterma_page_subtitle" name="_page_subtitle" value="<?php echo esc_attr($subtitle); ?>" style="width:100%;" placeholder="<?php esc_attr_e('Краткое описание или лид страницы…', 'quterma'); ?>">
    </p>
    <?php
}

/**
 * Save editorial meta boxes data
 */
function quterma_save_editorial_meta($post_id) {
    // Check autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // Save interview meta
    if (isset($_POST['quterma_interview_meta_nonce']) && wp_verify_nonce($_POST['quterma_interview_meta_nonce'], 'quterma_interview_meta_nonce_action')) {
        if (current_user_can('edit_post', $post_id) || current_user_can('edit_page', $post_id)) {
            if (isset($_POST['_iv_person'])) {
                update_post_meta($post_id, '_iv_person', sanitize_text_field($_POST['_iv_person']));
            }
            if (isset($_POST['_iv_role'])) {
                update_post_meta($post_id, '_iv_role', sanitize_text_field($_POST['_iv_role']));
            }
        }
    }

    // Save page subtitle meta
    if (isset($_POST['quterma_page_meta_nonce']) && wp_verify_nonce($_POST['quterma_page_meta_nonce'], 'quterma_page_meta_nonce_action')) {
        if (current_user_can('edit_post', $post_id) || current_user_can('edit_page', $post_id)) {
            if (isset($_POST['_page_subtitle'])) {
                update_post_meta($post_id, '_page_subtitle', sanitize_text_field($_POST['_page_subtitle']));
            }
        }
    }
}
add_action('save_post', 'quterma_save_editorial_meta');

/**
 * Enqueue Gutenberg Block Editor Sidebar assets
 */
function quterma_enqueue_block_editor_assets() {
    $asset_path = get_template_directory() . '/assets/js/editor.js';
    if (file_exists($asset_path)) {
        wp_enqueue_script(
            'quterma-editor-sidebar',
            get_template_directory_uri() . '/assets/js/editor.js',
            array('wp-plugins', 'wp-edit-post', 'wp-element', 'wp-components', 'wp-data'),
            defined('QUTERMA_VERSION') ? QUTERMA_VERSION : '2.1.0',
            true
        );
    }
}
add_action('enqueue_block_editor_assets', 'quterma_enqueue_block_editor_assets');

