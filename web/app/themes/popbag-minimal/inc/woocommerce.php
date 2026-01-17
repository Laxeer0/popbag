<?php
/**
 * WooCommerce integration (supports + helpers + small UX/styling hooks).
 *
 * We prefer hooks over template overrides; templates in /woocommerce are used only where markup must match the mockup.
 */
if (!defined('ABSPATH')) {
	exit;
}

add_action('after_setup_theme', static function (): void {
	add_theme_support('woocommerce');
	add_theme_support('wc-product-gallery-zoom');
	add_theme_support('wc-product-gallery-lightbox');
	add_theme_support('wc-product-gallery-slider');
});

// Disable default Woo styles (Tailwind handles styling via classes / overrides).
add_filter('woocommerce_enqueue_styles', '__return_empty_array');

/**
 * Minimal product helpers used by mockup templates.
 */
function popbag_has_woocommerce(): bool {
	return function_exists('wc_get_products') || class_exists('WooCommerce');
}

function popbag_get_new_arrivals(int $limit = 6) {
	if (!popbag_has_woocommerce()) {
		return [];
	}

	return popbag_cache_get(
		'new_arrivals_' . $limit,
		1800,
		static function () use ($limit) {
			return wc_get_products([
				'status'   => 'publish',
				'orderby'  => 'date',
				'order'    => 'DESC',
				'limit'    => $limit,
				'paginate' => false,
			]);
		}
	);
}

function popbag_get_best_sellers(int $limit = 6) {
	if (!popbag_has_woocommerce()) {
		return [];
	}

	return popbag_cache_get(
		'best_sellers_' . $limit,
		3600,
		static function () use ($limit) {
			return wc_get_products([
				'status'     => 'publish',
				'limit'      => $limit,
				'paginate'   => false,
				'meta_key'   => 'total_sales',
				'orderby'    => 'meta_value_num',
				'order'      => 'DESC',
				'visibility' => 'visible',
			]);
		}
	);
}

function popbag_get_products_by_category_slug(string $slug, int $limit = 12) {
	if (!popbag_has_woocommerce()) {
		return [];
	}

	$slug = sanitize_title($slug);
	if ('' === $slug) {
		return [];
	}

	return popbag_cache_get(
		'products_cat_' . $slug . '_' . $limit,
		1800,
		static function () use ($slug, $limit) {
			return wc_get_products([
				'status'   => 'publish',
				'limit'    => $limit,
				'paginate' => false,
				'category' => [$slug],
			]);
		}
	);
}

function popbag_get_product_categories_cached(array $args = []) {
	$defaults = [
		'taxonomy'   => 'product_cat',
		'hide_empty' => true,
		'number'     => 6,
	];

	$merged = wp_parse_args($args, $defaults);

	return popbag_cache_get(
		'product_categories_' . md5(wp_json_encode($merged)),
		21600,
		static function () use ($merged) {
			return get_terms($merged);
		}
	);
}

/**
 * Tailwind-friendly classes for Woo form fields.
 */
add_filter('woocommerce_form_field_args', static function (array $args, string $key, $value): array {
	// Ensure labels exist for accessibility.
	$args['label_class'] = array_merge((array) ($args['label_class'] ?? []), ['block', 'text-sm', 'font-semibold', 'text-[#003745]']);
	$args['input_class'] = array_merge((array) ($args['input_class'] ?? []), ['mt-1', 'w-full', 'rounded-[14px]', 'border', 'border-[#003745]/15', 'bg-white', 'px-4', 'py-3', 'text-[#003745]', 'placeholder-[#1F525E]/60', 'focus:border-[#003745]/40', 'focus:outline-none', 'focus:ring-2', 'focus:ring-[#FF2030]/20']);
	$args['class']       = array_merge((array) ($args['class'] ?? []), ['popbag-wc-field']);
	return $args;
}, 10, 3);

/**
 * Tailwind-friendly classes for "Add to cart" buttons in product loops.
 */
add_filter('woocommerce_loop_add_to_cart_args', static function (array $args, WC_Product $product): array {
	$classes = $args['class'] ?? 'button';

	$theme_btn = function_exists('popbag_button_classes')
		? popbag_button_classes('secondary', 'md', 'w-full hover:bg-[#FF2030]')
		: 'w-full rounded-full bg-[#003745] px-5 py-3 text-center text-sm font-bold uppercase tracking-[0.18em] text-white transition hover:-translate-y-px hover:shadow-md';

	$args['class'] = trim($classes . ' ' . $theme_btn);
	$args['attributes'] = array_merge(
		(array) ($args['attributes'] ?? []),
		[
			'aria-label' => sprintf(
				/* translators: %s: product name */
				__('Add “%s” to your cart', 'popbag-minimal'),
				$product->get_name()
			),
		]
	);

	return $args;
}, 10, 2);

/**
 * Provide a consistent container on Woo pages when templates aren't overridden.
 * (We only wrap, we do not change core behavior.)
 */
add_action('woocommerce_before_main_content', static function (): void {
	if (is_admin()) {
		return;
	}
	echo '<main class="bg-white"><div class="mx-auto max-w-6xl px-6 py-12">';
	woocommerce_output_all_notices();
}, 1);

add_action('woocommerce_after_main_content', static function (): void {
	if (is_admin()) {
		return;
	}
	echo '</div></main>';
}, 99);


