<?php
/**
 * The template for displaying the front page
 *
 * @package Quterma
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();

// 1. HERO CAROUSEL
get_template_part('template-parts/hero', 'carousel');

// 2. MAIN FEED AND SIDEBAR
$feed_query = quterma_get_feed_today_posts(8);
$culture_query = quterma_get_culture_layer_posts(3);
$specials_query = quterma_get_specials_posts(4);

$feed_page = get_page_by_path('feed');
$feed_url  = $feed_page ? get_permalink($feed_page) : home_url('/feed/');
?>

<div class="wrap">
  <div class="main-layout">
    <main>
      <!-- ЛЕНТА СЕГОДНЯ -->
      <div class="sec-div rev">
        <div class="sec-div-acc"></div>
        <h2 class="sec-div-title"><?php esc_html_e('Лента сегодня', 'quterma'); ?></h2>
        <div class="sec-div-line"></div>
        <a href="<?php echo esc_url($feed_url); ?>" class="sec-div-link">
          <?php esc_html_e('Все', 'quterma'); ?>
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
        </a>
      </div>

      <!-- ФИЛЬТРЫ РУБРИК ЛЕНТЫ -->
      <div class="filters">
        <button type="button" class="filter active" data-filter="all"><?php esc_html_e('Все', 'quterma'); ?></button>
        <button type="button" class="filter" data-filter="city"><?php esc_html_e('Город', 'quterma'); ?></button>
        <button type="button" class="filter" data-filter="improvement"><?php esc_html_e('Благоустройство', 'quterma'); ?></button>
        <button type="button" class="filter" data-filter="heritage"><?php esc_html_e('Наследие', 'quterma'); ?></button>
        <button type="button" class="filter" data-filter="culture"><?php esc_html_e('Культура', 'quterma'); ?></button>
        <button type="button" class="filter" data-filter="society"><?php esc_html_e('Общество', 'quterma'); ?></button>
        <button type="button" class="filter" data-filter="ecology"><?php esc_html_e('Экология', 'quterma'); ?></button>
        <button type="button" class="filter" data-filter="sport"><?php esc_html_e('Спорт', 'quterma'); ?></button>
      </div>

      <!-- СПИСОК НОВОСТЕЙ -->
      <div class="news-list" id="newsList">
        <?php
        if ($feed_query->have_posts()) :
            $post_count = 0;
            while ($feed_query->have_posts()) : $feed_query->the_post();
                $post_count++;
                if ($post_count === 1) {
                    get_template_part('template-parts/content', 'featured');
                } else {
                    get_template_part('template-parts/content', 'card');
                }
            endwhile;
            wp_reset_postdata();
        else :
        ?>
          <div class="empty-state show">
            <div class="empty-state-icon">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><line x1="21" y1="21" x2="16.5" y2="16.5"/></svg>
            </div>
            <p class="empty-state-text"><?php esc_html_e('Пока нет опубликованных материалов — лента скоро пополнится.', 'quterma'); ?></p>
          </div>
        <?php endif; ?>
      </div>

      <!-- СОСТОЯНИЕ ДЛЯ ПУСТОГО ФИЛЬТРА -->
      <div class="feed-empty" id="feedEmpty">
        <p class="feed-empty-text"><?php esc_html_e('Пока нет материалов в этой рубрике — но лента постоянно пополняется.', 'quterma'); ?></p>
        <button type="button" class="feed-empty-btn" id="feedEmptyReset"><?php esc_html_e('Показать всю ленту', 'quterma'); ?></button>
      </div>

      <?php if ($feed_query->max_num_pages > 1) : ?>
        <a href="<?php echo esc_url($feed_url); ?>" class="load-more" id="loadMoreBtn">
          <?php esc_html_e('Показать ещё', 'quterma'); ?>
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
        </a>
      <?php endif; ?>
    </main>

    <!-- САЙДБАР: ЧИТАЮТ СЕЙЧАС + ТЕМЫ -->
    <aside class="sidebar">
      <?php get_template_part('template-parts/sidebar', 'popular'); ?>
      <?php get_template_part('template-parts/sidebar', 'tags'); ?>
    </aside>
  </div>
</div>

<!-- 3. КУЛЬТУРНЫЙ СЛОЙ -->
<?php if ($culture_query->have_posts()) :
    $culture_cat = get_category_by_slug('kultura');
    $culture_url = $culture_cat ? get_category_link($culture_cat) : home_url('/category/kultura/');
?>
<section class="culture-section rev">
  <div class="wrap">
    <div class="sec-div" style="margin-top:0">
      <div class="sec-div-acc" style="background:var(--accent)"></div>
      <h2 class="sec-div-title"><?php esc_html_e('Культурный слой', 'quterma'); ?></h2>
      <div class="sec-div-line" style="background:rgba(46,107,78,.18)"></div>
      <a href="<?php echo esc_url($culture_url); ?>" class="sec-div-link" style="color:var(--accent)">
        <?php esc_html_e('Все', 'quterma'); ?>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
      </a>
    </div>

    <div class="culture-grid">
      <?php
      while ($culture_query->have_posts()) : $culture_query->the_post();
          $cat_info = quterma_get_post_category_info();
          $date_str = quterma_format_date(get_the_ID(), false);
      ?>
        <article class="cc-card" onclick="location.href='<?php the_permalink(); ?>';">
          <div class="cc-img">
            <?php if (has_post_thumbnail()) : ?>
              <?php the_post_thumbnail('quterma-card-4x3', array('loading' => 'lazy', 'alt' => the_title_attribute(array('echo' => false)))); ?>
            <?php else : ?>
              <?php echo quterma_placeholder_img(38, 38); ?>
            <?php endif; ?>
          </div>
          <div class="cc-body">
            <span class="sr-only"><?php echo esc_html($cat_info['name']); ?></span>
            <h3 class="cc-title"><?php the_title(); ?></h3>
            <p class="cc-excerpt"><?php echo wp_trim_words(get_the_excerpt(), 14, '…'); ?></p>
            <div class="cc-meta"><?php echo esc_html($date_str); ?></div>
          </div>
        </article>
      <?php endwhile; wp_reset_postdata(); ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- 4. ЛОНГРИДЫ И СПЕЦПРОЕКТЫ -->
<?php if ($specials_query->have_posts()) : ?>
<div class="wrap">
  <div class="sec-div rev">
    <div class="sec-div-acc" style="background:var(--accent)"></div>
    <h2 class="sec-div-title"><?php esc_html_e('Лонгриды и спецпроекты', 'quterma'); ?></h2>
    <div class="sec-div-line"></div>
  </div>

  <div class="specials rev" style="margin-bottom:60px">
    <?php
    while ($specials_query->have_posts()) : $specials_query->the_post();
        get_template_part('template-parts/content', 'special');
    endwhile;
    wp_reset_postdata();
    ?>
  </div>
</div>
<?php endif; ?>

<?php
get_footer();
