<?php
/**
 * Gastroguide Catalog (Гастрогид) - Custom Post Type and Taxonomies
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
 * Render Meta Box fields.
 */
function quterma_venue_render_meta_box($post) {
    wp_nonce_field('quterma_venue_save_meta', 'quterma_venue_nonce');

    $city        = get_post_meta($post->ID, '_venue_city', true);
    $type        = get_post_meta($post->ID, '_venue_type', true);
    $address     = get_post_meta($post->ID, '_venue_address', true);
    $price       = get_post_meta($post->ID, '_venue_price', true);
    $features    = get_post_meta($post->ID, '_venue_features', true);
    $website     = get_post_meta($post->ID, '_venue_website', true);

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
    ?>
    <table class="form-table">
        <tr>
            <th><label for="venue_city"><?php esc_html_e('Город', 'quterma'); ?></label></th>
            <td>
                <select name="venue_city" id="venue_city" style="width:250px;">
                    <?php foreach ($cities as $key => $name) : ?>
                        <option value="<?php echo esc_attr($key); ?>" <?php selected($city, $key); ?>>
                            <?php echo esc_html($name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </td>
        </tr>
        <tr>
            <th><label for="venue_type"><?php esc_html_e('Тип заведения / Кухня', 'quterma'); ?></label></th>
            <td>
                <input type="text" name="venue_type" id="venue_type" value="<?php echo esc_attr($type); ?>" placeholder="Например: Кофейня, Ресторан, Пекарня, Трактиръ" class="regular-text" />
            </td>
        </tr>
        <tr>
            <th><label for="venue_address"><?php esc_html_e('Адрес', 'quterma'); ?></label></th>
            <td>
                <input type="text" name="venue_address" id="venue_address" value="<?php echo esc_attr($address); ?>" placeholder="Например: ул. Кирова, 14" class="regular-text" />
            </td>
        </tr>
        <tr>
            <th><label for="venue_price"><?php esc_html_e('Ценовая категория', 'quterma'); ?></label></th>
            <td>
                <select name="venue_price" id="venue_price">
                    <option value="₽" <?php selected($price, '₽'); ?>>₽ (демократично)</option>
                    <option value="₽₽" <?php selected($price, '₽₽'); ?>>₽₽ (средний чек)</option>
                    <option value="₽₽₽" <?php selected($price, '₽₽₽'); ?>>₽₽₽ (высокий чек)</option>
                </select>
            </td>
        </tr>
        <tr>
            <th><label for="venue_features"><?php esc_html_e('Особенности (через запятую)', 'quterma'); ?></label></th>
            <td>
                <input type="text" name="venue_features" id="venue_features" value="<?php echo esc_attr($features); ?>" placeholder="Веранда, С животными, Веган-меню, Работает допоздна" class="large-text" />
            </td>
        </tr>
        <tr>
            <th><label for="venue_website"><?php esc_html_e('Ссылка (сайт/соцсети)', 'quterma'); ?></label></th>
            <td>
                <input type="url" name="venue_website" id="venue_website" value="<?php echo esc_attr($website); ?>" placeholder="https://..." class="regular-text" />
            </td>
        </tr>
    </table>
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

    if (isset($_POST['venue_city'])) {
        update_post_meta($post_id, '_venue_city', sanitize_text_field($_POST['venue_city']));
    }
    if (isset($_POST['venue_type'])) {
        update_post_meta($post_id, '_venue_type', sanitize_text_field($_POST['venue_type']));
    }
    if (isset($_POST['venue_address'])) {
        update_post_meta($post_id, '_venue_address', sanitize_text_field($_POST['venue_address']));
    }
    if (isset($_POST['venue_price'])) {
        update_post_meta($post_id, '_venue_price', sanitize_text_field($_POST['venue_price']));
    }
    if (isset($_POST['venue_features'])) {
        update_post_meta($post_id, '_venue_features', sanitize_text_field($_POST['venue_features']));
    }
    if (isset($_POST['venue_website'])) {
        update_post_meta($post_id, '_venue_website', esc_url_raw($_POST['venue_website']));
    }
}
add_action('save_post_quterma_venue', 'quterma_venue_save_meta');

/**
 * Custom Admin Columns for Gastroguide.
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

/**
 * Helper to get readable Russian city name from slug.
 */
function quterma_get_city_name($slug) {
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
    return isset($cities[$slug]) ? $cities[$slug] : $slug;
}
