<?php
/**
 * The header for Quterma theme
 *
 * @package Quterma
 */

if (!defined('ABSPATH')) {
    exit;
}
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo('charset'); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="skip-link" href="#content"><?php esc_html_e('Перейти к основному содержанию', 'quterma'); ?></a>

<div class="id-stripe"></div>

<header class="site-hdr" id="header">
  <div class="wrap">
    <div class="hdr-row">
      <?php if (has_custom_logo()) : ?>
        <div class="site-logo">
          <?php the_custom_logo(); ?>
        </div>
      <?php else : ?>
        <a href="<?php echo esc_url(home_url('/')); ?>" class="masthead" rel="home">
          <span class="masthead-logo"><?php bloginfo('name'); ?><span class="masthead-dot"></span></span>
        </a>
      <?php endif; ?>

      <nav class="nav-list" aria-label="<?php esc_attr_e('Главная навигация', 'quterma'); ?>">
        <?php
        if (has_nav_menu('primary')) {
            wp_nav_menu(array(
                'theme_location' => 'primary',
                'container'      => false,
                'items_wrap'     => '%3$s',
                'depth'          => 1,
                'walker'         => new Quterma_Nav_Walker(),
                'fallback_cb'    => 'quterma_default_desktop_nav',
            ));
        } else {
            quterma_default_desktop_nav();
        }
        ?>
      </nav>

      <div class="hdr-actions">
        <button type="button" class="icon-btn search-btn" id="searchOpenBtn" aria-label="<?php esc_attr_e('Поиск', 'quterma'); ?>">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        </button>
        <button type="button" class="icon-btn burger" id="burgerBtn" aria-label="<?php esc_attr_e('Меню', 'quterma'); ?>" aria-expanded="false" aria-controls="mobMenu">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="3" y1="8" x2="21" y2="8"/><line x1="3" y1="16" x2="21" y2="16"/></svg>
        </button>
      </div>
    </div>

    <div class="mob-srch">
      <form role="search" method="get" class="mob-srch-inner" action="<?php echo esc_url(home_url('/')); ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <label for="mobSearchInput" class="sr-only"><?php esc_html_e('Поиск по сайту', 'quterma'); ?></label>
        <input type="search" id="mobSearchInput" placeholder="<?php esc_attr_e('Поиск…', 'quterma'); ?>" value="<?php echo get_search_query(); ?>" name="s" autocomplete="off">
      </form>
    </div>
  </div>
</header>
