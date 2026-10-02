<?php
/**
 * Template Name: Интервью (Interview Archive Page)
 *
 * @package Quterma
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>

<div class="wrap">
  <div style="padding-top:28px">
    <?php get_template_part('template-parts/breadcrumbs'); ?>

    <div class="page-header">
      <h1 class="page-title"><?php esc_html_e('Интервью', 'quterma'); ?></h1>
    </div>
  </div>

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
    <main>
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
                        'alt'     => the_title_attribute(array('echo' => false)),
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
                <p class="iv-excerpt"><?php echo wp_trim_words(get_the_excerpt(), 18, '…'); ?></p>
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
          <a href="<?php echo esc_url(quterma_get_news_url()); ?>" class="empty-state-btn"><?php esc_html_e('Перейти в общую ленту', 'quterma'); ?></a>
        </div>
      <?php endif; ?>
    </main>

    <aside class="sidebar">
      <?php get_template_part('template-parts/sidebar-popular'); ?>
      <?php get_template_part('template-parts/sidebar-tags'); ?>
    </aside>
  </div>
</div>

<?php
get_footer();
