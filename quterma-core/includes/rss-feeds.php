<?php
/**
 * Specialized RSS feeds for Dzen, Dzen News, and Rambler News
 *
 * @package QutermaCore
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!defined('QUTERMA_CORE_RSS_CACHE_PREFIX')) {
    define('QUTERMA_CORE_RSS_CACHE_PREFIX', 'quterma_rss_feed_');
}
if (!defined('QUTERMA_CORE_RSS_CACHE_TTL')) {
    define('QUTERMA_CORE_RSS_CACHE_TTL', 300); // 5 minutes
}

if (!function_exists('quterma_core_register_custom_feeds')) {
    function quterma_core_register_custom_feeds() {
        add_feed('dzen', 'quterma_render_dzen_feed');
        add_feed('dzen-news', 'quterma_render_dzen_news_feed');
        add_feed('rambler', 'quterma_render_rambler_feed');
    }
    add_action('init', 'quterma_core_register_custom_feeds');
}

if (!function_exists('quterma_safe_cdata')) {
    function quterma_safe_cdata($text) {
        if (empty($text) || !is_string($text)) {
            return '';
        }
        // Remove invalid XML control characters (ASCII 0-8, 11-12, 14-31)
        $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', $text);
        return str_replace(']]>', ']]]]><![CDATA[>', $text);
    }
}

if (!function_exists('quterma_get_enclosure_length')) {
    function quterma_get_enclosure_length($attachment_id) {
        if (!$attachment_id) {
            return 150000;
        }
        $file_path = get_attached_file($attachment_id);
        if ($file_path && file_exists($file_path)) {
            $size = filesize($file_path);
            if ($size > 0) {
                return (int) $size;
            }
        }
        return 150000;
    }
}

if (!function_exists('quterma_serve_cached_feed')) {
    function quterma_serve_cached_feed($feed_name, $generator_callback) {
        $cache_key = QUTERMA_CORE_RSS_CACHE_PREFIX . $feed_name;
        $cached = get_transient($cache_key);

        if (false === $cached || !is_array($cached) || empty($cached['xml'])) {
            ob_start();
            call_user_func($generator_callback);
            $xml = ob_get_clean();

            $etag = md5($xml);
            $last_modified = time();

            $cached = array(
                'xml'           => $xml,
                'etag'          => $etag,
                'last_modified' => $last_modified,
            );

            set_transient($cache_key, $cached, QUTERMA_CORE_RSS_CACHE_TTL);
        } else {
            $xml           = $cached['xml'];
            $etag          = $cached['etag'];
            $last_modified = $cached['last_modified'];
        }

        $gmt_mtime = gmdate('D, d M Y H:i:s', $last_modified) . ' GMT';

        if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && trim($_SERVER['HTTP_IF_NONE_MATCH'], '"') === $etag) {
            status_header(304);
            exit;
        }

        if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) >= $last_modified) {
            status_header(304);
            exit;
        }

        header('Content-Type: application/rss+xml; charset=' . get_option('blog_charset'), true);
        header('ETag: "' . $etag . '"');
        header('Last-Modified: ' . $gmt_mtime);
        header('Cache-Control: public, max-age=300');

        echo $xml;
        exit;
    }
}

if (!function_exists('quterma_core_flush_rss_cache')) {
    function quterma_core_flush_rss_cache() {
        delete_transient(QUTERMA_CORE_RSS_CACHE_PREFIX . 'dzen');
        delete_transient(QUTERMA_CORE_RSS_CACHE_PREFIX . 'dzen-news');
        delete_transient(QUTERMA_CORE_RSS_CACHE_PREFIX . 'rambler');
    }
    add_action('save_post', 'quterma_core_flush_rss_cache');
    add_action('deleted_post', 'quterma_core_flush_rss_cache');
    add_action('transition_post_status', function ($new_status, $old_status) {
        if ($new_status !== $old_status) {
            quterma_core_flush_rss_cache();
        }
    }, 10, 2);
}

if (!function_exists('quterma_render_dzen_feed')) {
    function quterma_render_dzen_feed() {
        quterma_serve_cached_feed('dzen', function () {
            echo '<?xml version="1.0" encoding="' . esc_attr(get_option('blog_charset')) . '"?>' . "\n";
            ?>
<rss version="2.0"
    xmlns:content="http://purl.org/rss/1.0/modules/content/"
    xmlns:dc="http://purl.org/dc/elements/1.1/"
    xmlns:atom="http://www.w3.org/2005/Atom">
<channel>
    <title><?php bloginfo_rss('name'); ?> — Дзен</title>
    <link><?php bloginfo_rss('url'); ?></link>
    <description><?php bloginfo_rss('description'); ?></description>
    <language>ru</language>
    <atom:link href="<?php echo esc_url(get_feed_link('dzen')); ?>" rel="self" type="application/rss+xml" />
    <?php
    $query = new WP_Query(array(
        'post_type'      => 'post',
        'post_status'    => 'publish',
        'posts_per_page' => 20,
        'no_found_rows'  => true,
    ));
    while ($query->have_posts()) : $query->the_post();
        $excerpt_raw = wp_strip_all_tags(get_the_excerpt());
        $content_raw = get_the_content_feed('rss2');
    ?>
    <item>
        <title><?php the_title_rss(); ?></title>
        <link><?php the_permalink_rss(); ?></link>
        <guid isPermaLink="true"><?php the_permalink_rss(); ?></guid>
        <pubDate><?php echo mysql2date('D, d M Y H:i:s +0000', get_post_time('Y-m-d H:i:s', true), false); ?></pubDate>
        <dc:creator><?php the_author(); ?></dc:creator>
        <description><![CDATA[<?php echo quterma_safe_cdata($excerpt_raw); ?>]]></description>
        <content:encoded><![CDATA[<?php echo quterma_safe_cdata($content_raw); ?>]]></content:encoded>
        <?php if (has_post_thumbnail()) :
            $thumb_id   = get_post_thumbnail_id();
            $thumb_url  = wp_get_attachment_image_url($thumb_id, 'full');
            $mime_type  = get_post_mime_type($thumb_id) ?: 'image/jpeg';
            $enc_length = quterma_get_enclosure_length($thumb_id);
            if ($thumb_url) :
        ?>
        <enclosure url="<?php echo esc_url($thumb_url); ?>" type="<?php echo esc_attr($mime_type); ?>" length="<?php echo esc_attr($enc_length); ?>" />
        <?php endif; endif; ?>
    </item>
    <?php endwhile; wp_reset_postdata(); ?>
</channel>
</rss>
            <?php
        });
    }
}

if (!function_exists('quterma_render_dzen_news_feed')) {
    function quterma_render_dzen_news_feed() {
        quterma_serve_cached_feed('dzen-news', function () {
            echo '<?xml version="1.0" encoding="' . esc_attr(get_option('blog_charset')) . '"?>' . "\n";
            ?>
<rss version="2.0"
    xmlns:yandex="http://news.yandex.ru"
    xmlns:media="http://search.yahoo.com/mrss/"
    xmlns:atom="http://www.w3.org/2005/Atom">
<channel>
    <title><?php bloginfo_rss('name'); ?> — Новости</title>
    <link><?php bloginfo_rss('url'); ?></link>
    <description><?php bloginfo_rss('description'); ?></description>
    <language>ru</language>
    <atom:link href="<?php echo esc_url(get_feed_link('dzen-news')); ?>" rel="self" type="application/rss+xml" />
    <?php
    $query = new WP_Query(array(
        'post_type'      => 'post',
        'post_status'    => 'publish',
        'posts_per_page' => 30,
        'no_found_rows'  => true,
    ));
    while ($query->have_posts()) : $query->the_post();
        $categories  = get_the_category();
        $cat_name    = !empty($categories) ? $categories[0]->name : 'Город';
        $fulltext    = wp_strip_all_tags(strip_shortcodes(get_the_content()));
        $excerpt_raw = wp_strip_all_tags(get_the_excerpt());
    ?>
    <item>
        <title><?php the_title_rss(); ?></title>
        <link><?php the_permalink_rss(); ?></link>
        <pubDate><?php echo mysql2date('D, d M Y H:i:s +0000', get_post_time('Y-m-d H:i:s', true), false); ?></pubDate>
        <category><?php echo esc_html($cat_name); ?></category>
        <yandex:full-text><![CDATA[<?php echo quterma_safe_cdata($fulltext); ?>]]></yandex:full-text>
        <description><![CDATA[<?php echo quterma_safe_cdata($excerpt_raw); ?>]]></description>
        <?php if (has_post_thumbnail()) :
            $thumb_id   = get_post_thumbnail_id();
            $thumb_url  = wp_get_attachment_image_url($thumb_id, 'full');
            $mime_type  = get_post_mime_type($thumb_id) ?: 'image/jpeg';
            $enc_length = quterma_get_enclosure_length($thumb_id);
            if ($thumb_url) :
        ?>
        <enclosure url="<?php echo esc_url($thumb_url); ?>" type="<?php echo esc_attr($mime_type); ?>" length="<?php echo esc_attr($enc_length); ?>" />
        <?php endif; endif; ?>
    </item>
    <?php endwhile; wp_reset_postdata(); ?>
</channel>
</rss>
            <?php
        });
    }
}

if (!function_exists('quterma_render_rambler_feed')) {
    function quterma_render_rambler_feed() {
        quterma_serve_cached_feed('rambler', function () {
            echo '<?xml version="1.0" encoding="' . esc_attr(get_option('blog_charset')) . '"?>' . "\n";
            ?>
<rss version="2.0"
    xmlns:rambler="http://news.rambler.ru"
    xmlns:atom="http://www.w3.org/2005/Atom">
<channel>
    <title><?php bloginfo_rss('name'); ?> — Рамблер Новости</title>
    <link><?php bloginfo_rss('url'); ?></link>
    <description><?php bloginfo_rss('description'); ?></description>
    <language>ru</language>
    <atom:link href="<?php echo esc_url(get_feed_link('rambler')); ?>" rel="self" type="application/rss+xml" />
    <?php
    $query = new WP_Query(array(
        'post_type'      => 'post',
        'post_status'    => 'publish',
        'posts_per_page' => 25,
        'no_found_rows'  => true,
    ));
    while ($query->have_posts()) : $query->the_post();
        $fulltext    = wp_strip_all_tags(strip_shortcodes(get_the_content()));
        $excerpt_raw = wp_strip_all_tags(get_the_excerpt());
    ?>
    <item>
        <title><?php the_title_rss(); ?></title>
        <link><?php the_permalink_rss(); ?></link>
        <pubDate><?php echo mysql2date('D, d M Y H:i:s +0000', get_post_time('Y-m-d H:i:s', true), false); ?></pubDate>
        <rambler:fulltext><![CDATA[<?php echo quterma_safe_cdata($fulltext); ?>]]></rambler:fulltext>
        <description><![CDATA[<?php echo quterma_safe_cdata($excerpt_raw); ?>]]></description>
        <?php if (has_post_thumbnail()) :
            $thumb_id   = get_post_thumbnail_id();
            $thumb_url  = wp_get_attachment_image_url($thumb_id, 'full');
            $mime_type  = get_post_mime_type($thumb_id) ?: 'image/jpeg';
            $enc_length = quterma_get_enclosure_length($thumb_id);
            if ($thumb_url) :
        ?>
        <enclosure url="<?php echo esc_url($thumb_url); ?>" type="<?php echo esc_attr($mime_type); ?>" length="<?php echo esc_attr($enc_length); ?>" />
        <?php endif; endif; ?>
    </item>
    <?php endwhile; wp_reset_postdata(); ?>
</channel>
</rss>
            <?php
        });
    }
}
