<?php
/**
 * Theme setup and basic configuration
 *
 * @package Quterma
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Sets up theme defaults and registers support for various WordPress features.
 */
function quterma_setup() {
    // Make theme available for translation.
    load_theme_textdomain('quterma', get_template_directory() . '/languages');

    // Add default posts and comments RSS feed links to head.
    add_theme_support('automatic-feed-links');

    // Let WordPress manage the document title.
    add_theme_support('title-tag');

    // Enable support for Post Thumbnails on posts and pages.
    add_theme_support('post-thumbnails');

    // Switch default core markup for search form, gallery, and caption to output valid HTML5.
    add_theme_support('html5', array(
        'search-form',
        'gallery',
        'caption',
        'style',
        'script',
        'navigation-widgets',
    ));

    // Support Gutenberg features
    add_theme_support('responsive-embeds');
    add_theme_support('align-wide');
    add_theme_support('editor-styles');
    add_editor_style('assets/css/editor-style.css');

    // Custom background & logo (if needed by admin)
    add_theme_support('custom-logo', array(
        'height'      => 40,
        'width'       => 200,
        'flex-height' => true,
        'flex-width'  => true,
    ));

    // Set content width for responsive media
    if (!isset($GLOBALS['content_width'])) {
        $GLOBALS['content_width'] = 960;
    }
}
add_action('after_setup_theme', 'quterma_setup');

/**
 * Register navigation menus on 'init' hook to prevent _load_textdomain_just_in_time notice in WordPress 6.7+
 */
function quterma_register_menus() {
    register_nav_menus(array(
        'primary' => __('Главное меню (Шапка)', 'quterma'),
        'mobile'  => __('Мобильное меню', 'quterma'),
        'footer'  => __('Меню подвала', 'quterma'),
    ));
}
add_action('init', 'quterma_register_menus');

/**
 * COMPLETELY DISABLE COMMENTS IN WORDPRESS
 * Per requirements:
 * "Комментариев на сайте НЕТ и не будет.
 * Функционал комментариев необходимо полностью отключить на уровне темы."
 */

// 1. Close comments on the front-end and remove comments feed link
add_filter('comments_open', '__return_false', 20, 2);
add_filter('pings_open', '__return_false', 20, 2);
add_filter('feed_links_show_comments_feed', '__return_false');

// 2. Hide existing comments
add_filter('comments_array', '__return_empty_array', 10, 2);

// 3. Remove comments page in admin menu
add_action('admin_menu', function () {
    remove_menu_page('edit-comments.php');
});

// 4. Redirect any user trying to access comments page in admin
add_action('admin_init', function () {
    global $pagenow;
    if ($pagenow === 'edit-comments.php') {
        wp_safe_redirect(admin_url());
        exit;
    }

    // Remove comments metabox from post types
    $post_types = get_post_types();
    foreach ($post_types as $post_type) {
        if (post_type_supports($post_type, 'comments')) {
            remove_post_type_support($post_type, 'comments');
            remove_post_type_support($post_type, 'trackbacks');
        }
    }
});

// 5. Remove comments-related items from admin bar
add_action('wp_before_admin_bar_render', function () {
    global $wp_admin_bar;
    if ($wp_admin_bar) {
        $wp_admin_bar->remove_menu('comments');
    }
});

// 6. Disable comments feed across all feed formats
$quterma_disable_comments_feed = function ($is_comment_feed = false) {
    if ($is_comment_feed || (function_exists('is_comment_feed') && is_comment_feed())) {
        wp_die(__('Комментарии на сайте отключены.', 'quterma'), '', array('response' => 403));
    }
};
add_action('do_feed_rss2', $quterma_disable_comments_feed, 1, 1);
add_action('do_feed_atom', $quterma_disable_comments_feed, 1, 1);
add_action('do_feed_rss',  $quterma_disable_comments_feed, 1, 1);
add_action('do_feed_rdf',  $quterma_disable_comments_feed, 1, 1);

// 7. Register theme rewrite rules for core endpoints so WordPress natively recognizes them
function quterma_register_rewrite_rules() {
    add_rewrite_rule('^gastroguide/?$', 'index.php?pagename=gastroguide', 'top');
    add_rewrite_rule('^events/?$', 'index.php?pagename=events', 'top');
    add_rewrite_rule('^interview/?$', 'index.php?pagename=interview', 'top');
    add_rewrite_rule('^rubrics/?$', 'index.php?pagename=rubrics', 'top');
    add_rewrite_rule('^about/?$', 'index.php?pagename=about', 'top');
    add_rewrite_rule('^advertising/?$', 'index.php?pagename=advertising', 'top');
}
add_action('init', 'quterma_register_rewrite_rules');

// Flush rewrite rules upon theme activation
add_action('after_switch_theme', function () {
    quterma_register_rewrite_rules();
    flush_rewrite_rules();
});

/**
 * Ensure default WordPress categories exist upon activation/init
 */
function quterma_ensure_default_categories() {
    $default_cats = array(
        'city'      => array('name' => 'Город', 'desc' => 'Городская среда, архитектура, урбанистика и благоустройство Ярославля'),
        'culture'   => array('name' => 'Культура', 'desc' => 'Культурная жизнь, театры, выставки и фестивали'),
        'art'       => array('name' => 'Искусство', 'desc' => 'Современное и классическое искусство, художники и галереи'),
        'people'    => array('name' => 'Люди', 'desc' => 'Портреты горожан, мастеров, краеведов и создателей'),
        'history'   => array('name' => 'История', 'desc' => 'Краеведение, зона ЮНЕСКО, купеческий Ярославль, архивные хроники'),
        'interview' => array('name' => 'Интервью', 'desc' => 'Диалоги с архитекторами, историками и деятелями культуры'),
    );
    foreach ($default_cats as $slug => $data) {
        if (!get_category_by_slug($slug)) {
            wp_insert_term($data['name'], 'category', array(
                'slug'        => $slug,
                'description' => $data['desc'],
            ));
        }
    }
}
add_action('after_switch_theme', 'quterma_ensure_default_categories');
add_action('admin_init', 'quterma_ensure_default_categories');

/**
 * 8. Auto-provision standard pages if they don't exist yet in WP Admin -> Pages.
 * Ensures that /gastroguide/, /events/, /interview/, /rubrics/, /about/, /advertising/
 * always have concrete editable WordPress Pages.
 */
function quterma_auto_create_core_pages() {
    $pages = array(
        'gastroguide' => array(
            'title'    => 'Гастрогид',
            'excerpt'  => 'Рестораны, бистро, кофейни, волжская гастрономия и барная карта',
            'content'  => '',
            'template' => 'page-gastroguide.php',
        ),
        'events' => array(
            'title'    => 'События',
            'excerpt'  => 'Афиша событий города, спектакли, выставки, фестивали и концерты',
            'content'  => '',
            'template' => 'page-events.php',
        ),
        'interview' => array(
            'title'    => 'Интервью',
            'excerpt'  => 'Диалоги с интересными людьми, архитекторами, историками и деятелями культуры',
            'content'  => '',
            'template' => 'page-interview.php',
        ),
        'rubrics' => array(
            'title'    => 'Рубрики',
            'excerpt'  => 'Тематические рубрики и направления городского издания «Кутерьма»',
            'content'  => '',
            'template' => 'page-rubrics.php',
        ),
        'about' => array(
            'title'    => 'О редакции',
            'excerpt'  => 'Независимое городское медиа о культуре, истории и людях Ярославля',
            'content'  => '<p class="lead">«Кутерьма» — независимое городское интернет-издание о культуре, истории, архитектуре и людях Ярославля и волжских городов.</p><p>Мы пишем о городской среде, реставрации памятников, краеведении, современном искусстве и гастрономии. Наша цель — документировать жизнь города и рассказывать о тех, кто создает его облик.</p><h2>Связь с редакцией</h2><p>По вопросам публикации материалов, анонсов и сотрудничества: <strong>redaktsiya@quterma.ru</strong></p>',
            'template' => 'page.php',
        ),
        'advertising' => array(
            'title'    => 'Реклама и партнёрство',
            'excerpt'  => 'Форматы нативной рекламы, спецпроекты и интеграции для локальных брендов',
            'content'  => '<p class="lead">Мы предлагаем нативные спецпроекты, репортажи, фотоистории и интеграции в рубриках «Гастрогид» и «Культурный слой».</p><p>Для получения медиакита и обсуждения условий партнерства пишите: <strong>reklama@quterma.ru</strong></p>',
            'template' => 'page.php',
        ),
        'news' => array(
            'title'    => 'Все новости',
            'excerpt'  => 'Хроника событий, новости городской жизни, культуры и общества Ярославля',
            'content'  => '',
            'template' => 'home.php',
        ),
        'editorial-policy' => array(
            'title'    => 'Редакционная политика',
            'excerpt'  => 'Принципы фактчекинга, независимости и журналистские стандарты издания «Кутерьма»',
            'content'  => '<p class="lead">«Кутерьма» — независимое городское сетевое издание. Мы придерживаемся принципов честной и открытой журналистики, проверяем факты и разделяем редакционный контент и коммерческие материалы.</p><h2>Принципы работы</h2><p>Редакция не публикует заказные статьи под видом авторских репортажей. Все партнёрские и нативные материалы имеют явную маркировку. Мы уважаем авторское право и всегда указываем первоисточники и фотографов.</p>',
            'template' => 'page.php',
        ),
        'legal' => array(
            'title'    => 'Правовая информация',
            'excerpt'  => 'Правила использования материалов и правовой статус интернет-издания',
            'content'  => '<p class="lead">Все материалы издания защищены законодательством РФ об интеллектуальной собственности.</p><p>Использование текстовых и визуальных материалов сайта в коммерческих целях допускается только с письменного согласия редакции. Цитирование материалов в СМИ и блогах разрешено при условии обязательной гиперссылки на первоисточник.</p>',
            'template' => 'page.php',
        ),
        'privacy-policy' => array(
            'title'    => 'Политика конфиденциальности',
            'excerpt'  => 'Порядок обработки персональных данных пользователей и использование cookie',
            'content'  => '<p class="lead">Настоящая политика регулирует порядок обработки персональных данных и использование файлов cookie интернет-изданием «Кутерьма».</p><p>Сайт использует технические файлы cookie для улучшения взаимодействия с читателями, запоминания настроек и сбора обезличенной аналитической статистики посещаемости. Мы не передаём персональные данные третьим лицам без согласия пользователя.</p>',
            'template' => 'page.php',
        ),
    );

    foreach ($pages as $slug => $data) {
        $existing = get_page_by_path($slug);
        if (!$existing) {
            $page_id = wp_insert_post(array(
                'post_title'     => $data['title'],
                'post_name'      => $slug,
                'post_excerpt'   => $data['excerpt'],
                'post_content'   => $data['content'],
                'post_status'    => 'publish',
                'post_type'      => 'page',
                'comment_status' => 'closed',
                'ping_status'    => 'closed',
            ));
            if (!is_wp_error($page_id) && !empty($data['template']) && $data['template'] !== 'page.php') {
                update_post_meta($page_id, '_wp_page_template', $data['template']);
            }
        }
    }

    // Set page_for_posts if not set yet
    if ((int) get_option('page_for_posts') === 0) {
        $news_page = get_page_by_path('news');
        if (!$news_page) {
            $news_page = get_page_by_path('vse-novosti');
        }
        if ($news_page) {
            update_option('page_for_posts', $news_page->ID);
        }
    }
}
add_action('after_switch_theme', 'quterma_auto_create_core_pages');
add_action('admin_init', 'quterma_auto_create_core_pages');

/**
 * Helper to render a virtual or provisioned page with full WP_Query setup (200 OK)
 */
function quterma_render_core_page_view($slug, $title, $content, $template_file = 'page.php', $excerpt = '') {
    global $wp_query, $post;

    $page = get_page_by_path($slug);
    if (!$page) {
        $page_id = wp_insert_post(array(
            'post_title'     => $title,
            'post_name'      => $slug,
            'post_excerpt'   => $excerpt,
            'post_content'   => $content,
            'post_status'    => 'publish',
            'post_type'      => 'page',
            'comment_status' => 'closed',
            'ping_status'    => 'closed',
        ));
        if (!is_wp_error($page_id) && $template_file !== 'page.php') {
            update_post_meta($page_id, '_wp_page_template', $template_file);
        }
        $page = get_post($page_id);
    }

    if (!$page || is_wp_error($page)) {
        $page = new WP_Post((object) array(
            'ID'             => -999,
            'post_title'     => $title,
            'post_name'      => $slug,
            'post_excerpt'   => $excerpt,
            'post_content'   => $content,
            'post_status'    => 'publish',
            'post_type'      => 'page',
            'comment_status' => 'closed',
            'ping_status'    => 'closed',
            'filter'         => 'raw',
        ));
    }

    status_header(200);
    $wp_query->is_404            = false;
    $wp_query->is_page           = true;
    $wp_query->is_singular       = true;
    $wp_query->queried_object    = $page;
    $wp_query->queried_object_id = $page->ID;
    $wp_query->post              = $page;
    $wp_query->posts             = array($page);
    $wp_query->post_count        = 1;
    $GLOBALS['post']             = $page;
    setup_postdata($page);

    $file_path = get_template_directory() . '/' . $template_file;
    if (file_exists($file_path)) {
        include $file_path;
    } else {
        include get_template_directory() . '/page.php';
    }
    exit;
}

/**
 * 9. Fallback virtual router for core endpoints.
 * Intercepts 404 responses on core paths and renders the corresponding template with 200 OK.
 */
function quterma_resolve_virtual_routes() {
    if (!is_404()) {
        return;
    }

    $uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
    $req_path = trim(parse_url($uri, PHP_URL_PATH), '/');
    $site_path = trim(parse_url(home_url(), PHP_URL_PATH), '/');
    if (!empty($site_path) && strpos($req_path, $site_path) === 0) {
        $req_path = trim(substr($req_path, strlen($site_path)), '/');
    }

    $segments = explode('/', $req_path);
    $first = isset($segments[0]) ? $segments[0] : '';

    // News endpoint: /news or /news/ or /vse-novosti/
    if ($first === 'news' || $first === 'vse-novosti' || $first === 'all-news') {
        $page_for_posts = (int) get_option('page_for_posts');
        if ($page_for_posts > 0) {
            $news_url = get_permalink($page_for_posts);
            if ($news_url && untrailingslashit($news_url) !== untrailingslashit(home_url($first))) {
                wp_safe_redirect($news_url, 301);
                exit;
            }
        }
        $page = get_page_by_path($first);
        if ($page) {
            quterma_render_core_page_view($first, $page->post_title, $page->post_content, 'home.php');
        }
        status_header(200);
        global $wp_query;
        $wp_query->is_404  = false;
        $wp_query->is_home = true;
        include get_template_directory() . '/home.php';
        exit;
    }

    // Events endpoint: /events
    if ($first === 'events') {
        quterma_render_core_page_view(
            'events',
            __('События', 'quterma'),
            '',
            'page-events.php',
            __('Афиша событий города, спектакли, выставки, фестивали и концерты', 'quterma')
        );
    }

    // Gastroguide endpoint: /gastroguide
    if ($first === 'gastroguide') {
        quterma_render_core_page_view(
            'gastroguide',
            __('Гастрогид', 'quterma'),
            '',
            'page-gastroguide.php',
            __('Рестораны, бистро, кофейни, волжская гастрономия и барная карта', 'quterma')
        );
    }

    // Interview endpoint: /interview
    if ($first === 'interview') {
        $cat = get_category_by_slug('interview');
        if ($cat && count($segments) === 1) {
            wp_safe_redirect(get_category_link($cat), 301);
            exit;
        }
        quterma_render_core_page_view(
            'interview',
            __('Интервью', 'quterma'),
            '',
            'page-interview.php',
            __('Диалоги с интересными людьми, архитекторами, историками и деятелями культуры', 'quterma')
        );
    }

    // Rubrics endpoint: /rubrics
    if ($first === 'rubrics') {
        quterma_render_core_page_view(
            'rubrics',
            __('Рубрики', 'quterma'),
            '',
            'page-rubrics.php',
            __('Тематические рубрики и направления городского издания «Кутерьма»', 'quterma')
        );
    }

    // About endpoint: /about
    if ($first === 'about') {
        quterma_render_core_page_view(
            'about',
            __('О редакции', 'quterma'),
            '<p class="lead">«Кутерьма» — независимое городское интернет-издание о культуре, истории, архитектуре и людях Ярославля и волжских городов.</p><p>Мы пишем о городской среде, реставрации памятников, краеведении, современном искусстве и гастрономии. Наша цель — документировать жизнь города и рассказывать о тех, кто создает его облик.</p><h2>Связь с редакцией</h2><p>По вопросам публикации материалов, анонсов и сотрудничества: <strong>redaktsiya@quterma.ru</strong></p>',
            'page.php',
            __('Независимое городское медиа о культуре, истории и людях Ярославля', 'quterma')
        );
    }

    // Advertising endpoint: /advertising
    if ($first === 'advertising') {
        quterma_render_core_page_view(
            'advertising',
            __('Реклама и партнёрство', 'quterma'),
            '<p class="lead">Мы предлагаем нативные спецпроекты, репортажи, фотоистории и интеграции в рубриках «Гастрогид» и «Культурный слой».</p><p>Для получения медиакита и обсуждения условий партнерства пишите: <strong>reklama@quterma.ru</strong></p>',
            'page.php',
            __('Форматы нативной рекламы, спецпроекты и интеграции для локальных брендов', 'quterma')
        );
    }

    // Editorial policy endpoint: /editorial-policy
    if ($first === 'editorial-policy') {
        quterma_render_core_page_view(
            'editorial-policy',
            __('Редакционная политика', 'quterma'),
            '<p class="lead">«Кутерьма» — независимое городское сетевое издание. Мы придерживаемся принципов честной и открытой журналистики, проверяем факты и разделяем редакционный контент и коммерческие материалы.</p><h2>Принципы работы</h2><p>Редакция не публикует заказные статьи под видом авторских репортажей. Все партнёрские и нативные материалы имеют явную маркировку. Мы уважаем авторское право и всегда указываем первоисточники и фотографов.</p>',
            'page.php',
            __('Принципы фактчекинга, независимости и стандарты издания', 'quterma')
        );
    }

    // Legal information endpoint: /legal
    if ($first === 'legal') {
        quterma_render_core_page_view(
            'legal',
            __('Правовая информация', 'quterma'),
            '<p class="lead">Все материалы издания защищены законодательством РФ об интеллектуальной собственности.</p><p>Использование текстовых и визуальных материалов сайта в коммерческих целях допускается только с письменного согласия редакции. Цитирование материалов в СМИ и блогах разрешено при условии обязательной гиперссылки на первоисточник.</p>',
            'page.php',
            __('Правила использования материалов и правовой статус интернет-издания', 'quterma')
        );
    }

    // Privacy policy endpoint: /privacy-policy
    if ($first === 'privacy-policy' || $first === 'privacy') {
        quterma_render_core_page_view(
            'privacy-policy',
            __('Политика конфиденциальности', 'quterma'),
            '<p class="lead">Настоящая политика регулирует порядок обработки персональных данных и использование файлов cookie интернет-изданием «Кутерьма».</p><p>Сайт использует технические файлы cookie для улучшения взаимодействия с читателями, запоминания настроек и сбора обезличенной аналитической статистики посещаемости. Мы не передаём персональные данные третьим лицам без согласия пользователя.</p>',
            'page.php',
            __('Порядок обработки персональных данных пользователей и использование cookie', 'quterma')
        );
    }

    // Category kultura -> culture redirect
    if (($first === 'category' && isset($segments[1]) && ($segments[1] === 'kultura' || $segments[1] === 'culture-layer')) || $first === 'kultura') {
        $c_cat = get_category_by_slug('culture');
        if ($c_cat) {
            wp_safe_redirect(get_category_link($c_cat), 301);
            exit;
        }
        wp_safe_redirect(home_url('/category/culture/'), 301);
        exit;
    }

    // Direct category slug fallback (e.g. user opens /culture/ instead of /category/culture/)
    $cat = get_category_by_slug($first);
    if ($cat) {
        wp_safe_redirect(get_category_link($cat), 301);
        exit;
    }
}
add_action('template_redirect', 'quterma_resolve_virtual_routes');
