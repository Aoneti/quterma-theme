<?php
/**
 * Template part for displaying "Читают сейчас" popular materials
 *
 * @package Quterma
 */

if (!defined('ABSPATH')) {
    exit;
}

$popular_posts = quterma_get_popular_posts(5);

if (empty($popular_posts)) {
    return;
}
?>
<div>
  <h2 class="sb-title"><?php esc_html_e('Читают сейчас', 'quterma'); ?></h2>
  <div class="top-list">
    <?php
    $index = 0;
    foreach ($popular_posts as $p) :
        $index++;
        $num_str  = sprintf('%02d', $index);
        $cat_info = quterma_get_post_category_info($p);
        $date_str = quterma_format_date($p, false);
    ?>
      <a href="<?php echo esc_url(get_permalink($p)); ?>" class="top-item">
        <div class="top-num"><?php echo esc_html($num_str); ?></div>
        <div>
          <div class="top-title"><?php echo esc_html(get_the_title($p)); ?></div>
          <div class="top-meta">
            <span class="sr-only"><?php echo esc_html($cat_info['name']); ?> · </span>
            <?php echo quterma_time_tag($p, false); ?>
          </div>
        </div>
      </a>
    <?php endforeach; ?>
  </div>
</div>
