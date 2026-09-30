<?php
/**
 * The template for displaying all pages
 *
 * @package Quterma
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();

while (have_posts()) : the_post();
    $subtitle = get_post_meta(get_the_ID(), '_page_subtitle', true);
    if (empty($subtitle) && has_excerpt()) {
        $subtitle = get_the_excerpt();
    }
?>

<div class="wrap">
  <div style="padding-top:28px">
    <?php get_template_part('template-parts/breadcrumbs'); ?>

    <div class="page-header">
      <h1 class="page-title"><?php the_title(); ?></h1>
      <?php if (!empty($subtitle)) : ?>
        <p class="page-subtitle"><?php echo esc_html($subtitle); ?></p>
      <?php endif; ?>
    </div>
  </div>

  <div class="page-content rev" style="padding-bottom:60px">
    <?php the_content(); ?>
  </div>
</div>

<?php
endwhile;

get_footer();
