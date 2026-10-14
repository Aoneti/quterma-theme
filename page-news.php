<?php
/**
 * Template Name: Все новости
 *
 * The template for displaying all news / blog archive
 *
 * @package Quterma
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();

$paged = get_query_var('paged') ? (int) get_query_var('paged') : (get_query_var('page') ? (int) get_query_var('page') : 1);
if ($paged < 1) {
    $paged = 1;
}

$posts_per_page = (int) get_option('posts_per_page', 10);
if ($posts_per_page < 1) {
    $posts_per_page = 10;
}

$news_query = new WP_Query(array(
    'post_type'           => 'post',
    'post_status'         => 'publish',
    'paged'               => $paged,
    'posts_per_page'      => $posts_per_page,
    'ignore_sticky_posts' => false,
));

$page_title    = get_the_title();
if (empty($page_title) || $page_title === 'news' || $page_title === 'vse-novosti') {
    $page_title = __('Все новости', 'quterma');
}
$page_subtitle = get_post_field('post_excerpt', get_the_ID());
if (empty($page_subtitle)) {
    $page_subtitle = __('Хроника событий, новости городской жизни, культуры и общества Ярославля', 'quterma');
}
?>

<div class="wrap">
  <div style="padding-top:28px">
    <?php get_template_part('template-parts/breadcrumbs'); ?>
    <div class="page-header">
      <h1 class="page-title"><?php echo esc_html($page_title); ?></h1>
    </div>
  </div>

  <?php
  // Optional introductory content if edited via block editor
  if (have_posts()) :
      while (have_posts()) : the_post();
          if (get_the_content()) :
              echo '<div class="page-intro-content" style="margin-bottom:32px">';
              the_content();
              echo '</div>';
          endif;
      endwhile;
      wp_reset_postdata();
  endif;
  ?>

  <div class="main-layout">
    <main id="content">
      <?php if ($news_query->have_posts()) : ?>
        <div class="news-list" id="newsList">
          <?php
          $idx = 0;
          while ($news_query->have_posts()) : $news_query->the_post();
              $idx++;
              if ($idx === 1 && $paged === 1) {
                  get_template_part('template-parts/content', 'featured');
              } else {
                  get_template_part('template-parts/content', 'card');
              }
          endwhile;
          ?>
        </div>

        <?php
        $big = 999999999;
        $paginate_links = paginate_links(array(
            'base'      => str_replace($big, '%#%', esc_url(get_pagenum_link($big))),
            'format'    => '?paged=%#%',
            'current'   => max(1, $paged),
            'total'     => $news_query->max_num_pages,
            'prev_text' => '<span class="nav-prev">&larr; ' . __('Назад', 'quterma') . '</span>',
            'next_text' => '<span class="nav-next">' . __('Вперед', 'quterma') . ' &rarr;</span>',
            'type'      => 'list',
        ));
        if ($paginate_links) {
            echo '<nav class="navigation pagination" aria-label="' . esc_attr__('Навигация по записям', 'quterma') . '"><div class="nav-links">' . $paginate_links . '</div></nav>';
        }
        wp_reset_postdata();
        ?>
      <?php else : ?>
        <div class="empty-state show">
          <div class="empty-state-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" width="36" height="36"><circle cx="11" cy="11" r="7"/><line x1="21" y1="21" x2="16.5" y2="16.5"/></svg>
          </div>
          <p class="empty-state-text"><?php esc_html_e('Пока нет опубликованных новостей в этом разделе — но редакция готовит новые материалы.', 'quterma'); ?></p>
          <a href="<?php echo esc_url(home_url('/')); ?>" class="empty-state-btn"><?php esc_html_e('Вернуться на главную', 'quterma'); ?></a>
        </div>
      <?php endif; ?>
    </main>

    <aside class="sidebar">
      <?php get_template_part('template-parts/sidebar', 'popular'); ?>
      <?php get_template_part('template-parts/sidebar', 'tags'); ?>
    </aside>
  </div>
</div>

<?php
get_footer();
