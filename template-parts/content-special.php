<?php
/**
 * Template part for Longreads and Special Projects cards (Журнальный формат спецпроектов)
 *
 * @package Quterma
 */

if (!defined('ABSPATH')) {
    exit;
}

$cat_info     = quterma_get_post_category_info();
$reading_time = quterma_reading_time(get_the_ID());
$desc         = get_the_excerpt();
if (empty($desc)) {
    $desc = wp_trim_words(get_the_content(), 18, '…');
}

// Dynamic badge determination from child categories and tags
$post_id    = get_the_ID();
$all_cats   = get_the_category($post_id);
$all_tags   = get_the_tags($post_id);
$badge_text = '';

// 1. Check child categories of the post first
if (!empty($all_cats)) {
    foreach ($all_cats as $c) {
        $c_name = function_exists('mb_strtolower') ? mb_strtolower(trim($c->name), 'UTF-8') : strtolower(trim($c->name));
        $c_slug = strtolower(trim(urldecode($c->slug)));

        // Skip generic parent section names
        if (strpos($c_name, 'лонгриды и спецпроекты') !== false || strpos($c_slug, 'longridy-i-spetsproekty') !== false) {
            continue;
        }

        if (strpos($c_name, 'лонгрид') !== false || strpos($c_slug, 'longread') !== false || strpos($c_slug, 'longrid') !== false) {
            $badge_text = __('Лонгрид', 'quterma');
            break;
        } elseif (strpos($c_name, 'спецпроект') !== false || strpos($c_slug, 'special') !== false || strpos($c_slug, 'spets') !== false) {
            $badge_text = __('Спецпроект', 'quterma');
            break;
        } elseif (strpos($c_name, 'репортаж') !== false || strpos($c_slug, 'report') !== false) {
            $badge_text = __('Репортаж', 'quterma');
            break;
        } elseif (strpos($c_name, 'исследован') !== false || strpos($c_slug, 'research') !== false) {
            $badge_text = __('Исследование', 'quterma');
            break;
        } elseif (strpos($c_name, 'фотоистори') !== false || strpos($c_slug, 'photostory') !== false) {
            $badge_text = __('Фотоистория', 'quterma');
            break;
        } elseif ($c->parent > 0) {
            // Any specific child rubric
            $badge_text = $c->name;
            break;
        }
    }
}

// 2. Check tags (метки) if badge not yet resolved
if (empty($badge_text) && !empty($all_tags)) {
    foreach ($all_tags as $t) {
        $t_name = function_exists('mb_strtolower') ? mb_strtolower(trim($t->name), 'UTF-8') : strtolower(trim($t->name));
        $t_slug = strtolower(trim(urldecode($t->slug)));

        if (strpos($t_name, 'лонгрид') !== false || strpos($t_slug, 'longread') !== false || strpos($t_slug, 'longrid') !== false) {
            $badge_text = __('Лонгрид', 'quterma');
            break;
        } elseif (strpos($t_name, 'спецпроект') !== false || strpos($t_slug, 'special') !== false || strpos($t_slug, 'spets') !== false) {
            $badge_text = __('Спецпроект', 'quterma');
            break;
        } elseif (strpos($t_name, 'репортаж') !== false || strpos($t_slug, 'report') !== false) {
            $badge_text = __('Репортаж', 'quterma');
            break;
        } elseif (strpos($t_name, 'исследован') !== false || strpos($t_slug, 'research') !== false) {
            $badge_text = __('Исследование', 'quterma');
            break;
        } elseif (strpos($t_name, 'фотоистори') !== false || strpos($t_slug, 'photostory') !== false) {
            $badge_text = __('Фотоистория', 'quterma');
            break;
        }
    }

    // If still empty but post has tags, use first tag
    if (empty($badge_text) && isset($all_tags[0])) {
        $badge_text = $all_tags[0]->name;
    }
}

// 3. Fallback based on content length
if (empty($badge_text)) {
    $content_words = count(preg_split('/\s+/u', strip_tags(get_the_content()), -1, PREG_SPLIT_NO_EMPTY));
    $badge_text = ($content_words >= 600) ? __('Лонгрид', 'quterma') : __('Спецпроект', 'quterma');
}
?>
<article class="spec-card">
  <div class="spec-media">
    <div class="spec-badge"><?php echo esc_html($badge_text); ?></div>
    <?php if (has_post_thumbnail()) : ?>
      <div class="spec-img-wrap">
        <?php the_post_thumbnail('quterma-card-4x3', array(
            'loading' => 'lazy',
            'sizes'   => '(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 420px',
            'alt'     => '',
        )); ?>
      </div>
    <?php else : ?>
      <div class="ph-img spec-img-wrap" style="aspect-ratio:16/9;background:linear-gradient(135deg,#241C16 0%,#18120D 100%)">
        <svg viewBox="0 0 24 24" fill="none" stroke="var(--accent)" stroke-width="1.5" width="44" height="44"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
      </div>
    <?php endif; ?>
  </div>
  <div class="spec-body">
    <div class="spec-meta-top">
      <span class="spec-cat"><?php echo esc_html($cat_info['name']); ?></span>
      <span class="spec-dot">·</span>
      <span class="spec-time"><?php echo esc_html($reading_time); ?></span>
    </div>
    <h3 class="spec-title">
      <a href="<?php the_permalink(); ?>" class="card-permalink"><?php the_title(); ?></a>
    </h3>
    <p class="spec-desc"><?php echo esc_html($desc); ?></p>
    <div class="spec-meta-bottom">
      <span><?php echo quterma_time_tag(get_the_ID(), false); ?></span>
      <span class="spec-cta"><?php esc_html_e('Читать материал →', 'quterma'); ?></span>
    </div>
  </div>
</article>
