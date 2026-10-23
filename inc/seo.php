<?php
/**
 * SEO, Open Graph, and Schema.org structured data (NewsArticle, Breadcrumbs, WebSite)
 *
 * Implements:
 * - Full compatibility with popular SEO plugins (Yoast, Rank Math, All in One SEO, SEOPress, The SEO Framework)
 * - Clean display without &nbsp; or HTML entities in title, headline, alt, and breadcrumbs
 * - Schema.org compliance without invalid 'logo': null
 * - Secure JSON-LD output using JSON_HEX_TAG preventing script tag breakouts
 *
 * @package Quterma
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('quterma_has_seo_plugin')) {
    /**
     * Check if a dedicated SEO plugin is active.
     *
     * @return bool
     */
    function quterma_has_seo_plugin() {
        return (
            defined('WPSEO_VERSION') ||           // Yoast SEO
            defined('RANK_MATH_VERSION') ||       // Rank Math
            class_exists('RankMath') ||           // Rank Math class
            defined('AIOSEO_VERSION') ||          // All in One SEO
            defined('SEOPRESS_VERSION') ||        // SEOPress
            function_exists('the_seo_framework')  // The SEO Framework
        );
    }
}

if (!function_exists('quterma_clean_seo_text')) {
    /**
     * Clean plain-text string for SEO tags and JSON-LD (removes HTML tags and &nbsp; entities).
     *
     * @param string $text
     * @return string
     */
    function quterma_clean_seo_text($text) {
        if (empty($text) || !is_string($text)) {
            return '';
        }
        // Replace non-breaking spaces with normal spaces
        $clean = str_replace(array('&nbsp;', "\xc2\xa0", '&#160;', '&#xA0;'), ' ', $text);
        $clean = wp_strip_all_tags($clean, true);
        $clean = html_entity_decode($clean, ENT_QUOTES, 'UTF-8');
        $clean = str_replace(array('&nbsp;', "\xc2\xa0", "\u{00A0}"), ' ', $clean);
        return trim(preg_replace('/\s+/u', ' ', $clean));
    }
}

if (!function_exists('quterma_output_seo_meta')) {
    /**
     * Output SEO meta tags and Open Graph if no SEO plugin is active.
     */
    function quterma_output_seo_meta() {
        if (quterma_has_seo_plugin()) {
            return;
        }

        $site_name = quterma_clean_seo_text(get_bloginfo('name'));
        $title     = quterma_clean_seo_text(wp_get_document_title());
        $url       = is_singular() ? get_permalink() : home_url(add_query_arg(array(), $GLOBALS['wp']->request));
        $type      = is_singular('post') ? 'article' : (is_singular('quterma_venue') ? 'restaurant' : 'website');

        // Description
        $desc = get_bloginfo('description');
        if (is_singular()) {
            $post = get_post();
            if (!empty($post->post_excerpt)) {
                $desc = $post->post_excerpt;
            } else {
                $desc = wp_trim_words($post->post_content, 30, '…');
            }
        } elseif (is_category() || is_tag() || is_tax()) {
            $term_desc = term_description();
            if (!empty($term_desc)) {
                $desc = $term_desc;
            }
        }
        $desc = quterma_clean_seo_text($desc);

        // Image
        $image = '';
        if (is_singular() && has_post_thumbnail()) {
            $image = get_the_post_thumbnail_url(get_the_ID(), 'quterma-hero');
        }
        if (empty($image) && has_custom_logo()) {
            $logo_id = get_theme_mod('custom_logo');
            $image   = wp_get_attachment_image_url($logo_id, 'full');
        }

        // Standard meta & Google Discover optimization
        echo '<meta name="description" content="' . esc_attr($desc) . '">' . "\n";
        echo '<meta name="robots" content="max-image-preview:large, max-snippet:-1, max-video-preview:-1">' . "\n";

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
}

if (!function_exists('quterma_output_schema_json_ld')) {
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
        $site_name = quterma_clean_seo_text(get_bloginfo('name'));

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
            $post        = get_post();
            $author_id   = $post->post_author;
            $author_name = quterma_clean_seo_text(get_the_author_meta('display_name', $author_id));
            $author_url  = get_author_posts_url($author_id);
            $headline    = quterma_clean_seo_text(get_the_title($post));
            $excerpt     = !empty($post->post_excerpt) ? $post->post_excerpt : wp_trim_words($post->post_content, 30);
            $description = quterma_clean_seo_text($excerpt);
            $permalink   = get_permalink($post);

            // Publisher logo
            $publisher_logo = !empty($org_schema['logo']) ? $org_schema['logo'] : '';
            if (empty($publisher_logo) && has_custom_logo()) {
                $logo_id = get_theme_mod('custom_logo');
                $publisher_logo = wp_get_attachment_image_url($logo_id, 'full');
            }
            $publisher = array(
                '@type' => 'Organization',
                'name'  => $site_name,
                'url'   => $home_url,
            );
            if (!empty($publisher_logo)) {
                $publisher['logo'] = array(
                    '@type' => 'ImageObject',
                    'url'   => $publisher_logo,
                );
            }

            // Category & Article Section
            $primary_cat = function_exists('quterma_get_primary_category') ? quterma_get_primary_category($post) : null;
            if (!$primary_cat) {
                $post_cats = get_the_category($post->ID);
                $primary_cat = !empty($post_cats) ? $post_cats[0] : null;
            }
            $article_section = ($primary_cat && !empty($primary_cat->name)) ? quterma_clean_seo_text($primary_cat->name) : 'Новости';

            // Word count
            $clean_body = wp_strip_all_tags($post->post_content);
            $words_arr = preg_split('/\s+/u', $clean_body, -1, PREG_SPLIT_NO_EMPTY);
            $word_count = is_array($words_arr) ? count($words_arr) : 0;

            // Check for LiveBlogPosting (tags or meta)
            $is_liveblog = false;
            if (get_post_meta($post->ID, '_is_liveblog', true) || get_post_meta($post->ID, '_quterma_liveblog', true)) {
                $is_liveblog = true;
            }
            $post_tags = get_the_tags($post->ID);
            if (!$is_liveblog && !empty($post_tags) && is_array($post_tags)) {
                foreach ($post_tags as $pt) {
                    $pt_lower = function_exists('mb_strtolower') ? mb_strtolower(trim($pt->name), 'UTF-8') : strtolower(trim($pt->name));
                    if (in_array($pt_lower, array('live', 'онлайн', 'онлайн-трансляция', 'прямой эфир', 'прямой-эфир', 'хроника'))) {
                        $is_liveblog = true;
                        break;
                    }
                }
            }

            $schema_type = $is_liveblog ? 'LiveBlogPosting' : 'NewsArticle';

            $article_schema = array(
                '@context'            => 'https://schema.org',
                '@type'               => $schema_type,
                'mainEntityOfPage'    => array(
                    '@type' => 'WebPage',
                    '@id'   => $permalink,
                ),
                'url'                 => $permalink,
                'headline'            => $headline,
                'description'         => $description,
                'articleSection'      => $article_section,
                'inLanguage'          => 'ru',
                'isAccessibleForFree' => true,
                'datePublished'       => get_the_date('c', $post),
                'dateModified'        => get_the_modified_date('c', $post),
                'author'              => array(
                    '@type' => 'Person',
                    'name'  => $author_name,
                    'url'   => $author_url,
                ),
                'publisher'           => $publisher,
            );

            if ($is_liveblog) {
                $article_schema['coverageStartTime'] = get_the_date('c', $post);
                $article_schema['coverageEndTime']   = get_the_modified_date('c', $post);
                $article_schema['liveBlogUpdate']    = array(
                    array(
                        '@type'         => 'BlogPosting',
                        'headline'      => $headline,
                        'datePublished' => get_the_modified_date('c', $post),
                        'dateModified'  => get_the_modified_date('c', $post),
                        'articleBody'   => $description,
                    ),
                );
            }

            if ($word_count > 0) {
                $article_schema['wordCount'] = $word_count;
            }

            // Tags / Keywords
            $post_tags = get_the_tags($post->ID);
            if (!empty($post_tags) && is_array($post_tags)) {
                $tag_names = array();
                foreach ($post_tags as $pt) {
                    $cleaned_tag = quterma_clean_seo_text($pt->name);
                    if (!empty($cleaned_tag)) {
                        $tag_names[] = $cleaned_tag;
                    }
                }
                if (!empty($tag_names)) {
                    $article_schema['keywords'] = implode(', ', $tag_names);
                }
            }

            // Image with dimensions
            if (has_post_thumbnail($post)) {
                $thumb_id  = get_post_thumbnail_id($post);
                $thumb_src = wp_get_attachment_image_src($thumb_id, 'full');
                if ($thumb_src && !empty($thumb_src[0])) {
                    $img_obj = array(
                        '@type'  => 'ImageObject',
                        'url'    => $thumb_src[0],
                    );
                    if (!empty($thumb_src[1])) {
                        $img_obj['width'] = (int) $thumb_src[1];
                    }
                    if (!empty($thumb_src[2])) {
                        $img_obj['height'] = (int) $thumb_src[2];
                    }
                    $article_schema['image'] = $img_obj;
                }
            }

            $schemas[] = $article_schema;
        }

        // 4. Restaurant / FoodEstablishment Schema for Gastroguide venues
        if (is_singular('quterma_venue')) {
            $post = get_post();
            $venue_city    = get_post_meta($post->ID, '_venue_city', true);
            $city_name     = function_exists('quterma_get_city_name') ? quterma_get_city_name($venue_city) : $venue_city;
            $venue_addr    = get_post_meta($post->ID, '_venue_address', true);
            $venue_price   = get_post_meta($post->ID, '_venue_price', true);
            $venue_phone   = get_post_meta($post->ID, '_venue_phone', true);
            $venue_website = get_post_meta($post->ID, '_venue_website', true);
            $venue_hours   = get_post_meta($post->ID, '_venue_hours', true);
            $venue_type    = get_post_meta($post->ID, '_venue_type', true);

            $venue_desc = !empty($post->post_excerpt) ? $post->post_excerpt : wp_trim_words($post->post_content, 30);

            $restaurant_schema = array(
                '@context' => 'https://schema.org',
                '@type'    => 'Restaurant',
                '@id'      => get_permalink($post),
                'name'     => quterma_clean_seo_text(get_the_title($post)),
                'url'      => !empty($venue_website) ? esc_url($venue_website) : get_permalink($post),
            );

            if (!empty($venue_desc)) {
                $restaurant_schema['description'] = quterma_clean_seo_text($venue_desc);
            }
            if (!empty($venue_price)) {
                $restaurant_schema['priceRange'] = esc_html($venue_price);
            }
            if (!empty($venue_type)) {
                $restaurant_schema['servesCuisine'] = quterma_clean_seo_text($venue_type);
            }
            if (!empty($venue_phone)) {
                $restaurant_schema['telephone'] = esc_html($venue_phone);
            }
            if (!empty($venue_hours)) {
                $restaurant_schema['openingHours'] = esc_html($venue_hours);
            }

            if (!empty($venue_addr) || !empty($city_name)) {
                $address_obj = array(
                    '@type'          => 'PostalAddress',
                    'addressRegion'  => 'Ярославская область',
                    'addressCountry' => 'RU',
                );
                if (!empty($venue_addr)) {
                    $address_obj['streetAddress'] = quterma_clean_seo_text($venue_addr);
                }
                if (!empty($city_name)) {
                    $address_obj['addressLocality'] = quterma_clean_seo_text($city_name);
                }
                $restaurant_schema['address'] = $address_obj;
            }

            if (has_post_thumbnail($post)) {
                $thumb_url = get_the_post_thumbnail_url($post, 'full');
                if ($thumb_url) {
                    $restaurant_schema['image'] = array(
                        '@type' => 'ImageObject',
                        'url'   => $thumb_url,
                    );
                }
            }

            $schemas[] = $restaurant_schema;
        }

        // 5. BreadcrumbList for single posts, venues, category and city taxonomy pages
        if (is_singular('post') || is_category() || is_singular('quterma_venue') || is_tax('quterma_city')) {
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
                $primary_cat = function_exists('quterma_get_primary_category') ? quterma_get_primary_category() : null;
                if ($primary_cat) {
                    $breadcrumbs['itemListElement'][] = array(
                        '@type'    => 'ListItem',
                        'position' => $pos++,
                        'name'     => quterma_clean_seo_text($primary_cat->name),
                        'item'     => get_category_link($primary_cat),
                    );
                }
                $breadcrumbs['itemListElement'][] = array(
                    '@type'    => 'ListItem',
                    'position' => $pos,
                    'name'     => quterma_clean_seo_text(get_the_title()),
                    'item'     => get_permalink(),
                );
            } elseif (is_singular('quterma_venue')) {
                $gastro_url = function_exists('quterma_get_page_url') ? quterma_get_page_url('gastroguide', home_url('/gastroguide/')) : home_url('/gastroguide/');
                $breadcrumbs['itemListElement'][] = array(
                    '@type'    => 'ListItem',
                    'position' => $pos++,
                    'name'     => __('Гастрогид', 'quterma'),
                    'item'     => $gastro_url,
                );
                $venue_city = get_post_meta(get_the_ID(), '_venue_city', true);
                if (!empty($venue_city)) {
                    $city_term = get_term_by('slug', $venue_city, 'quterma_city');
                    if ($city_term && !is_wp_error($city_term)) {
                        $breadcrumbs['itemListElement'][] = array(
                            '@type'    => 'ListItem',
                            'position' => $pos++,
                            'name'     => quterma_clean_seo_text($city_term->name),
                            'item'     => get_term_link($city_term),
                        );
                    }
                }
                $breadcrumbs['itemListElement'][] = array(
                    '@type'    => 'ListItem',
                    'position' => $pos,
                    'name'     => quterma_clean_seo_text(get_the_title()),
                    'item'     => get_permalink(),
                );
            } elseif (is_tax('quterma_city')) {
                $gastro_url = function_exists('quterma_get_page_url') ? quterma_get_page_url('gastroguide', home_url('/gastroguide/')) : home_url('/gastroguide/');
                $breadcrumbs['itemListElement'][] = array(
                    '@type'    => 'ListItem',
                    'position' => 2,
                    'name'     => __('Гастрогид', 'quterma'),
                    'item'     => $gastro_url,
                );
                $city_term = get_queried_object();
                if ($city_term && !empty($city_term->name)) {
                    $breadcrumbs['itemListElement'][] = array(
                        '@type'    => 'ListItem',
                        'position' => 3,
                        'name'     => quterma_clean_seo_text($city_term->name),
                        'item'     => get_term_link($city_term),
                    );
                }
            } elseif (is_category()) {
                $cat = get_queried_object();
                if ($cat && !empty($cat->name)) {
                    $breadcrumbs['itemListElement'][] = array(
                        '@type'    => 'ListItem',
                        'position' => 2,
                        'name'     => quterma_clean_seo_text($cat->name),
                        'item'     => get_category_link($cat),
                    );
                }
            }

            $schemas[] = $breadcrumbs;
        }

        if (!empty($schemas)) {
            $json_flags = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_PRETTY_PRINT;
            foreach ($schemas as $schema) {
                echo '<script type="application/ld+json">' . wp_json_encode($schema, $json_flags) . '</script>' . "\n";
            }
        }
    }
    add_action('wp_head', 'quterma_output_schema_json_ld', 5);
}

/**
 * Google News XML Sitemap generator & rewrite rules.
 */
if (!function_exists('quterma_register_news_sitemap')) {
    function quterma_register_news_sitemap() {
        add_feed('news-sitemap', 'quterma_render_news_sitemap');
        add_rewrite_rule('^news-sitemap\.xml$', 'index.php?feed=news-sitemap', 'top');
        add_rewrite_rule('^sitemap-news\.xml$', 'index.php?feed=news-sitemap', 'top');
    }
    add_action('init', 'quterma_register_news_sitemap', 10);
}

if (!function_exists('quterma_render_news_sitemap')) {
    function quterma_render_news_sitemap() {
        header('Content-Type: application/xml; charset=UTF-8');
        header('X-Robots-Tag: noindex, follow', true);

        $site_name = quterma_clean_seo_text(get_bloginfo('name'));
        $lang      = 'ru';

        // Query posts published in the last 48 hours (Google News standard requirement),
        // with fallback to the last 50 published posts if the site is in testing or low frequency.
        $two_days_ago = date('Y-m-d H:i:s', strtotime('-48 hours'));
        $news_posts = get_posts(array(
            'post_type'      => 'post',
            'post_status'    => 'publish',
            'posts_per_page' => 100,
            'date_query'     => array(
                array(
                    'after'     => $two_days_ago,
                    'inclusive' => true,
                ),
            ),
            'orderby'        => 'date',
            'order'          => 'DESC',
            'no_found_rows'  => true,
        ));

        if (empty($news_posts)) {
            $news_posts = get_posts(array(
                'post_type'      => 'post',
                'post_status'    => 'publish',
                'posts_per_page' => 50,
                'orderby'        => 'date',
                'order'          => 'DESC',
                'no_found_rows'  => true,
            ));
        }

        echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:news="http://www.google.com/schemas/sitemap-news/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">' . "\n";

        foreach ($news_posts as $post) {
            $loc      = get_permalink($post);
            $title    = quterma_clean_seo_text(get_the_title($post));
            $pub_date = get_the_date('c', $post);

            echo '  <url>' . "\n";
            echo '    <loc>' . esc_url($loc) . '</loc>' . "\n";
            echo '    <news:news>' . "\n";
            echo '      <news:publication>' . "\n";
            echo '        <news:name>' . esc_html($site_name) . '</news:name>' . "\n";
            echo '        <news:language>' . esc_html($lang) . '</news:language>' . "\n";
            echo '      </news:publication>' . "\n";
            echo '      <news:publication_date>' . esc_html($pub_date) . '</news:publication_date>' . "\n";
            echo '      <news:title>' . esc_html($title) . '</news:title>' . "\n";
            echo '    </news:news>' . "\n";

            if (has_post_thumbnail($post)) {
                $img_url = get_the_post_thumbnail_url($post, 'full');
                if ($img_url) {
                    echo '    <image:image>' . "\n";
                    echo '      <image:loc>' . esc_url($img_url) . '</image:loc>' . "\n";
                    echo '      <image:title>' . esc_html($title) . '</image:title>' . "\n";
                    echo '    </image:image>' . "\n";
                }
            }
            echo '  </url>' . "\n";
        }

        echo '</urlset>' . "\n";
        exit;
    }
}

/**
 * Dynamic robots.txt support for Google News & standard sitemaps.
 */
if (!function_exists('quterma_filter_robots_txt')) {
    function quterma_filter_robots_txt($output, $public) {
        $sitemap_url      = home_url('/sitemap.xml');
        $news_sitemap_url = home_url('/news-sitemap.xml');

        $lines = array();
        $lines[] = 'User-agent: *';
        $lines[] = 'Disallow: /wp-admin/';
        $lines[] = 'Allow: /wp-admin/admin-ajax.php';
        $lines[] = '';
        $lines[] = 'Sitemap: ' . $sitemap_url;
        $lines[] = 'Sitemap: ' . $news_sitemap_url;

        return implode("\n", $lines) . "\n";
    }
    add_filter('robots_txt', 'quterma_filter_robots_txt', 20, 2);
}

