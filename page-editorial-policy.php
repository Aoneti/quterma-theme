<?php
/**
 * Template Name: Редакционная политика
 * Template for Editorial Policy page
 *
 * @package Quterma
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();

while (have_posts()) : the_post();
    $email = quterma_get_setting('quterma_email', 'quterma@yandex.ru');
    $ad_url = quterma_get_page_url('advertising', home_url('/advertising/'));
?>

<main id="content" class="wrap">
  <div style="padding-top:28px">
    <?php get_template_part('template-parts/breadcrumbs'); ?>

    <div class="page-header">
      <h1 class="page-title"><?php esc_html_e('Редакционная политика', 'quterma'); ?></h1>
      <p class="page-subtitle"><?php esc_html_e('Принципы нашей работы, стандарты фактчекинга, правила взаимодействия с источниками и отношение к рекламе.', 'quterma'); ?></p>
    </div>
  </div>

  <div class="article-col article-body rev" style="margin-bottom:48px">
    <p class="lead" style="font-size:18px;font-family:var(--fs);font-weight:600;line-height:1.6;color:var(--ink)">
      <?php esc_html_e('«Кутерьма» — независимое городское издание о Ярославле. Мы пишем о людях, городской среде, культуре, истории и гастрономии. Наша главная ценность — доверие горожан, поэтому вся работа редакции строится на прозрачных и проверяемых правилах.', 'quterma'); ?>
    </p>
  </div>

  <!-- КЛЮЧЕВЫЕ ПРИНЦИПЫ -->
  <div class="sec-div rev" style="margin-top:0">
    <div class="sec-div-acc"></div>
    <h2 class="sec-div-title"><?php esc_html_e('Основные стандарты редакции', 'quterma'); ?></h2>
    <div class="sec-div-line"></div>
  </div>

  <div class="info-grid rev" style="margin-bottom:56px">
    <div class="info-card">
      <div class="info-card-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2l3 7h7l-5.5 4.5L18 21l-6-4-6 4 1.5-7.5L2 9h7z"/></svg>
      </div>
      <div class="info-card-title"><?php esc_html_e('Независимость', 'quterma'); ?></div>
      <div class="info-card-text"><?php esc_html_e('Редакционные решения принимаются исключительно журналистами. Ни рекламодатели, ни чиновники, ни учредители не могут влиять на темы, тональность или выводы материалов.', 'quterma'); ?></div>
    </div>

    <div class="info-card">
      <div class="info-card-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><polyline points="12 6 12 12 14 14"/></svg>
      </div>
      <div class="info-card-title"><?php esc_html_e('Фактчекинг и источники', 'quterma'); ?></div>
      <div class="info-card-text"><?php esc_html_e('Любые утверждения проверяются по первоисточникам или подтверждаются минимум двумя независимыми свидетельствами. Мы всегда указываем авторов цитат, фото и исследований.', 'quterma'); ?></div>
    </div>

    <div class="info-card">
      <div class="info-card-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
      </div>
      <div class="info-card-title"><?php esc_html_e('Честное исправление ошибок', 'quterma'); ?></div>
      <div class="info-card-text"><?php esc_html_e('Мы не замалчиваем неточности. При обнаружении фактической ошибки материал обновляется, а в тексте открыто указывается дата и суть внесённой правки.', 'quterma'); ?></div>
    </div>
  </div>

  <!-- ПОДРОБНЫЕ ПРАВИЛА -->
  <div class="sec-div rev" style="margin-top:0">
    <div class="sec-div-acc"></div>
    <h2 class="sec-div-title"><?php esc_html_e('Реклама, мнения и герои', 'quterma'); ?></h2>
    <div class="sec-div-line"></div>
  </div>

  <div class="info-grid rev" style="margin-bottom:56px">
    <div class="info-card">
      <div class="info-card-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
      </div>
      <div class="info-card-title"><?php esc_html_e('Прозрачная реклама', 'quterma'); ?></div>
      <div class="info-card-text"><?php esc_html_e('Все коммерческие публикации явно маркируются как «Партнёрский материал» или «Реклама». Мы никогда не маскируем рекламу под редакционный репортаж.', 'quterma'); ?></div>
    </div>

    <div class="info-card">
      <div class="info-card-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
      </div>
      <div class="info-card-title"><?php esc_html_e('Баланс мнений', 'quterma'); ?></div>
      <div class="info-card-text"><?php esc_html_e('В конфликтных и спорных ситуациях мы предоставляем слово всем вовлечённым сторонам, отделяя установленные факты от субъективных оценок участников.', 'quterma'); ?></div>
    </div>

    <div class="info-card">
      <div class="info-card-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
      </div>
      <div class="info-card-title"><?php esc_html_e('Защита источников', 'quterma'); ?></div>
      <div class="info-card-text"><?php esc_html_e('Мы уважает конфиденциальность собеседников, если открытое раскрытие имени несёт для них неоправданные риски, при условии внутренней верификации сведений.', 'quterma'); ?></div>
    </div>
  </div>

  <!-- ОБРАТНАЯ СВЯЗЬ -->
  <div class="sec-div rev" style="margin-top:0">
    <div class="sec-div-acc"></div>
    <h2 class="sec-div-title"><?php esc_html_e('Сообщить об ошибке или предложить тему', 'quterma'); ?></h2>
    <div class="sec-div-line"></div>
  </div>

  <div class="rev" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:24px;margin-bottom:60px;padding:32px;background:var(--paper);border-radius:10px;box-shadow:var(--sh-sm);border:1px solid var(--bd)">
    <div>
      <div class="foot-brand-name" style="color:var(--ink);font-size:var(--t-xl)"><?php esc_html_e('Мы ценим внимательность читателей', 'quterma'); ?></div>
      <p style="font-size:var(--t-sm);color:var(--ink-3);margin-top:8px;max-width:540px;line-height:1.6">
        <?php esc_html_e('Если вы нашли неточность в статье, хотите высказать дополнение к материалу или предложить важную для города историю — напишите дежурному редактору.', 'quterma'); ?>
      </p>
    </div>
    <div style="display:flex;gap:12px;flex-wrap:wrap">
      <a href="mailto:<?php echo esc_attr($email); ?>?subject=<?php echo esc_attr(rawurlencode(__('Замечание к материалу / Редакционная политика', 'quterma'))); ?>" class="btn-pill" style="display:inline-block;background:var(--brand);color:#fff;padding:12px 24px;border-radius:999px;font-family:var(--fd);font-size:13px;font-weight:700;text-decoration:none">
        <?php esc_html_e('Написать редактору →', 'quterma'); ?>
      </a>
      <a href="<?php echo esc_url($ad_url); ?>" class="btn-pill" style="display:inline-block;background:var(--paper-2);border:1px solid var(--bd);color:var(--ink);padding:12px 24px;border-radius:999px;font-family:var(--fd);font-size:13px;font-weight:700;text-decoration:none">
        <?php esc_html_e('Реклама и спецпроекты', 'quterma'); ?>
      </a>
    </div>
  </div>
</main>

<?php
endwhile;

get_footer();
