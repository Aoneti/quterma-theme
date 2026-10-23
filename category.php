<?php
/**
 * The template for displaying Category Archive pages
 *
 * @package Quterma
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();

$current_cat = get_queried_object();
?>

<div class="wrap">
  <div style="padding-top:28px">
    <?php get_template_part('template-parts/breadcrumbs'); ?>
    <div class="page-header">
      <h1 class="page-title"><?php single_cat_title(); ?></h1>
    </div>
  </div>

  <!-- STANDARD EDITORIAL FEED LAYOUT FOR ALL CATEGORIES (КРАСИВАЯ РЕДАКЦИОННАЯ СЕТКА) -->
  <div class="main-layout">
    <main id="content">
      <?php if (have_posts()) : ?>
        <div class="news-list" id="newsList">
          <?php
          $item_index = 0;
          while (have_posts()) : the_post();
              $item_index++;
              if ($item_index === 1 && !is_paged()) {
                  get_template_part('template-parts/content', 'featured');
              } else {
                  get_template_part('template-parts/content', 'card');
              }
          endwhile;
          ?>
        </div>

        <?php get_template_part('template-parts/pagination'); ?>

      <?php else : ?>
        <div class="empty-state show">
          <div class="empty-state-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><line x1="21" y1="21" x2="16.5" y2="16.5"/></svg>
          </div>
          <p class="empty-state-text"><?php esc_html_e('В этой рубрике пока нет опубликованных материалов.', 'quterma'); ?></p>
          <a href="<?php echo esc_url(quterma_get_news_url()); ?>" class="empty-state-btn"><?php esc_html_e('Перейти в ленту', 'quterma'); ?></a>
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
