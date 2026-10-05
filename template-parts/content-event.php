<?php
/**
 * Template part for displaying an Event card (.event-card) from API «Культура.РФ»
 *
 * @package Quterma
 */

if (!defined('ABSPATH')) {
    exit;
}

$event = get_query_var('event_item', array());

if (empty($event)) {
    return;
}

$name        = !empty($event['name']) ? $event['name'] : '';
$type        = !empty($event['type']) ? $event['type'] : __('Событие', 'quterma');
$city_name   = !empty($event['city_name']) ? $event['city_name'] : 'Ярославль';
$city_slug   = !empty($event['city_slug']) ? $event['city_slug'] : 'yaroslavl';
$when_tokens = !empty($event['when_tokens']) ? $event['when_tokens'] : (!empty($event['when_slug']) ? $event['when_slug'] : 'today');
$day         = !empty($event['day']) ? $event['day'] : wp_date('j');
$month       = !empty($event['month']) ? $event['month'] : wp_date('M');
$time        = !empty($event['time']) ? $event['time'] : '19:00';
$iso         = !empty($event['iso']) ? $event['iso'] : '';
$place       = !empty($event['place']) ? $event['place'] : '';
$image       = !empty($event['image']) ? $event['image'] : '';
$url         = !empty($event['url']) ? $event['url'] : '#';
?>
<a href="<?php echo esc_url($url); ?>" class="event-card" data-city="<?php echo esc_attr($city_slug); ?>" data-when="<?php echo esc_attr($when_tokens); ?>" target="<?php echo ($url !== '#') ? '_blank' : '_self'; ?>" rel="noopener noreferrer">
  <div class="event-img">
    <div class="event-date-badge">
      <div class="event-date-day"><?php echo esc_html($day); ?></div>
      <div class="event-date-month"><?php echo esc_html($month); ?></div>
    </div>
    <span class="event-city-badge"><?php echo esc_html($city_name); ?></span>

    <?php if (!empty($image)) : ?>
      <img src="<?php echo esc_url($image); ?>" alt="<?php echo esc_attr($name); ?>" loading="lazy" onerror="this.style.display='none';if(this.nextElementSibling){this.nextElementSibling.style.display='flex';}" />
      <div class="event-fallback-ph ph-img" style="display:none;width:100%;height:100%;align-items:center;justify-content:center;background:linear-gradient(135deg,#1E2620 0%,#141916 100%);color:var(--brand-2)">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" width="42" height="42">
          <rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
        </svg>
      </div>
    <?php else : ?>
      <div class="event-fallback-ph ph-img" style="display:flex;width:100%;height:100%;align-items:center;justify-content:center;background:linear-gradient(135deg,#1E2620 0%,#141916 100%);color:var(--brand-2)">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" width="42" height="42">
          <rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
        </svg>
      </div>
    <?php endif; ?>
  </div>

  <div class="event-body">
    <div class="event-type"><?php echo esc_html($type); ?></div>
    <div class="event-name"><?php echo esc_html($name); ?></div>
    <?php if (!empty($time)) : ?>
      <div class="event-when">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="12" cy="12" r="9"/><polyline points="12 7 12 12 15.5 14"/>
        </svg>
        <?php if (!empty($iso)) : ?>
          <time datetime="<?php echo esc_attr($iso); ?>"><?php echo esc_html($time); ?></time>
        <?php else : ?>
          <span><?php echo esc_html($time); ?></span>
        <?php endif; ?>
      </div>
    <?php endif; ?>
    <?php if (!empty($place)) : ?>
      <div class="event-place">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>
        </svg>
        <?php echo esc_html($place); ?>
      </div>
    <?php endif; ?>
  </div>
</a>
