<?php
/**
 * The template for displaying Tag Archive pages (#Тема)
 *
 * @package Quterma
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();

$current_tag = get_queried_object();
?>

<div class="wrap">
  <div style="padding-top:28px">
    <?php get_template_part('template-parts/breadcrumbs'); ?>
    <div class="page-header">
      <div style="display:flex;align-items:center;gap:10px;margin-bottom:4px">
        <span style="font-family:var(--fd);font-size:var(--t-xs);font-weight:600;letter-spacing:.1em;text-transform:uppercase;color:var(--brand)">
          <?php esc_html_e('Тема', 'quterma'); ?>
        </span>
      </div>
      <h1 class="page-title">#<?php single_tag_title(); ?></h1>
      <?php if (!empty($current_tag->description)) : ?>
        <p class="page-subtitle"><?php echo esc_html($current_tag->description); ?></p>
      <?php endif; ?>
    </div>
  </div>

  <div class="main-layout">
    <main id="content">
      <?php if (have_posts()) : ?>
        <div class="news-list" id="newsList">
          <?php
          $item_idx = 0;
          while (have_posts()) : the_post();
              $item_idx++;
              if ($item_idx === 1 && !is_paged()) {
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
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><line x1="21" y1="21" x2="16.5" y2="16.5"/></svg>
          </div>
          <p class="empty-state-text"><?php esc_html_e('По этой теме пока нет опубликованных материалов.', 'quterma'); ?></p>
          <a href="<?php echo esc_url(quterma_get_news_url()); ?>" class="empty-state-btn"><?php esc_html_e('Перейти в общую ленту', 'quterma'); ?></a>
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
