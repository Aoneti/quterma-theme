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
    'posts_per_page' => 24,
    'orderby'        => 'menu_order title',
    'order'          => 'ASC',
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
        <span class="tag" data-filter-type="кофейня"><?php esc_html_e('Кофейня', 'quterma'); ?></span>
        <span class="tag" data-filter-type="ресторан"><?php esc_html_e('Ресторан', 'quterma'); ?></span>
        <span class="tag" data-filter-type="кафе"><?php esc_html_e('Кафе', 'quterma'); ?></span>
        <span class="tag" data-filter-type="пекарня"><?php esc_html_e('Пекарня', 'quterma'); ?></span>
        <span class="tag" data-filter-type="стрит-фуд"><?php esc_html_e('Стрит-фуд', 'quterma'); ?></span>
      </div>
    </div>
    <div class="filter-extra-group">
      <div class="filter-extra-label"><?php esc_html_e('Цена', 'quterma'); ?></div>
      <div class="filter-extra-opts">
        <span class="tag" data-filter-price="₽">₽</span>
        <span class="tag" data-filter-price="₽₽">₽₽</span>
        <span class="tag" data-filter-price="₽₽₽">₽₽₽</span>
      </div>
    </div>
    <div class="filter-extra-group">
      <div class="filter-extra-label"><?php esc_html_e('Особенности', 'quterma'); ?></div>
      <div class="filter-extra-opts">
        <span class="tag" data-filter-feature="веранда"><?php esc_html_e('Веранда', 'quterma'); ?></span>
        <span class="tag" data-filter-feature="животными"><?php esc_html_e('С животными', 'quterma'); ?></span>
        <span class="tag" data-filter-feature="веган"><?php esc_html_e('Веган-меню', 'quterma'); ?></span>
        <span class="tag" data-filter-feature="допоздна"><?php esc_html_e('Работает допоздна', 'quterma'); ?></span>
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
    else :
        // Fallback default sample venues if no CPT entries created yet
        $default_venues = array(
            array('city' => 'yaroslavl', 'type' => 'Кофейня', 'city_name' => 'Ярославль', 'title' => 'Скамейка', 'addr' => 'ул. Кирова, 14', 'desc' => 'Небольшая кофейня в центре с авторскими сиропами и террасой во дворе.'),
            array('city' => 'yaroslavl', 'type' => 'Ресторан', 'city_name' => 'Ярославль', 'title' => 'Дом на набережной', 'addr' => 'Волжская наб., 2', 'desc' => 'Вид на Волгу и сезонное меню из локальных продуктов.'),
            array('city' => 'yaroslavl', 'type' => 'Пекарня', 'city_name' => 'Ярославль', 'title' => 'Хлебный двор', 'addr' => 'ул. Свободы, 46', 'desc' => 'Ремесленный хлеб и выпечка на закваске, свежая партия к 8 утра.'),
            array('city' => 'yaroslavl', 'type' => 'Кофейня', 'city_name' => 'Ярославль', 'title' => 'Полдник', 'addr' => 'пр-т Октября, 21', 'desc' => 'Домашние десерты и большой выбор чая, тихо по будням днём.'),
            array('city' => 'rybinsk', 'type' => 'Кафе', 'city_name' => 'Рыбинск', 'title' => 'У пристани', 'addr' => 'Волжская наб., 8', 'desc' => 'Простое меню, большие окна на реку, работает с раннего утра.'),
            array('city' => 'tutaev', 'type' => 'Ресторан', 'city_name' => 'Тутаев', 'title' => 'Мельница', 'addr' => 'ул. Соборная, 5', 'desc' => 'Кухня по мотивам местных рецептов в здании старой мельницы.'),
            array('city' => 'rostov', 'type' => 'Кафе', 'city_name' => 'Ростов Великий', 'title' => 'Самовар', 'addr' => 'ул. Ленинская, 10', 'desc' => 'Чаепития у стен кремля, домашняя выпечка и варенье.'),
            array('city' => 'uglich', 'type' => 'Ресторан', 'city_name' => 'Углич', 'title' => 'Сыроварня', 'addr' => 'ул. Ярославская, 3', 'desc' => 'Своя сыроварня при ресторане, дегустации по выходным.'),
            array('city' => 'myshkin', 'type' => 'Трактиръ', 'city_name' => 'Мышкин', 'title' => 'Мышкинский трактиръ', 'addr' => 'ул. Никольская, 12', 'desc' => 'Простая сытная кухня в двух шагах от музея мыши.'),
        );
        foreach ($default_venues as $dv) :
    ?>
      <div class="venue-card" data-city="<?php echo esc_attr($dv['city']); ?>" data-type="<?php echo esc_attr(mb_strtolower($dv['type'])); ?>">
        <div class="venue-img">
          <span class="venue-type-badge"><?php echo esc_html($dv['type']); ?></span>
          <span class="venue-city-badge"><?php echo esc_html($dv['city_name']); ?></span>
          <?php echo quterma_placeholder_img(38, 38); ?>
        </div>
        <div class="venue-body">
          <div class="venue-name"><?php echo esc_html($dv['title']); ?></div>
          <div class="venue-address">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>
            </svg>
            <?php echo esc_html($dv['addr']); ?>
          </div>
          <p class="venue-desc"><?php echo esc_html($dv['desc']); ?></p>
        </div>
      </div>
    <?php
        endforeach;
    endif;
    ?>
  </div>

  <div class="empty-state" id="venueEmpty">
    <div class="empty-state-icon">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><line x1="21" y1="21" x2="16.5" y2="16.5"/></svg>
    </div>
    <p class="empty-state-text"><?php esc_html_e('В этом городе пока нет заведений в гастрогиде — но мы постоянно пополняем каталог.', 'quterma'); ?></p>
    <button type="button" class="empty-state-btn" id="venueEmptyReset"><?php esc_html_e('Показать все города', 'quterma'); ?></button>
  </div>
</div>

<?php
get_footer();
