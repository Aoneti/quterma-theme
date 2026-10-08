<?php
/**
 * The template for displaying Category Archive pages
 *
 * @package Quterma
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();

$current_cat  = get_queried_object();
$cat_slug     = $current_cat->slug;
$tile_categories = array('kultura', 'kulture', 'culture', 'istoriya', 'history', 'lyudi', 'people');
$is_tile_layout  = in_array($cat_slug, $tile_categories);
?>

<div class="wrap">
  <div style="padding-top:28px">
    <?php get_template_part('template-parts/breadcrumbs'); ?>
    <div class="page-header">
      <h1 class="page-title"><?php single_cat_title(); ?></h1>
      <?php if (!empty($current_cat->description)) : ?>
        <p class="page-subtitle"><?php echo esc_html($current_cat->description); ?></p>
      <?php endif; ?>
    </div>
  </div>

  <?php if ($is_tile_layout && !is_paged() && have_posts()) :
      // 1. TILE GRID LAYOUT for Culture / History / People on page 1
      $tile_sizes_cycle = array('size-hero', 'size-tall', 'size-wide', '', '', 'size-wide', 'size-wide', '');
      $tile_posts = array();
      $feed_posts = array();
      $count = 0;

      while (have_posts()) : the_post();
          $count++;
          if ($count <= 8) {
              $tile_posts[] = get_post();
          } else {
              $feed_posts[] = get_post();
          }
      endwhile;
  ?>
    <div class="tile-grid rev" style="margin-bottom:60px">
      <?php
      foreach ($tile_posts as $idx => $p) :
          $tile_size = isset($tile_sizes_cycle[$idx]) ? $tile_sizes_cycle[$idx] : '';
          set_query_var('tile_size', $tile_size);
          // Set global post
          $post = $p;
          setup_postdata($post);
          get_template_part('template-parts/content', 'tile');
      endforeach;
      wp_reset_postdata();
      ?>
    </div>

    <!-- ДОПОЛНИТЕЛЬНЫЕ МАТЕРИАЛЫ С САЙДБАРОМ -->
    <div class="main-layout" style="padding-bottom:0">
      <main id="content">
        <?php if (!empty($feed_posts)) : ?>
          <div class="sec-div" style="margin-top:0">
            <div class="sec-div-acc"></div>
            <h2 class="sec-div-title"><?php esc_html_e('Ещё материалы', 'quterma'); ?></h2>
            <div class="sec-div-line"></div>
          </div>

          <div class="news-list" id="newsList">
            <?php
            foreach ($feed_posts as $p) :
                $post = $p;
                setup_postdata($post);
                get_template_part('template-parts/content', 'card');
            endforeach;
            wp_reset_postdata();
            ?>
          </div>
        <?php endif; ?>

        <?php get_template_part('template-parts/pagination'); ?>
      </main>

      <aside class="sidebar">
        <?php get_template_part('template-parts/sidebar', 'popular'); ?>
        <?php get_template_part('template-parts/sidebar', 'tags'); ?>
      </aside>
    </div>

  <?php else : ?>

    <!-- 2. STANDARD FEED LAYOUT (Город, Общество, Экология, or paged Culture/History/People) -->
    <div class="main-layout">
      <main id="content">
        <?php if (have_posts()) : ?>
          <div class="news-list" id="newsList">
            <?php
            $item_index = 0;
            while (have_posts()) : the_post();
                $item_index++;
                if ($item_index === 1 && !is_paged() && !$is_tile_layout) {
                    get_template_part('template-parts/content', 'featured');
                } else {
                    get_template_part('template-parts/content', 'card');
                }
            endwhile;
            ?>
          </div>

          <?php get_template_part('template-parts/pagination'); ?>

        <?php else : ?>
          <div class="empty-state show">
            <div class="empty-state-icon">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><line x1="21" y1="21" x2="16.5" y2="16.5"/></svg>
            </div>
            <p class="empty-state-text"><?php esc_html_e('В этой рубрике пока нет опубликованных материалов.', 'quterma'); ?></p>
            <a href="<?php echo esc_url(quterma_get_news_url()); ?>" class="empty-state-btn"><?php esc_html_e('Перейти в ленту', 'quterma'); ?></a>
          </div>
        <?php endif; ?>
      </main>

      <aside class="sidebar">
        <?php get_template_part('template-parts/sidebar', 'popular'); ?>
        <?php get_template_part('template-parts/sidebar', 'tags'); ?>
      </aside>
    </div>

  <?php endif; ?>
</div>

<?php
get_footer();
