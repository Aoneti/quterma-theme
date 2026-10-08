<?php
/**
 * The template for displaying a single Gastroguide venue
 *
 * @package Quterma
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();

while (have_posts()) : the_post();
    $post_id   = get_the_ID();
    $city_slug = get_post_meta($post_id, '_venue_city', true);
    if (empty($city_slug)) {
        $city_slug = 'yaroslavl';
    }
    $city_name = quterma_get_city_name($city_slug);
    $type      = get_post_meta($post_id, '_venue_type', true);
    if (empty($type)) {
        $type = __('Заведение', 'quterma');
    }
    $address   = get_post_meta($post_id, '_venue_address', true);
    $price     = get_post_meta($post_id, '_venue_price', true);
    $features  = get_post_meta($post_id, '_venue_features', true);
    $website   = get_post_meta($post_id, '_venue_website', true);
    $phone     = get_post_meta($post_id, '_venue_phone', true);
    $hours     = get_post_meta($post_id, '_venue_hours', true);

    // Price explanations from single source of truth
    $price_desc = quterma_get_venue_price_desc($price);

    if (is_array($features)) {
        $features_list = $features;
    } elseif (is_string($features) && !empty($features)) {
        $features_list = array_map('trim', explode(',', $features));
    } else {
        $features_list = array();
    }

    // Clean address for navigation links
    $clean_search_query = trim($city_name . ', ' . ($address ? $address : get_the_title()));
    $route_url = 'https://yandex.ru/maps/?text=' . rawurlencode($clean_search_query);

    // Other venues in this city
    $other_venues = new WP_Query(array(
        'post_type'      => 'quterma_venue',
        'post_status'    => 'publish',
        'posts_per_page' => 3,
        'post__not_in'   => array($post_id),
        'meta_query'     => array(
            array(
                'key'   => '_venue_city',
                'value' => $city_slug,
            ),
        ),
        'no_found_rows'  => true,
    ));
?>

<main id="content">
<article id="venue-<?php the_ID(); ?>" <?php post_class('venue-single-page'); ?>>
  <div class="wrap" style="padding-top:24px">
    <div style="max-width:960px;margin-left:auto;margin-right:auto">
      <?php get_template_part('template-parts/breadcrumbs'); ?>

      <div class="page-header" style="padding-bottom:18px">
        <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px;flex-wrap:wrap">
          <?php if (!empty($type)) : ?>
            <span class="venue-type-pill"><?php echo esc_html($type); ?></span>
          <?php endif; ?>
          <?php if (!empty($city_name)) : ?>
            <span class="venue-city-pill"><?php echo esc_html($city_name); ?></span>
          <?php endif; ?>
          <?php if (!empty($price)) : ?>
            <span class="venue-price-pill" title="<?php echo esc_attr($price_desc); ?>"><?php echo esc_html($price); ?></span>
          <?php endif; ?>
        </div>
        <h1 class="page-title" style="margin-bottom:8px"><?php the_title(); ?></h1>
      </div>

      <!-- ПРАКТИЧЕСКАЯ ИНФО-ПАНЕЛЬ ЗАВЕДЕНИЯ -->
      <?php if (!empty($address) || !empty($hours) || !empty($price) || !empty($phone) || !empty($features_list)) : ?>
      <div class="venue-practical-panel rev">
        <div class="venue-practical-grid">
          <!-- 1. Адрес и Маршрут -->
          <?php if (!empty($address)) : ?>
          <div class="vp-item vp-address-item">
            <div class="vp-icon">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
            </div>
            <div>
              <div class="vp-label"><?php esc_html_e('Адрес', 'quterma'); ?></div>
              <div class="vp-val"><?php echo esc_html($address); ?></div>
            </div>
          </div>
          <?php endif; ?>

          <!-- 2. Часы работы -->
          <?php if (!empty($hours)) : ?>
          <div class="vp-item">
            <div class="vp-icon">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </div>
            <div>
              <div class="vp-label"><?php esc_html_e('Режим работы', 'quterma'); ?></div>
              <div class="vp-val"><?php echo esc_html($hours); ?></div>
            </div>
          </div>
          <?php endif; ?>

          <!-- 3. Средний чек -->
          <?php if (!empty($price)) : ?>
          <div class="vp-item">
            <div class="vp-icon">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
            </div>
            <div>
              <div class="vp-label"><?php esc_html_e('Средний чек', 'quterma'); ?></div>
              <div class="vp-val">
                <strong><?php echo esc_html($price); ?></strong>
                <?php if (!empty($price_desc)) : ?>
                  <span class="vp-subval">(<?php echo esc_html($price_desc); ?>)</span>
                <?php endif; ?>
              </div>
            </div>
          </div>
          <?php endif; ?>

          <!-- 4. Телефон -->
          <?php if (!empty($phone)) : ?>
          <div class="vp-item">
            <div class="vp-icon">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
            </div>
            <div>
              <div class="vp-label"><?php esc_html_e('Телефон', 'quterma'); ?></div>
              <div class="vp-val">
                <a href="tel:<?php echo esc_attr(preg_replace('/[^\d+]/', '', $phone)); ?>" class="vp-phone-link">
                  <?php echo esc_html($phone); ?>
                </a>
              </div>
            </div>
          </div>
          <?php endif; ?>
        </div>

        <!-- КНОПКИ ДЕЙСТВИЙ (ПОСТРОИТЬ МАРШРУТ, ПОЗВОНИТЬ, САЙТ) -->
        <div class="venue-actions-row">
          <?php if (!empty($address) || !empty($city_name)) : ?>
          <a href="<?php echo esc_url($route_url); ?>" target="_blank" rel="noopener noreferrer" class="venue-btn-primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="16" height="16"><polygon points="3 11 22 2 13 21 11 13 3 11"/></svg>
            <?php esc_html_e('Построить маршрут на карте', 'quterma'); ?>
          </a>
          <?php endif; ?>

          <?php if (!empty($phone)) : ?>
            <a href="tel:<?php echo esc_attr(preg_replace('/[^\d+]/', '', $phone)); ?>" class="venue-btn-secondary">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="16" height="16"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
              <?php esc_html_e('Позвонить', 'quterma'); ?>
            </a>
          <?php endif; ?>

          <?php if (!empty($website)) : ?>
            <a href="<?php echo esc_url($website); ?>" target="_blank" rel="noopener noreferrer" class="venue-btn-ghost">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="16" height="16"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
              <?php esc_html_e('Сайт заведения', 'quterma'); ?>
            </a>
          <?php endif; ?>
        </div>

        <?php if (!empty($features_list)) : ?>
          <div class="venue-panel-features">
            <span class="venue-pf-title"><?php esc_html_e('Особенности:', 'quterma'); ?></span>
            <div class="venue-chips-row">
              <?php foreach ($features_list as $f) : ?>
                <span class="venue-feat-chip"><?php echo esc_html(trim($f)); ?></span>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endif; ?>
      </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- ВТОРОЙ СЛОЙ: ФОТОГРАФИИ И РЕДАКТОРСКИЙ ОБЗОР -->
  <div class="wrap" style="padding-top:28px">
    <div style="max-width:960px;margin-left:auto;margin-right:auto">
      <?php if (has_post_thumbnail()) : ?>
        <figure class="article-wide venue-main-photo" style="margin-bottom:32px;border-radius:10px;overflow:hidden">
          <?php the_post_thumbnail('quterma-hero', array(
              'loading'       => 'eager',
              'fetchpriority' => 'high',
              'sizes'         => '(max-width: 960px) 100vw, 960px',
              'alt'           => the_title_attribute(array('echo' => false)),
          )); ?>
        </figure>
      <?php endif; ?>

      <div class="article-col article-body rev">
        <?php the_content(); ?>
      </div>
    </div>
  </div>
</article>

<?php if ($other_venues->have_posts()) : ?>
<section class="article-related rev">
  <div class="wrap">
    <div class="sec-div" style="margin-top:0">
      <div class="sec-div-acc"></div>
      <h2 class="sec-div-title"><?php printf(__('Другие заведения: %s', 'quterma'), esc_html($city_name)); ?></h2>
      <div class="sec-div-line"></div>
      <a href="<?php echo esc_url(home_url('/gastroguide/?city=' . $city_slug)); ?>" class="sec-div-link">
        <?php esc_html_e('Все заведения в городе', 'quterma'); ?>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
      </a>
    </div>
    <div class="venue-grid">
      <?php
      while ($other_venues->have_posts()) : $other_venues->the_post();
          get_template_part('template-parts/content', 'venue');
      endwhile;
      wp_reset_postdata();
      ?>
    </div>
  </div>
</section>
<?php endif; ?>
</main>

<?php
endwhile;

get_footer();
