<?php
/**
 * The template for displaying the blog posts index (Все новости / Лента)
 *
 * @package Quterma
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();

$posts_page_id = get_option('page_for_posts');
$page_title    = $posts_page_id ? get_the_title($posts_page_id) : __('Все новости', 'quterma');
$page_subtitle = $posts_page_id ? get_post_field('post_excerpt', $posts_page_id) : __('Хроника событий, новости городской жизни, культуры и общества Ярославля', 'quterma');
?>

<div class="wrap">
  <div style="padding-top:28px">
    <?php get_template_part('template-parts/breadcrumbs'); ?>
    <div class="page-header">
      <h1 class="page-title"><?php echo esc_html($page_title); ?></h1>
      <?php if (!empty($page_subtitle)) : ?>
        <p class="page-subtitle"><?php echo esc_html($page_subtitle); ?></p>
      <?php endif; ?>
    </div>
  </div>

  <div class="main-layout">
    <main>
      <?php if (have_posts()) : ?>
        <div class="news-list" id="newsList">
          <?php
          $idx = 0;
          while (have_posts()) : the_post();
              $idx++;
              if ($idx === 1 && !is_paged()) {
                  get_template_part('template-parts/content', 'featured');
              } else {
                  get_template_part('template-parts/content', 'card');
              }
          endwhile;
          ?>
        </div>

        <?php
        the_posts_pagination(array(
            'mid_size'           => 2,
            'prev_text'          => '<span class="nav-prev">&larr; ' . __('Назад', 'quterma') . '</span>',
            'next_text'          => '<span class="nav-next">' . __('Вперед', 'quterma') . ' &rarr;</span>',
            'screen_reader_text' => __('Навигация по записям', 'quterma'),
        ));
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
