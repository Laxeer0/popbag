<?php
/**
 * Variation add to cart button (theme-styled).
 *
 * @package WooCommerce\Templates
 * @version 10.2.0
 */

defined('ABSPATH') || exit;

global $product;
?>

<div class="woocommerce-variation-add-to-cart variations_button flex flex-wrap items-end gap-3">
	<?php do_action('woocommerce_before_add_to_cart_button'); ?>

	<?php
	do_action('woocommerce_before_add_to_cart_quantity');

	woocommerce_quantity_input([
		'min_value'   => apply_filters('woocommerce_quantity_input_min', $product->get_min_purchase_quantity(), $product),
		'max_value'   => apply_filters('woocommerce_quantity_input_max', $product->get_max_purchase_quantity(), $product),
		'input_value' => isset($_POST['quantity']) ? wc_stock_amount(wp_unslash($_POST['quantity'])) : $product->get_min_purchase_quantity(), // phpcs:ignore WordPress.Security.NonceVerification.Missing
	]);

	do_action('woocommerce_after_add_to_cart_quantity');
	?>

	<button type="submit" class="<?php echo esc_attr(popbag_classnames('single_add_to_cart_button button alt', popbag_button_classes('primary', 'md', 'w-full'))); ?>">
		<?php echo esc_html($product->single_add_to_cart_text()); ?>
	</button>

	<?php do_action('woocommerce_after_add_to_cart_button'); ?>

	<input type="hidden" name="add-to-cart" value="<?php echo absint($product->get_id()); ?>" />
	<input type="hidden" name="product_id" value="<?php echo absint($product->get_id()); ?>" />
	<input type="hidden" name="variation_id" class="variation_id" value="0" />
</div>

