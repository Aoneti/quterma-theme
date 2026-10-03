<?php
/**
 * The template for displaying a single Gastroguide venue
 *
 * @package Quterma
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();

while (have_posts()) : the_post();
    $post_id   = get_the_ID();
    $city_slug = get_post_meta($post_id, '_venue_city', true);
    if (empty($city_slug)) {
        $city_slug = 'yaroslavl';
    }
    $city_name = quterma_get_city_name($city_slug);
    $type      = get_post_meta($post_id, '_venue_type', true);
    $address   = get_post_meta($post_id, '_venue_address', true);
    $price     = get_post_meta($post_id, '_venue_price', true);
    $features  = get_post_meta($post_id, '_venue_features', true);
    $website   = get_post_meta($post_id, '_venue_website', true);
    $phone     = get_post_meta($post_id, '_venue_phone', true);
    $hours     = get_post_meta($post_id, '_venue_hours', true);

    // Other venues in this city
    $other_venues = new WP_Query(array(
        'post_type'      => 'quterma_venue',
        'post_status'    => 'publish',
        'posts_per_page' => 3,
        'post__not_in'   => array($post_id),
        'meta_query'     => array(
            array(
                'key'   => '_venue_city',
                'value' => $city_slug,
            ),
        ),
        'no_found_rows'  => true,
    ));
?>

<article id="venue-<?php the_ID(); ?>" <?php post_class(); ?>>
  <div class="wrap" style="padding-top:24px">
    <div style="max-width:820px;margin-left:auto;margin-right:auto">
      <?php get_template_part('template-parts/breadcrumbs'); ?>
    </div>

    <div class="article-header">
      <div class="article-kicker"><?php echo esc_html($type ? $type : 'Гастрогид'); ?> · <?php echo esc_html($city_name); ?></div>
      <h1 class="article-headline"><?php the_title(); ?></h1>

      <div class="article-meta-row" style="justify-content:space-between;flex-wrap:wrap">
        <div>
          <?php if (!empty($address)) : ?>
            <div style="display:flex;align-items:center;gap:6px;font-size:var(--t-sm);color:var(--ink-2);font-weight:600">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="16" height="16">
                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>
              </svg>
              <?php echo esc_html($address); ?>
            </div>
          <?php endif; ?>
          <?php if (!empty($price)) : ?>
            <div style="font-size:var(--t-xs);color:var(--ink-3);margin-top:4px">
              <?php esc_html_e('Чек:', 'quterma'); ?> <strong><?php echo esc_html($price); ?></strong>
            </div>
          <?php endif; ?>
          <?php if (!empty($hours)) : ?>
            <div style="font-size:var(--t-xs);color:var(--ink-3);margin-top:2px">
              <?php esc_html_e('Часы работы:', 'quterma'); ?> <?php echo esc_html($hours); ?>
            </div>
          <?php endif; ?>
          <?php if (!empty($phone)) : ?>
            <div style="font-size:var(--t-xs);color:var(--ink-3);margin-top:2px">
              <?php esc_html_e('Телефон:', 'quterma'); ?> <a href="tel:<?php echo esc_attr(preg_replace('/[^\d+]/', '', $phone)); ?>" style="color:inherit;text-decoration:underline"><?php echo esc_html($phone); ?></a>
            </div>
          <?php endif; ?>
        </div>

        <?php if (!empty($website)) : ?>
          <a href="<?php echo esc_url($website); ?>" target="_blank" rel="noopener noreferrer" class="btn-pill" style="padding:8px 18px">
            <?php esc_html_e('Сайт заведения', 'quterma'); ?>
          </a>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div class="wrap">
    <?php if (has_post_thumbnail()) : ?>
      <figure class="article-wide" style="margin-bottom:28px">
        <?php the_post_thumbnail('quterma-hero', array(
            'loading'       => 'eager',
            'fetchpriority' => 'high',
            'sizes'         => '(max-width: 1200px) 100vw, 1200px',
            'alt'           => the_title_attribute(array('echo' => false)),
        )); ?>
      </figure>
    <?php endif; ?>

    <div class="article-col article-body rev">
      <?php the_content(); ?>

      <?php if (!empty($features)) :
          $feats_array = explode(',', $features);
      ?>
        <div style="margin-top:34px">
          <div style="font-family:var(--fd);font-size:var(--t-xs);font-weight:600;letter-spacing:.08em;text-transform:uppercase;color:var(--ink-3);margin-bottom:10px">
            <?php esc_html_e('Особенности', 'quterma'); ?>
          </div>
          <div class="tags-cloud">
            <?php foreach ($feats_array as $f) : ?>
              <span class="tag"><?php echo esc_html(trim($f)); ?></span>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </div>
</article>

<?php if ($other_venues->have_posts()) : ?>
<section class="article-related rev">
  <div class="wrap">
    <div class="sec-div" style="margin-top:0">
      <div class="sec-div-acc"></div>
      <h2 class="sec-div-title"><?php printf(__('Другие заведения: %s', 'quterma'), esc_html($city_name)); ?></h2>
      <div class="sec-div-line"></div>
    </div>
    <div class="venue-grid">
      <?php
      while ($other_venues->have_posts()) : $other_venues->the_post();
          get_template_part('template-parts/content', 'venue');
      endwhile;
      wp_reset_postdata();
      ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php
endwhile;

get_footer();
