<?php
/**
 * Template Name: О редакции
 * Template for "About" editorial page
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
        $subtitle = __('Кутерьма — независимое городское медиа о Ярославле. Мы верим, что городу нужен голос, который не боится быть человечным.', 'quterma');
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

  <div class="article-col article-body rev" style="margin-bottom:50px">
    <?php if (!empty(trim(strip_tags($content)))) : ?>
      <?php the_content(); ?>
    <?php else : ?>
      <p><?php esc_html_e('Кутерьма родилась из простого наблюдения: городские новости часто пишут языком отчётов, а не языком людей, которые в этом городе живут. Мы решили попробовать иначе — писать так, будто рассказываем другу, что произошло, а не составляем протокол.', 'quterma'); ?></p>
      <p><?php esc_html_e('Название редакция выбрала не случайно: «кутерьма» — это суматоха, движение, живой поток событий. Именно так мы видим город — не статичную картинку, а постоянное, немного хаотичное, но настоящее движение.', 'quterma'); ?></p>
      <p><?php esc_html_e('Мы — небольшая редакция, которая работает без давления со стороны крупных рекламодателей или органов власти. Наша независимость — это то, чем мы дорожим больше всего.', 'quterma'); ?></p>
    <?php endif; ?>
  </div>

  <div class="sec-div rev" style="margin-top:0">
    <div class="sec-div-acc"></div>
    <h2 class="sec-div-title"><?php esc_html_e('Принципы редакции', 'quterma'); ?></h2>
    <div class="sec-div-line"></div>
  </div>
  <div class="info-grid rev" style="margin-bottom:56px">
    <div class="info-card">
      <div class="info-card-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2l3 7h7l-5.5 4.5L18 21l-6-4-6 4 1.5-7.5L2 9h7z"/></svg></div>
      <div class="info-card-title"><?php esc_html_e('Независимость', 'quterma'); ?></div>
      <div class="info-card-text"><?php esc_html_e('Редакционные решения принимаются редакцией — реклама никогда не влияет на содержание материалов.', 'quterma'); ?></div>
    </div>
    <div class="info-card">
      <div class="info-card-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/></svg></div>
      <div class="info-card-title"><?php esc_html_e('Открытость', 'quterma'); ?></div>
      <div class="info-card-text"><?php esc_html_e('Мы указываем источники, признаём ошибки и отвечаем на вопросы читателей напрямую.', 'quterma'); ?></div>
    </div>
    <div class="info-card">
      <div class="info-card-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg></div>
      <div class="info-card-title"><?php esc_html_e('Люди прежде всего', 'quterma'); ?></div>
      <div class="info-card-text"><?php esc_html_e('За каждой новостью — конкретные люди. Мы стараемся не терять это из виду ни в одном материале.', 'quterma'); ?></div>
    </div>
  </div>

  <div class="sec-div rev" style="margin-top:0">
    <div class="sec-div-acc"></div>
    <h2 class="sec-div-title"><?php esc_html_e('Связь с редакцией', 'quterma'); ?></h2>
    <div class="sec-div-line"></div>
  </div>
  <div class="rev" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:24px;margin-bottom:60px;padding:32px;background:var(--paper);border-radius:10px;box-shadow:var(--sh-sm)">
    <div>
      <div class="foot-brand-name" style="color:var(--ink);font-size:var(--t-xl)"><?php esc_html_e('Напишите нам', 'quterma'); ?></div>
      <p style="font-size:var(--t-sm);color:var(--ink-3);margin-top:8px;max-width:420px"><?php esc_html_e('По любым вопросам, идеям для материалов или замеченным неточностям — мы отвечаем на все письма.', 'quterma'); ?></p>
      <a href="mailto:quterma@yandex.ru" style="display:inline-block;margin-top:14px;font-weight:700;color:var(--brand);font-size:var(--t-sm)">quterma@yandex.ru</a>
    </div>
    <div class="foot-socs-big" style="flex-shrink:0">
      <a href="https://t.me/kuterma" class="foot-soc-big" style="color:var(--ink-2);border-color:var(--bd)" target="_blank" rel="noopener noreferrer" aria-label="Telegram"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm5.894 8.221-1.97 9.28c-.145.658-.537.818-1.084.508l-3-2.21-1.447 1.394c-.16.16-.295.295-.605.295l.213-3.053 5.56-5.023c.242-.213-.054-.333-.373-.12l-6.869 4.326-2.96-.924c-.643-.204-.657-.643.136-.953l11.57-4.461c.537-.194 1.006.131.829.941z"/></svg></a>
      <a href="https://vk.com/kuterma" class="foot-soc-big" style="color:var(--ink-2);border-color:var(--bd)" target="_blank" rel="noopener noreferrer" aria-label="ВКонтакте"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M15.684 0H8.316C1.592 0 0 1.592 0 8.316v7.368C0 22.408 1.592 24 8.316 24h7.368C22.408 24 24 22.408 24 15.684V8.316C24 1.592 22.391 0 15.684 0zm3.692 17.123h-1.744c-.66 0-.864-.525-2.05-1.727-1.033-1-1.49-1.135-1.744-1.135-.356 0-.458.102-.458.593v1.575c0 .424-.135.678-1.253.678-1.846 0-3.896-1.118-5.335-3.202C4.624 10.857 4.03 8.57 4.03 8.096c0-.254.102-.491.593-.491h1.744c.44 0 .61.203.78.678.863 2.49 2.303 4.675 2.896 4.675.22 0 .322-.102.322-.66V9.721c-.068-1.186-.695-1.287-.695-1.71 0-.203.17-.407.44-.407h2.744c.373 0 .508.203.508.643v3.473c0 .372.17.508.271.508.22 0 .407-.136.813-.542 1.253-1.406 2.15-3.574 2.15-3.574.119-.254.322-.491.763-.491h1.744c.525 0 .644.27.525.643-.22 1.017-2.354 4.031-2.354 4.031-.186.305-.254.44 0 .78.186.254.796.779 1.203 1.253.745.847 1.32 1.558 1.473 2.05.17.49-.085.744-.576.744z"/></svg></a>
      <a href="https://dzen.ru/quterma" class="foot-soc-big" style="color:var(--ink-2);border-color:var(--bd)" target="_blank" rel="noopener noreferrer" aria-label="Дзен"><svg viewBox="0 0 169 169" fill="currentColor" style="width:24px;height:24px"><path fill-rule="evenodd" clip-rule="evenodd" d="M84.0337 168.01H84.7036C118.068 168.01 137.434 164.651 151.152 151.333C165.139 137.206 168.369 117.709 168.369 84.4749V83.5351C168.369 50.311 165.139 30.9445 151.152 16.677C137.444 3.3594 117.938 0 84.7136 0H84.0437C50.6797 0 31.3031 3.3594 17.5856 16.677C3.59808 30.8045 0.368652 50.311 0.368652 83.5351V84.4749C0.368652 117.699 3.59808 137.066 17.5856 151.333C31.1732 164.651 50.6797 168.01 84.0337 168.01ZM148.369 82.7304C148.369 82.0906 147.849 81.5608 147.209 81.5308C124.246 80.661 110.271 77.732 100.494 67.955C90.6967 58.1581 87.7776 44.1724 86.9079 21.1596C86.8879 20.5198 86.358 20 85.7082 20H83.0291C82.3893 20 81.8594 20.5198 81.8295 21.1596C80.9597 44.1624 78.0406 58.1581 68.2437 67.955C58.4568 77.742 44.4911 80.661 21.5283 81.5308C20.8885 81.5508 20.3687 82.0806 20.3687 82.7304V85.4096C20.3687 86.0494 20.8885 86.5792 21.5283 86.6092C44.4911 87.4789 58.4667 90.408 68.2437 100.185C78.0206 109.962 80.9397 123.908 81.8195 146.83C81.8394 147.47 82.3693 147.99 83.0191 147.99H85.7082C86.348 147.99 86.8779 147.47 86.9079 146.83C87.7876 123.908 90.7067 109.962 100.484 100.185C110.271 90.398 124.236 87.4789 147.199 86.6092C147.839 86.5892 148.359 86.0594 148.359 85.4096V82.7304H148.369Z" /></svg></a>
      <a href="<?php bloginfo('rss2_url'); ?>" class="foot-soc-big" style="color:var(--ink-2);border-color:var(--bd)" target="_blank" rel="noopener noreferrer" aria-label="RSS"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 11a9 9 0 0 1 9 9"/><path d="M4 4a16 16 0 0 1 16 16"/><circle cx="5" cy="19" r="1"/></svg></a>
    </div>
  </div>
</div>

<?php
endwhile;

get_footer();
