<?php
/**
 * Template Name: События (Афиша API Культура.РФ)
 * Description: Шаблон страницы афиши культурных событий с интеграцией API «Культура.РФ»
 *
 * @package Quterma
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();

// Fetch events from API «Культура.РФ» (preloaded via WP-Cron)
$events = quterma_get_culture_events(array(
    'city'  => 'all',
    'when'  => 'all',
    'limit' => 100,
));
?>

<div class="wrap">
  <div style="padding-top:28px">
    <?php get_template_part('template-parts/breadcrumbs'); ?>
    <div class="page-header">
      <h1 class="page-title"><?php the_title(); ?></h1>
      <?php if (has_excerpt()) : ?>
        <p class="page-subtitle"><?php echo esc_html(get_the_excerpt()); ?></p>
      <?php endif; ?>
    </div>
  </div>

  <!-- ФИЛЬТРЫ ДАТЫ -->
  <div class="filters-slider-wrap date-filters rev">
    <button class="filter-scroll-btn filter-scroll-prev" type="button" aria-label="<?php esc_attr_e('Назад', 'quterma'); ?>">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
    </button>
    <div class="filters" id="dateFilters">
      <button type="button" class="filter active" data-when="all"><?php esc_html_e('Все даты', 'quterma'); ?></button>
      <button type="button" class="filter" data-when="today"><?php esc_html_e('Сегодня', 'quterma'); ?></button>
      <button type="button" class="filter" data-when="tomorrow"><?php esc_html_e('Завтра', 'quterma'); ?></button>
      <button type="button" class="filter" data-when="week"><?php esc_html_e('На этой неделе', 'quterma'); ?></button>
      <button type="button" class="filter" data-when="month"><?php esc_html_e('В этом месяце', 'quterma'); ?></button>
    </div>
    <button class="filter-scroll-btn filter-scroll-next" type="button" aria-label="<?php esc_attr_e('Вперед', 'quterma'); ?>">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
    </button>
  </div>

  <!-- ФИЛЬТРЫ ГОРОДОВ -->
  <div class="filter-bar rev">
    <div class="filters-slider-wrap">
      <button class="filter-scroll-btn filter-scroll-prev" type="button" aria-label="<?php esc_attr_e('Назад', 'quterma'); ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
      </button>
      <div class="filters" id="cityFilters">
        <button type="button" class="filter active" data-city="all"><?php esc_html_e('Все города', 'quterma'); ?></button>
        <button type="button" class="filter" data-city="yaroslavl"><?php esc_html_e('Ярославль', 'quterma'); ?></button>
        <button type="button" class="filter" data-city="rybinsk"><?php esc_html_e('Рыбинск', 'quterma'); ?></button>
        <button type="button" class="filter" data-city="rostov"><?php esc_html_e('Ростов Великий', 'quterma'); ?></button>
        <button type="button" class="filter" data-city="pereslavl"><?php esc_html_e('Переславль-Залесский', 'quterma'); ?></button>
        <button type="button" class="filter" data-city="tutaev"><?php esc_html_e('Тутаев', 'quterma'); ?></button>
        <button type="button" class="filter" data-city="uglich"><?php esc_html_e('Углич', 'quterma'); ?></button>
        <button type="button" class="filter" data-city="gavrilov-yam"><?php esc_html_e('Гаврилов-Ям', 'quterma'); ?></button>
        <button type="button" class="filter" data-city="danilov"><?php esc_html_e('Данилов', 'quterma'); ?></button>
        <button type="button" class="filter" data-city="lyubim"><?php esc_html_e('Любим', 'quterma'); ?></button>
        <button type="button" class="filter" data-city="myshkin"><?php esc_html_e('Мышкин', 'quterma'); ?></button>
        <button type="button" class="filter" data-city="poshekhonye"><?php esc_html_e('Пошехонье', 'quterma'); ?></button>
        <button type="button" class="filter" data-city="breytovo"><?php esc_html_e('Брейтово', 'quterma'); ?></button>
      </div>
      <button class="filter-scroll-btn filter-scroll-next" type="button" aria-label="<?php esc_attr_e('Вперед', 'quterma'); ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
      </button>
    </div>
  </div>

  <!-- СЕТКА СОБЫТИЙ -->
  <div class="event-grid rev" id="eventGrid" style="margin-bottom:24px">
    <?php
    if (!empty($events)) :
        foreach ($events as $event) :
            set_query_var('event_item', $event);
            get_template_part('template-parts/content', 'event');
        endforeach;
    endif;
    ?>
  </div>

  <div class="empty-state" id="eventEmpty">
    <div class="empty-state-icon">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
        <rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
      </svg>
    </div>
    <p class="empty-state-text"><?php esc_html_e('На выбранные даты и город пока ничего не запланировано.', 'quterma'); ?></p>
    <button type="button" class="empty-state-btn" id="eventEmptyReset"><?php esc_html_e('Сбросить фильтры', 'quterma'); ?></button>
  </div>
</div>

<?php
get_footer();
