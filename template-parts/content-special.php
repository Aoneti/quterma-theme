<?php
/**
 * Template part for Longreads and Special Projects cards
 *
 * @package Quterma
 */

if (!defined('ABSPATH')) {
    exit;
}

$cat_info = quterma_get_post_category_info();
$desc     = get_the_excerpt();
if (empty($desc)) {
    $desc = wp_trim_words(get_the_content(), 15, '…');
}
?>
<a href="<?php the_permalink(); ?>" class="spec-card">
  <div class="spec-acc"></div>
  <span class="sr-only"><?php echo esc_html($cat_info['name']); ?></span>
  <h3 class="spec-title"><?php the_title(); ?></h3>
  <p class="spec-desc"><?php echo esc_html($desc); ?></p>
</a>
