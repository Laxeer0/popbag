<?php
/**
 * Simple product add to cart (theme-styled).
 *
 * @package WooCommerce\Templates
 * @version 10.2.0
 */

defined('ABSPATH') || exit;

global $product;

if (!$product->is_purchasable()) {
	return;
}

echo wc_get_stock_html($product); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

if ($product->is_in_stock()) :
	do_action('woocommerce_before_add_to_cart_form');
	?>

	<form class="cart space-y-3" action="<?php echo esc_url(apply_filters('woocommerce_add_to_cart_form_action', $product->get_permalink())); ?>" method="post" enctype="multipart/form-data">
		<?php do_action('woocommerce_before_add_to_cart_button'); ?>

		<div class="flex flex-wrap items-end gap-3">
			<div class="flex items-center gap-3">
				<?php
				do_action('woocommerce_before_add_to_cart_quantity');

				woocommerce_quantity_input([
					'min_value'   => $product->get_min_purchase_quantity(),
					'max_value'   => $product->get_max_purchase_quantity(),
					'input_value' => isset($_POST['quantity']) ? wc_stock_amount(wp_unslash($_POST['quantity'])) : $product->get_min_purchase_quantity(), // phpcs:ignore WordPress.Security.NonceVerification.Missing
				]);

				do_action('woocommerce_after_add_to_cart_quantity');
				?>
			</div>

			<button
				type="submit"
				name="add-to-cart"
				value="<?php echo esc_attr($product->get_id()); ?>"
				class="<?php echo esc_attr(popbag_classnames('single_add_to_cart_button button alt', popbag_button_classes('primary', 'md', 'w-full'))); ?>"
			>
				<?php echo esc_html($product->single_add_to_cart_text()); ?>
			</button>
		</div>

		<?php do_action('woocommerce_after_add_to_cart_button'); ?>
	</form>

	<?php
	do_action('woocommerce_after_add_to_cart_form');
endif;

