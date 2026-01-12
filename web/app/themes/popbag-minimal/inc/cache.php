<?php
/**
 * Small caching helpers (versioned transients).
 */
if (!defined('ABSPATH')) {
	exit;
}

function popbag_cache_get(string $key, int $ttl, callable $callback) {
	$version       = (int) get_option('popbag_cache_v', 1);
	$transient_key = 'popbag_' . $version . '_' . md5($key);

	$cached = get_transient($transient_key);
	if (false !== $cached) {
		return $cached;
	}

	$value = call_user_func($callback);
	if (false !== $value && null !== $value) {
		set_transient($transient_key, $value, $ttl);
	}

	return $value;
}

function popbag_bump_cache_version(): void {
	$version = (int) get_option('popbag_cache_v', 1);
	update_option('popbag_cache_v', $version + 1, false);
}

add_action('init', static function (): void {
	// Products.
	add_action('save_post_product', 'popbag_bump_cache_version');
	add_action('deleted_post', static function (int $post_id): void {
		if ('product' === get_post_type($post_id)) {
			popbag_bump_cache_version();
		}
	});
	add_action('transition_post_status', static function ($new_status, $old_status, $post): void {
		if ($post instanceof WP_Post && 'product' === $post->post_type && $new_status !== $old_status) {
			popbag_bump_cache_version();
		}
	}, 10, 3);

	add_action('woocommerce_update_product', 'popbag_bump_cache_version');
	add_action('woocommerce_product_set_stock', 'popbag_bump_cache_version');

	// Bags (CPT poppins_bag).
	add_action('save_post_poppins_bag', 'popbag_bump_cache_version');
	add_action('deleted_post', static function (int $post_id): void {
		if ('poppins_bag' === get_post_type($post_id)) {
			popbag_bump_cache_version();
		}
	});
	add_action('transition_post_status', static function ($new_status, $old_status, $post): void {
		if ($post instanceof WP_Post && 'poppins_bag' === $post->post_type && $new_status !== $old_status) {
			popbag_bump_cache_version();
		}
	}, 10, 3);

	// Product categories.
	$term_bump = static function ($term_id, $tt_id, $taxonomy): void {
		if ('product_cat' === $taxonomy) {
			popbag_bump_cache_version();
		}
	};
	add_action('created_term', $term_bump, 10, 3);
	add_action('edited_term', $term_bump, 10, 3);
	add_action('delete_term', $term_bump, 10, 3);
});



