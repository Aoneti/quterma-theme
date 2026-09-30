<?php
/**
 * Template Name: Все рубрики (All Rubrics Page)
 *
 * @package Quterma
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();

$categories = get_categories(array(
    'orderby'    => 'count',
    'order'      => 'DESC',
    'hide_empty' => false,
    'exclude'    => array(),
));

// Filter out carousel/lenta or unwanted technical slugs
$excluded_slugs = array('karusel', 'carousel', 'lenta');
$valid_cats = array();
foreach ($categories as $cat) {
    if (in_array($cat->slug, $excluded_slugs)) {
        continue;
    }
    $valid_cats[] = $cat;
}
?>

<div class="wrap">
  <div style="padding-top:28px">
    <?php get_template_part('template-parts/breadcrumbs'); ?>

    <div class="page-header">
      <h1 class="page-title"><?php esc_html_e('Все рубрики', 'quterma'); ?></h1>
    </div>
  </div>

  <div class="rubrics-grid-page rev" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:24px;margin-bottom:80px">
    <?php foreach ($valid_cats as $cat) : 
      $cat_link = get_category_link($cat);
      $cat_desc = !empty($cat->description) ? $cat->description : __('Материалы и публикации издания по теме «', 'quterma') . $cat->name . '».';
    ?>
      <a href="<?php echo esc_url($cat_link); ?>" class="rubric-big-card">
        <div class="rubric-big-card-hdr">
          <h2 class="rubric-big-title"><?php echo esc_html($cat->name); ?></h2>
          <div class="rubric-big-badge">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="18" height="18"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
          </div>
        </div>
        <p class="rubric-big-desc"><?php echo esc_html($cat_desc); ?></p>
        <div class="rubric-meta-row">
          <span><?php esc_html_e('Перейти в рубрику →', 'quterma'); ?></span>
        </div>
      </a>
    <?php endforeach; ?>
  </div>
</div>

<?php
get_footer();
