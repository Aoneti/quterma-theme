<?php
/**
 * Template Name: Реклама и партнёрство
 * Template for Advertising & Partnership page
 *
 * @package Quterma
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();

while (have_posts()) : the_post();
    $subtitle = get_post_meta(get_the_ID(), '_page_subtitle', true);
    if (empty($subtitle) && has_excerpt()) {
        $subtitle = get_the_excerpt();
    }
    if (empty($subtitle)) {
        $subtitle = __('Кутерьму читают горожане, которым интересен их город — это лучшая аудитория для локального бизнеса и партнёров, которые говорят на одном языке с городом.', 'quterma');
    }
    $content = get_the_content();
?>

<div class="wrap">
  <div style="padding-top:28px">
    <?php get_template_part('template-parts/breadcrumbs'); ?>

    <div class="page-header">
      <h1 class="page-title"><?php the_title(); ?></h1>
      <?php if (!empty($subtitle)) : ?>
        <p class="page-subtitle"><?php echo esc_html($subtitle); ?></p>
      <?php endif; ?>
    </div>
  </div>

  <div class="stat-row rev" style="margin-bottom:56px;padding:28px;background:var(--paper);border-radius:10px;box-shadow:var(--sh-sm);display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:24px">
    <div><div class="stat-num" style="font-family:var(--fd);font-size:32px;font-weight:700;color:var(--brand)">40K+</div><div class="stat-label" style="font-size:13px;color:var(--ink-3);margin-top:4px"><?php esc_html_e('Читателей в месяц', 'quterma'); ?></div></div>
    <div><div class="stat-num" style="font-family:var(--fd);font-size:32px;font-weight:700;color:var(--brand)">65%</div><div class="stat-label" style="font-size:13px;color:var(--ink-3);margin-top:4px"><?php esc_html_e('Возраст 18–34', 'quterma'); ?></div></div>
    <div><div class="stat-num" style="font-family:var(--fd);font-size:32px;font-weight:700;color:var(--brand)">12</div><div class="stat-label" style="font-size:13px;color:var(--ink-3);margin-top:4px"><?php esc_html_e('Городов области', 'quterma'); ?></div></div>
    <div><div class="stat-num" style="font-family:var(--fd);font-size:32px;font-weight:700;color:var(--brand)">4 мин</div><div class="stat-label" style="font-size:13px;color:var(--ink-3);margin-top:4px"><?php esc_html_e('Среднее время на статье', 'quterma'); ?></div></div>
  </div>

  <?php if (!empty(trim(strip_tags($content)))) : ?>
    <div class="article-col article-body rev" style="margin-bottom:50px">
      <?php the_content(); ?>
    </div>
  <?php endif; ?>

  <div class="sec-div rev" style="margin-top:0">
    <div class="sec-div-acc"></div>
    <h2 class="sec-div-title"><?php esc_html_e('Почему с нами', 'quterma'); ?></h2>
    <div class="sec-div-line"></div>
  </div>
  <div class="info-grid rev" style="margin-bottom:56px">
    <div class="info-card">
      <div class="info-card-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg></div>
      <div class="info-card-title"><?php esc_html_e('Живая аудитория', 'quterma'); ?></div>
      <div class="info-card-text"><?php esc_html_e('Читатели, которым не всё равно на город — они читают внимательно и доверяют рекомендациям редакции.', 'quterma'); ?></div>
    </div>
    <div class="info-card">
      <div class="info-card-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2l3 7h7l-5.5 4.5L18 21l-6-4-6 4 1.5-7.5L2 9h7z"/></svg></div>
      <div class="info-card-title"><?php esc_html_e('Нативный формат', 'quterma'); ?></div>
      <div class="info-card-text"><?php esc_html_e('Материалы о партнёрах делаем так же качественно, как редакционные — без баннерной усталости.', 'quterma'); ?></div>
    </div>
    <div class="info-card">
      <div class="info-card-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg></div>
      <div class="info-card-title"><?php esc_html_e('Прозрачная отчётность', 'quterma'); ?></div>
      <div class="info-card-text"><?php esc_html_e('Показываем реальные охваты и вовлечённость — без завышенных обещаний.', 'quterma'); ?></div>
    </div>
  </div>

  <div class="sec-div rev" style="margin-top:0">
    <div class="sec-div-acc"></div>
    <h2 class="sec-div-title"><?php esc_html_e('Варианты размещения', 'quterma'); ?></h2>
    <div class="sec-div-line"></div>
  </div>
  <div class="specials rev" style="margin-bottom:56px">
    <div class="spec-card">
      <div class="spec-acc"></div>
      <div class="spec-lbl"><?php esc_html_e('Нативный материал', 'quterma'); ?></div>
      <h3 class="spec-title"><?php esc_html_e('Публикация в ленте', 'quterma'); ?></h3>
      <p class="spec-desc"><?php esc_html_e('Полноценный материал о вашем деле или событии, в редакционном стиле Кутерьмы.', 'quterma'); ?></p>
    </div>
    <div class="spec-card">
      <div class="spec-acc"></div>
      <div class="spec-lbl"><?php esc_html_e('Каталог', 'quterma'); ?></div>
      <h3 class="spec-title"><?php esc_html_e('Карточка в Гастрогиде', 'quterma'); ?></h3>
      <p class="spec-desc"><?php esc_html_e('Постоянное присутствие в каталоге заведений — с фото, описанием и расширенным профилем.', 'quterma'); ?></p>
    </div>
    <div class="spec-card">
      <div class="spec-acc"></div>
      <div class="spec-lbl"><?php esc_html_e('Афиша', 'quterma'); ?></div>
      <h3 class="spec-title"><?php esc_html_e('Продвижение события', 'quterma'); ?></h3>
      <p class="spec-desc"><?php esc_html_e('Размещение вашего мероприятия в разделе «События» с приоритетным показом.', 'quterma'); ?></p>
    </div>
    <div class="spec-card">
      <div class="spec-acc"></div>
      <div class="spec-lbl"><?php esc_html_e('Долгосрочно', 'quterma'); ?></div>
      <h3 class="spec-title"><?php esc_html_e('Партнёрство с рубрикой', 'quterma'); ?></h3>
      <p class="spec-desc"><?php esc_html_e('Генеральное партнёрство ключевого раздела издания с интеграцией в шапку и карточки.', 'quterma'); ?></p>
    </div>
  </div>

  <div class="sec-div rev" style="margin-top:0">
    <div class="sec-div-acc"></div>
    <h2 class="sec-div-title"><?php esc_html_e('Связаться с рекламным отделом', 'quterma'); ?></h2>
    <div class="sec-div-line"></div>
  </div>
  <div class="rev" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:24px;margin-bottom:60px;padding:32px;background:var(--paper);border-radius:10px;box-shadow:var(--sh-sm)">
    <div>
      <div class="foot-brand-name" style="color:var(--ink);font-size:var(--t-xl)"><?php esc_html_e('Обсудить проект', 'quterma'); ?></div>
      <p style="font-size:var(--t-sm);color:var(--ink-3);margin-top:8px;max-width:480px"><?php esc_html_e('Напишите нам о вашем бренде, задаче и желаемых сроках — мы подготовим предложение с медиакитом и ценами в течение рабочего дня.', 'quterma'); ?></p>
      <a href="mailto:quterma@yandex.ru" style="display:inline-block;margin-top:14px;font-weight:700;color:var(--brand);font-size:var(--t-sm)">quterma@yandex.ru</a>
    </div>
    <div>
      <a href="mailto:quterma@yandex.ru?subject=Реклама%20в%20Кутерьме" class="btn-pill" style="display:inline-block;background:var(--brand);color:#fff;padding:12px 24px;border-radius:999px;font-family:var(--fd);font-size:13px;font-weight:700;text-decoration:none">
        <?php esc_html_e('Написать на почту →', 'quterma'); ?>
      </a>
    </div>
  </div>
</div>

<?php
endwhile;

get_footer();
