<?php
/**
 * Template Name: Правовая информация
 * Template for Legal Information & Copyright page
 *
 * @package Quterma
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();

while (have_posts()) : the_post();
    $email = quterma_get_setting('quterma_email', 'quterma@yandex.ru');
    $privacy_url = function_exists('get_privacy_policy_url') && get_privacy_policy_url() ? get_privacy_policy_url() : quterma_get_page_url('privacy-policy', home_url('/privacy-policy/'));
?>

<main id="content" class="wrap">
  <div style="padding-top:28px">
    <?php get_template_part('template-parts/breadcrumbs'); ?>

    <div class="page-header">
      <h1 class="page-title"><?php esc_html_e('Правовая информация', 'quterma'); ?></h1>
      <p class="page-subtitle"><?php esc_html_e('Использование материалов, правила цитирования, авторские права и официальные реквизиты издания «Кутерьма».', 'quterma'); ?></p>
    </div>
  </div>

  <div class="article-col article-body rev" style="margin-bottom:48px">
    <p class="lead" style="font-size:18px;font-family:var(--fs);font-weight:600;line-height:1.6;color:var(--ink)">
      <?php esc_html_e('Все права на материалы, опубликованные на сайте сетевого издания «Кутерьма» (тексты, фотографии, инфографика, видеоматериалы, подкасты и фирменный стиль), охраняются в соответствии с законодательством Российской Федерации об авторском праве и смежных правах.', 'quterma'); ?>
    </p>
  </div>

  <!-- КЛЮЧЕВЫЕ ЮРИДИЧЕСКИЕ ПРАВИЛА -->
  <div class="sec-div rev" style="margin-top:0">
    <div class="sec-div-acc"></div>
    <h2 class="sec-div-title"><?php esc_html_e('Правила использования контента', 'quterma'); ?></h2>
    <div class="sec-div-line"></div>
  </div>

  <div class="info-grid rev" style="margin-bottom:56px">
    <div class="info-card">
      <div class="info-card-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
      </div>
      <div class="info-card-title"><?php esc_html_e('Свободное цитирование', 'quterma'); ?></div>
      <div class="info-card-text"><?php esc_html_e('Допускается свободное некоммерческое цитирование материалов в объёме не более 30% оригинального текста с обязательной активной гиперссылкой на первоисточник на сайте «Кутерьма».', 'quterma'); ?></div>
    </div>

    <div class="info-card">
      <div class="info-card-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
      </div>
      <div class="info-card-title"><?php esc_html_e('Полная перепечатка', 'quterma'); ?></div>
      <div class="info-card-text"><?php esc_html_e('Полное воспроизведение материалов, фоторепортажей или использование в коммерческих целях третьими лицами возможно исключительно с предварительного письменного согласия редакции.', 'quterma'); ?></div>
    </div>

    <div class="info-card">
      <div class="info-card-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
      </div>
      <div class="info-card-title"><?php esc_html_e('Фотографии и авторство', 'quterma'); ?></div>
      <div class="info-card-text"><?php esc_html_e('Иллюстрации, фотоснимки и инфографика авторов издания защищены исключительными правами. Запрещается удаление водяных знаков и атрибуции авторов при любом использовании.', 'quterma'); ?></div>
    </div>
  </div>

  <!-- ДАННЫЕ И 152-ФЗ -->
  <div class="sec-div rev" style="margin-top:0">
    <div class="sec-div-acc"></div>
    <h2 class="sec-div-title"><?php esc_html_e('Персональные данные и безопасность', 'quterma'); ?></h2>
    <div class="sec-div-line"></div>
  </div>

  <div class="info-grid rev" style="margin-bottom:56px">
    <div class="info-card">
      <div class="info-card-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
      </div>
      <div class="info-card-title"><?php esc_html_e('Защита данных (152-ФЗ)', 'quterma'); ?></div>
      <div class="info-card-text"><?php esc_html_e('Мы собираем данные (e-mail при подписке, обезличенные метрики посещаемости) строго для функционирования сайта и никогда не передаем их третьим лицам.', 'quterma'); ?></div>
    </div>

    <div class="info-card">
      <div class="info-card-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
      </div>
      <div class="info-card-title"><?php esc_html_e('Ответственность пользователей', 'quterma'); ?></div>
      <div class="info-card-text"><?php esc_html_e('В комментариях и обратной связи запрещено распространение спама, разжигание розни, клевета и публикация чужих конфиденциальных данных.', 'quterma'); ?></div>
    </div>

    <div class="info-card">
      <div class="info-card-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
      </div>
      <div class="info-card-title"><?php esc_html_e('Актуализация правил', 'quterma'); ?></div>
      <div class="info-card-text"><?php esc_html_e('Редакция оставляет за собой право обновлять правила. Актуальная версия всегда опубликована на этой странице с указанием даты редакции.', 'quterma'); ?></div>
    </div>
  </div>

  <!-- ВЫХОДНЫЕ ДАННЫЕ -->
  <div class="sec-div rev" style="margin-top:0">
    <div class="sec-div-acc"></div>
    <h2 class="sec-div-title"><?php esc_html_e('Выходные данные и реквизиты', 'quterma'); ?></h2>
    <div class="sec-div-line"></div>
  </div>

  <div class="rev" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:24px;margin-bottom:60px;padding:32px;background:var(--paper);border-radius:10px;box-shadow:var(--sh-sm);border:1px solid var(--bd)">
    <div>
      <div class="foot-brand-name" style="color:var(--ink);font-size:var(--t-xl)"><?php esc_html_e('Сетевое издание «Кутерьма»', 'quterma'); ?></div>
      <p style="font-size:var(--t-sm);color:var(--ink-3);margin-top:8px;max-width:540px;line-height:1.6">
        <?php esc_html_e('Городское независимое медиа о Ярославле. Территория распространения: Российская Федерация и зарубежные страны. Язык: русский. По вопросам лицензирования и сотрудничества обращайтесь в редакцию.', 'quterma'); ?>
      </p>
    </div>
    <div style="display:flex;gap:12px;flex-wrap:wrap">
      <a href="mailto:<?php echo esc_attr($email); ?>?subject=<?php echo esc_attr(rawurlencode(__('Запрос прав на материал / Правовая информация', 'quterma'))); ?>" class="btn-pill" style="display:inline-block;background:var(--brand);color:#fff;padding:12px 24px;border-radius:999px;font-family:var(--fd);font-size:13px;font-weight:700;text-decoration:none">
        <?php esc_html_e('Запрос прав на перепечатку →', 'quterma'); ?>
      </a>
      <a href="<?php echo esc_url($privacy_url); ?>" class="btn-pill" style="display:inline-block;background:var(--paper-2);border:1px solid var(--bd);color:var(--ink);padding:12px 24px;border-radius:999px;font-family:var(--fd);font-size:13px;font-weight:700;text-decoration:none">
        <?php esc_html_e('Политика конфиденциальности', 'quterma'); ?>
      </a>
    </div>
  </div>
</main>

<?php
endwhile;

get_footer();
