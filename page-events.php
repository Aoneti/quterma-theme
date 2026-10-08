<?php
/**
 * Template Name: Афиша событий
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

$has_events = !empty($events);
$total_events = $has_events ? count($events) : 0;

// Cities in Yaroslavl Region
$cities = array(
    'yaroslavl'    => __('Ярославль', 'quterma'),
    'rybinsk'      => __('Рыбинск', 'quterma'),
    'rostov'       => __('Ростов Великий', 'quterma'),
    'pereslavl'    => __('Переславль-Залесский', 'quterma'),
    'tutaev'       => __('Тутаев', 'quterma'),
    'uglich'       => __('Углич', 'quterma'),
    'gavrilov-yam' => __('Гаврилов-Ям', 'quterma'),
    'danilov'      => __('Данилов', 'quterma'),
    'lyubim'       => __('Любим', 'quterma'),
    'myshkin'      => __('Мышкин', 'quterma'),
    'poshekhonye'  => __('Пошехонье', 'quterma'),
    'breytovo'     => __('Брейтово', 'quterma'),
);

$city_counts = array();
if ($has_events) {
    foreach ($events as $ev) {
        $c_slug = !empty($ev['city_slug']) ? $ev['city_slug'] : 'yaroslavl';
        $city_counts[$c_slug] = ($city_counts[$c_slug] ?? 0) + 1;
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
        <?php endif; ?>
      </div>
      <?php if (get_the_content()) : ?>
        <div class="page-content rev" style="margin-bottom:28px">
          <?php the_content(); ?>
        </div>
      <?php endif; ?>
    <?php endwhile; else : ?>
      <div class="page-header">
        <h1 class="page-title"><?php esc_html_e('События', 'quterma'); ?></h1>
      </div>
    <?php endif; ?>
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
        <button type="button" class="filter active" data-city="all">
          <?php esc_html_e('Все города', 'quterma'); ?> <span class="filter-cnt">(<?php echo $total_events; ?>)</span>
        </button>
        <?php foreach ($cities as $c_slug => $c_name) :
            $cnt = $city_counts[$c_slug] ?? 0;
            $empty_class = ($cnt === 0) ? ' is-empty' : '';
        ?>
          <button type="button" class="filter<?php echo $empty_class; ?>" data-city="<?php echo esc_attr($c_slug); ?>" data-count="<?php echo $cnt; ?>">
            <?php echo esc_html($c_name); ?> <span class="filter-cnt">(<?php echo $cnt; ?>)</span>
          </button>
        <?php endforeach; ?>
      </div>
      <button class="filter-scroll-btn filter-scroll-next" type="button" aria-label="<?php esc_attr_e('Вперед', 'quterma'); ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
      </button>
    </div>
  </div>

  <!-- СВОДКА ФИЛЬТРОВ И СЧЕТЧИК НАЙДЕННОГО -->
  <div class="filter-summary-bar rev" id="eventSummaryBar">
    <div class="filter-summary-count">
      <?php esc_html_e('Найдено событий:', 'quterma'); ?> <strong id="eventCountNum"><?php echo $total_events; ?></strong>
    </div>
    <div class="active-chips" id="eventActiveChips"></div>
    <button type="button" class="filter-reset-link" id="eventResetLink" style="display:none">
      <?php esc_html_e('Сбросить фильтры ✕', 'quterma'); ?>
    </button>
  </div>

  <?php if (!$has_events) : ?>
    <!-- СОСТОЯНИЕ: СЕРВИС СИНХРОНИЗИРУЕТСЯ / НЕТ СОБЫТИЙ В БАЗЕ -->
    <div class="empty-state show" id="eventInitialEmpty">
      <div class="empty-state-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
          <rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
        </svg>
      </div>
      <h3 class="empty-state-title" style="font-size:18px;font-weight:700;color:var(--ink);margin:0"><?php esc_html_e('Афиша обновляется', 'quterma'); ?></h3>
      <p class="empty-state-text"><?php esc_html_e('В данный момент афиша культурных событий Ярославской области синхронизируется. Загляните чуть позже или перейдите в ленту культурных новостей.', 'quterma'); ?></p>
      <div style="display:flex;gap:12px;flex-wrap:wrap;justify-content:center">
        <a href="<?php echo esc_url(quterma_get_category_url('culture', home_url('/category/culture/'))); ?>" class="empty-state-btn"><?php esc_html_e('Культурный слой', 'quterma'); ?></a>
        <a href="<?php echo esc_url(quterma_get_news_url()); ?>" class="empty-state-btn" style="background:var(--paper-2);color:var(--ink)"><?php esc_html_e('Все новости', 'quterma'); ?></a>
      </div>
    </div>
  <?php else : ?>
    <!-- СЕТКА СОБЫТИЙ -->
    <div class="event-grid rev" id="eventGrid" style="margin-bottom:24px">
      <?php
      foreach ($events as $event) :
          set_query_var('event_item', $event);
          get_template_part('template-parts/content', 'event');
      endforeach;
      ?>
    </div>

    <!-- СОСТОЯНИЕ: НИЧЕГО НЕ НАЙДЕНО ПО ФИЛЬТРАМ -->
    <div class="empty-state" id="eventEmpty">
      <div class="empty-state-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
        </svg>
      </div>
      <p class="empty-state-text"><?php esc_html_e('На выбранные даты и город пока ничего не запланировано.', 'quterma'); ?></p>
      <button type="button" class="empty-state-btn" id="eventEmptyReset"><?php esc_html_e('Сбросить фильтры', 'quterma'); ?></button>
    </div>
  <?php endif; ?>
</main>

<?php
get_footer();
