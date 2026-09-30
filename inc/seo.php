<?php
/**
 * SEO, Open Graph, and Schema.org structured data (NewsArticle, Breadcrumbs, WebSite)
 *
 * @package Quterma
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Check if a dedicated SEO plugin is active.
 *
 * @return bool
 */
function quterma_has_seo_plugin() {
    return (
        defined('WPSEO_VERSION') ||           // Yoast SEO
        class_exists('RankMath') ||           // Rank Math
        defined('AIOSEO_VERSION') ||          // All in One SEO
        defined('SEOPRESS_VERSION') ||        // SEOPress
        function_exists('the_seo_framework')  // The SEO Framework
    );
}

/**
 * Output SEO meta tags and Open Graph if no SEO plugin is active.
 */
function quterma_output_seo_meta() {
    if (quterma_has_seo_plugin()) {
        return;
    }

    $site_name = get_bloginfo('name');
    $title     = wp_get_document_title();
    $url       = is_singular() ? get_permalink() : home_url(add_query_arg(array(), $GLOBALS['wp']->request));
    $type      = is_singular('post') ? 'article' : 'website';

    // Description
    $desc = get_bloginfo('description');
    if (is_singular()) {
        $post = get_post();
        if (!empty($post->post_excerpt)) {
            $desc = wp_strip_all_tags($post->post_excerpt);
        } else {
            $desc = wp_trim_words(wp_strip_all_tags($post->post_content), 30, '…');
        }
    } elseif (is_category() || is_tag() || is_tax()) {
        $term_desc = term_description();
        if (!empty($term_desc)) {
            $desc = wp_strip_all_tags($term_desc);
        }
    }

    // Image
    $image = '';
    if (is_singular() && has_post_thumbnail()) {
        $image = get_the_post_thumbnail_url(get_the_ID(), 'quterma-hero');
    }
    if (empty($image) && has_custom_logo()) {
        $logo_id = get_theme_mod('custom_logo');
        $image   = wp_get_attachment_image_url($logo_id, 'full');
    }

    // Standard meta
    echo '<meta name="description" content="' . esc_attr($desc) . '">' . "\n";

    // Open Graph
    echo '<meta property="og:site_name" content="' . esc_attr($site_name) . '">' . "\n";
    echo '<meta property="og:type" content="' . esc_attr($type) . '">' . "\n";
    echo '<meta property="og:title" content="' . esc_attr($title) . '">' . "\n";
    echo '<meta property="og:description" content="' . esc_attr($desc) . '">' . "\n";
    echo '<meta property="og:url" content="' . esc_url($url) . '">' . "\n";
    if (!empty($image)) {
        echo '<meta property="og:image" content="' . esc_url($image) . '">' . "\n";
    }

    // Twitter Card
    echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
    echo '<meta name="twitter:title" content="' . esc_attr($title) . '">' . "\n";
    echo '<meta name="twitter:description" content="' . esc_attr($desc) . '">' . "\n";
    if (!empty($image)) {
        echo '<meta name="twitter:image" content="' . esc_url($image) . '">' . "\n";
    }
}
add_action('wp_head', 'quterma_output_seo_meta', 2);

/**
 * Output Schema.org JSON-LD Structured Data
 * (NewsArticle, WebSite, Organization, BreadcrumbList)
 */
function quterma_output_schema_json_ld() {
    if (quterma_has_seo_plugin()) {
        return;
    }

    $schemas = array();
    $home_url = home_url('/');
    $site_name = get_bloginfo('name');

    // 1. Organization Schema
    $org_schema = array(
        '@context' => 'https://schema.org',
        '@type'    => 'Organization',
        'name'     => $site_name,
        'url'      => $home_url,
    );
    if (has_custom_logo()) {
        $logo_id = get_theme_mod('custom_logo');
        $logo_url = wp_get_attachment_image_url($logo_id, 'full');
        if ($logo_url) {
            $org_schema['logo'] = $logo_url;
        }
    }
    $schemas[] = $org_schema;

    // 2. WebSite with SearchAction
    if (is_front_page()) {
        $schemas[] = array(
            '@context'        => 'https://schema.org',
            '@type'           => 'WebSite',
            'name'            => $site_name,
            'url'             => $home_url,
            'potentialAction' => array(
                '@type'       => 'SearchAction',
                'target'      => home_url('/?s={search_term_string}'),
                'query-input' => 'required name=search_term_string',
            ),
        );
    }

    // 3. NewsArticle for single posts
    if (is_singular('post')) {
        $post = get_post();
        $author_name = get_the_author_meta('display_name', $post->post_author);

        $article_schema = array(
            '@context'         => 'https://schema.org',
            '@type'            => 'NewsArticle',
            'mainEntityOfPage' => array(
                '@type' => 'WebPage',
                '@id'   => get_permalink($post),
            ),
            'headline'         => get_the_title($post),
            'description'      => !empty($post->post_excerpt) ? wp_strip_all_tags($post->post_excerpt) : wp_trim_words(wp_strip_all_tags($post->post_content), 30),
            'datePublished'    => get_the_date('c', $post),
            'dateModified'     => get_the_modified_date('c', $post),
            'author'           => array(
                '@type' => 'Person',
                'name'  => $author_name,
            ),
            'publisher'        => array(
                '@type' => 'Organization',
                'name'  => $site_name,
                'logo'  => !empty($org_schema['logo']) ? array('@type' => 'ImageObject', 'url' => $org_schema['logo']) : null,
            ),
        );

        if (has_post_thumbnail($post)) {
            $thumb_url = get_the_post_thumbnail_url($post, 'full');
            if ($thumb_url) {
                $article_schema['image'] = array(
                    '@type' => 'ImageObject',
                    'url'   => $thumb_url,
                );
            }
        }

        $schemas[] = $article_schema;
    }

    // 4. BreadcrumbList for single and category pages
    if (is_singular('post') || is_category()) {
        $breadcrumbs = array(
            '@context'        => 'https://schema.org',
            '@type'           => 'BreadcrumbList',
            'itemListElement' => array(
                array(
                    '@type'    => 'ListItem',
                    'position' => 1,
                    'name'     => __('Главная', 'quterma'),
                    'item'     => $home_url,
                ),
            ),
        );

        $pos = 2;
        if (is_singular('post')) {
            $cats = get_the_category();
            if (!empty($cats)) {
                $primary_cat = $cats[0];
                $breadcrumbs['itemListElement'][] = array(
                    '@type'    => 'ListItem',
                    'position' => $pos++,
                    'name'     => $primary_cat->name,
                    'item'     => get_category_link($primary_cat),
                );
            }
            $breadcrumbs['itemListElement'][] = array(
                '@type'    => 'ListItem',
                'position' => $pos,
                'name'     => get_the_title(),
                'item'     => get_permalink(),
            );
        } elseif (is_category()) {
            $cat = get_queried_object();
            $breadcrumbs['itemListElement'][] = array(
                '@type'    => 'ListItem',
                'position' => 2,
                'name'     => $cat->name,
                'item'     => get_category_link($cat),
            );
        }

        $schemas[] = $breadcrumbs;
    }

    if (!empty($schemas)) {
        foreach ($schemas as $schema) {
            echo '<script type="application/ld+json">' . wp_json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . '</script>' . "\n";
        }
    }
}
add_action('wp_head', 'quterma_output_schema_json_ld', 5);
