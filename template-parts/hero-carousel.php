<?php
/**
 * Template part for the Hero Carousel on the front page
 * Category: 'Карусель' (slug 'karusel' or 'carousel')
 *
 * @package Quterma
 */

if (!defined('ABSPATH')) {
    exit;
}

$carousel_query = isset($args['carousel_query']) ? $args['carousel_query'] : get_query_var('carousel_query');
if (!$carousel_query) {
    $carousel_query = quterma_get_carousel_posts(6);
}

if (!$carousel_query->have_posts()) {
    return;
}
?>
<section class="carousel-section rev" aria-label="<?php esc_attr_e('Главные материалы', 'quterma'); ?>">
  <div class="wrap">
    <div class="hero-carousel" id="heroCarousel">
      <div class="hc-track" id="hcTrack">
        <?php
        $slide_index = 0;
        while ($carousel_query->have_posts()) : $carousel_query->the_post();
            $slide_index++;
            $cat_info = quterma_get_post_category_info();
            $date_str = quterma_format_date(get_the_ID(), false);
            $is_first = ($slide_index === 1);
        ?>
          <div class="hc-slide">
            <?php if (has_post_thumbnail()) : ?>
              <?php
              the_post_thumbnail('quterma-hero', array(
                  'loading'       => $is_first ? 'eager' : 'lazy',
                  'fetchpriority' => $is_first ? 'high' : 'auto',
                  'sizes'         => '(max-width: 768px) 100vw, (max-width: 1240px) 960px, 1200px',
                  'alt'           => the_title_attribute(array('echo' => false)),
              ));
              ?>
            <?php else : ?>
              <div class="ph-img">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round">
                  <rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="9" cy="9" r="1.7"/><path d="M21 15l-4.5-4.5a1.5 1.5 0 0 0-2.12 0L4 21"/>
                </svg>
              </div>
            <?php endif; ?>

            <a href="<?php the_permalink(); ?>" class="hc-slide-link">
              <div class="hc-content">
                <span class="sr-only"><?php echo esc_html($cat_info['name']); ?></span>
                <h2 class="hc-title"><?php the_title(); ?></h2>
                <div class="hc-meta"><?php echo quterma_time_tag(get_the_ID(), false); ?></div>
              </div>
            </a>
          </div>
        <?php endwhile; wp_reset_postdata(); ?>
      </div>

      <button type="button" class="hc-arrow prev" id="hcPrev" aria-label="<?php esc_attr_e('Предыдущий слайд', 'quterma'); ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
      </button>
      <button type="button" class="hc-arrow next" id="hcNext" aria-label="<?php esc_attr_e('Следующий слайд', 'quterma'); ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
      </button>
      <div class="hc-dots" id="hcDots"></div>
    </div>
  </div>
</section>
