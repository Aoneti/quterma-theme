<?php
/**
 * Template Name: Интервью
 * Description: Шаблон раздела интервью
 *
 * @package Quterma
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();

$current_id = get_the_ID();
$iv_person  = get_post_meta($current_id, '_iv_person', true);
$iv_role    = get_post_meta($current_id, '_iv_role', true);
$sub        = get_post_meta($current_id, '_page_subtitle', true);
$raw_content = get_post_field('post_content', $current_id);

// Determine whether this page is a single interview material or a listing archive
$is_single_interview = is_singular('post') || (!empty($iv_person) && !empty($iv_role));
?>

<main id="content" class="wrap">
  <div style="padding-top:28px">
    <?php get_template_part('template-parts/breadcrumbs'); ?>

    <div class="page-header" style="max-width:860px">
      <?php 
      $raw_title_lower = function_exists('quterma_strtolower') ? quterma_strtolower(trim(get_the_title())) : (function_exists('mb_strtolower') ? mb_strtolower(trim(get_the_title()), 'UTF-8') : strtolower(trim(get_the_title())));
      if ($is_single_interview && $raw_title_lower !== 'интервью') : ?>
        <div class="article-kicker kicker-interview">
          <?php esc_html_e('Интервью', 'quterma'); ?>
        </div>
      <?php endif; ?>
      <h1 class="page-title" style="margin-bottom:12px"><?php the_title(); ?></h1>

      <?php if ($is_single_interview && (!empty($iv_person) || !empty($iv_role))) : 
          $display_person = !empty($iv_person) ? $iv_person : get_the_title();
      ?>
        <div class="interview-person-hero">
          <div class="iv-hero-badge"><?php esc_html_e('Герой интервью', 'quterma'); ?></div>
          <div class="iv-hero-name"><?php echo esc_html($display_person); ?></div>
          <?php if (!empty($iv_role)) : ?>
            <div class="iv-hero-role"><?php echo esc_html($iv_role); ?></div>
          <?php endif; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <?php if ($is_single_interview) : ?>
    <!-- 1. ИНДИВИДУАЛЬНЫЙ МАТЕРИАЛ ИНТЕРВЬЮ -->
    <div class="wrap" style="padding-bottom:32px">
      <?php if (has_post_thumbnail()) : 
          $thumb_id  = get_post_thumbnail_id();
          $photo_alt = get_post_meta($thumb_id, '_wp_attachment_image_alt', true);
          $caption   = wp_get_attachment_caption($thumb_id);
      ?>
        <figure class="article-wide" style="margin-top:10px;margin-bottom:28px;max-width:860px">
          <?php the_post_thumbnail('quterma-hero', array(
              'loading' => 'eager',
              'sizes'   => '(max-width: 860px) 100vw, 860px',
              'alt'     => !empty($photo_alt) ? $photo_alt : (!empty($caption) ? $caption : ''),
          )); ?>
          <?php if (!empty($caption)) : ?>
            <figcaption style="font-size:13.5px;color:var(--ink-3);margin-top:10px;font-style:italic;line-height:1.5;text-align:left;border-left:2px solid var(--bd);padding-left:12px">
              <?php echo esc_html($caption); ?>
            </figcaption>
          <?php endif; ?>
        </figure>
      <?php endif; ?>

      <?php while (have_posts()) : the_post(); ?>
        <div class="article-col article-body rev" style="max-width:860px">
          <?php the_content(); ?>
        </div>
      <?php endwhile; wp_reset_postdata(); ?>
    </div>

    <!-- ДРУГИЕ ИНТЕРВЬЮ ВЫПУСКА -->
    <?php
    $other_args = array(
        'post_type'      => array('post', 'page'),
        'post_status'    => 'publish',
        'posts_per_page' => 4,
        'post__not_in'   => array($current_id),
        'tax_query'      => array(
            'relation' => 'OR',
            array(
                'taxonomy' => 'category',
                'field'    => 'slug',
                'terms'    => array('interview', 'interviews', 'intervyu'),
            ),
        ),
    );
    $other_query = new WP_Query($other_args);
    if ($other_query->have_posts()) : ?>
      <div class="main-layout" style="margin-top:40px;margin-bottom:48px;padding-top:24px;border-top:1px solid var(--bd)">
        <div class="main-content-col">
          <div class="sec-div" style="margin-top:0">
            <div class="sec-div-acc"></div>
            <h2 class="sec-div-title"><?php esc_html_e('Ещё интервью', 'quterma'); ?></h2>
            <div class="sec-div-line"></div>
          </div>
          <div class="interview-grid rev" style="grid-template-columns:repeat(auto-fill, minmax(280px, 1fr));gap:20px">
            <?php while ($other_query->have_posts()) : $other_query->the_post();
              $p_person = get_post_meta(get_the_ID(), '_iv_person', true);
              $p_name   = !empty($p_person) ? $p_person : get_the_title();
              $p_role   = get_post_meta(get_the_ID(), '_iv_role', true);
            ?>
              <a href="<?php the_permalink(); ?>" class="iv-card">
                <div class="iv-media">
                  <div class="iv-badge"><?php esc_html_e('Интервью', 'quterma'); ?></div>
                  <?php if (has_post_thumbnail()) : ?>
                    <div style="aspect-ratio:1/1;overflow:hidden">
                      <?php the_post_thumbnail('quterma-card-4x3', array(
                          'loading' => 'lazy',
                          'sizes'   => '(max-width: 768px) 100vw, 360px',
                          'alt'     => '',
                      )); ?>
                    </div>
                  <?php else : ?>
                    <div class="ph-img" style="aspect-ratio:1/1"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" width="40" height="40"><circle cx="12" cy="7" r="4"/><path d="M6 21v-2a4 4 0 0 1 4-4h4a4 4 0 0 1 4 4v2"/></svg></div>
                  <?php endif; ?>
                </div>
                <div class="iv-body">
                  <div class="iv-person"><?php echo esc_html($p_name); ?></div>
                  <?php if (!empty($p_role)) : ?>
                    <div class="iv-role"><?php echo esc_html($p_role); ?></div>
                  <?php endif; ?>
                  <p class="iv-excerpt"><?php echo esc_html(wp_trim_words(get_the_excerpt(), 15, '…')); ?></p>
                </div>
              </a>
            <?php endwhile; wp_reset_postdata(); ?>
          </div>
        </div>
      </div>
    <?php endif; ?>

  <?php else : ?>
    <!-- 2. АРХИВНЫЙ РАЗДЕЛ ВСЕХ ИНТЕРВЬЮ -->
    <?php
    $paged = max(1, (int) get_query_var('paged'), (int) get_query_var('page'));
    $args = array(
        'post_type'      => 'post',
        'post_status'    => 'publish',
        'posts_per_page' => 12,
        'paged'          => $paged,
        'category_name'  => 'interview,interviews,intervyu',
    );
    $iv_archive_query = new WP_Query($args);
    ?>

    <div class="main-layout" style="margin-bottom:24px;padding-bottom:72px">
      <div class="main-content-col">
        <?php if ($iv_archive_query->have_posts()) : ?>
          <div class="interview-grid rev" style="grid-template-columns:1fr 1fr;gap:22px">
            <?php while ($iv_archive_query->have_posts()) : $iv_archive_query->the_post();
              $custom_person = get_post_meta(get_the_ID(), '_iv_person', true);
              $person_name   = !empty($custom_person) ? $custom_person : get_the_title();
              $custom_role   = get_post_meta(get_the_ID(), '_iv_role', true);
            ?>
              <a href="<?php the_permalink(); ?>" class="iv-card">
                <div class="iv-media">
                  <div class="iv-badge"><?php esc_html_e('Интервью', 'quterma'); ?></div>
                  <?php if (has_post_thumbnail()) : ?>
                    <div style="aspect-ratio:1/1;overflow:hidden">
                      <?php the_post_thumbnail('quterma-card-4x3', array(
                          'loading' => 'lazy',
                          'sizes'   => '(max-width: 768px) 100vw, 420px',
                          'alt'     => '',
                      )); ?>
                    </div>
                  <?php else : ?>
                    <div class="ph-img" style="aspect-ratio:1/1"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" width="40" height="40"><circle cx="12" cy="7" r="4"/><path d="M6 21v-2a4 4 0 0 1 4-4h4a4 4 0 0 1 4 4v2"/></svg></div>
                  <?php endif; ?>
                </div>
                <div class="iv-body">
                  <div class="iv-person"><?php echo esc_html($person_name); ?></div>
                  <?php if (!empty($custom_role)) : ?>
                    <div class="iv-role"><?php echo esc_html($custom_role); ?></div>
                  <?php endif; ?>
                  <p class="iv-excerpt"><?php echo esc_html(wp_trim_words(get_the_excerpt(), 18, '…')); ?></p>
                  <div class="iv-meta">
                    <span><?php echo quterma_time_tag(get_the_ID(), false); ?></span>
                    <span class="iv-meta-cta"><?php esc_html_e('Читать диалог →', 'quterma'); ?></span>
                  </div>
                </div>
              </a>
            <?php endwhile; wp_reset_postdata(); ?>
          </div>

          <div style="margin-top:40px">
            <?php get_template_part('template-parts/pagination', null, array(
                'total_pages' => $iv_archive_query->max_num_pages,
                'current'     => $paged,
            )); ?>
          </div>
        <?php else : ?>
          <div class="empty-state show">
            <div class="empty-state-icon">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="7" r="4"/><path d="M6 21v-2a4 4 0 0 1 4-4h4a4 4 0 0 1 4 4v2"/></svg>
            </div>
            <p class="empty-state-text"><?php esc_html_e('В разделе «Интервью» пока нет опубликованных материалов.', 'quterma'); ?></p>
            <p style="font-size:var(--t-xs);color:var(--ink-3);max-width:440px;margin:8px auto 16px auto">
              <?php esc_html_e('Чтобы опубликовать диалог, создайте запись в рубрике «Интервью» или выберите шаблон «Интервью» в атрибутах записи.', 'quterma'); ?>
            </p>
            <a href="<?php echo esc_url(quterma_get_news_url()); ?>" class="empty-state-btn"><?php esc_html_e('Перейти в общую ленту', 'quterma'); ?></a>
          </div>
        <?php endif; ?>
      </div>

      <aside class="sidebar">
        <?php get_template_part('template-parts/sidebar-popular'); ?>
        <?php get_template_part('template-parts/sidebar-tags'); ?>
      </aside>
    </div>
  <?php endif; ?>
</main>

<?php
get_footer();
