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

  <?php if ($is_tile_layout && have_posts()) :
      // 1. TILE GRID LAYOUT for Culture / History / People (5 rows x 6 cols fully packed, no bottom gaps)
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
      <main>
        <div class="sec-div" style="margin-top:0">
          <div class="sec-div-acc"></div>
          <h2 class="sec-div-title"><?php esc_html_e('Ещё материалы', 'quterma'); ?></h2>
          <div class="sec-div-line"></div>
        </div>

        <div class="news-list" id="newsList">
          <?php
          if (!empty($feed_posts)) :
              foreach ($feed_posts as $p) :
                  $post = $p;
                  setup_postdata($post);
                  get_template_part('template-parts/content', 'card');
              endforeach;
              wp_reset_postdata();
          else :
              // Fallback query for more news if fewer than 9 posts in this specific cat
              $more_query = new WP_Query(array(
                  'post_type'      => 'post',
                  'post_status'    => 'publish',
                  'posts_per_page' => 4,
                  'post__not_in'   => wp_list_pluck($tile_posts, 'ID'),
                  'no_found_rows'  => true,
              ));
              while ($more_query->have_posts()) : $more_query->the_post();
                  get_template_part('template-parts/content', 'card');
              endwhile;
              wp_reset_postdata();
          endif;
          ?>
        </div>
      </main>

      <aside class="sidebar">
        <?php get_template_part('template-parts/sidebar', 'popular'); ?>
        <?php get_template_part('template-parts/sidebar', 'tags'); ?>
      </aside>
    </div>

  <?php else : ?>

    <!-- 2. STANDARD FEED LAYOUT (Город, Общество, Экология, etc.) -->
    <div class="main-layout">
      <main>
        <?php if (have_posts()) : ?>
          <div class="news-list" id="newsList">
            <?php
            $item_index = 0;
            while (have_posts()) : the_post();
                $item_index++;
                if ($item_index === 1 && !is_paged()) {
                    get_template_part('template-parts/content', 'featured');
                } else {
                    get_template_part('template-parts/content', 'card');
                }
            endwhile;
            ?>
          </div>

          <?php
          the_posts_pagination(array(
              'mid_size'           => 2,
              'prev_text'          => '<span class="nav-prev">&larr; ' . __('Назад', 'quterma') . '</span>',
              'next_text'          => '<span class="nav-next">' . __('Вперед', 'quterma') . ' &rarr;</span>',
              'screen_reader_text' => __('Навигация по записям', 'quterma'),
          ));
          ?>

        <?php else : ?>
          <div class="empty-state show">
            <div class="empty-state-icon">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><line x1="21" y1="21" x2="16.5" y2="16.5"/></svg>
            </div>
            <p class="empty-state-text"><?php esc_html_e('В этой рубрике пока нет опубликованных материалов.', 'quterma'); ?></p>
            <a href="<?php echo esc_url(home_url('/feed/')); ?>" class="empty-state-btn"><?php esc_html_e('Перейти в ленту', 'quterma'); ?></a>
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
