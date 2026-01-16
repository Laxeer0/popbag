<?php
/**
 * BAG integration (CPT poppins_bag from mu-plugin poppins-bags.php).
 *
 * What exists already (mu-plugin):
 * - CPT: poppins_bag (slug /bags)
 * - Meta: _poppins_bag_slug, _poppins_bag_capacity, _poppins_bag_category_limits
 * - Product meta: _poppins_bags_available (bag slugs where product can be selected)
 *
 * What we add here (theme-side UI + cart/order metadata):
 * - Bag builder form on single bag to select products and add them to cart as a linked group
 * - Cart/checkout/order: show bag label + group id, keep items linked via cart item meta
 */
if (!defined('ABSPATH')) {
	exit;
}

const POPBAG_BAG_CART_META_KEY = 'popbag_bag';
const POPBAG_BAG_CONTAINER_PRODUCT_OPTION = 'popbag_bag_container_product_id';

/**
 * Return the WooCommerce "container product" used to represent a Bag in cart.
 *
 * We create it only in admin (capability-gated) to avoid creating products from frontend visitors.
 */
function popbag_get_bag_container_product_id(): int {
	$id = absint(get_option(POPBAG_BAG_CONTAINER_PRODUCT_OPTION));
	if ($id > 0 && function_exists('wc_get_product')) {
		$p = wc_get_product($id);
		if ($p instanceof WC_Product) {
			return $id;
		}
	}

	// Only admins/managers can create the container product.
	if (!is_admin() || !current_user_can('manage_woocommerce') || !class_exists('WC_Product_Simple')) {
		return 0;
	}

	$product = new WC_Product_Simple();
	$product->set_name(__('Bag', 'popbag-minimal'));
	$product->set_status('publish');
	$product->set_catalog_visibility('hidden');
	$product->set_virtual(true);
	$product->set_sold_individually(true);
	$product->set_regular_price('0');
	$product->set_price('0');
	$new_id = (int) $product->save();

	if ($new_id > 0) {
		update_option(POPBAG_BAG_CONTAINER_PRODUCT_OPTION, $new_id, true);
		return $new_id;
	}

	return 0;
}

// Ensure the container product exists (admin-side).
add_action('admin_init', static function (): void {
	if (!function_exists('WC')) {
		return;
	}
	popbag_get_bag_container_product_id();
});

/**
 * Fetch published bags (CPT poppins_bag) with caching.
 *
 * @return WP_Post[]
 */
function popbag_get_bag_posts(int $limit = 12): array {
	$limit = max(1, min(24, absint($limit)));

	if (!post_type_exists('poppins_bag')) {
		return [];
	}

	$cache_key = 'bag_posts_' . $limit;
	if (function_exists('popbag_cache_get')) {
		return (array) popbag_cache_get(
			$cache_key,
			900,
			static function () use ($limit) {
				$q = new WP_Query([
					'post_type'      => 'poppins_bag',
					'posts_per_page' => $limit,
					'post_status'    => 'publish',
					'no_found_rows'  => true,
				]);
				return $q->posts;
			}
		);
	}

	$q = new WP_Query([
		'post_type'      => 'poppins_bag',
		'posts_per_page' => $limit,
		'post_status'    => 'publish',
		'no_found_rows'  => true,
	]);
	return $q->posts;
}

/**
 * Template helper: fetch bag data from a poppins_bag post ID.
 *
 * @return array{post_id:int, slug:string, label:string, capacity:int, price:float, limits:array<int,int>}
 */
function popbag_get_bag_data(int $bag_post_id): array {
	$slug = (string) get_post_meta($bag_post_id, '_poppins_bag_slug', true);
	$slug = sanitize_title($slug);
	$raw_price = (string) get_post_meta($bag_post_id, '_poppins_bag_price', true);
	$raw_price = str_replace(',', '.', $raw_price);
	$price = max(0, (float) $raw_price);

	return [
		'post_id'   => $bag_post_id,
		'slug'      => $slug,
		'label'     => get_the_title($bag_post_id),
		'capacity'  => max(1, absint(get_post_meta($bag_post_id, '_poppins_bag_capacity', true))),
		'price'     => $price,
		'limits'    => (array) get_post_meta($bag_post_id, '_poppins_bag_category_limits', true),
	];
}

/**
 * Validate selected products for a given bag post.
 *
 * @return array{ok:bool, message:string, product_ids:int[]}
 */
function popbag_validate_bag_selection(int $bag_post_id, array $raw_product_ids): array {
	if (!function_exists('poppins_get_products_for_bag_post')) {
		return ['ok' => false, 'message' => __('Bag feature is not available.', 'popbag-minimal'), 'product_ids' => []];
	}

	$bag = popbag_get_bag_data($bag_post_id);
	if (!$bag['slug']) {
		return ['ok' => false, 'message' => __('Invalid bag.', 'popbag-minimal'), 'product_ids' => []];
	}

	$allowed_ids = array_map('absint', poppins_get_products_for_bag_post($bag_post_id));
	$allowed_set = array_fill_keys($allowed_ids, true);

	$selected = array_values(array_unique(array_filter(array_map('absint', $raw_product_ids))));
	$selected = array_values(array_filter($selected, static function (int $id) use ($allowed_set): bool {
		return $id > 0 && isset($allowed_set[$id]);
	}));

	if (!$selected) {
		return ['ok' => false, 'message' => __('Select at least one product for this bag.', 'popbag-minimal'), 'product_ids' => []];
	}

	if (count($selected) > $bag['capacity']) {
		return [
			'ok'         => false,
			/* translators: %d: capacity */
			'message'    => sprintf(__('You can select up to %d products for this bag.', 'popbag-minimal'), $bag['capacity']),
			'product_ids'=> [],
		];
	}

	// Validate products exist/purchasable/in stock.
	if (function_exists('wc_get_product')) {
		foreach ($selected as $product_id) {
			$product = wc_get_product($product_id);
			if (!$product) {
				return ['ok' => false, 'message' => __('One of the selected products is not available.', 'popbag-minimal'), 'product_ids' => []];
			}
			if (!$product->is_purchasable()) {
				return ['ok' => false, 'message' => __('One of the selected products cannot be purchased.', 'popbag-minimal'), 'product_ids' => []];
			}
			if (!$product->is_in_stock()) {
				return ['ok' => false, 'message' => __('One of the selected products is out of stock.', 'popbag-minimal'), 'product_ids' => []];
			}
		}
	}

	// Category limits (term_id => limit).
	$limits = [];
	foreach ((array) ($bag['limits'] ?? []) as $term_id => $limit) {
		$term_id = absint($term_id);
		$limit = absint($limit);
		if ($term_id > 0 && $limit > 0) {
			$limits[$term_id] = $limit;
		}
	}

	if ($limits) {
		$counts = [];
		foreach ($selected as $product_id) {
			$terms = wp_get_post_terms($product_id, 'product_cat', ['fields' => 'ids']);
			if (is_wp_error($terms)) {
				continue;
			}
			foreach ($terms as $term_id) {
				$term_id = absint($term_id);
				if (!isset($limits[$term_id])) {
					continue;
				}
				$counts[$term_id] = ($counts[$term_id] ?? 0) + 1;
				if ($counts[$term_id] > $limits[$term_id]) {
					$term = get_term($term_id, 'product_cat');
					$name = ($term && !is_wp_error($term)) ? $term->name : (string) $term_id;
					return [
						'ok' => false,
						/* translators: 1: category name, 2: limit */
						'message' => sprintf(__('Category limit exceeded: %1$s (max %2$d).', 'popbag-minimal'), $name, $limits[$term_id]),
						'product_ids' => [],
					];
				}
			}
		}
	}

	return ['ok' => true, 'message' => '', 'product_ids' => $selected];
}

/**
 * Handle bag builder submission (POST on single bag).
 */
add_action('template_redirect', static function (): void {
	if (!function_exists('WC') || !is_singular('poppins_bag')) {
		return;
	}

	if ('POST' !== ($_SERVER['REQUEST_METHOD'] ?? 'GET')) {
		return;
	}

	$bag_post_id = get_queried_object_id();
	if (!$bag_post_id) {
		return;
	}

	if (!isset($_POST['popbag_bag_nonce']) || !wp_verify_nonce((string) $_POST['popbag_bag_nonce'], 'popbag_add_bag_to_cart')) {
		wc_add_notice(__('Security check failed. Please try again.', 'popbag-minimal'), 'error');
		wp_safe_redirect(get_permalink($bag_post_id));
		exit;
	}

	$raw_ids = (array) ($_POST['popbag_bag_products'] ?? []);
	$validation = popbag_validate_bag_selection($bag_post_id, $raw_ids);
	if (!$validation['ok']) {
		wc_add_notice($validation['message'], 'error');
		wp_safe_redirect(get_permalink($bag_post_id));
		exit;
	}

	$bag = popbag_get_bag_data($bag_post_id);

	$container_product_id = popbag_get_bag_container_product_id();
	if ($container_product_id <= 0) {
		wc_add_notice(__('Bag product is not configured yet. Please contact the shop manager.', 'popbag-minimal'), 'error');
		wp_safe_redirect(get_permalink($bag_post_id));
		exit;
	}

	$unique = wp_generate_uuid4();
	$added = WC()->cart->add_to_cart(
		$container_product_id,
		1,
		0,
		[],
		[
			POPBAG_BAG_CART_META_KEY => [
				'unique'              => $unique,
				'bag_post_id'          => $bag['post_id'],
				'bag_slug'             => $bag['slug'],
				'bag_label'            => $bag['label'],
				'bag_price'            => (float) ($bag['price'] ?? 0),
				'selected_product_ids' => array_map('absint', (array) $validation['product_ids']),
			],
		]
	);

	if (!$added) {
		wc_add_notice(__('Some items could not be added to the cart.', 'popbag-minimal'), 'error');
		wp_safe_redirect(get_permalink($bag_post_id));
		exit;
	}

	wc_add_notice(__('Bag added to cart.', 'popbag-minimal'), 'success');
	wp_safe_redirect(wc_get_cart_url());
	exit;
});

/**
 * Make bag cart items unique (so different selections don't merge).
 */
add_filter('woocommerce_add_cart_item_data', static function (array $cart_item_data, int $product_id, int $variation_id): array {
	if (!isset($cart_item_data[POPBAG_BAG_CART_META_KEY]) || !is_array($cart_item_data[POPBAG_BAG_CART_META_KEY])) {
		return $cart_item_data;
	}
	$meta = $cart_item_data[POPBAG_BAG_CART_META_KEY];
	if (empty($meta['unique'])) {
		return $cart_item_data;
	}
	$cart_item_data['unique_key'] = sanitize_text_field((string) $meta['unique']);
	return $cart_item_data;
}, 10, 3);

/**
 * Override cart item price for bag items.
 */
add_action('woocommerce_before_calculate_totals', static function (WC_Cart $cart): void {
	if (is_admin() && !defined('DOING_AJAX')) {
		return;
	}

	foreach ($cart->get_cart() as $key => $cart_item) {
		$meta = $cart_item[POPBAG_BAG_CART_META_KEY] ?? null;
		if (!is_array($meta) || !isset($meta['bag_post_id'])) {
			continue;
		}
		$price = isset($meta['bag_price']) ? (float) $meta['bag_price'] : 0.0;
		$price = max(0, $price);
		if (isset($cart_item['data']) && $cart_item['data'] instanceof WC_Product) {
			$cart_item['data']->set_price($price);
		}
	}
}, 20, 1);

/**
 * Show bag metadata under cart item name (cart/checkout).
 */
add_filter('woocommerce_get_item_data', static function (array $item_data, array $cart_item): array {
	$meta = $cart_item[POPBAG_BAG_CART_META_KEY] ?? null;
	if (!is_array($meta) || empty($meta['bag_label'])) {
		return $item_data;
	}

	$item_data[] = [
		'key'   => __('Bag', 'popbag-minimal'),
		'value' => sanitize_text_field((string) $meta['bag_label']),
	];

	// Edit link (most reliable place in this theme, since cart template prints formatted item data).
	$bag_post_id = isset($meta['bag_post_id']) ? absint($meta['bag_post_id']) : 0;
	if ($bag_post_id > 0 && function_exists('is_cart') && is_cart()) {
		$bag_link = get_permalink($bag_post_id);
		if ($bag_link) {
			$selected_ids = isset($meta['selected_product_ids']) ? array_map('absint', (array) $meta['selected_product_ids']) : [];
			$selected_ids = array_values(array_filter($selected_ids, static fn(int $id): bool => $id > 0));
			$edit_link = $selected_ids ? add_query_arg(['selected' => implode(',', $selected_ids)], $bag_link) : $bag_link;
			$item_data[] = [
				'key'   => __('Modifica', 'popbag-minimal'),
				'value' => '<a class="text-xs uppercase tracking-[0.14em] text-[#003745] underline underline-offset-4" href="' . esc_url($edit_link) . '">' . esc_html__('Modifica bag', 'popbag-minimal') . '</a>',
			];
		}
	}

	$ids = isset($meta['selected_product_ids']) ? array_map('absint', (array) $meta['selected_product_ids']) : [];
	if ($ids) {
		$names = [];
		if (function_exists('wc_get_product')) {
			foreach ($ids as $pid) {
				$p = wc_get_product($pid);
				if ($p instanceof WC_Product) {
					$names[] = $p->get_name();
				}
			}
		}
		if ($names) {
			$item_data[] = [
				'key'   => __('Capi selezionati', 'popbag-minimal'),
				'value' => implode(', ', array_map('sanitize_text_field', $names)),
			];
		}
	}

	return $item_data;
}, 10, 2);

/**
 * Persist bag metadata to order items.
 */
add_action('woocommerce_checkout_create_order_line_item', static function ($item, $cart_item_key, $values, $order): void {
	$meta = $values[POPBAG_BAG_CART_META_KEY] ?? null;
	if (!is_array($meta) || empty($meta['bag_label'])) {
		return;
	}

	$item->add_meta_data(__('Bag', 'popbag-minimal'), sanitize_text_field((string) $meta['bag_label']), true);
	if (!empty($meta['selected_product_ids'])) {
		$item->add_meta_data(__('Capi selezionati (IDs)', 'popbag-minimal'), implode(',', array_map('absint', (array) $meta['selected_product_ids'])), true);
	}
}, 10, 4);


