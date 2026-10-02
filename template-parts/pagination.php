<?php
/**
 * Template part for displaying posts pagination
 *
 * @package Quterma
 */

if (!defined('ABSPATH')) {
    exit;
}

global $wp_query;

$total_pages = isset($args['total_pages']) ? (int) $args['total_pages'] : (int) $wp_query->max_num_pages;
$current     = isset($args['current']) ? (int) $args['current'] : max(1, get_query_var('paged'));

if ($total_pages <= 1) {
    return;
}

$pagination_links = paginate_links(array(
    'base'      => str_replace(999999999, '%#%', esc_url(get_pagenum_link(999999999))),
    'format'    => '?paged=%#%',
    'current'   => $current,
    'total'     => $total_pages,
    'type'      => 'array',
    'prev_text' => '<span class="nav-prev">&larr; ' . __('Назад', 'quterma') . '</span>',
    'next_text' => '<span class="nav-next">' . __('Вперед', 'quterma') . ' &rarr;</span>',
    'mid_size'  => 2,
    'end_size'  => 1,
));

if (!empty($pagination_links)) :
?>
<nav class="navigation pagination" aria-label="<?php esc_attr_e('Навигация по записям', 'quterma'); ?>">
  <h2 class="screen-reader-text sr-only"><?php esc_html_e('Навигация по записям', 'quterma'); ?></h2>
  <div class="nav-links">
    <?php foreach ($pagination_links as $link) : ?>
      <?php echo wp_kses_post($link); ?>
    <?php endforeach; ?>
  </div>
</nav>
<?php endif; ?>
