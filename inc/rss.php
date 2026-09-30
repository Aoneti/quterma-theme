<?php
/**
 * Specialized RSS feeds for Dzen, Dzen News, and Rambler News
 *
 * @package Quterma
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register custom RSS feeds.
 */
function quterma_register_custom_feeds() {
    add_feed('dzen', 'quterma_render_dzen_feed');
    add_feed('dzen-news', 'quterma_render_dzen_news_feed');
    add_feed('rambler', 'quterma_render_rambler_feed');
}
add_action('init', 'quterma_register_custom_feeds');

/**
 * Render RSS feed for Dzen (articles)
 */
function quterma_render_dzen_feed() {
    header('Content-Type: application/rss+xml; charset=' . get_option('blog_charset'), true);
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
    ?>
    <item>
        <title><?php the_title_rss(); ?></title>
        <link><?php the_permalink_rss(); ?></link>
        <guid isPermaLink="true"><?php the_permalink_rss(); ?></guid>
        <pubDate><?php echo mysql2date('D, d M Y H:i:s +0000', get_post_time('Y-m-d H:i:s', true), false); ?></pubDate>
        <dc:creator><?php the_author(); ?></dc:creator>
        <description><![CDATA[<?php echo wp_strip_all_tags(get_the_excerpt()); ?>]]></description>
        <content:encoded><![CDATA[<?php the_content_feed('rss2'); ?>]]></content:encoded>
        <?php if (has_post_thumbnail()) :
            $thumb_id  = get_post_thumbnail_id();
            $thumb_url = wp_get_attachment_image_url($thumb_id, 'full');
            $mime_type = get_post_mime_type($thumb_id);
            if ($thumb_url) :
        ?>
        <enclosure url="<?php echo esc_url($thumb_url); ?>" type="<?php echo esc_attr($mime_type); ?>" />
        <?php endif; endif; ?>
    </item>
    <?php endwhile; wp_reset_postdata(); ?>
</channel>
</rss>
    <?php
    exit;
}

/**
 * Render RSS feed for Dzen News (Yandex News format)
 * Requirements: https://dzen.ru/help/ru/export-content/export.html
 */
function quterma_render_dzen_news_feed() {
    header('Content-Type: application/rss+xml; charset=' . get_option('blog_charset'), true);
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
        $categories = get_the_category();
        $cat_name = !empty($categories) ? $categories[0]->name : 'Город';
    ?>
    <item>
        <title><?php the_title_rss(); ?></title>
        <link><?php the_permalink_rss(); ?></link>
        <pubDate><?php echo mysql2date('D, d M Y H:i:s +0000', get_post_time('Y-m-d H:i:s', true), false); ?></pubDate>
        <category><?php echo esc_html($cat_name); ?></category>
        <yandex:full-text><![CDATA[<?php echo wp_strip_all_tags(get_the_content()); ?>]]></yandex:full-text>
        <description><![CDATA[<?php echo wp_strip_all_tags(get_the_excerpt()); ?>]]></description>
        <?php if (has_post_thumbnail()) :
            $thumb_id  = get_post_thumbnail_id();
            $thumb_url = wp_get_attachment_image_url($thumb_id, 'full');
            $mime_type = get_post_mime_type($thumb_id);
            if ($thumb_url) :
        ?>
        <enclosure url="<?php echo esc_url($thumb_url); ?>" type="<?php echo esc_attr($mime_type); ?>" />
        <?php endif; endif; ?>
    </item>
    <?php endwhile; wp_reset_postdata(); ?>
</channel>
</rss>
    <?php
    exit;
}

/**
 * Render RSS feed for Rambler News
 * Requirements: https://help.rambler.ru/news/novosti-pravila-oformleniya-novostnogo-potoka/4
 */
function quterma_render_rambler_feed() {
    header('Content-Type: application/rss+xml; charset=' . get_option('blog_charset'), true);
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
    ?>
    <item>
        <title><?php the_title_rss(); ?></title>
        <link><?php the_permalink_rss(); ?></link>
        <pubDate><?php echo mysql2date('D, d M Y H:i:s +0000', get_post_time('Y-m-d H:i:s', true), false); ?></pubDate>
        <rambler:fulltext><![CDATA[<?php echo wp_strip_all_tags(get_the_content()); ?>]]></rambler:fulltext>
        <description><![CDATA[<?php echo wp_strip_all_tags(get_the_excerpt()); ?>]]></description>
        <?php if (has_post_thumbnail()) :
            $thumb_id  = get_post_thumbnail_id();
            $thumb_url = wp_get_attachment_image_url($thumb_id, 'full');
            $mime_type = get_post_mime_type($thumb_id);
            if ($thumb_url) :
        ?>
        <enclosure url="<?php echo esc_url($thumb_url); ?>" type="<?php echo esc_attr($mime_type); ?>" />
        <?php endif; endif; ?>
    </item>
    <?php endwhile; wp_reset_postdata(); ?>
</channel>
</rss>
    <?php
    exit;
}
