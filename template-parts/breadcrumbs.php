<?php
/**
 * Template part for Breadcrumbs navigation
 *
 * @package Quterma
 */

if (!defined('ABSPATH')) {
    exit;
}

if (is_front_page()) {
    return;
}
?>
<nav class="breadcrumbs" aria-label="<?php esc_attr_e('Хлебные крошки', 'quterma'); ?>">
  <a href="<?php echo esc_url(home_url('/')); ?>"><?php esc_html_e('Главная', 'quterma'); ?></a>
  <span class="bc-sep">/</span>

  <?php if (is_singular('post')) : ?>
    <?php
    $cats = get_the_category();
    if (!empty($cats)) :
        $cat = $cats[0];
    ?>
      <a href="<?php echo esc_url(get_category_link($cat)); ?>"><?php echo esc_html($cat->name); ?></a>
      <span class="bc-sep">/</span>
    <?php endif; ?>
    <span class="bc-current"><?php the_title(); ?></span>

  <?php elseif (is_singular('quterma_venue')) : ?>
    <a href="<?php echo esc_url(home_url('/gastroguide/')); ?>"><?php esc_html_e('Гастрогид', 'quterma'); ?></a>
    <span class="bc-sep">/</span>
    <span class="bc-current"><?php the_title(); ?></span>

  <?php elseif (is_category()) : ?>
    <span class="bc-current"><?php single_cat_title(); ?></span>

  <?php elseif (is_tag()) : ?>
    <span class="bc-current"><?php printf(__('Тема: %s', 'quterma'), single_tag_title('', false)); ?></span>

  <?php elseif (is_search()) : ?>
    <span class="bc-current"><?php printf(__('Поиск: %s', 'quterma'), get_search_query()); ?></span>

  <?php elseif (is_page()) : ?>
    <span class="bc-current"><?php the_title(); ?></span>

  <?php elseif (is_404()) : ?>
    <span class="bc-current"><?php esc_html_e('Ошибка 404', 'quterma'); ?></span>

  <?php else : ?>
    <span class="bc-current"><?php the_archive_title(); ?></span>
  <?php endif; ?>
</nav>
