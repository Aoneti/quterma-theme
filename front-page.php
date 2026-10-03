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
$carousel_query = quterma_get_carousel_posts(6);
set_query_var('carousel_query', $carousel_query);
get_template_part('template-parts/hero', 'carousel', array('carousel_query' => $carousel_query));

// Exclude IDs accumulator to prevent duplicates across homepage sections
$exclude_ids = array();
if ($carousel_query->have_posts()) {
    $exclude_ids = wp_list_pluck($carousel_query->posts, 'ID');
}

// 2. MAIN FEED AND SIDEBAR
$feed_query = quterma_get_feed_today_posts(8, $exclude_ids);
if ($feed_query->have_posts()) {
    $exclude_ids = array_merge($exclude_ids, wp_list_pluck($feed_query->posts, 'ID'));
}

$culture_query = quterma_get_culture_layer_posts(3, $exclude_ids);
if ($culture_query->have_posts()) {
    $exclude_ids = array_merge($exclude_ids, wp_list_pluck($culture_query->posts, 'ID'));
}

$specials_query = quterma_get_specials_posts(4, $exclude_ids);
if ($specials_query->have_posts()) {
    $exclude_ids = array_merge($exclude_ids, wp_list_pluck($specials_query->posts, 'ID'));
}

$cinema_music_query = quterma_get_cinema_music_posts(3, $exclude_ids);
if ($cinema_music_query->have_posts()) {
    $exclude_ids = array_merge($exclude_ids, wp_list_pluck($cinema_music_query->posts, 'ID'));
}

$cinema_music_url = quterma_get_page_url('cinema-music', home_url('/category/cinema-music/'));
$feed_url         = quterma_get_news_url();
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
        <button type="button" class="filter" data-filter="improvement"><?php esc_html_e('Благоустройство', 'quterma'); ?></button>
      </div>

      <!-- СПИСОК НОВОСТЕЙ -->
      <div class="news-list" id="newsList">
        <?php
        if ($feed_query->have_posts()) :
            $feed_count = 0;
            while ($feed_query->have_posts()) : $feed_query->the_post();
                $feed_count++;
                $cat_info  = quterma_get_post_category_info();
                $date_str  = quterma_format_date(get_the_ID(), true);
                $is_lead   = ($feed_count === 1);
                $post_tags = get_the_tags();
                $filter_cat = 'all';
                if ($post_tags) {
                    foreach ($post_tags as $t) {
                        if (in_array(strtolower($t->slug), array('blagoustroystvo', 'improvement', 'remont', 'blag'))) {
                            $filter_cat = 'improvement';
                            break;
                        }
                    }
                }
            ?>
              <article class="news-card<?php echo $is_lead ? ' featured' : ''; ?>" data-category="<?php echo esc_attr($filter_cat); ?>" onclick="location.href='<?php the_permalink(); ?>';">
                <div class="nc-img">
                  <?php if (has_post_thumbnail()) : ?>
                    <?php
                    $thumb_size = $is_lead ? 'quterma-hero' : 'quterma-card-4x3';
                    $thumb_sizes = $is_lead ? '(max-width: 768px) 100vw, (max-width: 1200px) 66vw, 800px' : '(max-width: 480px) 100vw, (max-width: 768px) 240px, 360px';
                    the_post_thumbnail($thumb_size, array(
                        'loading' => 'lazy',
                        'sizes'   => $thumb_sizes,
                        'alt'     => the_title_attribute(array('echo' => false)),
                    ));
                    ?>
                  <?php else : ?>
                    <?php echo quterma_placeholder_img(38, 38); ?>
                  <?php endif; ?>
                </div>
                <div class="nc-body">
                  <span class="sr-only"><?php echo esc_html($cat_info['name']); ?></span>
                  <h3 class="nc-title"><?php the_title(); ?></h3>
                  <p class="nc-excerpt"><?php echo wp_trim_words(get_the_excerpt(), $is_lead ? 22 : 14, '…'); ?></p>
                  <div class="nc-meta"><?php echo quterma_time_tag(get_the_ID(), true); ?></div>
                </div>
              </article>
            <?php
            endwhile;
            wp_reset_postdata();
        endif;
        ?>
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
              <?php the_post_thumbnail('quterma-card-4x3', array(
                  'loading' => 'lazy',
                  'sizes'   => '(max-width: 480px) 100vw, (max-width: 900px) 50vw, 280px',
                  'alt'     => the_title_attribute(array('echo' => false)),
              )); ?>
            <?php else : ?>
              <?php echo quterma_placeholder_img(38, 38); ?>
            <?php endif; ?>
          </div>
          <div class="cc-body">
            <span class="sr-only"><?php echo esc_html($cat_info['name']); ?></span>
            <h3 class="cc-title"><?php the_title(); ?></h3>
            <p class="cc-excerpt"><?php echo wp_trim_words(get_the_excerpt(), 14, '…'); ?></p>
            <div class="cc-meta"><?php echo quterma_time_tag(get_the_ID(), false); ?></div>
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

  <div class="specials rev" style="margin-bottom:40px">
    <?php
    while ($specials_query->have_posts()) : $specials_query->the_post();
        get_template_part('template-parts/content', 'special');
    endwhile;
    wp_reset_postdata();
    ?>
  </div>
</div>
<?php endif; ?>

<!-- 5. КИНО И МУЗЫКА -->
<?php if ($cinema_music_query->have_posts()) : ?>
<section class="cinema-section rev" aria-label="<?php esc_attr_e('Кино и музыка', 'quterma'); ?>">
  <div class="wrap">
    <div class="sec-div" style="margin-top:0">
      <div class="sec-div-acc"></div>
      <h2 class="sec-div-title"><?php esc_html_e('Кино и музыка', 'quterma'); ?></h2>
      <div class="sec-div-line"></div>
      <a href="<?php echo esc_url($cinema_music_url); ?>" class="sec-div-link">
        <?php esc_html_e('Все', 'quterma'); ?>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
      </a>
    </div>

    <div class="cinema-grid">
      <?php
      while ($cinema_music_query->have_posts()) : $cinema_music_query->the_post();
          $cat_info   = quterma_get_post_category_info();
          $is_music   = (stripos($cat_info['slug'], 'music') !== false || stripos($cat_info['slug'], 'muzyk') !== false || stripos($cat_info['name'], 'музык') !== false);
          $badge_name = $is_music ? __('Музыка', 'quterma') : __('Кино', 'quterma');
          $post_tags  = get_the_tags();
          $tag_label  = (!empty($post_tags) && isset($post_tags[0])) ? $post_tags[0]->name : ($is_music ? __('Плейлист', 'quterma') : __('Премьера', 'quterma'));
      ?>
        <a href="<?php the_permalink(); ?>" class="cm-card">
          <div class="cm-media">
            <div class="cm-badge"><?php echo esc_html($badge_name); ?></div>
            <span class="cm-vinyl-tag"><?php echo esc_html($tag_label); ?></span>
            <?php if (has_post_thumbnail()) : ?>
              <div style="aspect-ratio:16/10;overflow:hidden">
                <?php the_post_thumbnail('quterma-card-4x3', array(
                    'loading' => 'lazy',
                    'sizes'   => '(max-width: 480px) 100vw, (max-width: 768px) 50vw, 360px',
                    'alt'     => the_title_attribute(array('echo' => false)),
                )); ?>
              </div>
            <?php else : ?>
              <div class="ph-img" style="aspect-ratio:16/10"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round" width="38" height="38"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="9" cy="9" r="1.7"/><path d="M21 15l-4.5-4.5a1.5 1.5 0 0 0-2.12 0L4 21"/></svg></div>
            <?php endif; ?>
          </div>
          <div class="cm-body">
            <div class="cm-genre"><?php echo esc_html($cat_info['name']); ?></div>
            <h3 class="cm-title"><?php the_title(); ?></h3>
            <p class="cm-desc"><?php echo wp_trim_words(get_the_excerpt(), 14, '…'); ?></p>
            <div class="cm-meta">
              <span class="cm-play-hint"><?php echo $is_music ? esc_html__('Слушать', 'quterma') : esc_html__('Читать', 'quterma'); ?> <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg></span>
            </div>
          </div>
        </a>
      <?php endwhile; wp_reset_postdata(); ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- 6. ИНТЕРВЬЮ -->
<?php
$interview_url = quterma_get_page_url('interview', home_url('/category/interview/'));
$iv_query      = quterma_get_interview_posts(4, $exclude_ids);
if ($iv_query->have_posts()) :
?>
<section class="interview-section rev">
  <div class="wrap">
    <div class="sec-div" style="margin-top:0">
      <div class="sec-div-acc"></div>
      <h2 class="sec-div-title"><?php esc_html_e('Интервью', 'quterma'); ?></h2>
      <div class="sec-div-line"></div>
      <a href="<?php echo esc_url($interview_url); ?>" class="sec-div-link">
        <?php esc_html_e('Все', 'quterma'); ?>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
      </a>
    </div>

    <div class="interview-grid">
      <?php while ($iv_query->have_posts()) : $iv_query->the_post();
        $custom_person = get_post_meta(get_the_ID(), '_iv_person', true);
        $person_name   = !empty($custom_person) ? $custom_person : get_the_title();
        $custom_role   = get_post_meta(get_the_ID(), '_iv_role', true);
      ?>
        <a href="<?php the_permalink(); ?>" class="iv-card">
          <div class="iv-media">
            <div class="iv-badge"><?php esc_html_e('Интервью', 'quterma'); ?></div>
            <?php if (has_post_thumbnail()) : ?>
              <div style="aspect-ratio:16/11;overflow:hidden">
                <?php the_post_thumbnail('quterma-card-4x3', array(
                    'loading' => 'lazy',
                    'sizes'   => '(max-width: 480px) 100vw, (max-width: 768px) 50vw, 300px',
                    'alt'     => the_title_attribute(array('echo' => false)),
                )); ?>
              </div>
            <?php else : ?>
              <div class="ph-img" style="aspect-ratio:16/11"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" width="40" height="40"><circle cx="12" cy="7" r="4"/><path d="M6 21v-2a4 4 0 0 1 4-4h4a4 4 0 0 1 4 4v2"/></svg></div>
            <?php endif; ?>
          </div>
          <div class="iv-body">
            <div class="iv-person"><?php echo esc_html($person_name); ?></div>
            <?php if (!empty($custom_role)) : ?>
              <div class="iv-role"><?php echo esc_html($custom_role); ?></div>
            <?php endif; ?>
            <p class="iv-excerpt"><?php echo wp_trim_words(get_the_excerpt(), 14, '…'); ?></p>
            <div class="iv-meta">
              <span><?php echo quterma_time_tag(get_the_ID(), false); ?></span>
              <span class="iv-meta-cta"><?php esc_html_e('Читать диалог →', 'quterma'); ?></span>
            </div>
          </div>
        </a>
      <?php endwhile; wp_reset_postdata(); ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php
get_footer();
