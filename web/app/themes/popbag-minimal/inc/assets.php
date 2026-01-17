<?php
/**
 * Enqueue compiled theme assets (Tailwind build in /dist).
 *
 * Pipeline (current repo):
 * - Tailwind CLI: `npm run build` compiles `src/css/app.css` -> `dist/app.css`
 * - No manifest: version via filemtime().
 */
if (!defined('ABSPATH')) {
	exit;
}

add_action('wp_enqueue_scripts', static function (): void {
	$theme = wp_get_theme();

	$css_rel  = 'dist/app.css';
	$css_path = get_theme_file_path($css_rel);
	$css_ver  = file_exists($css_path) ? (string) filemtime($css_path) : $theme->get('Version');

	if (file_exists($css_path)) {
		wp_enqueue_style('popbag-app', get_theme_file_uri($css_rel), [], $css_ver);
	}

	// Header dropdowns (WP menu sub-menus).
	$nav_css_rel  = 'assets/css/nav-dropdown.css';
	$nav_css_path = get_theme_file_path($nav_css_rel);
	if (file_exists($nav_css_path)) {
		wp_enqueue_style('popbag-nav-dropdown', get_theme_file_uri($nav_css_rel), ['popbag-app'], (string) filemtime($nav_css_path));
	}

	// Swiper (CDN) for carousels in the mockup.
	wp_enqueue_style('popbag-swiper', 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css', [], '11');
	wp_enqueue_script('popbag-swiper', 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js', [], '11', true);

	$header_js_rel  = 'assets/js/header.js';
	$header_js_path = get_theme_file_path($header_js_rel);
	if (file_exists($header_js_path)) {
		wp_enqueue_script('popbag-header', get_theme_file_uri($header_js_rel), [], (string) filemtime($header_js_path), true);
	}

	$swiper_init_rel  = 'assets/js/swiper-init.js';
	$swiper_init_path = get_theme_file_path($swiper_init_rel);
	if (file_exists($swiper_init_path)) {
		wp_enqueue_script('popbag-swiper-init', get_theme_file_uri($swiper_init_rel), ['popbag-swiper'], (string) filemtime($swiper_init_path), true);
	}

	// Bag builder (single poppins_bag): product cards + modal.
	if (is_singular('poppins_bag')) {
		$bag_css_rel  = 'assets/css/bag.css';
		$bag_css_path = get_theme_file_path($bag_css_rel);
		if (file_exists($bag_css_path)) {
			wp_enqueue_style('popbag-bag', get_theme_file_uri($bag_css_rel), ['popbag-app'], (string) filemtime($bag_css_path));
		}

		$bag_js_rel  = 'assets/js/bag-builder.js';
		$bag_js_path = get_theme_file_path($bag_js_rel);
		if (file_exists($bag_js_path)) {
			wp_enqueue_script('popbag-bag', get_theme_file_uri($bag_js_rel), [], (string) filemtime($bag_js_path), true);
		}
	}

	// WooCommerce: account pages only (login/register/lost-password/dashboard/orders/etc).
	if (function_exists('is_account_page') && is_account_page()) {
		$account_css_rel  = 'assets/css/woo-account.css';
		$account_css_path = get_theme_file_path($account_css_rel);
		if (file_exists($account_css_path)) {
			wp_enqueue_style('popbag-woo-account', get_theme_file_uri($account_css_rel), ['popbag-app'], (string) filemtime($account_css_path));
		}
	}

	// WooCommerce: single product page only.
	if (function_exists('is_product') && is_product()) {
		$product_css_rel  = 'assets/css/woo-single-product.css';
		$product_css_path = get_theme_file_path($product_css_rel);
		if (file_exists($product_css_path)) {
			wp_enqueue_style('popbag-woo-single-product', get_theme_file_uri($product_css_rel), ['popbag-app'], (string) filemtime($product_css_path));
		}
	}
});



