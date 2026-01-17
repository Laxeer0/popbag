<?php
/**
 * Single product meta (theme-styled).
 *
 * @package WooCommerce\Templates
 * @version 9.7.0
 */

use Automattic\WooCommerce\Enums\ProductType;

defined('ABSPATH') || exit;

global $product;
?>

<div class="product_meta rounded-[16px] border border-[#003745]/10 bg-[#003745]/5 p-4 text-sm text-[#003745]">
	<?php do_action('woocommerce_product_meta_start'); ?>

	<?php if (wc_product_sku_enabled() && ($product->get_sku() || $product->is_type(ProductType::VARIABLE))) : ?>
		<p class="m-0">
			<span class="font-semibold"><?php esc_html_e('SKU:', 'woocommerce'); ?></span>
			<span class="sku"><?php echo ($sku = $product->get_sku()) ? esc_html($sku) : esc_html__('N/A', 'woocommerce'); ?></span>
		</p>
	<?php endif; ?>

	<?php
	$cats = wc_get_product_category_list($product->get_id(), ', ', '<span class="posted_in"><span class="font-semibold">' . _n('Category:', 'Categories:', count($product->get_category_ids()), 'woocommerce') . '</span> ', '</span>');
	if ($cats) {
		echo '<p class="m-0">' . wp_kses_post($cats) . '</p>';
	}

	$tags = wc_get_product_tag_list($product->get_id(), ', ', '<span class="tagged_as"><span class="font-semibold">' . _n('Tag:', 'Tags:', count($product->get_tag_ids()), 'woocommerce') . '</span> ', '</span>');
	if ($tags) {
		echo '<p class="m-0 mt-2">' . wp_kses_post($tags) . '</p>';
	}
	?>

	<?php do_action('woocommerce_product_meta_end'); ?>
</div>

