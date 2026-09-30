<?php
/**
 * Analytics, Webmaster Verification, and Metric Counters
 *
 * @package Quterma
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Output webmaster verification tags in <head>.
 */
function quterma_output_verification_tags() {
    $yandex_webmaster = get_theme_mod('quterma_yandex_verification', '');
    $google_search    = get_theme_mod('quterma_google_verification', '');

    if (!empty($yandex_webmaster)) {
        echo '<meta name="yandex-verification" content="' . esc_attr($yandex_webmaster) . '">' . "\n";
    }
    if (!empty($google_search)) {
        echo '<meta name="google-site-verification" content="' . esc_attr($google_search) . '">' . "\n";
    }
}
add_action('wp_head', 'quterma_output_verification_tags', 1);

/**
 * Output analytics tracking scripts in <head>.
 */
function quterma_output_analytics_head() {
    // Only on frontend, not in admin or previews
    if (is_admin() || is_preview()) {
        return;
    }

    // 1. Google Analytics 4 (GA4)
    $ga4_id = get_theme_mod('quterma_ga4_id', '');
    if (!empty($ga4_id)) {
        $ga4_id = preg_replace('/[^A-Za-z0-9\-]/', '', $ga4_id);
        ?>
        <!-- Google tag (gtag.js) -->
        <script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo esc_attr($ga4_id); ?>"></script>
        <script>
          window.dataLayer = window.dataLayer || [];
          function gtag(){dataLayer.push(arguments);}
          gtag('js', new Date());
          gtag('config', '<?php echo esc_attr($ga4_id); ?>');
        </script>
        <?php
    }

    // 2. Yandex Metrika
    $ym_id = get_theme_mod('quterma_ym_id', '');
    $ym_webvisor = get_theme_mod('quterma_ym_webvisor', true);
    if (!empty($ym_id)) {
        $ym_id = (int) $ym_id;
        ?>
        <!-- Yandex.Metrika counter -->
        <script type="text/javascript">
           (function(m,e,t,r,i,k,a){m[i]=m[i]||function(){(m[i].a=m[i].a||[]).push(arguments)};
           m[i].l=1*new Date();
           for (var j = 0; j < document.scripts.length; j++) {if (document.scripts[j].src === r) { return; }}
           k=e.createElement(t),a=e.getElementsByTagName(t)[0],k.async=1,k.src=r,a.parentNode.insertBefore(k,a)})
           (window, document, "script", "https://mc.yandex.ru/metrika/tag.js", "ym");

           ym(<?php echo esc_js($ym_id); ?>, "init", {
                clickmap:true,
                trackLinks:true,
                accurateTrackBounce:true,
                webvisor:<?php echo $ym_webvisor ? 'true' : 'false'; ?>
           });
        </script>
        <noscript><div><img src="https://mc.yandex.ru/watch/<?php echo esc_attr($ym_id); ?>" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
        <!-- /Yandex.Metrika counter -->
        <?php
    }
}
add_action('wp_head', 'quterma_output_analytics_head', 99);

/**
 * Output footer counters (e.g. LiveInternet) if enabled.
 */
function quterma_output_analytics_footer() {
    if (is_admin() || is_preview()) {
        return;
    }

    $li_enabled = get_theme_mod('quterma_liveinternet_enabled', false);
    if ($li_enabled) {
        ?>
        <!-- LiveInternet counter -->
        <script>
        new Image().src = "//counter.yadro.ru/hit?r"+
        escape(document.referrer)+((typeof(screen)=="undefined")?"":
        ";s"+screen.width+"*"+screen.height+"*"+(screen.colorDepth?
        screen.colorDepth:screen.pixelDepth))+";u"+escape(document.URL)+
        ";h"+escape(document.title.substring(0,150))+
        ";"+Math.random();
        </script>
        <!-- /LiveInternet -->
        <?php
    }
}
add_action('wp_footer', 'quterma_output_analytics_footer', 99);
