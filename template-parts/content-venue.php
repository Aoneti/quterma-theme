<?php
/**
 * Template part for displaying a Gastroguide venue card (.venue-card)
 *
 * @package Quterma
 */

if (!defined('ABSPATH')) {
    exit;
}

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
$desc      = get_the_excerpt();
if (empty($desc)) {
    $desc = wp_trim_words(get_the_content(), 15, '…');
}
?>
<a href="<?php the_permalink(); ?>" class="venue-card" data-city="<?php echo esc_attr($city_slug); ?>" data-type="<?php echo esc_attr(mb_strtolower($type)); ?>" data-price="<?php echo esc_attr($price); ?>">
  <div class="venue-img">
    <span class="venue-type-badge"><?php echo esc_html($type); ?></span>
    <span class="venue-city-badge"><?php echo esc_html($city_name); ?></span>
    <?php if (has_post_thumbnail()) : ?>
      <?php the_post_thumbnail('quterma-card-4x3', array('loading' => 'lazy', 'alt' => the_title_attribute(array('echo' => false)))); ?>
    <?php else : ?>
      <?php echo quterma_placeholder_img(38, 38); ?>
    <?php endif; ?>
  </div>
  <div class="venue-body">
    <div class="venue-name"><?php the_title(); ?></div>
    <?php if (!empty($address)) : ?>
      <div class="venue-address">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>
        </svg>
        <?php echo esc_html($address); ?>
      </div>
    <?php endif; ?>
    <?php if (!empty($desc)) : ?>
      <p class="venue-desc"><?php echo esc_html($desc); ?></p>
    <?php endif; ?>
  </div>
</a>
