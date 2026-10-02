<?php
/**
 * The template for displaying all single posts (Article)
 *
 * @package Quterma
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();

while (have_posts()) : the_post();
    $cat_info     = quterma_get_post_category_info();
    $date_str     = quterma_format_date(get_the_ID(), true);
    $author_id    = get_the_author_meta('ID');
    $author_name  = get_the_author_meta('display_name');
    $initials     = quterma_get_author_initials();
    $lede         = has_excerpt() ? get_the_excerpt() : '';

    // Related posts query (from the primary editorial category, excluding service placement categories)
    $primary_cat = quterma_get_primary_category();
    $related_cat_id = $primary_cat ? $primary_cat->term_id : 0;
    $related_query = new WP_Query(array(
        'post_type'           => 'post',
        'post_status'         => 'publish',
        'posts_per_page'      => 3,
        'cat'                 => $related_cat_id,
        'post__not_in'        => array(get_the_ID()),
        'ignore_sticky_posts' => true,
        'no_found_rows'       => true,
    ));
?>

<main id="content">
<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
  <div class="wrap" style="padding-top:24px">
    <div style="max-width:820px;margin-left:auto;margin-right:auto">
      <?php get_template_part('template-parts/breadcrumbs'); ?>
    </div>

    <div class="article-header">
      <div class="article-kicker"><?php echo esc_html($cat_info['name']); ?></div>
      <h1 class="article-headline"><?php the_title(); ?></h1>

      <?php if (!empty($lede)) : ?>
        <p class="article-lede"><?php echo esc_html($lede); ?></p>
      <?php endif; ?>

      <div class="article-meta-row" style="border-top:1px solid var(--bd);margin-top:24px;padding-top:16px;display:flex;align-items:center;gap:10px;font-size:var(--t-xs)">
        <?php if (!empty($author_name)) : ?>
          <span class="article-meta-author" style="font-weight:700;color:var(--ink)"><?php echo esc_html($author_name); ?></span>
          <span class="article-meta-sep" style="color:var(--ink-4)">·</span>
        <?php endif; ?>
        <span class="article-meta-date" style="color:var(--ink-3);font-weight:500"><?php echo quterma_time_tag(get_the_ID(), true); ?></span>
      </div>
    </div>
  </div>

  <div class="wrap">
    <?php if (has_post_thumbnail()) :
        $thumb_id = get_post_thumbnail_id();
        $caption  = wp_get_attachment_caption($thumb_id);
    ?>
      <figure class="article-wide" style="margin-bottom:0">
        <?php the_post_thumbnail('quterma-hero', array(
            'loading'       => 'eager',
            'fetchpriority' => 'high',
            'sizes'         => '(max-width: 768px) 100vw, 820px',
            'alt'           => the_title_attribute(array('echo' => false)),
        )); ?>
      </figure>
      <?php if (!empty($caption)) : ?>
        <p class="article-caption"><?php echo esc_html($caption); ?></p>
      <?php endif; ?>
    <?php endif; ?>

    <div class="article-col article-body rev">
      <?php the_content(); ?>

      <?php
      wp_link_pages(array(
          'before'      => '<nav class="page-links" aria-label="' . esc_attr__('Страницы материала', 'quterma') . '"><span class="page-links-title">' . __('Страницы:', 'quterma') . '</span>',
          'after'       => '</nav>',
          'link_before' => '<span class="page-number">',
          'link_after'  => '</span>',
      ));
      ?>
    </div>

    <?php
    $post_tags = get_the_tags();
    if (!empty($post_tags)) :
    ?>
      <div class="article-tags">
        <div class="tags-cloud">
          <?php foreach ($post_tags as $tag) : ?>
            <a href="<?php echo esc_url(get_tag_link($tag)); ?>" class="tag">
              <?php echo esc_html($tag->name); ?>
            </a>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>
    <?php
    $author_bio = get_the_author_meta('description');
    if (!empty($author_name)) :
    ?>
      <div class="article-author-box">
        <div class="article-author-av"><?php echo esc_html($initials); ?></div>
        <div>
          <div class="article-author-box-name"><?php echo esc_html($author_name); ?></div>
          <?php if (!empty($author_bio)) : ?>
            <div class="article-author-box-bio"><?php echo esc_html($author_bio); ?></div>
          <?php endif; ?>
        </div>
      </div>
    <?php endif; ?>
  </div>
</article>

<!-- БЛОК «ЧИТАЙТЕ ТАКЖЕ» -->
<?php if ($related_query->have_posts()) : ?>
<section class="article-related rev">
  <div class="wrap">
    <div class="sec-div" style="margin-top:0">
      <div class="sec-div-acc"></div>
      <h2 class="sec-div-title"><?php esc_html_e('Читайте также', 'quterma'); ?></h2>
      <div class="sec-div-line"></div>
    </div>
    <div class="related-grid">
      <?php
      while ($related_query->have_posts()) : $related_query->the_post();
      ?>
        <a href="<?php the_permalink(); ?>" class="cc-card">
          <div class="cc-img">
            <?php if (has_post_thumbnail()) : ?>
              <?php the_post_thumbnail('quterma-card-4x3', array(
                  'loading' => 'lazy',
                  'sizes'   => '(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 380px',
                  'alt'     => the_title_attribute(array('echo' => false)),
              )); ?>
            <?php else : ?>
              <?php echo quterma_placeholder_img(38, 38); ?>
            <?php endif; ?>
          </div>
          <div class="cc-body">
            <h3 class="cc-title"><?php the_title(); ?></h3>
            <div class="cc-meta"><?php echo quterma_time_tag(get_the_ID(), false); ?></div>
          </div>
        </a>
      <?php endwhile; wp_reset_postdata(); ?>
    </div>
  </div>
</section>
<?php endif; ?>
</main>


<?php
endwhile;

get_footer();
