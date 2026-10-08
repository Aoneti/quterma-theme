<?php
/**
 * The template for displaying 404 pages (Not Found)
 *
 * @package Quterma
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>

<main id="content" class="err-wrap">
  <div class="err-inner rev on">
    <div class="err-scene">
      <span class="err-shape s1"></span>
      <span class="err-shape s2"></span>
      <span class="err-shape s3"></span>
      <span class="err-shape s4"></span>
      <div class="err-404">404</div>
    </div>
    <h1 class="err-title"><?php esc_html_e('Вот это кутерьма!', 'quterma'); ?></h1>
    <p class="err-text">
      <?php esc_html_e('Такой страницы не существует — возможно, материал переехал, удалён, или в адресе опечатка. Даже у нас в редакции иногда бывает беспорядок.', 'quterma'); ?>
    </p>
    <div class="err-actions">
      <a href="<?php echo esc_url(home_url('/')); ?>" class="btn-pill"><?php esc_html_e('На главную', 'quterma'); ?></a>
      <button type="button" class="btn-pill outline" onclick="document.getElementById('searchOpenBtn').click();"><?php esc_html_e('Поискать материал', 'quterma'); ?></button>
    </div>
  </div>
</main>

<?php
get_footer();
