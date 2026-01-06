<?php
/**
 * Theme bootstrap.
 */

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Theme setup.
 */
function popbag_theme_setup(): void {
	add_theme_support('title-tag');
	add_theme_support('post-thumbnails');
	add_theme_support('woocommerce');
	add_theme_support('html5', ['search-form', 'gallery', 'caption', 'style', 'script']);
	add_theme_support('align-wide');
	add_theme_support('responsive-embeds');
	register_nav_menus([
		'primary' => __('Primary Menu', 'popbag-minimal'),
		'footer'  => __('Footer Menu', 'popbag-minimal'),
	]);
}
add_action('after_setup_theme', 'popbag_theme_setup');

/**
 * Enqueue assets.
 */
function popbag_enqueue_assets(): void {
	$theme      = wp_get_theme();
	$cache_bust = file_exists(get_template_directory() . '/dist/app.css') ? filemtime(get_template_directory() . '/dist/app.css') : $theme->get('Version');

	wp_enqueue_style(
		'popbag-app',
		get_template_directory_uri() . '/dist/app.css',
		[],
		$cache_bust
	);

	$header_js_path = get_template_directory() . '/assets/js/header.js';
	if (file_exists($header_js_path)) {
		wp_enqueue_script(
			'popbag-header',
			get_template_directory_uri() . '/assets/js/header.js',
			[],
			filemtime($header_js_path),
			true
		);
	}
}
add_action('wp_enqueue_scripts', 'popbag_enqueue_assets');

/**
 * Theme asset helpers.
 */
function popbag_asset_uri(string $relative_path): string {
	$relative_path = ltrim($relative_path, '/');
	return get_template_directory_uri() . '/' . $relative_path;
}

/**
 * Render site logo (navbar).
 *
 * Neutral component: uses <img> and does not alter the SVG.
 */
function popbag_render_site_logo(string $wrapper_class = '', string $img_class = 'h-12 w-auto md:h-14 lg:h-16'): void {
	$logo_src = popbag_asset_uri('assets/images/logo-orizzontale.svg');
	$site_name = get_bloginfo('name');
	?>
	<a href="<?php echo esc_url(home_url('/')); ?>" class="<?php echo esc_attr(trim('flex items-center ' . $wrapper_class)); ?>" aria-label="<?php echo esc_attr($site_name); ?>">
		<img
			src="<?php echo esc_url($logo_src); ?>"
			alt="<?php echo esc_attr($site_name); ?>"
			class="<?php echo esc_attr($img_class); ?>"
			loading="eager"
			decoding="async"
		/>
	</a>
	<?php
}

/**
 * Disable WooCommerce default styles to rely on Tailwind layer.
 */
add_filter('woocommerce_enqueue_styles', '__return_empty_array');

/**
 * Cache helper with versioned transients.
 */
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

/**
 * Invalidate cached datasets by bumping version.
 */
function popbag_bump_cache_version(): void {
	$version = (int) get_option('popbag_cache_v', 1);
	update_option('popbag_cache_v', $version + 1, false);
}

/**
 * Register cache invalidation hooks.
 */
function popbag_register_cache_invalidation(): void {
	add_action('save_post_product', 'popbag_bump_cache_version');
	add_action('deleted_post', function (int $post_id) {
		if ('product' === get_post_type($post_id)) {
			popbag_bump_cache_version();
		}
	});
	add_action('transition_post_status', function ($new_status, $old_status, $post) {
		if ('product' === $post->post_type && $new_status !== $old_status) {
			popbag_bump_cache_version();
		}
	}, 10, 3);

	add_action('woocommerce_update_product', 'popbag_bump_cache_version');
	add_action('woocommerce_product_set_stock', 'popbag_bump_cache_version');

	$term_bump = static function ($term_id, $tt_id, $taxonomy) {
		if ('product_cat' === $taxonomy) {
			popbag_bump_cache_version();
		}
	};

	add_action('created_term', $term_bump, 10, 3);
	add_action('edited_term', $term_bump, 10, 3);
	add_action('delete_term', $term_bump, 10, 3);
}
add_action('init', 'popbag_register_cache_invalidation');

/**
 * Cached product helpers.
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



