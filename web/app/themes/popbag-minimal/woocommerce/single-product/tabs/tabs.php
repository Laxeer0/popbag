<?php
/**
 * Single product tabs (theme-styled wrapper; keeps Woo hooks).
 *
 * @package WooCommerce\Templates
 * @version 9.8.0
 */

defined('ABSPATH') || exit;

$product_tabs = apply_filters('woocommerce_product_tabs', []);

if (!empty($product_tabs)) : ?>
	<div class="woocommerce-tabs wc-tabs-wrapper rounded-[16px] border border-[#003745]/10 bg-white p-6 shadow-sm">
		<ul class="tabs wc-tabs" role="tablist">
			<?php foreach ($product_tabs as $key => $product_tab) : ?>
				<li role="presentation" class="<?php echo esc_attr($key); ?>_tab" id="tab-title-<?php echo esc_attr($key); ?>">
					<a href="#tab-<?php echo esc_attr($key); ?>" role="tab" aria-controls="tab-<?php echo esc_attr($key); ?>">
						<?php echo wp_kses_post(apply_filters('woocommerce_product_' . $key . '_tab_title', $product_tab['title'], $key)); ?>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>

		<?php foreach ($product_tabs as $key => $product_tab) : ?>
			<div class="woocommerce-Tabs-panel woocommerce-Tabs-panel--<?php echo esc_attr($key); ?> panel entry-content wc-tab" id="tab-<?php echo esc_attr($key); ?>" role="tabpanel" aria-labelledby="tab-title-<?php echo esc_attr($key); ?>">
				<?php
				if (isset($product_tab['callback'])) {
					call_user_func($product_tab['callback'], $key, $product_tab);
				}
				?>
			</div>
		<?php endforeach; ?>

		<?php do_action('woocommerce_product_after_tabs'); ?>
	</div>
<?php endif; ?>

