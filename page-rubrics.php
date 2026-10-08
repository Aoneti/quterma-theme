<?php
/**
 * Template Name: Все рубрики
 * Description: Шаблон страницы всех рубрик и тем
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
    'hide_empty' => true,
));

// Filter out placement and technical categories using canonical theme helper
$valid_cats = array();
foreach ($categories as $cat) {
    if (function_exists('quterma_is_technical_category') && quterma_is_technical_category($cat)) {
        continue;
    }
    if ($cat->count < 1) {
        continue;
    }
    $valid_cats[] = $cat;
}

// Single-query batch priming for latest posts to eliminate N+1 database queries
$valid_cat_ids    = wp_list_pluck($valid_cats, 'term_id');
$latest_posts_map = array();

if (!empty($valid_cat_ids)) {
    $batch_query = new WP_Query(array(
        'post_type'           => 'post',
        'post_status'         => 'publish',
        'posts_per_page'      => 60,
        'category__in'        => $valid_cat_ids,
        'orderby'             => 'date',
        'order'               => 'DESC',
        'ignore_sticky_posts' => true,
        'no_found_rows'       => true,
    ));

    if ($batch_query->have_posts()) {
        foreach ($batch_query->posts as $p) {
            $p_cats = wp_get_post_categories($p->ID);
            foreach ($p_cats as $cid) {
                if (!isset($latest_posts_map[$cid])) {
                    $thumb = has_post_thumbnail($p) ? get_the_post_thumbnail_url($p, 'quterma-card-4x3') : '';
                    $latest_posts_map[$cid] = array(
                        'title' => get_the_title($p),
                        'thumb' => $thumb,
                    );
                }
            }
        }
    }
}
?>

<main id="content" class="wrap">
  <div style="padding-top:28px">
    <?php get_template_part('template-parts/breadcrumbs'); ?>
    <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
      <div class="page-header">
        <h1 class="page-title"><?php the_title(); ?></h1>
        <?php if (has_excerpt()) : ?>
          <p class="page-subtitle"><?php echo esc_html(get_the_excerpt()); ?></p>
        <?php else : ?>
          <p class="page-subtitle"><?php esc_html_e('Тематические метки, разделы и специальные направления издания «Кутерьма».', 'quterma'); ?></p>
        <?php endif; ?>
      </div>
      <?php if (get_the_content()) : ?>
        <div class="page-content rev" style="margin-bottom:28px">
          <?php the_content(); ?>
        </div>
      <?php endif; ?>
    <?php endwhile; else : ?>
      <div class="page-header">
        <h1 class="page-title"><?php esc_html_e('Все темы и рубрики', 'quterma'); ?></h1>
        <p class="page-subtitle"><?php esc_html_e('Тематические метки, разделы и специальные направления издания «Кутерьма».', 'quterma'); ?></p>
      </div>
    <?php endif; ?>
  </div>

  <?php
  // Tags with counts (safely limited to top 50 to avoid unbounded page payload)
  $all_tags = get_tags(array(
      'orderby'    => 'count',
      'order'      => 'DESC',
      'hide_empty' => true,
      'number'     => 50,
  ));
  if (!empty($all_tags)) :
  ?>
  <div class="wrap" style="margin-bottom:48px">
    <div class="tags-cloud" style="display:flex;flex-wrap:wrap;gap:10px">
      <?php foreach ($all_tags as $t) :
        $t_count = (int) $t->count;
        $cnt_label = sprintf('%d %s', $t_count, quterma_plural($t_count, array('материал', 'материала', 'материалов')));
      ?>
        <a href="<?php echo esc_url(get_tag_link($t)); ?>" class="tag theme-tag" style="padding:8px 16px;font-size:14px;display:inline-flex;align-items:center;gap:6px" title="<?php echo esc_attr($cnt_label); ?>">
          <span>#<?php echo esc_html($t->name); ?></span>
          <span style="font-size:12px;opacity:.7;font-weight:700">(<?php echo $t_count; ?>)</span>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>

  <div class="wrap">
    <div class="sec-div" style="margin-top:0">
      <div class="sec-div-acc"></div>
      <h2 class="sec-div-title"><?php esc_html_e('Разделы издания', 'quterma'); ?></h2>
      <div class="sec-div-line"></div>
    </div>
  </div>

  <div class="rubrics-grid-page rev" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(340px,1fr));gap:24px;margin-bottom:80px">
    <?php foreach ($valid_cats as $cat) : 
      $cat_link  = get_category_link($cat);
      $cat_count = $cat->count;
      $count_str = sprintf('%d %s', $cat_count, quterma_plural($cat_count, array('материал', 'материала', 'материалов')));
      
      $latest_info  = isset($latest_posts_map[$cat->term_id]) ? $latest_posts_map[$cat->term_id] : null;
      $latest_title = $latest_info ? $latest_info['title'] : '';
      $latest_thumb = $latest_info ? $latest_info['thumb'] : '';

      $cat_desc = !empty($cat->description) ? $cat->description : '';
      if (empty($cat_desc)) {
          $slug_descriptions = array(
              'city'    => __('Городская среда, архитектура, урбанистика и благоустройство улиц', 'quterma'),
              'culture' => __('Театр, выставки, фестивали, музейные премьеры и арт-жизнь', 'quterma'),
              'art'     => __('Художники, мастерские, галереи и актуальные арт-практики', 'quterma'),
              'people'  => __('Портреты горожан, интервью с создателями знаковых городских проектов', 'quterma'),
              'history' => __('Исторические очерки, архивные хроники и краеведческие находки', 'quterma'),
              'sport'   => __('Локальный спорт, городские забеги, водные прогулки и активности', 'quterma'),
          );
          $cat_desc = isset($slug_descriptions[$cat->slug]) ? $slug_descriptions[$cat->slug] : sprintf(__('Материалы редакции в разделе «%s»', 'quterma'), $cat->name);
      }
    ?>
      <a href="<?php echo esc_url($cat_link); ?>" class="rubric-big-card">
        <?php if (!empty($latest_thumb)) : ?>
          <div class="rubric-card-cover" style="aspect-ratio:16/9;overflow:hidden;border-radius:6px;margin-bottom:16px;background:var(--paper-2)">
            <img src="<?php echo esc_url($latest_thumb); ?>" alt="" loading="lazy" style="width:100%;height:100%;object-fit:cover" />
          </div>
        <?php endif; ?>

        <div class="rubric-big-card-hdr">
          <div>
            <h2 class="rubric-big-title"><?php echo esc_html($cat->name); ?></h2>
            <div class="rubric-count-badge"><?php echo esc_html($count_str); ?></div>
          </div>
          <div class="rubric-big-badge" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="18" height="18"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
          </div>
        </div>

        <p class="rubric-big-desc"><?php echo esc_html($cat_desc); ?></p>

        <?php if (!empty($latest_title)) : ?>
          <div class="rubric-latest-row">
            <span class="rubric-latest-label"><?php esc_html_e('Свежее:', 'quterma'); ?></span>
            <span class="rubric-latest-text"><?php echo esc_html($latest_title); ?></span>
          </div>
        <?php endif; ?>
      </a>
    <?php endforeach; ?>
  </div>
</main>

<?php
get_footer();
