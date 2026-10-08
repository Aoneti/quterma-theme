<?php
/**
 * SEO, Open Graph, and Schema.org structured data
 *
 * @package QutermaCore
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('quterma_has_seo_plugin')) {
    function quterma_has_seo_plugin() {
        return (
            defined('WPSEO_VERSION') ||
            defined('RANK_MATH_VERSION') ||
            class_exists('RankMath') ||
            defined('AIOSEO_VERSION') ||
            defined('SEOPRESS_VERSION') ||
            function_exists('the_seo_framework')
        );
    }
}

if (!function_exists('quterma_clean_seo_text')) {
    function quterma_clean_seo_text($text) {
        if (empty($text) || !is_string($text)) {
            return '';
        }
        $clean = str_replace(array('&nbsp;', "\xc2\xa0", '&#160;', '&#xA0;'), ' ', $text);
        $clean = wp_strip_all_tags($clean, true);
        $clean = html_entity_decode($clean, ENT_QUOTES, 'UTF-8');
        $clean = str_replace(array('&nbsp;', "\xc2\xa0", "\u{00A0}"), ' ', $clean);
        return trim(preg_replace('/\s+/u', ' ', $clean));
    }
}

if (!function_exists('quterma_output_seo_meta')) {
    function quterma_output_seo_meta() {
        if (quterma_has_seo_plugin()) {
            return;
        }

        $site_name = quterma_clean_seo_text(get_bloginfo('name'));
        $title     = quterma_clean_seo_text(wp_get_document_title());
        $url       = is_singular() ? get_permalink() : home_url(add_query_arg(array(), $GLOBALS['wp']->request));
        $type      = is_singular('post') ? 'article' : 'website';

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

        $image = '';
        if (is_singular() && has_post_thumbnail()) {
            $image = get_the_post_thumbnail_url(get_the_ID(), 'quterma-hero');
        }
        if (empty($image) && has_custom_logo()) {
            $logo_id = get_theme_mod('custom_logo');
            $image   = wp_get_attachment_image_url($logo_id, 'full');
        }

        echo '<meta name="description" content="' . esc_attr($desc) . '">' . "\n";
        echo '<meta property="og:site_name" content="' . esc_attr($site_name) . '">' . "\n";
        echo '<meta property="og:type" content="' . esc_attr($type) . '">' . "\n";
        echo '<meta property="og:title" content="' . esc_attr($title) . '">' . "\n";
        echo '<meta property="og:description" content="' . esc_attr($desc) . '">' . "\n";
        echo '<meta property="og:url" content="' . esc_url($url) . '">' . "\n";
        if (!empty($image)) {
            echo '<meta property="og:image" content="' . esc_url($image) . '">' . "\n";
        }

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
    function quterma_output_schema_json_ld() {
        if (quterma_has_seo_plugin()) {
            return;
        }

        $schemas = array();
        $home_url = home_url('/');
        $site_name = quterma_clean_seo_text(get_bloginfo('name'));

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

        if (is_singular('post')) {
            $post = get_post();
            $author_name = quterma_clean_seo_text(get_the_author_meta('display_name', $post->post_author));
            $headline    = quterma_clean_seo_text(get_the_title($post));
            $excerpt     = !empty($post->post_excerpt) ? $post->post_excerpt : wp_trim_words($post->post_content, 30);
            $description = quterma_clean_seo_text($excerpt);

            $publisher = array(
                '@type' => 'Organization',
                'name'  => $site_name,
            );
            if (!empty($org_schema['logo'])) {
                $publisher['logo'] = array(
                    '@type' => 'ImageObject',
                    'url'   => $org_schema['logo'],
                );
            }

            $article_schema = array(
                '@context'         => 'https://schema.org',
                '@type'            => 'NewsArticle',
                'mainEntityOfPage' => array(
                    '@type' => 'WebPage',
                    '@id'   => get_permalink($post),
                ),
                'headline'         => $headline,
                'description'      => $description,
                'datePublished'    => get_the_date('c', $post),
                'dateModified'     => get_the_modified_date('c', $post),
                'author'           => array(
                    '@type' => 'Person',
                    'name'  => $author_name,
                ),
                'publisher'        => $publisher,
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
