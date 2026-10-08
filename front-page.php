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
$feed_per_page = 6;
$feed_query    = quterma_get_feed_today_posts($feed_per_page, $exclude_ids);
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

$cinema_music_query = quterma_get_cinema_music_posts(4, $exclude_ids);
if ($cinema_music_query->have_posts()) {
    $exclude_ids = array_merge($exclude_ids, wp_list_pluck($cinema_music_query->posts, 'ID'));
}

$cinema_music_url = quterma_get_page_url('cinema-music', home_url('/category/cinema-music/'));
$feed_url         = quterma_get_news_url();
?>

<div class="wrap">
  <div class="main-layout">
    <main id="content">
      <h1 class="sr-only"><?php bloginfo('name'); ?><?php if (get_bloginfo('description')) : ?> — <?php bloginfo('description'); ?><?php endif; ?></h1>
      <!-- ЛЕНТА -->
      <div class="sec-div">
        <div class="sec-div-acc"></div>
        <h2 class="sec-div-title"><?php esc_html_e('Лента', 'quterma'); ?></h2>
        <div class="sec-div-line"></div>
        <a href="<?php echo esc_url($feed_url); ?>" class="sec-div-link">
          <?php esc_html_e('Все', 'quterma'); ?>
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
        </a>
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
            ?>
              <article class="news-card<?php echo $is_lead ? ' featured' : ''; ?>" data-id="<?php the_ID(); ?>" data-post-id="<?php the_ID(); ?>">
                <div class="nc-img">
                  <?php if (has_post_thumbnail()) : ?>
                    <?php
                    $thumb_size = $is_lead ? 'quterma-hero' : 'quterma-card-4x3';
                    $thumb_sizes = $is_lead ? '(max-width: 768px) 100vw, (max-width: 1200px) 66vw, 800px' : '(max-width: 480px) 100vw, (max-width: 768px) 240px, 360px';
                    the_post_thumbnail($thumb_size, array(
                        'loading' => 'lazy',
                        'sizes'   => $thumb_sizes,
                        'alt'     => '',
                    ));
                    ?>
                  <?php else : ?>
                    <?php echo quterma_placeholder_img(38, 38); ?>
                  <?php endif; ?>
                </div>
                <div class="nc-body">
                  <span class="sr-only"><?php echo esc_html($cat_info['name']); ?></span>
                  <h3 class="nc-title"><a href="<?php the_permalink(); ?>" class="card-permalink"><?php the_title(); ?></a></h3>
                  <p class="nc-excerpt"><?php echo esc_html(wp_trim_words(get_the_excerpt(), $is_lead ? 22 : 14, '…')); ?></p>
                  <div class="nc-meta"><?php echo quterma_time_tag(get_the_ID(), true); ?></div>
                </div>
              </article>
            <?php
            endwhile;
            wp_reset_postdata();
        endif;
        ?>
      </div>

      <?php if ($feed_query->max_num_pages > 1) : ?>
        <button type="button" class="load-more" id="loadMoreBtn" 
                data-page="1" 
                data-max="<?php echo esc_attr($feed_query->max_num_pages); ?>" 
                data-per-page="<?php echo esc_attr($feed_per_page); ?>"
                data-exclude="<?php echo esc_attr(implode(',', $exclude_ids)); ?>"
                data-url="<?php echo esc_url($feed_url); ?>">
          <span><?php esc_html_e('Показать ещё', 'quterma'); ?></span>
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
        </button>
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
    $culture_url = quterma_get_category_url('culture', home_url('/category/culture/'));
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
        <article class="cc-card">
          <div class="cc-img">
            <?php if (has_post_thumbnail()) : ?>
              <?php the_post_thumbnail('quterma-card-4x3', array(
                  'loading' => 'lazy',
                  'sizes'   => '(max-width: 480px) 100vw, (max-width: 900px) 50vw, 280px',
                  'alt'     => '',
              )); ?>
            <?php else : ?>
              <?php echo quterma_placeholder_img(38, 38); ?>
            <?php endif; ?>
          </div>
          <div class="cc-body">
            <span class="sr-only"><?php echo esc_html($cat_info['name']); ?></span>
            <h3 class="cc-title"><a href="<?php the_permalink(); ?>" class="card-permalink"><?php the_title(); ?></a></h3>
            <p class="cc-excerpt"><?php echo esc_html(wp_trim_words(get_the_excerpt(), 14, '…')); ?></p>
            <div class="cc-meta"><?php echo quterma_time_tag(get_the_ID(), false); ?></div>
          </div>
        </article>
      <?php endwhile; wp_reset_postdata(); ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- 4. ЛОНГРИДЫ И СПЕЦПРОЕКТЫ -->
<?php if ($specials_query->have_posts()) :
    $specials_url = quterma_get_page_url('longreads', quterma_get_category_url('specials', home_url('/category/specials/')));
?>
<div class="wrap">
  <div class="sec-div rev">
    <div class="sec-div-acc" style="background:var(--accent)"></div>
    <h2 class="sec-div-title"><?php esc_html_e('Лонгриды и спецпроекты', 'quterma'); ?></h2>
    <div class="sec-div-line"></div>
    <a href="<?php echo esc_url($specials_url); ?>" class="sec-div-link" style="color:var(--accent)">
      <?php esc_html_e('Все', 'quterma'); ?>
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
    </a>
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
          $post_id     = get_the_ID();
          $cat_info    = quterma_get_post_category_info();
          $all_cats    = get_the_category($post_id);
          $post_tags   = get_the_tags($post_id);
          $badge_name  = '';
          $tag_label   = '';
          $is_music    = false;

          // 1. Check child categories first
          if (!empty($all_cats)) {
              // Find any child category (parent > 0) or specific category
              foreach ($all_cats as $c) {
                  $c_name = function_exists('mb_strtolower') ? mb_strtolower(trim($c->name), 'UTF-8') : strtolower(trim($c->name));
                  $c_slug = strtolower(trim(urldecode($c->slug)));

                  if ($c->parent > 0 || (strpos($c_name, 'кино и музыка') === false && strpos($c_slug, 'cinema-music') === false && strpos($c_slug, 'kino-i-muzyka') === false)) {
                      if (strpos($c_name, 'музык') !== false || strpos($c_slug, 'music') !== false || strpos($c_slug, 'muzyk') !== false) {
                          $badge_name = __('Музыка', 'quterma');
                          $is_music   = true;
                          break;
                      } elseif (strpos($c_name, 'кино') !== false || strpos($c_slug, 'cinema') !== false || strpos($c_slug, 'kino') !== false || strpos($c_name, 'фильм') !== false) {
                          $badge_name = __('Кино', 'quterma');
                          break;
                      } else {
                          // Child category like "Сериалы", "Концерты", "Релизы", etc.
                          $badge_name = $c->name;
                          if (strpos($c_name, 'концерт') !== false || strpos($c_name, 'плейлист') !== false || strpos($c_name, 'альбом') !== false) {
                              $is_music = true;
                          }
                          break;
                      }
                  }
              }
          }

          // 2. Check tags if badge not determined by child category
          if (empty($badge_name) && !empty($post_tags)) {
              foreach ($post_tags as $t) {
                  $t_name = function_exists('mb_strtolower') ? mb_strtolower(trim($t->name), 'UTF-8') : strtolower(trim($t->name));
                  $t_slug = strtolower(trim(urldecode($t->slug)));

                  if ($t_name === 'музыка' || strpos($t_name, 'музык') !== false || strpos($t_slug, 'music') !== false || strpos($t_slug, 'muzyk') !== false) {
                      $badge_name = __('Музыка', 'quterma');
                      $is_music   = true;
                      break;
                  } elseif ($t_name === 'кино' || strpos($t_name, 'кино') !== false || strpos($t_slug, 'cinema') !== false || strpos($t_slug, 'kino') !== false) {
                      $badge_name = __('Кино', 'quterma');
                      break;
                  }
              }
          }

          // 3. Audio / Video post format check
          if (empty($badge_name)) {
              $format = get_post_format($post_id);
              if ($format === 'audio') {
                  $badge_name = __('Музыка', 'quterma');
                  $is_music   = true;
              } elseif ($format === 'video') {
                  $badge_name = __('Кино', 'quterma');
              }
          }

          // 4. Keyword heuristics from title/content if still unresolved
          if (empty($badge_name)) {
              $title_lower = function_exists('mb_strtolower') ? mb_strtolower(get_the_title(), 'UTF-8') : strtolower(get_the_title());
              if (preg_match('/(музык|трек|альбом|песн|плейлист|концерт|клип|звук|оркестр)/u', $title_lower)) {
                  $badge_name = __('Музыка', 'quterma');
                  $is_music   = true;
              } else {
                  $badge_name = __('Кино', 'quterma');
              }
          }

          // Pick the first editorial tag for the vinyl badge in top-right
          if (!empty($post_tags)) {
              foreach ($post_tags as $t) {
                  $t_name_check = function_exists('mb_strtolower') ? mb_strtolower(trim($t->name), 'UTF-8') : strtolower(trim($t->name));
                  $b_name_check = function_exists('mb_strtolower') ? mb_strtolower(trim($badge_name), 'UTF-8') : strtolower(trim($badge_name));
                  if ($t_name_check !== 'кино' && $t_name_check !== 'музыка' && $t_name_check !== 'кино и музыка' && $t_name_check !== $b_name_check) {
                      $tag_label = $t->name;
                      break;
                  }
              }
          }
      ?>
        <a href="<?php the_permalink(); ?>" class="cm-card">
          <div class="cm-media">
            <div class="cm-badge"><?php echo esc_html($badge_name); ?></div>
            <?php if (!empty($tag_label)) : ?>
              <span class="cm-vinyl-tag"><?php echo esc_html($tag_label); ?></span>
            <?php endif; ?>
            <?php if (has_post_thumbnail()) : ?>
              <div style="aspect-ratio:16/10;overflow:hidden">
                <?php the_post_thumbnail('quterma-card-4x3', array(
                    'loading' => 'lazy',
                    'sizes'   => '(max-width: 480px) 100vw, (max-width: 768px) 50vw, 360px',
                    'alt'     => '',
                )); ?>
              </div>
            <?php else : ?>
              <div class="ph-img" style="aspect-ratio:16/10"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round" width="38" height="38"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="9" cy="9" r="1.7"/><path d="M21 15l-4.5-4.5a1.5 1.5 0 0 0-2.12 0L4 21"/></svg></div>
            <?php endif; ?>
          </div>
          <div class="cm-body">
            <div class="cm-genre"><?php echo esc_html($cat_info['name']); ?></div>
            <h3 class="cm-title"><?php the_title(); ?></h3>
            <p class="cm-desc"><?php echo esc_html(wp_trim_words(get_the_excerpt(), 14, '…')); ?></p>
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
$iv_query      = quterma_get_interview_posts(3, $exclude_ids);
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
                    'sizes'   => '(max-width: 480px) 100vw, (max-width: 768px) 50vw, 360px',
                    'alt'     => '',
                )); ?>
              </div>
            <?php else : ?>
              <div class="ph-img" style="aspect-ratio:16/11"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" width="40" height="40"><circle cx="12" cy="7" r="4"/><path d="M6 21v-2a4 4 0 0 1 4-4h4a4 4 0 0 1 4 4v2"/></svg></div>
            <?php endif; ?>
          </div>
          <div class="iv-body">
            <div class="iv-person-row">
              <span class="iv-person-name"><?php echo esc_html($person_name); ?></span>
              <?php if (!empty($custom_role)) : ?>
                <span class="iv-person-role"><?php echo esc_html($custom_role); ?></span>
              <?php endif; ?>
            </div>
            <h3 class="iv-title"><?php the_title(); ?></h3>
            <p class="iv-excerpt"><?php echo esc_html(wp_trim_words(get_the_excerpt(), 14, '…')); ?></p>
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
