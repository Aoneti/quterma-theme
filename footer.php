<?php
/**
 * The template for displaying the footer
 *
 * @package Quterma
 */

if (!defined('ABSPATH')) {
    exit;
}

$about_url    = quterma_get_page_url('about', home_url('/about/'));
$ad_url       = quterma_get_page_url('advertising', home_url('/advertising/'));
$privacy_url  = function_exists('get_privacy_policy_url') && get_privacy_policy_url() ? get_privacy_policy_url() : quterma_get_page_url('privacy-policy', home_url('/privacy-policy/'));
$legal_url    = quterma_get_page_url('legal', home_url('/legal/'));
$editorial_url = quterma_get_page_url('editorial-policy', home_url('/editorial-policy/'));
$support_url  = quterma_get_page_url('support', home_url('/about/#support'));
$email        = get_theme_mod('quterma_email', 'quterma@yandex.ru');
$telegram     = get_theme_mod('quterma_telegram', 'https://t.me/kuterma');
$vk           = get_theme_mod('quterma_vk', 'https://vk.com/kuterma');
$about_text   = get_theme_mod('quterma_footer_about', 'Независимое городское издание о Ярославле. С 2026 года.');
$marquee_text = get_theme_mod('quterma_marquee_text', 'Что происходит?');
?>

<footer class="footer">
  <div class="wrap">
    <div class="foot-top">
      <div class="foot-brand">
        <div class="foot-brand-name"><?php bloginfo('name'); ?></div>
        <p class="foot-about"><?php echo esc_html($about_text); ?></p>
        <a href="<?php echo esc_url($about_url); ?>" class="foot-cta-btn"><?php esc_html_e('О редакции', 'quterma'); ?></a>
      </div>

      <div class="foot-contact">
        <div class="foot-col-title"><?php esc_html_e('Редакция и право', 'quterma'); ?></div>
        <div class="foot-links">
          <?php
          if (has_nav_menu('footer')) {
              wp_nav_menu(array(
                  'theme_location' => 'footer',
                  'container'      => false,
                  'items_wrap'     => '%3$s',
                  'depth'          => 1,
                  'walker'         => new Quterma_Footer_Nav_Walker(),
                  'fallback_cb'    => false,
              ));
          } else {
          ?>
            <a href="<?php echo esc_url($ad_url); ?>" class="foot-link"><?php esc_html_e('Реклама и партнёрство', 'quterma'); ?></a>
            <a href="<?php echo esc_url($support_url); ?>" class="foot-link"><?php esc_html_e('Поддержать редакцию', 'quterma'); ?></a>
            <a href="<?php echo esc_url($editorial_url); ?>" class="foot-link"><?php esc_html_e('Редакционная политика', 'quterma'); ?></a>
            <a href="<?php echo esc_url($legal_url); ?>" class="foot-link"><?php esc_html_e('Правовая информация', 'quterma'); ?></a>
            <a href="<?php echo esc_url($privacy_url); ?>" class="foot-link"><?php esc_html_e('Политика конфиденциальности', 'quterma'); ?></a>
          <?php } ?>
          <a href="mailto:<?php echo esc_attr($email); ?>" class="foot-link foot-email"><?php echo esc_html($email); ?></a>
        </div>
      </div>

      <div class="foot-socs-big">
        <?php if (!empty($telegram)) : ?>
          <a href="<?php echo esc_url($telegram); ?>" class="foot-soc-big" target="_blank" rel="noopener noreferrer" aria-label="<?php esc_attr_e('Telegram', 'quterma'); ?>">
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm5.894 8.221-1.97 9.28c-.145.658-.537.818-1.084.508l-3-2.21-1.447 1.394c-.16.16-.295.295-.605.295l.213-3.053 5.56-5.023c.242-.213-.054-.333-.373-.12l-6.869 4.326-2.96-.924c-.643-.204-.657-.643.136-.953l11.57-4.461c.537-.194 1.006.131.829.941z"/></svg>
          </a>
        <?php endif; ?>
        <?php if (!empty($vk)) : ?>
          <a href="<?php echo esc_url($vk); ?>" class="foot-soc-big" target="_blank" rel="noopener noreferrer" aria-label="<?php esc_attr_e('ВКонтакте', 'quterma'); ?>">
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M15.684 0H8.316C1.592 0 0 1.592 0 8.316v7.368C0 22.408 1.592 24 8.316 24h7.368C22.408 24 24 22.408 24 15.684V8.316C24 1.592 22.391 0 15.684 0zm3.692 17.123h-1.744c-.66 0-.864-.525-2.05-1.727-1.033-1-1.49-1.135-1.744-1.135-.356 0-.458.102-.458.593v1.575c0 .424-.135.678-1.253.678-1.846 0-3.896-1.118-5.335-3.202C4.624 10.857 4.03 8.57 4.03 8.096c0-.254.102-.491.593-.491h1.744c.44 0 .61.203.78.678.863 2.49 2.303 4.675 2.896 4.675.22 0 .322-.102.322-.66V9.721c-.068-1.186-.695-1.287-.695-1.71 0-.203.17-.407.44-.407h2.744c.373 0 .508.203.508.643v3.473c0 .372.17.508.271.508.22 0 .407-.136.813-.542 1.253-1.406 2.15-3.574 2.15-3.574.119-.254.322-.491.763-.491h1.744c.525 0 .644.27.525.643-.22 1.017-2.354 4.031-2.354 4.031-.186.305-.254.44 0 .78.186.254.796.779 1.203 1.253.745.847 1.32 1.558 1.473 2.05.17.49-.085.744-.576.744z"/></svg>
          </a>
        <?php endif; ?>
        <a href="https://dzen.ru/quterma" target="_blank" rel="noopener noreferrer" class="foot-soc-big" aria-label="Дзен"><svg viewBox="0 0 169 169" fill="currentColor" style="width:24px;height:24px"><path fill-rule="evenodd" clip-rule="evenodd" d="M84.0337 168.01H84.7036C118.068 168.01 137.434 164.651 151.152 151.333C165.139 137.206 168.369 117.709 168.369 84.4749V83.5351C168.369 50.311 165.139 30.9445 151.152 16.677C137.444 3.3594 117.938 0 84.7136 0H84.0437C50.6797 0 31.3031 3.3594 17.5856 16.677C3.59808 30.8045 0.368652 50.311 0.368652 83.5351V84.4749C0.368652 117.699 3.59808 137.066 17.5856 151.333C31.1732 164.651 50.6797 168.01 84.0337 168.01ZM148.369 82.7304C148.369 82.0906 147.849 81.5608 147.209 81.5308C124.246 80.661 110.271 77.732 100.494 67.955C90.6967 58.1581 87.7776 44.1724 86.9079 21.1596C86.8879 20.5198 86.358 20 85.7082 20H83.0291C82.3893 20 81.8594 20.5198 81.8295 21.1596C80.9597 44.1624 78.0406 58.1581 68.2437 67.955C58.4568 77.742 44.4911 80.661 21.5283 81.5308C20.8885 81.5508 20.3687 82.0806 20.3687 82.7304V85.4096C20.3687 86.0494 20.8885 86.5792 21.5283 86.6092C44.4911 87.4789 58.4667 90.408 68.2437 100.185C78.0206 109.962 80.9397 123.908 81.8195 146.83C81.8394 147.47 82.3693 147.99 83.0191 147.99H85.7082C86.348 147.99 86.8779 147.47 86.9079 146.83C87.7876 123.908 90.7067 109.962 100.484 100.185C110.271 90.398 124.236 87.4789 147.199 86.6092C147.839 86.5892 148.359 86.0594 148.359 85.4096V82.7304H148.369Z" /></svg></a>
        <a href="<?php echo esc_url(get_feed_link()); ?>" class="foot-soc-big" aria-label="<?php esc_attr_e('RSS-лента', 'quterma'); ?>">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 11a9 9 0 0 1 9 9"/><path d="M4 4a16 16 0 0 1 16 16"/><circle cx="5" cy="19" r="1"/></svg>
        </a>
      </div>
    </div>
  </div>

  <div class="foot-marquee-wrap">
    <div class="foot-marquee" id="footMarquee">
      <?php for ($i = 0; $i < 8; $i++) : ?>
        <span><?php echo esc_html($marquee_text); ?></span>
      <?php endfor; ?>
    </div>
  </div>

  <div class="wrap">
    <div class="footer-bot">
      <span>© <?php bloginfo('name'); ?>. <?php echo wp_date('Y'); ?></span>
      <span><?php esc_html_e('Ярославль', 'quterma'); ?></span>
    </div>
  </div>
</footer>

<!-- SEARCH OVERLAY -->
<div class="srch-overlay" id="srchOverlay">
  <div class="srch-box">
    <form role="search" method="get" class="srch-inner" action="<?php echo esc_url(home_url('/')); ?>">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--ink-3)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;margin-right:11px"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
      <label for="srchInput" class="sr-only"><?php esc_html_e('Поиск по сайту', 'quterma'); ?></label>
      <input type="search" placeholder="<?php esc_attr_e('Поиск…', 'quterma'); ?>" name="s" id="srchInput" autocomplete="off" value="<?php echo get_search_query(); ?>">
      <button type="button" class="srch-x" id="srchClose" aria-label="<?php esc_attr_e('Закрыть поиск', 'quterma'); ?>"><svg viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18" stroke-linecap="round"/><line x1="6" y1="6" x2="18" y2="18" stroke-linecap="round"/></svg></button>
    </form>
  </div>
</div>

<!-- MOBILE OVERLAY & MENU -->
<div class="mob-overlay" id="mobOverlay" tabindex="-1"></div>
<div class="mob-menu" id="mobMenu" aria-hidden="true" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e('Навигация сайта', 'quterma'); ?>">
  <div class="mob-hdr">
    <span class="masthead-logo" style="font-size:20px"><?php bloginfo('name'); ?><span class="masthead-dot"></span></span>
    <button type="button" class="mob-close" id="mobClose" aria-label="<?php esc_attr_e('Закрыть меню', 'quterma'); ?>"><svg viewBox="0 0 24 24" fill="none"><line x1="18" y1="6" x2="6" y2="18" stroke-linecap="round"/><line x1="6" y1="6" x2="18" y2="18" stroke-linecap="round"/></svg></button>
  </div>
  <div class="mob-nav">
    <?php
    if (has_nav_menu('mobile')) {
        wp_nav_menu(array(
            'theme_location' => 'mobile',
            'container'      => false,
            'items_wrap'     => '%3$s',
            'depth'          => 1,
            'walker'         => new Quterma_Mobile_Nav_Walker(),
            'fallback_cb'    => 'quterma_default_mobile_nav',
        ));
    } else {
        quterma_default_mobile_nav();
    }
    ?>
  </div>
</div>

<button type="button" class="scroll-top" id="scrollTopBtn" aria-label="<?php esc_attr_e('Наверх', 'quterma'); ?>">
  <svg viewBox="0 0 24 24" fill="none"><polyline points="18 15 12 9 6 15"/></svg>
</button>

<?php wp_footer(); ?>
</body>
</html>
