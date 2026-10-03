<?php
/**
 * Gastroguide Catalog (Гастрогид) - Custom Post Type, Taxonomies, and Custom Fields
 *
 * @package Quterma
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register 'quterma_venue' Custom Post Type.
 */
function quterma_register_gastroguide_cpt() {
    $labels = array(
        'name'               => __('Гастрогид', 'quterma'),
        'singular_name'      => __('Заведение', 'quterma'),
        'menu_name'          => __('Гастрогид', 'quterma'),
        'name_admin_bar'     => __('Заведение гастрогида', 'quterma'),
        'add_new'            => __('Добавить заведение', 'quterma'),
        'add_new_item'       => __('Добавить новое заведение', 'quterma'),
        'new_item'           => __('Новое заведение', 'quterma'),
        'edit_item'          => __('Редактировать заведение', 'quterma'),
        'view_item'          => __('Посмотреть заведение', 'quterma'),
        'all_items'          => __('Все заведения', 'quterma'),
        'search_items'       => __('Искать заведения', 'quterma'),
        'not_found'          => __('Заведений не найдено', 'quterma'),
        'not_found_in_trash' => __('В корзине заведений не найдено', 'quterma'),
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => array('slug' => 'gastroguide', 'with_front' => false),
        'capability_type'    => 'post',
        'has_archive'        => true,
        'hierarchical'       => false,
        'menu_position'      => 6,
        'menu_icon'          => 'dashicons-food',
        'supports'           => array('title', 'editor', 'thumbnail', 'excerpt'),
        'taxonomies'         => array('quterma_city', 'post_tag'),
        'show_in_rest'       => true,
    );

    register_post_type('quterma_venue', $args);

    // Register City taxonomy for gastroguide
    register_taxonomy('quterma_city', array('quterma_venue'), array(
        'labels'            => array(
            'name'          => __('Города', 'quterma'),
            'singular_name' => __('Город', 'quterma'),
            'search_items'  => __('Искать город', 'quterma'),
            'all_items'     => __('Все города', 'quterma'),
            'edit_item'     => __('Редактировать город', 'quterma'),
            'update_item'   => __('Обновить город', 'quterma'),
            'add_new_item'  => __('Добавить новый город', 'quterma'),
            'new_item_name' => __('Название нового города', 'quterma'),
            'menu_name'     => __('Города', 'quterma'),
        ),
        'hierarchical'      => true,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array('slug' => 'gastroguide-city'),
        'show_in_rest'      => true,
    ));
}
add_action('init', 'quterma_register_gastroguide_cpt');

/**
 * Register post metadata for Gutenberg / REST API
 */
function quterma_register_venue_meta() {
    $meta_fields = array(
        '_venue_city'     => 'string',
        '_venue_type'     => 'string',
        '_venue_address'  => 'string',
        '_venue_price'    => 'string',
        '_venue_features' => 'string',
        '_venue_website'  => 'string',
        '_venue_phone'    => 'string',
        '_venue_hours'    => 'string',
    );

    foreach ($meta_fields as $key => $type) {
        register_post_meta('quterma_venue', $key, array(
            'show_in_rest'      => true,
            'single'            => true,
            'type'              => $type,
            'sanitize_callback' => ($key === '_venue_website') ? 'esc_url_raw' : 'sanitize_text_field',
            'auth_callback'     => function () {
                return current_user_can('edit_posts');
            },
        ));
    }
}
add_action('init', 'quterma_register_venue_meta');

/**
 * Add Meta Box for Venue details.
 */
function quterma_venue_add_meta_box() {
    add_meta_box(
        'quterma_venue_details',
        __('Параметры заведения (Гастрогид)', 'quterma'),
        'quterma_venue_render_meta_box',
        'quterma_venue',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'quterma_venue_add_meta_box');

/**
 * Render Meta Box fields with intuitive controls.
 */
function quterma_venue_render_meta_box($post) {
    wp_nonce_field('quterma_venue_save_meta', 'quterma_venue_nonce');

    $city        = get_post_meta($post->ID, '_venue_city', true);
    if (empty($city)) {
        $city = 'yaroslavl';
    }
    $type        = get_post_meta($post->ID, '_venue_type', true);
    $address     = get_post_meta($post->ID, '_venue_address', true);
    $price       = get_post_meta($post->ID, '_venue_price', true);
    if (empty($price)) {
        $price = '₽₽';
    }
    $features    = get_post_meta($post->ID, '_venue_features', true);
    $website     = get_post_meta($post->ID, '_venue_website', true);
    $phone       = get_post_meta($post->ID, '_venue_phone', true);
    $hours       = get_post_meta($post->ID, '_venue_hours', true);

    $cities = array(
        'yaroslavl'    => 'Ярославль',
        'rybinsk'      => 'Рыбинск',
        'rostov'       => 'Ростов Великий',
        'pereslavl'    => 'Переславль-Залесский',
        'tutaev'       => 'Тутаев',
        'uglich'       => 'Углич',
        'gavrilov-yam' => 'Гаврилов-Ям',
        'danilov'      => 'Данилов',
        'lyubim'       => 'Любим',
        'myshkin'      => 'Мышкин',
        'poshekhonye'  => 'Пошехонье',
        'breytovo'     => 'Брейтово',
    );

    // Predefined features in lowercase for comparison
    $features_lower = mb_strtolower($features);
    $has_veranda = (mb_strpos($features_lower, 'веранда') !== false);
    $has_pets    = (mb_strpos($features_lower, 'животн') !== false || mb_strpos($features_lower, 'pet') !== false);
    $has_vegan   = (mb_strpos($features_lower, 'веган') !== false || mb_strpos($features_lower, 'vegan') !== false);
    $has_late    = (mb_strpos($features_lower, 'допоздна') !== false || mb_strpos($features_lower, '24') !== false);
    ?>
    <style>
      .qv-row { margin-bottom: 16px; }
      .qv-row label.qv-label { display: block; font-weight: 600; margin-bottom: 6px; font-size: 13px; }
      .qv-presets { margin-top: 6px; display: flex; flex-wrap: wrap; gap: 6px; }
      .qv-preset-btn { cursor: pointer; padding: 3px 9px; font-size: 11px; border: 1px solid #ccc; background: #f0f0f1; border-radius: 3px; }
      .qv-preset-btn:hover { background: #e0e0e0; }
      .qv-features-grid { display: flex; flex-wrap: wrap; gap: 14px; margin-bottom: 8px; }
      .qv-features-grid label { display: flex; align-items: center; gap: 6px; font-weight: normal; cursor: pointer; }
    </style>

    <div class="qv-row">
        <label class="qv-label" for="venue_city"><?php esc_html_e('Город заведения', 'quterma'); ?></label>
        <select name="venue_city" id="venue_city" style="width:100%;max-width:320px;">
            <?php foreach ($cities as $key => $name) : ?>
                <option value="<?php echo esc_attr($key); ?>" <?php selected($city, $key); ?>>
                    <?php echo esc_html($name); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <p class="description"><?php esc_html_e('Автоматически синхронизируется с рубрикой городов в сайдбаре.', 'quterma'); ?></p>
    </div>

    <div class="qv-row">
        <label class="qv-label" for="venue_type"><?php esc_html_e('Формат / Кухня', 'quterma'); ?></label>
        <input type="text" name="venue_type" id="venue_type" value="<?php echo esc_attr($type); ?>" placeholder="<?php esc_attr_e('Например: Кофейня, Ресторан, Пекарня, Стрит-фуд', 'quterma'); ?>" style="width:100%;max-width:400px;" />
        <div class="qv-presets">
            <span style="font-size:11px;color:#666;align-self:center"><?php esc_html_e('Быстрый выбор:', 'quterma'); ?></span>
            <button type="button" class="qv-preset-btn" onclick="document.getElementById('venue_type').value='Кофейня';">Кофейня</button>
            <button type="button" class="qv-preset-btn" onclick="document.getElementById('venue_type').value='Ресторан';">Ресторан</button>
            <button type="button" class="qv-preset-btn" onclick="document.getElementById('venue_type').value='Кафе';">Кафе</button>
            <button type="button" class="qv-preset-btn" onclick="document.getElementById('venue_type').value='Пекарня';">Пекарня</button>
            <button type="button" class="qv-preset-btn" onclick="document.getElementById('venue_type').value='Стрит-фуд';">Стрит-фуд</button>
            <button type="button" class="qv-preset-btn" onclick="document.getElementById('venue_type').value='Бар / Паб';">Бар / Паб</button>
        </div>
    </div>

    <div class="qv-row">
        <label class="qv-label"><?php esc_html_e('Ценовой диапазон (средний чек)', 'quterma'); ?></label>
        <div style="display:flex;gap:18px;margin-top:4px">
            <label style="cursor:pointer">
                <input type="radio" name="venue_price" value="₽" <?php checked($price, '₽'); ?>>
                <strong>₽</strong> (демократично, до 700 ₽)
            </label>
            <label style="cursor:pointer">
                <input type="radio" name="venue_price" value="₽₽" <?php checked($price, '₽₽'); ?>>
                <strong>₽₽</strong> (средний чек, 700–1500 ₽)
            </label>
            <label style="cursor:pointer">
                <input type="radio" name="venue_price" value="₽₽₽" <?php checked($price, '₽₽₽'); ?>>
                <strong>₽₽₽</strong> (высокий чек, от 1500 ₽)
            </label>
        </div>
    </div>

    <div class="qv-row">
        <label class="qv-label"><?php esc_html_e('Особенности заведения (для фильтров каталога)', 'quterma'); ?></label>
        <div class="qv-features-grid">
            <label>
                <input type="checkbox" name="qv_feat_veranda" value="1" <?php checked($has_veranda); ?>>
                <?php esc_html_e('Веранда (летняя терраса)', 'quterma'); ?>
            </label>
            <label>
                <input type="checkbox" name="qv_feat_pets" value="1" <?php checked($has_pets); ?>>
                <?php esc_html_e('С животными (pet-friendly)', 'quterma'); ?>
            </label>
            <label>
                <input type="checkbox" name="qv_feat_vegan" value="1" <?php checked($has_vegan); ?>>
                <?php esc_html_e('Веган-меню', 'quterma'); ?>
            </label>
            <label>
                <input type="checkbox" name="qv_feat_late" value="1" <?php checked($has_late); ?>>
                <?php esc_html_e('Работает допоздна', 'quterma'); ?>
            </label>
        </div>
        <input type="text" name="venue_features_custom" id="venue_features_custom" value="<?php echo esc_attr($features); ?>" placeholder="<?php esc_attr_e('Дополнительные особенности через запятую (например: завтраки весь день, спешелти кофе)', 'quterma'); ?>" style="width:100%;max-width:600px;" />
    </div>

    <div class="qv-row">
        <label class="qv-label" for="venue_address"><?php esc_html_e('Точный адрес', 'quterma'); ?></label>
        <input type="text" name="venue_address" id="venue_address" value="<?php echo esc_attr($address); ?>" placeholder="<?php esc_attr_e('Например: ул. Кирова, 14', 'quterma'); ?>" style="width:100%;max-width:500px;" />
    </div>

    <div class="qv-row">
        <label class="qv-label" for="venue_website"><?php esc_html_e('Официальный сайт или страница в соцсетях', 'quterma'); ?></label>
        <input type="url" name="venue_website" id="venue_website" value="<?php echo esc_attr($website); ?>" placeholder="https://..." style="width:100%;max-width:500px;" />
    </div>

    <div style="display:flex;gap:20px;flex-wrap:wrap">
        <div class="qv-row" style="flex:1;min-width:240px">
            <label class="qv-label" for="venue_phone"><?php esc_html_e('Контактный телефон (необязательно)', 'quterma'); ?></label>
            <input type="text" name="venue_phone" id="venue_phone" value="<?php echo esc_attr($phone); ?>" placeholder="+7 (4852) 00-00-00" style="width:100%" />
        </div>
        <div class="qv-row" style="flex:1;min-width:240px">
            <label class="qv-label" for="venue_hours"><?php esc_html_e('Режим работы (необязательно)', 'quterma'); ?></label>
            <input type="text" name="venue_hours" id="venue_hours" value="<?php echo esc_attr($hours); ?>" placeholder="Ежедневно 09:00 — 23:00" style="width:100%" />
        </div>
    </div>
    <?php
}

/**
 * Save Venue Meta Box fields.
 */
function quterma_venue_save_meta($post_id) {
    if (!isset($_POST['quterma_venue_nonce']) || !wp_verify_nonce($_POST['quterma_venue_nonce'], 'quterma_venue_save_meta')) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    // 1. City
    if (isset($_POST['venue_city'])) {
        $city = sanitize_text_field($_POST['venue_city']);
        update_post_meta($post_id, '_venue_city', $city);

        // Sync with taxonomy quterma_city
        $city_name = quterma_get_city_name($city);
        $term = term_exists($city, 'quterma_city');
        if (!$term) {
            $term = wp_insert_term($city_name, 'quterma_city', array('slug' => $city));
        }
        if ($term && !is_wp_error($term)) {
            $term_id = is_array($term) ? $term['term_id'] : $term;
            wp_set_post_terms($post_id, array((int) $term_id), 'quterma_city');
        }
    }

    // 2. Type
    if (isset($_POST['venue_type'])) {
        update_post_meta($post_id, '_venue_type', sanitize_text_field($_POST['venue_type']));
    }

    // 3. Address
    if (isset($_POST['venue_address'])) {
        update_post_meta($post_id, '_venue_address', sanitize_text_field($_POST['venue_address']));
    }

    // 4. Price
    if (isset($_POST['venue_price'])) {
        update_post_meta($post_id, '_venue_price', sanitize_text_field($_POST['venue_price']));
    }

    // 5. Features (combine checkboxes and custom input)
    $feat_tokens = array();
    if (!empty($_POST['qv_feat_veranda'])) {
        $feat_tokens[] = 'Веранда';
    }
    if (!empty($_POST['qv_feat_pets'])) {
        $feat_tokens[] = 'С животными';
    }
    if (!empty($_POST['qv_feat_vegan'])) {
        $feat_tokens[] = 'Веган-меню';
    }
    if (!empty($_POST['qv_feat_late'])) {
        $feat_tokens[] = 'Работает допоздна';
    }
    if (!empty($_POST['venue_features_custom'])) {
        $custom_parts = explode(',', sanitize_text_field($_POST['venue_features_custom']));
        foreach ($custom_parts as $cp) {
            $cp = trim($cp);
            if (!empty($cp) && !in_array($cp, $feat_tokens, true)) {
                $feat_tokens[] = $cp;
            }
        }
    }
    $features_str = implode(', ', $feat_tokens);
    update_post_meta($post_id, '_venue_features', $features_str);

    // 6. Website
    if (isset($_POST['venue_website'])) {
        update_post_meta($post_id, '_venue_website', esc_url_raw($_POST['venue_website']));
    }

    // 7. Phone
    if (isset($_POST['venue_phone'])) {
        update_post_meta($post_id, '_venue_phone', sanitize_text_field($_POST['venue_phone']));
    }

    // 8. Hours
    if (isset($_POST['venue_hours'])) {
        update_post_meta($post_id, '_venue_hours', sanitize_text_field($_POST['venue_hours']));
    }
}
add_action('save_post_quterma_venue', 'quterma_venue_save_meta');

/**
 * Custom Admin Columns for Gastroguide list.
 */
function quterma_venue_columns($columns) {
    $new_cols = array();
    foreach ($columns as $k => $v) {
        $new_cols[$k] = $v;
        if ($k === 'title') {
            $new_cols['venue_city']    = __('Город', 'quterma');
            $new_cols['venue_type']    = __('Тип', 'quterma');
            $new_cols['venue_address'] = __('Адрес', 'quterma');
            $new_cols['venue_price']   = __('Цена', 'quterma');
        }
    }
    return $new_cols;
}
add_filter('manage_quterma_venue_posts_columns', 'quterma_venue_columns');

function quterma_venue_custom_column($column, $post_id) {
    if ($column === 'venue_city') {
        $city = get_post_meta($post_id, '_venue_city', true);
        echo esc_html(quterma_get_city_name($city));
    }
    if ($column === 'venue_type') {
        echo esc_html(get_post_meta($post_id, '_venue_type', true));
    }
    if ($column === 'venue_address') {
        echo esc_html(get_post_meta($post_id, '_venue_address', true));
    }
    if ($column === 'venue_price') {
        echo esc_html(get_post_meta($post_id, '_venue_price', true));
    }
}
add_action('manage_quterma_venue_posts_custom_column', 'quterma_venue_custom_column', 10, 2);
