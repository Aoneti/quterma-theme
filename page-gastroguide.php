<?php
/**
 * Template Name: Гастрогид
 * Description: Шаблон страницы каталога заведений «Гастрогид» с фильтрацией по городам и кухням
 *
 * @package Quterma
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();

$paged        = (get_query_var('paged')) ? get_query_var('paged') : ((get_query_var('page')) ? get_query_var('page') : 1);
$per_page     = apply_filters('quterma_gastroguide_per_page', -1);
$venues_query = new WP_Query(array(
    'post_type'      => 'quterma_venue',
    'post_status'    => 'publish',
    'posts_per_page' => $per_page,
    'paged'          => $paged,
    'orderby'        => 'menu_order title',
    'order'          => 'ASC',
    'no_found_rows'  => false,
));

$total_venues = $venues_query->found_posts;
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
if ($venues_query->have_posts()) {
    foreach ($venues_query->posts as $p) {
        $c_slug = get_post_meta($p->ID, '_venue_city', true);
        if (empty($c_slug)) $c_slug = 'yaroslavl';
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
        <h1 class="page-title"><?php esc_html_e('Гастрогид', 'quterma'); ?></h1>
      </div>
    <?php endif; ?>
  </div>

  <!-- ФИЛЬТРЫ ГОРОДОВ И КНОПКА РАСШИРЕННЫХ ФИЛЬТРОВ -->
  <div class="filter-bar rev">
    <div class="filters-slider-wrap">
      <button class="filter-scroll-btn filter-scroll-prev" type="button" aria-label="<?php esc_attr_e('Назад', 'quterma'); ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
      </button>
      <div class="filters" id="cityFilters">
        <button type="button" class="filter active" data-city="all" aria-pressed="true">
          <?php esc_html_e('Все города', 'quterma'); ?> <span class="filter-cnt">(<?php echo $total_venues; ?>)</span>
        </button>
        <?php foreach ($cities as $c_slug => $c_name) :
            $cnt = $city_counts[$c_slug] ?? 0;
            $empty_class = ($cnt === 0) ? ' is-empty' : '';
        ?>
          <button type="button" class="filter<?php echo $empty_class; ?>" data-city="<?php echo esc_attr($c_slug); ?>" data-count="<?php echo $cnt; ?>" aria-pressed="false">
            <?php echo esc_html($c_name); ?> <span class="filter-cnt">(<?php echo $cnt; ?>)</span>
          </button>
        <?php endforeach; ?>
      </div>
      <button class="filter-scroll-btn filter-scroll-next" type="button" aria-label="<?php esc_attr_e('Вперед', 'quterma'); ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
      </button>
    </div>
    <button type="button" class="filter-more-btn" id="moreFiltersBtn" aria-expanded="false" aria-controls="extraFilters">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <line x1="4" y1="6" x2="20" y2="6"/><circle cx="9" cy="6" r="2" fill="var(--paper)"/>
        <line x1="4" y1="12" x2="20" y2="12"/><circle cx="15" cy="12" r="2" fill="var(--paper)"/>
        <line x1="4" y1="18" x2="20" y2="18"/><circle cx="11" cy="18" r="2" fill="var(--paper)"/>
      </svg>
      <?php esc_html_e('Ещё фильтры', 'quterma'); ?>
    </button>
  </div>

  <!-- РАСШИРЕННЫЕ ФИЛЬТРЫ -->
  <div class="filter-extra" id="extraFilters">
    <div class="filter-extra-group">
      <div class="filter-extra-label"><?php esc_html_e('Кухня / Формат', 'quterma'); ?></div>
      <div class="filter-extra-opts">
        <button type="button" class="tag" aria-pressed="false" data-filter-type="кофейня"><?php esc_html_e('Кофейня', 'quterma'); ?></button>
        <button type="button" class="tag" aria-pressed="false" data-filter-type="ресторан"><?php esc_html_e('Ресторан', 'quterma'); ?></button>
        <button type="button" class="tag" aria-pressed="false" data-filter-type="кафе"><?php esc_html_e('Кафе', 'quterma'); ?></button>
        <button type="button" class="tag" aria-pressed="false" data-filter-type="пекарня"><?php esc_html_e('Пекарня', 'quterma'); ?></button>
        <button type="button" class="tag" aria-pressed="false" data-filter-type="стрит-фуд"><?php esc_html_e('Стрит-фуд', 'quterma'); ?></button>
      </div>
    </div>
    <div class="filter-extra-group">
      <div class="filter-extra-label"><?php esc_html_e('Цена', 'quterma'); ?></div>
      <div class="filter-extra-opts">
        <button type="button" class="tag" aria-pressed="false" data-filter-price="₽">₽</button>
        <button type="button" class="tag" aria-pressed="false" data-filter-price="₽₽">₽₽</button>
        <button type="button" class="tag" aria-pressed="false" data-filter-price="₽₽₽">₽₽₽</button>
      </div>
    </div>
    <div class="filter-extra-group">
      <div class="filter-extra-label"><?php esc_html_e('Особенности', 'quterma'); ?></div>
      <div class="filter-extra-opts">
        <button type="button" class="tag" aria-pressed="false" data-filter-feature="веранда"><?php esc_html_e('Веранда', 'quterma'); ?></button>
        <button type="button" class="tag" aria-pressed="false" data-filter-feature="животными"><?php esc_html_e('С животными', 'quterma'); ?></button>
        <button type="button" class="tag" aria-pressed="false" data-filter-feature="веган"><?php esc_html_e('Веган-меню', 'quterma'); ?></button>
        <button type="button" class="tag" aria-pressed="false" data-filter-feature="допоздна"><?php esc_html_e('Работает допоздна', 'quterma'); ?></button>
      </div>
    </div>
  </div>

  <!-- СВОДКА ФИЛЬТРОВ И СЧЕТЧИК НАЙДЕННОГО -->
  <div class="filter-summary-bar rev" id="venueSummaryBar">
    <div class="filter-summary-count">
      <?php esc_html_e('Найдено заведений:', 'quterma'); ?> <strong id="venueCountNum"><?php echo $total_venues; ?></strong>
    </div>
    <div class="active-chips" id="venueActiveChips"></div>
    <button type="button" class="filter-reset-link" id="venueResetLink" style="display:none">
      <?php esc_html_e('Сбросить фильтры ✕', 'quterma'); ?>
    </button>
  </div>

  <!-- СЕТКА ЗАВЕДЕНИЙ -->
  <div class="venue-grid rev" id="venueGrid" style="margin-bottom:24px">
    <?php
    if ($venues_query->have_posts()) :
        while ($venues_query->have_posts()) : $venues_query->the_post();
            get_template_part('template-parts/content', 'venue');
        endwhile;
        wp_reset_postdata();
    endif;
    ?>
  </div>

  <div class="empty-state<?php echo !$venues_query->have_posts() ? ' show' : ''; ?>" id="venueEmpty">
    <div class="empty-state-icon">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
        <path d="M18 8h1a4 4 0 0 1 0 8h-1"/><path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"/>
        <line x1="6" y1="1" x2="6" y2="4"/><line x1="10" y1="1" x2="10" y2="4"/><line x1="14" y1="1" x2="14" y2="4"/>
      </svg>
    </div>
    <p class="empty-state-text"><?php esc_html_e('По выбранным параметрам заведений не найдено.', 'quterma'); ?></p>
    <button type="button" class="empty-state-btn" id="venueEmptyReset"><?php esc_html_e('Сбросить фильтры', 'quterma'); ?></button>
  </div>
</main>

<?php
get_footer();
