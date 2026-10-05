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

// Popular rubrics for empty state or quick navigation
$popular_rubrics = array(
    array('title' => __('Город', 'quterma'), 'url' => quterma_get_category_url('city', home_url('/category/city/'))),
    array('title' => __('Культура', 'quterma'), 'url' => quterma_get_category_url('culture', home_url('/category/culture/'))),
    array('title' => __('История', 'quterma'), 'url' => quterma_get_category_url('history', home_url('/category/history/'))),
    array('title' => __('Искусство', 'quterma'), 'url' => quterma_get_category_url('art', home_url('/category/art/'))),
    array('title' => __('Люди', 'quterma'), 'url' => quterma_get_category_url('people', home_url('/category/people/'))),
    array('title' => __('Гастрогид', 'quterma'), 'url' => quterma_get_page_url('gastroguide', home_url('/gastroguide/'))),
    array('title' => __('События', 'quterma'), 'url' => quterma_get_page_url('events', home_url('/events/'))),
);
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
      <label for="searchPageInput" class="sr-only"><?php esc_html_e('Поисковый запрос', 'quterma'); ?></label>
      <input type="search" id="searchPageInput" name="s" value="<?php echo esc_attr($query_str); ?>" placeholder="<?php esc_attr_e('Что вы хотите найти? Например: набережная, театр, ремонт дорог…', 'quterma'); ?>" autocomplete="off" <?php echo empty($query_str) ? 'autofocus' : ''; ?>>
      <button type="submit" class="search-submit-btn"><?php esc_html_e('Найти', 'quterma'); ?></button>
    </form>
  </div>

  <?php if (!empty($query_str)) : ?>
    <div class="search-info">
      <?php
      if ($total_results > 0) {
          $word = quterma_plural($total_results, array('материал', 'материала', 'материалов'));
          printf(
              __('Найдено %1$d %2$s по запросу <strong>«%3$s»</strong>', 'quterma'),
              $total_results,
              $word,
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
        <div class="search-results-list" id="searchResultsList">
          <?php
          while (have_posts()) : the_post();
              $p_type = get_post_type();
              if ($p_type === 'quterma_venue') {
                  $type_label = __('Заведение · Гастрогид', 'quterma');
                  $venue_city = get_post_meta(get_the_ID(), '_venue_city', true);
                  $venue_addr = get_post_meta(get_the_ID(), '_venue_address', true);
                  $meta_str   = trim(quterma_get_city_name($venue_city) . ($venue_addr ? ', ' . $venue_addr : ''));
              } elseif ($p_type === 'page') {
                  $type_label = __('Раздел', 'quterma');
                  $meta_str   = __('Страница издания', 'quterma');
              } else {
                  $cat_info   = quterma_get_post_category_info();
                  $type_label = $cat_info['name'];
                  $meta_str   = quterma_format_date(get_the_ID(), true);
              }

              $raw_excerpt = get_the_excerpt();
              if (empty($raw_excerpt)) {
                  $raw_excerpt = wp_trim_words(get_the_content(), 18, '…');
              }
          ?>
            <article class="search-row news-card">
              <div class="nc-img">
                <?php if (has_post_thumbnail()) : ?>
                  <?php the_post_thumbnail('quterma-card-4x3', array(
                      'loading' => 'lazy',
                      'sizes'   => '(max-width: 480px) 96px, (max-width: 768px) 140px, 200px',
                      'alt'     => the_title_attribute(array('echo' => false)),
                  )); ?>
                <?php else : ?>
                  <?php echo quterma_placeholder_img(38, 38); ?>
                <?php endif; ?>
              </div>
              <div class="nc-body">
                <div class="search-type-badge"><?php echo esc_html($type_label); ?></div>
                <h2 class="nc-title">
                  <a href="<?php the_permalink(); ?>" class="card-permalink">
                    <?php echo quterma_highlight(get_the_title(), $query_str); ?>
                  </a>
                </h2>
                <p class="nc-excerpt">
                  <?php echo quterma_highlight($raw_excerpt, $query_str); ?>
                </p>
                <div class="nc-meta"><?php echo esc_html($meta_str); ?></div>
              </div>
            </article>
          <?php endwhile; ?>
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
        <div class="empty-state show search-empty-state">
          <div class="empty-state-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><line x1="21" y1="21" x2="16.5" y2="16.5"/></svg>
          </div>
          <h3 style="font-size:18px;font-weight:700;color:var(--ink);margin:0"><?php esc_html_e('Ничего не найдено', 'quterma'); ?></h3>
          <p class="empty-state-text"><?php esc_html_e('Попробуйте изменить формулировку, проверить опечатки или перейти в один из популярных разделов издания:', 'quterma'); ?></p>

          <div class="search-suggest-chips" style="display:flex;flex-wrap:wrap;gap:8px;justify-content:center;margin:12px 0 18px;max-width:480px">
            <?php foreach ($popular_rubrics as $rub) : ?>
              <a href="<?php echo esc_url($rub['url']); ?>" class="tag rubric-tag">
                <?php echo esc_html($rub['title']); ?>
              </a>
            <?php endforeach; ?>
          </div>

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
