<?php
/**
 * Template part for displaying a standard news card in lists/feeds
 *
 * @package Quterma
 */

if (!defined('ABSPATH')) {
    exit;
}

$cat_info = quterma_get_post_category_info();
$date_str = quterma_format_date(get_the_ID(), true);
$excerpt  = get_the_excerpt();
if (empty($excerpt)) {
    $excerpt = wp_trim_words(get_the_content(), 20, '…');
}
?>
<article class="news-card" data-category="<?php echo esc_attr($cat_info['slug']); ?>">
  <div class="nc-img">
    <?php if (has_post_thumbnail()) : ?>
      <?php the_post_thumbnail('quterma-card', array(
          'loading' => 'lazy',
          'sizes'   => '(max-width: 480px) 100vw, (max-width: 768px) 150px, 240px',
          'alt'     => the_title_attribute(array('echo' => false)),
      )); ?>
    <?php else : ?>
      <?php echo quterma_placeholder_img(30, 30); ?>
    <?php endif; ?>
  </div>
  <div class="nc-body">
    <span class="sr-only"><?php echo esc_html($cat_info['name']); ?></span>
    <h3 class="nc-title">
      <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
    </h3>
    <p class="nc-excerpt"><?php echo esc_html($excerpt); ?></p>
    <div class="nc-meta"><?php echo quterma_time_tag(get_the_ID(), true); ?></div>
  </div>
</article>
