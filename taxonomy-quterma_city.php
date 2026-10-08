<?php
/**
 * The template for displaying Gastroguide City taxonomy archives (quterma_city)
 *
 * @package Quterma
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();

$current_city = get_queried_object();
$city_name    = ($current_city && !empty($current_city->name)) ? $current_city->name : __('Город', 'quterma');
$city_desc    = ($current_city && !empty($current_city->description)) ? $current_city->description : sprintf(__('Рестораны, кофейни, бистро и гастрономические места в г. %s', 'quterma'), $city_name);
$gastro_url   = function_exists('quterma_get_page_url') ? quterma_get_page_url('gastroguide', home_url('/gastroguide/')) : home_url('/gastroguide/');
?>

<main id="content" class="wrap">
  <div style="padding-top:28px">
    <?php get_template_part('template-parts/breadcrumbs'); ?>
    <div class="page-header">
      <div style="display:flex;align-items:center;gap:10px;margin-bottom:6px">
        <a href="<?php echo esc_url($gastro_url); ?>" style="font-family:var(--fd);font-size:var(--t-xs);font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--brand)">
          &larr; <?php esc_html_e('Все города Гастрогида', 'quterma'); ?>
        </a>
      </div>
      <h1 class="page-title"><?php echo esc_html(sprintf(__('Гастрогид · %s', 'quterma'), $city_name)); ?></h1>
      <?php if (!empty($city_desc)) : ?>
        <p class="page-subtitle"><?php echo esc_html($city_desc); ?></p>
      <?php endif; ?>
    </div>
  </div>

  <?php if (have_posts()) : ?>
    <div class="venue-grid rev" id="venueGrid" style="margin-bottom:32px">
      <?php
      while (have_posts()) : the_post();
          get_template_part('template-parts/content', 'venue');
      endwhile;
      ?>
    </div>

    <?php
    the_posts_pagination(array(
        'mid_size'           => 2,
        'prev_text'          => '<span class="nav-prev">&larr; ' . __('Назад', 'quterma') . '</span>',
        'next_text'          => '<span class="nav-next">' . __('Вперед', 'quterma') . ' &rarr;</span>',
        'screen_reader_text' => __('Навигация по заведениям', 'quterma'),
    ));
    ?>

  <?php else : ?>
    <div class="empty-state show">
      <div class="empty-state-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
          <path d="M18 8h1a4 4 0 0 1 0 8h-1"/><path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"/>
          <line x1="6" y1="1" x2="6" y2="4"/><line x1="10" y1="1" x2="10" y2="4"/><line x1="14" y1="1" x2="14" y2="4"/>
        </svg>
      </div>
      <p class="empty-state-text"><?php esc_html_e('В этом городе пока нет заведений в Гастрогиде.', 'quterma'); ?></p>
      <a href="<?php echo esc_url($gastro_url); ?>" class="empty-state-btn"><?php esc_html_e('Перейти в каталог Гастрогида', 'quterma'); ?></a>
    </div>
  <?php endif; ?>
</main>

<?php
get_footer();
