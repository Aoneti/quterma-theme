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
$features  = get_post_meta($post_id, '_venue_features', true);

if (is_array($features)) {
    $features_str = implode(' ', array_map(function ($f) {
        return function_exists('quterma_strtolower') ? quterma_strtolower($f) : (function_exists('mb_strtolower') ? mb_strtolower($f, 'UTF-8') : strtolower($f));
    }, $features));
    $features_list = $features;
} elseif (is_string($features) && !empty($features)) {
    $features_str = function_exists('quterma_strtolower') ? quterma_strtolower($features) : (function_exists('mb_strtolower') ? mb_strtolower($features, 'UTF-8') : strtolower($features));
    $features_list = array_map('trim', explode(',', $features));
} else {
    $features_str = '';
    $features_list = array();
}

$desc = get_the_excerpt();
if (empty($desc)) {
    $desc = wp_trim_words(get_the_content(), 15, '…');
}

$type_attr = function_exists('quterma_strtolower') ? quterma_strtolower($type) : (function_exists('mb_strtolower') ? mb_strtolower($type, 'UTF-8') : strtolower($type));
?>
<a href="<?php the_permalink(); ?>" class="venue-card" data-city="<?php echo esc_attr($city_slug); ?>" data-type="<?php echo esc_attr($type_attr); ?>" data-price="<?php echo esc_attr($price); ?>" data-features="<?php echo esc_attr($features_str); ?>">
  <div class="venue-img">
    <?php if (!empty($type)) : ?>
      <span class="venue-type-badge"><?php echo esc_html($type); ?></span>
    <?php endif; ?>
    <?php if (!empty($city_name)) : ?>
      <span class="venue-city-badge"><?php echo esc_html($city_name); ?></span>
    <?php endif; ?>
    <?php if (has_post_thumbnail()) : ?>
      <?php the_post_thumbnail('quterma-card-4x3', array(
          'loading' => 'lazy',
          'sizes'   => '(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 380px',
          'alt'     => '',
      )); ?>
    <?php else : ?>
      <?php echo quterma_placeholder_img(38, 38); ?>
    <?php endif; ?>
  </div>
  <div class="venue-body">
    <div class="venue-name"><?php the_title(); ?></div>

    <!-- ЦЕНА И ОСОБЕННОСТИ ЗАВЕДЕНИЯ -->
    <?php if (!empty($price) || !empty($features_list)) : ?>
    <div class="venue-chips-row">
      <?php if (!empty($price)) : ?>
        <span class="venue-price-chip" title="<?php esc_attr_e('Ценовой диапазон', 'quterma'); ?>"><?php echo esc_html($price); ?></span>
      <?php endif; ?>
      <?php if (!empty($features_list)) : ?>
        <?php foreach (array_slice($features_list, 0, 3) as $feat_item) : ?>
          <span class="venue-feat-chip"><?php echo esc_html($feat_item); ?></span>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
    <?php endif; ?>

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
