<?php
/**
 * Template Name: Гастрогид (Каталог заведений)
 * Description: Шаблон страницы каталога заведений «Гастрогид» с фильтрацией по городам и кухням
 *
 * @package Quterma
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();

$venues_query = new WP_Query(array(
    'post_type'      => 'quterma_venue',
    'post_status'    => 'publish',
    'posts_per_page' => -1,
    'orderby'        => 'menu_order title',
    'order'          => 'ASC',
    'no_found_rows'  => true,
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

  <!-- ФИЛЬТРЫ ГОРОДОВ И КНОПКА РАСШИРЕННЫХ ФИЛЬТРОВ -->
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
    <button type="button" class="filter-more-btn" id="moreFiltersBtn">
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
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><line x1="21" y1="21" x2="16.5" y2="16.5"/></svg>
    </div>
    <p class="empty-state-text"><?php esc_html_e('В этом разделе пока нет заведений в гастрогиде — но мы постоянно пополняем каталог.', 'quterma'); ?></p>
    <button type="button" class="empty-state-btn" id="venueEmptyReset"><?php esc_html_e('Показать все города', 'quterma'); ?></button>
  </div>
</div>

<?php
get_footer();
