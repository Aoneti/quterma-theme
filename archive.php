<?php
/**
 * The template for displaying archive pages
 *
 * @package Quterma
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>

<div class="wrap">
  <div style="padding-top:28px">
    <?php get_template_part('template-parts/breadcrumbs'); ?>
    <div class="page-header">
      <h1 class="page-title"><?php the_archive_title(); ?></h1>
      <?php the_archive_description('<p class="page-subtitle">', '</p>'); ?>
    </div>
  </div>

  <div class="main-layout">
    <main id="content">
      <?php if (have_posts()) : ?>
        <div class="news-list" id="newsList">
          <?php
          $idx = 0;
          while (have_posts()) : the_post();
              if (get_post_type() === 'quterma_venue') {
                  get_template_part('template-parts/content', 'venue');
              } else {
                  $idx++;
                  if ($idx === 1 && !is_paged()) {
                      get_template_part('template-parts/content', 'featured');
                  } else {
                      get_template_part('template-parts/content', 'card');
                  }
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
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><line x1="21" y1="21" x2="16.5" y2="16.5"/></svg>
          </div>
          <p class="empty-state-text"><?php esc_html_e('В этом архиве пока нет записей.', 'quterma'); ?></p>
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
