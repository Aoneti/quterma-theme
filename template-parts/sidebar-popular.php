<?php
/**
 * Template part for displaying "Читают сейчас" popular materials with period switcher
 *
 * @package Quterma
 */

if (!defined('ABSPATH')) {
    exit;
}

$periods = array(
    'today'     => __('Сегодня', 'quterma'),
    'yesterday' => __('Вчера', 'quterma'),
    'week'      => __('Неделя', 'quterma'),
    'month'     => __('Месяц', 'quterma'),
);

$period_posts = array();
$has_any_posts = false;
foreach (array_keys($periods) as $pkey) {
    $popular_items = quterma_get_popular_posts_by_period($pkey, 5);
    $period_posts[$pkey] = $popular_items;
    if (!empty($popular_items)) {
        $has_any_posts = true;
    }
}

// Global fallback if database has posts but specific periods returned empty
$all_time_popular = quterma_get_popular_posts(5);
if (!empty($all_time_popular)) {
    $has_any_posts = true;
    // Ensure at least 'today' or 'week' has posts if fresh install
    if (empty($period_posts['today']) && empty($period_posts['week'])) {
        $period_posts['today'] = $all_time_popular;
    }
}

if (!$has_any_posts) {
    return;
}
?>
<div>
  <h2 class="sb-title"><?php esc_html_e('Читают сейчас', 'quterma'); ?></h2>

  <div class="top-period-nav" role="tablist" aria-label="<?php esc_attr_e('Период популярного', 'quterma'); ?>">
    <?php
    $first = true;
    foreach ($periods as $pkey => $plabel) :
        $active_cls = $first ? ' active' : '';
        $is_selected = $first ? 'true' : 'false';
        $tabindex = $first ? '0' : '-1';
        $first = false;
    ?>
      <button type="button" class="top-period-btn<?php echo esc_attr($active_cls); ?>" id="tab-top-<?php echo esc_attr($pkey); ?>" data-period="<?php echo esc_attr($pkey); ?>" role="tab" aria-selected="<?php echo $is_selected; ?>" aria-controls="panel-top-<?php echo esc_attr($pkey); ?>" tabindex="<?php echo $tabindex; ?>">
        <?php echo esc_html($plabel); ?>
      </button>
    <?php endforeach; ?>
  </div>

  <?php
  $first_list = true;
  foreach ($periods as $pkey => $plabel) :
      $display = $first_list ? 'flex' : 'none';
      $is_hidden = !$first_list;
      $first_list = false;
      $current_list = !empty($period_posts[$pkey]) ? $period_posts[$pkey] : array();
  ?>
    <div class="top-list" id="panel-top-<?php echo esc_attr($pkey); ?>" role="tabpanel" aria-labelledby="tab-top-<?php echo esc_attr($pkey); ?>" data-period="<?php echo esc_attr($pkey); ?>" tabindex="0" style="display:<?php echo esc_attr($display); ?>"<?php if ($is_hidden) echo ' hidden'; ?>>
      <?php if (!empty($current_list)) : ?>
        <?php
        $index = 0;
        foreach ($current_list as $p) :
            $index++;
            $num_str  = sprintf('%02d', $index);
            $cat_info = quterma_get_post_category_info($p);
        ?>
          <a href="<?php echo esc_url(get_permalink($p)); ?>" class="top-item">
            <div class="top-num"><?php echo esc_html($num_str); ?></div>
            <div>
              <div class="top-title"><?php echo esc_html(get_the_title($p)); ?></div>
              <div class="top-meta">
                <span class="sr-only"><?php echo esc_html($cat_info['name']); ?> · </span>
                <?php echo quterma_time_tag($p, false); ?>
              </div>
            </div>
          </a>
        <?php endforeach; ?>
      <?php else : ?>
        <div class="top-empty" style="padding:18px 4px;font-size:var(--t-xs);color:var(--ink-3);line-height:1.4">
          <?php esc_html_e('Нет материалов за этот период', 'quterma'); ?>
        </div>
      <?php endif; ?>
    </div>
  <?php endforeach; ?>
</div>
