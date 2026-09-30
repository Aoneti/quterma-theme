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
$cinema_music_query = quterma_get_cinema_music_posts(4);

$feed_page = get_page_by_path('feed');
$feed_url  = $feed_page ? get_permalink($feed_page) : home_url('/feed/');

$culture_layer_page = get_page_by_path('culture');
$culture_layer_url  = $culture_layer_page ? get_permalink($culture_layer_page) : (file_exists(get_template_directory() . '/culture.html') ? home_url('/culture.html') : home_url('/culture/'));

$longreads_page = get_page_by_path('longreads');
$longreads_url  = $longreads_page ? get_permalink($longreads_page) : (file_exists(get_template_directory() . '/longreads.html') ? home_url('/longreads.html') : home_url('/longreads/'));

$cinema_music_page = get_page_by_path('cinema-music');
$cinema_music_url  = $cinema_music_page ? get_permalink($cinema_music_page) : (file_exists(get_template_directory() . '/cinema-music.html') ? home_url('/cinema-music.html') : home_url('/cinema-music/'));
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

      <!-- ФИЛЬТРЫ РУБРИК ЛЕНТЫ С ПОЛЗУНКОМ И СТРЕЛКАМИ -->
      <div class="filters-slider-wrap">
        <button class="filter-scroll-btn filter-scroll-prev" type="button" aria-label="<?php esc_attr_e('Назад', 'quterma'); ?>">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
        </button>
        <div class="filters" id="feedFilters">
          <button type="button" class="filter active" data-filter="all"><?php esc_html_e('Все', 'quterma'); ?></button>
          <button type="button" class="filter" data-filter="city"><?php esc_html_e('Город', 'quterma'); ?></button>
          <button type="button" class="filter" data-filter="people"><?php esc_html_e('Люди', 'quterma'); ?></button>
          <button type="button" class="filter" data-filter="culture"><?php esc_html_e('Культура', 'quterma'); ?></button>
          <button type="button" class="filter" data-filter="art"><?php esc_html_e('Искусство', 'quterma'); ?></button>
          <button type="button" class="filter" data-filter="heritage"><?php esc_html_e('Наследие', 'quterma'); ?></button>
          <button type="button" class="filter" data-filter="improvement"><?php esc_html_e('Благоустройство', 'quterma'); ?></button>
          <button type="button" class="filter" data-filter="ecology"><?php esc_html_e('Экология', 'quterma'); ?></button>
          <button type="button" class="filter" data-filter="sport"><?php esc_html_e('Спорт', 'quterma'); ?></button>
        </div>
        <button class="filter-scroll-btn filter-scroll-next" type="button" aria-label="<?php esc_attr_e('Вперед', 'quterma'); ?>">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
        </button>
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
<?php if ($culture_query->have_posts()) : ?>
<section class="culture-section rev">
  <div class="wrap">
    <div class="sec-div" style="margin-top:0">
      <div class="sec-div-acc" style="background:var(--accent)"></div>
      <h2 class="sec-div-title"><?php esc_html_e('Культурный слой', 'quterma'); ?></h2>
      <div class="sec-div-line" style="background:rgba(46,107,78,.18)"></div>
      <a href="<?php echo esc_url($culture_layer_url); ?>" class="sec-div-link" style="color:var(--accent)">
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
    <a href="<?php echo esc_url($longreads_url); ?>" class="sec-div-link" style="color:var(--accent)">
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
<section class="cinema-section rev" aria-label="<?php esc_attr_e('Кино и музыка', 'quterma'); ?>">
  <div class="wrap">
    <div class="sec-div" style="margin-top:0">
      <div class="sec-div-acc" style="background:var(--accent-2)"></div>
      <h2 class="sec-div-title"><?php esc_html_e('Кино и музыка', 'quterma'); ?></h2>
      <div class="sec-div-line"></div>
      <a href="<?php echo esc_url($cinema_music_url); ?>" class="sec-div-link">
        <?php esc_html_e('Все', 'quterma'); ?>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
      </a>
    </div>

    <div class="cinema-grid">
      <?php
      if ($cinema_music_query->have_posts()) :
          $cm_idx = 0;
          $cm_types = array(
              array('badge' => __('Кино', 'quterma'), 'tag' => __('Премьера', 'quterma'), 'genre' => __('Детектив · История', 'quterma'), 'meta' => __('12 мин чтения', 'quterma'), 'hint' => __('Обзор', 'quterma'), 'is_music' => false),
              array('badge' => __('Музыка', 'quterma'), 'tag' => __('Плейлист', 'quterma'), 'genre' => __('Инди-фолк · Локальная сцена', 'quterma'), 'meta' => __('42 мин трек-лист', 'quterma'), 'hint' => __('Слушать', 'quterma'), 'is_music' => true),
              array('badge' => __('Кино', 'quterma'), 'tag' => __('Рецензия', 'quterma'), 'genre' => __('Фестивальное кино · Авторское', 'quterma'), 'meta' => __('8 мин чтения', 'quterma'), 'hint' => __('Читать', 'quterma'), 'is_music' => false),
              array('badge' => __('Музыка', 'quterma'), 'tag' => __('Интервью', 'quterma'), 'genre' => __('Джаз & Соул · Интервью', 'quterma'), 'meta' => __('15 мин чтения', 'quterma'), 'hint' => __('Интервью', 'quterma'), 'is_music' => true),
          );
          while ($cinema_music_query->have_posts()) : $cinema_music_query->the_post();
              $cfg = isset($cm_types[$cm_idx % 4]) ? $cm_types[$cm_idx % 4] : $cm_types[0];
              $cm_idx++;
      ?>
        <a href="<?php the_permalink(); ?>" class="cm-card">
          <div class="cm-media">
            <div class="cm-badge">
              <?php if ($cfg['is_music']) : ?>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="3"/><line x1="12" y1="2" x2="12" y2="4"/><line x1="12" y1="20" x2="12" y2="22"/></svg>
              <?php else : ?>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="20" rx="2.18" ry="2.18"/><line x1="7" y1="2" x2="7" y2="22"/><line x1="17" y1="2" x2="17" y2="22"/><line x1="2" y1="12" x2="22" y2="12"/><line x1="2" y1="7" x2="7" y2="7"/><line x1="2" y1="17" x2="7" y2="17"/><line x1="17" y1="17" x2="22" y2="17"/><line x1="17" y1="7" x2="22" y2="7"/></svg>
              <?php endif; ?>
              <?php echo esc_html($cfg['badge']); ?>
            </div>
            <span class="cm-vinyl-tag"><?php echo esc_html($cfg['tag']); ?></span>
            <?php if (has_post_thumbnail()) : ?>
              <div style="aspect-ratio:16/10;overflow:hidden">
                <?php the_post_thumbnail('quterma-card-4x3', array('loading' => 'lazy', 'alt' => the_title_attribute(array('echo' => false)))); ?>
              </div>
            <?php else : ?>
              <div class="ph-img" style="aspect-ratio:16/10"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round" width="38" height="38"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="9" cy="9" r="1.7"/><path d="M21 15l-4.5-4.5a1.5 1.5 0 0 0-2.12 0L4 21"/></svg></div>
            <?php endif; ?>
          </div>
          <div class="cm-body">
            <div class="cm-genre"><?php echo esc_html($cfg['genre']); ?></div>
            <h3 class="cm-title"><?php the_title(); ?></h3>
            <p class="cm-desc"><?php echo wp_trim_words(get_the_excerpt(), 14, '…'); ?></p>
            <div class="cm-meta">
              <span class="cm-meta-time"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg> <?php echo esc_html($cfg['meta']); ?></span>
              <span class="cm-play-hint"><?php echo esc_html($cfg['hint']); ?> <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg></span>
            </div>
          </div>
        </a>
      <?php endwhile; wp_reset_postdata(); else : ?>
        <a href="<?php echo esc_url($cinema_music_url); ?>" class="cm-card">
          <div class="cm-media">
            <div class="cm-badge">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="20" rx="2.18" ry="2.18"/><line x1="7" y1="2" x2="7" y2="22"/><line x1="17" y1="2" x2="17" y2="22"/><line x1="2" y1="12" x2="22" y2="12"/><line x1="2" y1="7" x2="7" y2="7"/><line x1="2" y1="17" x2="7" y2="17"/><line x1="17" y1="17" x2="22" y2="17"/><line x1="17" y1="7" x2="22" y2="7"/></svg>
              <?php esc_html_e('Кино', 'quterma'); ?>
            </div>
            <span class="cm-vinyl-tag"><?php esc_html_e('Премьера', 'quterma'); ?></span>
            <div class="ph-img" style="aspect-ratio:16/10"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round" width="38" height="38"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="9" cy="9" r="1.7"/><path d="M21 15l-4.5-4.5a1.5 1.5 0 0 0-2.12 0L4 21"/></svg></div>
          </div>
          <div class="cm-body">
            <div class="cm-genre"><?php esc_html_e('Детектив · История', 'quterma'); ?></div>
            <h3 class="cm-title"><?php esc_html_e('Новый исторический сериал о ярославском сыске XIX века', 'quterma'); ?></h3>
            <p class="cm-desc"><?php esc_html_e('Съёмки проходили в исторических кварталах зоны ЮНЕСКО — как воссоздавали дух эпохи.', 'quterma'); ?></p>
            <div class="cm-meta">
              <span class="cm-meta-time"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg> 12 мин чтения</span>
              <span class="cm-play-hint"><?php esc_html_e('Обзор', 'quterma'); ?> <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg></span>
            </div>
          </div>
        </a>
        <a href="<?php echo esc_url($cinema_music_url); ?>" class="cm-card">
          <div class="cm-media">
            <div class="cm-badge">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="3"/><line x1="12" y1="2" x2="12" y2="4"/><line x1="12" y1="20" x2="12" y2="22"/></svg>
              <?php esc_html_e('Музыка', 'quterma'); ?>
            </div>
            <span class="cm-vinyl-tag"><?php esc_html_e('Плейлист', 'quterma'); ?></span>
            <div class="ph-img" style="aspect-ratio:16/10"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round" width="38" height="38"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="9" cy="9" r="1.7"/><path d="M21 15l-4.5-4.5a1.5 1.5 0 0 0-2.12 0L4 21"/></svg></div>
          </div>
          <div class="cm-body">
            <div class="cm-genre"><?php esc_html_e('Инди-фолк · Локальная сцена', 'quterma'); ?></div>
            <h3 class="cm-title"><?php esc_html_e('Звуки Волги: 10 свежих треков от ярославских независимых групп', 'quterma'); ?></h3>
            <p class="cm-desc"><?php esc_html_e('От акустического эмбиента до драйвового пост-панка — плейлист редакции для долгих прогулок.', 'quterma'); ?></p>
            <div class="cm-meta">
              <span class="cm-meta-time"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg> 42 мин трек-лист</span>
              <span class="cm-play-hint"><?php esc_html_e('Слушать', 'quterma'); ?> <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg></span>
            </div>
          </div>
        </a>
      <?php endif; ?>
    </div>
  </div>
</section>

<?php
// INTERVIEWS SECTION
$interview_page = get_page_by_path('interview');
$interview_url  = $interview_page ? get_permalink($interview_page) : (file_exists(get_template_directory() . '/interview.html') ? home_url('/interview.html') : home_url('/interview/'));
$iv_query       = quterma_get_interview_posts(4);
?>
<section class="interview-sec" style="background:var(--paper-2);border-top:1px solid var(--bd);border-bottom:1px solid var(--bd);padding:48px 0;margin-top:40px">
  <div class="wrap">
    <div class="sec-hdr rev">
      <div>
        <div class="sec-label" style="color:var(--brand)"><?php esc_html_e('Разговоры', 'quterma'); ?></div>
        <h2 class="sec-title"><?php esc_html_e('Интервью', 'quterma'); ?></h2>
      </div>
      <a href="<?php echo esc_url($interview_url); ?>" class="sec-more">
        <?php esc_html_e('Все интервью', 'quterma'); ?>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
      </a>
    </div>

    <div class="interview-grid rev" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:22px">
      <?php if ($iv_query->have_posts()) : while ($iv_query->have_posts()) : $iv_query->the_post();
        $custom_person = get_post_meta(get_the_ID(), '_iv_person', true);
        $person_name   = !empty($custom_person) ? $custom_person : get_the_title();
      ?>
        <a href="<?php the_permalink(); ?>" class="iv-card">
          <div class="iv-media">
            <div class="iv-badge"><?php esc_html_e('Интервью', 'quterma'); ?></div>
            <?php if (has_post_thumbnail()) : ?>
              <div style="aspect-ratio:1/1;overflow:hidden">
                <?php the_post_thumbnail('quterma-card-4x3', array('loading' => 'lazy', 'alt' => the_title_attribute(array('echo' => false)))); ?>
              </div>
            <?php else : ?>
              <div class="ph-img" style="aspect-ratio:1/1"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" width="40" height="40"><circle cx="12" cy="7" r="4"/><path d="M6 21v-2a4 4 0 0 1 4-4h4a4 4 0 0 1 4 4v2"/></svg></div>
            <?php endif; ?>
          </div>
          <div class="iv-body">
            <div class="iv-person"><?php echo esc_html($person_name); ?></div>
            <p class="iv-excerpt"><?php echo wp_trim_words(get_the_excerpt(), 14, '…'); ?></p>
            <div class="iv-meta">
              <span><?php echo esc_html(get_the_date('j F')); ?></span>
              <span class="iv-meta-cta"><?php esc_html_e('Читать →', 'quterma'); ?></span>
            </div>
          </div>
        </a>
      <?php endwhile; wp_reset_postdata(); else : ?>
        <a href="<?php echo esc_url($interview_url); ?>" class="iv-card">
          <div class="iv-media">
            <div class="iv-badge"><?php esc_html_e('Интервью', 'quterma'); ?></div>
            <div class="ph-img" style="aspect-ratio:1/1"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" width="40" height="40"><circle cx="12" cy="7" r="4"/><path d="M6 21v-2a4 4 0 0 1 4-4h4a4 4 0 0 1 4 4v2"/></svg></div>
          </div>
          <div class="iv-body">
            <div class="iv-person"><?php esc_html_e('Елена Смирнова', 'quterma'); ?></div>
            <div class="iv-role"><?php esc_html_e('Шеф-повар волжского бистро', 'quterma'); ?></div>
            <p class="iv-excerpt"><?php esc_html_e('«Локальная кухня Ярославля — это не только волжская рыба, но и дикоросы лесов, ремесленные сыры и забытые рецепты купеческих трапез».', 'quterma'); ?></p>
            <div class="iv-meta">
              <span><?php esc_html_e('16 минут · Вчера', 'quterma'); ?></span>
              <span class="iv-meta-cta"><?php esc_html_e('Читать →', 'quterma'); ?></span>
            </div>
          </div>
        </a>
        <a href="<?php echo esc_url($interview_url); ?>" class="iv-card">
          <div class="iv-media">
            <div class="iv-badge"><?php esc_html_e('Интервью', 'quterma'); ?></div>
            <div class="ph-img" style="aspect-ratio:1/1"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" width="40" height="40"><circle cx="12" cy="7" r="4"/><path d="M6 21v-2a4 4 0 0 1 4-4h4a4 4 0 0 1 4 4v2"/></svg></div>
          </div>
          <div class="iv-body">
            <div class="iv-person"><?php esc_html_e('Дмитрий Васильев', 'quterma'); ?></div>
            <div class="iv-role"><?php esc_html_e('Художник-монументалист, создатель арт-резиденции', 'quterma'); ?></div>
            <p class="iv-excerpt"><?php esc_html_e('«Современное искусство в древнем городе не должно спорить с церквями — оно должно вступать в диалог с фабричным наследием».', 'quterma'); ?></p>
            <div class="iv-meta">
              <span><?php esc_html_e('24 минуты · 3 дня назад', 'quterma'); ?></span>
              <span class="iv-meta-cta"><?php esc_html_e('Читать →', 'quterma'); ?></span>
            </div>
          </div>
        </a>
      <?php endif; ?>
    </div>
  </div>
</section>

<?php
get_footer();
