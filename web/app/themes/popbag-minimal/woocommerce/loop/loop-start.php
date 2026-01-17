<?php
/**
 * Product Loop Start (theme grid).
 *
 * @package WooCommerce\Templates
 * @version 3.3.0
 */

defined('ABSPATH') || exit;

$columns = (int) wc_get_loop_prop('columns');
$columns = $columns > 0 ? $columns : 3;
?>

<ul class="<?php echo esc_attr('products columns-' . $columns . ' grid gap-8 sm:grid-cols-2 lg:grid-cols-3'); ?>">

