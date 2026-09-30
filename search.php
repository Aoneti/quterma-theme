<?php
/**
 * The template for displaying search results
 *
 * @package Quterma
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();

global $wp_query;
$total_results = $wp_query->found_posts;
$query_str     = get_search_query();
?>

<div class="wrap">
  <div style="padding-top:28px">
    <?php get_template_part('template-parts/breadcrumbs'); ?>
    <div class="page-header">
      <h1 class="page-title"><?php esc_html_e('Поиск', 'quterma'); ?></h1>
    </div>
  </div>

  <div class="search-form-wrap rev">
    <form role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>" class="search-input-box">
      <input type="search" name="s" value="<?php echo esc_attr($query_str); ?>" placeholder="<?php esc_attr_e('Что вы хотите найти? Например: набережная, театр, ремонт дорог…', 'quterma'); ?>" autocomplete="off" autofocus>
      <button type="submit" class="search-submit-btn"><?php esc_html_e('Найти', 'quterma'); ?></button>
    </form>
  </div>

  <?php if (!empty($query_str)) : ?>
    <div class="search-info">
      <?php
      if ($total_results > 0) {
          printf(
              _n('Найдено %1$d материал по запросу <strong>«%2$s»</strong>', 'Найдено %1$d материалов по запросу <strong>«%2$s»</strong>', $total_results, 'quterma'),
              $total_results,
              esc_html($query_str)
          );
      } else {
          printf(__('По запросу <strong>«%s»</strong> ничего не найдено', 'quterma'), esc_html($query_str));
      }
      ?>
    </div>
  <?php endif; ?>

  <div class="main-layout">
    <main>
      <?php if (have_posts()) : ?>
        <div class="news-list" id="newsList">
          <?php
          while (have_posts()) : the_post();
              if (get_post_type() === 'quterma_venue') {
                  get_template_part('template-parts/content', 'venue');
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
            'screen_reader_text' => __('Навигация по результатам', 'quterma'),
        ));
        ?>

      <?php else : ?>
        <div class="empty-state show">
          <div class="empty-state-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><line x1="21" y1="21" x2="16.5" y2="16.5"/></svg>
          </div>
          <p class="empty-state-text"><?php esc_html_e('Ничего не найдено. Попробуйте изменить формулировку, проверить опечатки или выбрать одну из популярных тем.', 'quterma'); ?></p>
          <a href="<?php echo esc_url(home_url('/')); ?>" class="empty-state-btn"><?php esc_html_e('На главную страницу', 'quterma'); ?></a>
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
