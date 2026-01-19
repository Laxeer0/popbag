<?php
/**
 * Theme onboarding (safe defaults on first activation).
 *
 * - Creates a "Home" page (if missing) and sets it as front page
 * - Creates a "Blog" page (if missing) and sets it as posts page
 *
 * IMPORTANT: It does NOT override existing Reading settings if already configured.
 */
if (!defined('ABSPATH')) {
	exit;
}

/**
 * Ensure required plugins are active when the theme is activated.
 *
 * NOTE: This runs independently from the "first-run" onboarding below (which may early-return).
 */
add_action('after_switch_theme', static function (): void {
	// Theme activation happens in wp-admin, but keep it safe for edge cases (CLI/tests).
	if (!function_exists('activate_plugin')) {
		@include_once ABSPATH . 'wp-admin/includes/plugin.php';
	}
	if (!function_exists('activate_plugin')) {
		return;
	}

	// Only users who can activate plugins should trigger this.
	if (function_exists('current_user_can') && !current_user_can('activate_plugins')) {
		return;
	}

	$plugins = [
		'woocommerce/woocommerce.php',
		'sumup-payment-gateway-for-woocommerce/sumup-payment-gateway-for-woocommerce.php',
		'goodtill-stock-sync/gtc-stock-sync.php',
	];

	foreach ($plugins as $plugin) {
		$plugin_path = WP_PLUGIN_DIR . '/' . $plugin;
		if (!file_exists($plugin_path)) {
			continue;
		}

		$is_active = false;
		if (function_exists('is_plugin_active') && is_plugin_active($plugin)) {
			$is_active = true;
		}
		if (!$is_active && function_exists('is_plugin_active_for_network') && is_plugin_active_for_network($plugin)) {
			$is_active = true;
		}
		if ($is_active) {
			continue;
		}

		// Silent activation to avoid redirect noise.
		activate_plugin($plugin, '', false, true);
	}
}, 5);

add_action('after_switch_theme', static function (): void {
	// If front page is already configured, do nothing.
	$page_on_front = (int) get_option('page_on_front', 0);
	if ($page_on_front > 0) {
		return;
	}

	// Find or create Home page.
	$home = get_page_by_path('home');
	if (!$home) {
		$home = get_page_by_title('Home');
	}

	$home_id = $home instanceof WP_Post ? (int) $home->ID : 0;
	if ($home_id <= 0) {
		$home_id = (int) wp_insert_post([
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_title'   => 'Home',
			'post_name'    => 'home',
			// Use the registered pattern (Gutenberg) as starter content.
			'post_content' => '<!-- wp:pattern {"slug":"popbag/home-starter"} /-->',
		], true);
	}

	if ($home_id > 0) {
		update_option('show_on_front', 'page');
		update_option('page_on_front', $home_id);
	}

	// Optional: set a Blog page if not set.
	$page_for_posts = (int) get_option('page_for_posts', 0);
	if ($page_for_posts > 0) {
		return;
	}

	$blog = get_page_by_path('blog');
	if (!$blog) {
		$blog = get_page_by_title('Blog');
	}

	$blog_id = $blog instanceof WP_Post ? (int) $blog->ID : 0;
	if ($blog_id <= 0) {
		$blog_id = (int) wp_insert_post([
			'post_type'   => 'page',
			'post_status' => 'publish',
			'post_title'  => 'Blog',
			'post_name'   => 'blog',
		], true);
	}

	if ($blog_id > 0) {
		update_option('page_for_posts', $blog_id);
	}

	/**
	 * Default primary menu (only if not configured yet).
	 * Items:
	 * - Home
	 * - Chi siamo
	 * - Product categories used on homepage
	 */
	$locations = (array) get_theme_mod('nav_menu_locations', []);
	$primary_menu_id = isset($locations['primary']) ? absint($locations['primary']) : 0;
	if ($primary_menu_id > 0) {
		return;
	}

	$menu_name = 'Menu principale';
	$menu_obj = wp_get_nav_menu_object($menu_name);
	$menu_id = $menu_obj ? (int) $menu_obj->term_id : 0;
	if ($menu_id <= 0) {
		$menu_id = (int) wp_create_nav_menu($menu_name);
	}
	if ($menu_id <= 0) {
		return;
	}

	// Assign to primary location.
	$locations['primary'] = $menu_id;
	set_theme_mod('nav_menu_locations', $locations);

	// Fetch existing menu items to avoid duplicates.
	$existing = wp_get_nav_menu_items($menu_id);
	$existing_object_ids = [];
	$existing_urls = [];
	if (is_array($existing)) {
		foreach ($existing as $item) {
			if (!($item instanceof WP_Post)) {
				continue;
			}
			$existing_object_ids[(string) ($item->object . ':' . (int) $item->object_id)] = true;
			if (!empty($item->url)) {
				$existing_urls[(string) $item->url] = true;
			}
		}
	}

	// Home item (front page if set, otherwise site root).
	$home_target_id = (int) get_option('page_on_front', 0);
	if ($home_target_id > 0 && empty($existing_object_ids['page:' . $home_target_id])) {
		wp_update_nav_menu_item($menu_id, 0, [
			'menu-item-title'     => 'Home',
			'menu-item-object'    => 'page',
			'menu-item-object-id' => $home_target_id,
			'menu-item-type'      => 'post_type',
			'menu-item-status'    => 'publish',
		]);
	} elseif (empty($existing_urls[home_url('/')])) {
		wp_update_nav_menu_item($menu_id, 0, [
			'menu-item-title'  => 'Home',
			'menu-item-url'    => home_url('/'),
			'menu-item-type'   => 'custom',
			'menu-item-status' => 'publish',
		]);
	}

	// Chi siamo page (create if missing).
	$about = get_page_by_path('chi-siamo');
	if (!$about) {
		$about = get_page_by_title('Chi siamo');
	}
	$about_id = $about instanceof WP_Post ? (int) $about->ID : 0;
	if ($about_id <= 0) {
		$about_id = (int) wp_insert_post([
			'post_type'   => 'page',
			'post_status' => 'publish',
			'post_title'  => 'Chi siamo',
			'post_name'   => 'chi-siamo',
			'post_content'=> '',
		], true);
	}
	if ($about_id > 0 && empty($existing_object_ids['page:' . $about_id])) {
		wp_update_nav_menu_item($menu_id, 0, [
			'menu-item-title'     => 'Chi siamo',
			'menu-item-object'    => 'page',
			'menu-item-object-id' => $about_id,
			'menu-item-type'      => 'post_type',
			'menu-item-status'    => 'publish',
		]);
	}

	// Product categories used on homepage.
	$home_category_slugs = [
		'levis',
		'felpe',
		'giubbotti',
		'pantaloni',
		'maglieria',
		'tute',
		'profumi',
	];

	if (taxonomy_exists('product_cat')) {
		foreach ($home_category_slugs as $slug) {
			$term = get_term_by('slug', $slug, 'product_cat');
			if (!$term || is_wp_error($term)) {
				continue;
			}
			$key = 'product_cat:' . (int) $term->term_id;
			if (!empty($existing_object_ids[$key])) {
				continue;
			}
			wp_update_nav_menu_item($menu_id, 0, [
				'menu-item-title'     => $term->name,
				'menu-item-object'    => 'product_cat',
				'menu-item-object-id' => (int) $term->term_id,
				'menu-item-type'      => 'taxonomy',
				'menu-item-status'    => 'publish',
			]);
		}
	}
});



