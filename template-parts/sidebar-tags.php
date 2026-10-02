<?php
/**
 * Template part for displaying "Рубрики" popular tags/categories cloud in sidebar
 *
 * @package Quterma
 */

if (!defined('ABSPATH')) {
    exit;
}

$rubrics_url = quterma_get_page_url('rubrics', home_url('/rubrics/'));

$tags = get_tags(array(
    'number'  => 16,
    'orderby' => 'count',
    'order'   => 'DESC',
    'exclude' => array(), // Gastroguide and Events are post types/venues, not tags
));

if (empty($tags)) {
    return;
}
?>
<div>
  <h2 class="sb-title">
    <span><?php esc_html_e('Рубрики', 'quterma'); ?></span>
    <a href="<?php echo esc_url($rubrics_url); ?>" class="sb-more">
      <?php esc_html_e('Все', 'quterma'); ?>
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
    </a>
  </h2>
  <div class="tags-cloud">
    <?php foreach ($tags as $tag) : ?>
      <a href="<?php echo esc_url(get_tag_link($tag)); ?>" class="tag" data-count="<?php echo (int) $tag->count; ?>">
        <?php echo esc_html($tag->name); ?>
      </a>
    <?php endforeach; ?>
  </div>
</div>
