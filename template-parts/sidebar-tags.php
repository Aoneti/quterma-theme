<?php
/**
 * Template part for displaying "Темы" (Tags) in sidebar
 *
 * @package Quterma
 */

if (!defined('ABSPATH')) {
    exit;
}

$all_tags_url = quterma_get_page_url('rubrics', home_url('/rubrics/'));

// Tags ordered by count (popularity)
$tags = get_tags(array(
    'number'     => 16,
    'orderby'    => 'count',
    'order'      => 'DESC',
    'hide_empty' => true,
));
?>
<div class="sb-block sb-themes-block">
  <h2 class="sb-title">
    <span><?php esc_html_e('Темы', 'quterma'); ?></span>
    <a href="<?php echo esc_url($all_tags_url); ?>" class="sb-more">
      <?php esc_html_e('Все', 'quterma'); ?>
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
    </a>
  </h2>
  <div class="tags-cloud">
    <?php if (!empty($tags)) : ?>
      <?php foreach ($tags as $tag) : ?>
        <a href="<?php echo esc_url(get_tag_link($tag)); ?>" class="tag" data-count="<?php echo (int) $tag->count; ?>">
          <?php echo esc_html($tag->name); ?>
        </a>
      <?php endforeach; ?>
    <?php else : ?>
      <span style="font-size:13px;color:var(--ink-4)"><?php esc_html_e('Метки пока не добавлены', 'quterma'); ?></span>
    <?php endif; ?>
  </div>
</div>
