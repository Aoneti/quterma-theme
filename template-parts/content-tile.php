<?php
/**
 * Template part for displaying a tile in .tile-grid (Culture / History / People)
 *
 * @package Quterma
 */

if (!defined('ABSPATH')) {
    exit;
}

$tile_size = get_query_var('tile_size', '');
$class_name = 'tile' . (!empty($tile_size) ? ' ' . esc_attr($tile_size) : '');
$date_str = quterma_format_date(get_the_ID(), false);
?>
<a href="<?php the_permalink(); ?>" class="<?php echo esc_attr($class_name); ?>">
  <?php if (has_post_thumbnail()) : ?>
    <?php
    $thumb_size = (!empty($tile_size) && strpos($tile_size, 'size-hero') !== false) ? 'quterma-tile-large' : 'quterma-tile';
    $sizes_attr = (!empty($tile_size) && strpos($tile_size, 'size-hero') !== false) ? '(max-width: 768px) 100vw, 840px' : '(max-width: 768px) 100vw, (max-width: 1024px) 50vw, 420px';
    the_post_thumbnail($thumb_size, array(
        'class'   => 'tile-img',
        'loading' => 'lazy',
        'sizes'   => $sizes_attr,
        'alt'     => '',
    ));
    ?>
  <?php else : ?>
    <div class="tile-ph ph-img">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round" width="38" height="38">
        <rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="9" cy="9" r="1.7"/><path d="M21 15l-4.5-4.5a1.5 1.5 0 0 0-2.12 0L4 21"/>
      </svg>
    </div>
  <?php endif; ?>

  <div class="tile-content">
    <div class="tile-title"><?php the_title(); ?></div>
    <?php echo quterma_time_tag(get_the_ID(), false, 'tile-meta'); ?>
  </div>
</a>
