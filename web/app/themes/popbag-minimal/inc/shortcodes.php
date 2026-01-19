<?php
/**
 * Shortcodes to make mockup sections editable in Gutenberg without extra plugins.
 *
 * Usage examples (in a Shortcode block):
 * - [popbag_product_swiper title="Bags" subtitle="Shop the essentials" source="new" limit="12" cta_label="Vedi tutto" cta_url="/shop/"]
 * - [popbag_product_swiper title="Vintage" source="category" category="vintage" limit="12"]
 */
if (!defined('ABSPATH')) {
	exit;
}

add_action('init', static function (): void {
	add_shortcode('popbag_product_swiper', 'popbag_shortcode_product_swiper');
});

function popbag_shortcode_product_swiper(array $atts = []): string {
	if (!function_exists('popbag_has_woocommerce') || !popbag_has_woocommerce()) {
		return '';
	}

	$atts = shortcode_atts([
		'title'     => '',
		'subtitle'  => '',
		'source'    => 'new', // new|best|category
		'category'  => '',
		'limit'     => 12,
		'cta_label' => '',
		'cta_url'   => '',
	], $atts, 'popbag_product_swiper');

	$title     = sanitize_text_field($atts['title']);
	$subtitle  = sanitize_text_field($atts['subtitle']);
	$source    = sanitize_key($atts['source']);
	$category  = sanitize_title($atts['category']);
	$limit     = max(1, min(24, absint($atts['limit'])));
	$cta_label = sanitize_text_field($atts['cta_label']);
	$cta_url   = $atts['cta_url'] ? esc_url_raw($atts['cta_url']) : '';

	$products = [];
	if ('best' === $source && function_exists('popbag_get_best_sellers')) {
		$products = popbag_get_best_sellers($limit);
	} elseif ('category' === $source && $category && function_exists('popbag_get_products_by_category_slug')) {
		$products = popbag_get_products_by_category_slug($category, $limit);
	} elseif (function_exists('popbag_get_new_arrivals')) {
		$products = popbag_get_new_arrivals($limit);
	}

	ob_start();
	?>
	<section class="bg-white">
		<div class="mx-auto max-w-6xl px-6 py-16">
			<div class="flex flex-wrap items-end justify-between gap-4">
				<div>
					<?php if ($subtitle) : ?>
						<p class="text-xl font-extrabold uppercase tracking-[0.3em] text-[#F9E2B0] popbag-stroke-blue"><?php echo esc_html($subtitle); ?></p>
					<?php endif; ?>
					<?php if ($title) : ?>
						<h2 class="mt-1 text-3xl font-black text-[#003745] popbag-stroke-yellow"><?php echo esc_html($title); ?></h2>
					<?php endif; ?>
				</div>
				<div class="flex items-center gap-3">
					<?php if ($cta_label && $cta_url) : ?>
						<a href="<?php echo esc_url($cta_url); ?>" class="text-sm font-semibold text-[#FF2030] underline decoration-[#FF2030] decoration-2 underline-offset-4"><?php echo esc_html($cta_label); ?></a>
					<?php endif; ?>
				</div>
			</div>

			<div class="mt-10" data-popbag-swiper>
				<div class="mb-4 flex items-center justify-end gap-3">
					<button type="button" class="flex h-10 w-10 items-center justify-center rounded-full border border-[#003745]/15 bg-white text-[#003745]" aria-label="<?php echo esc_attr__('Prev', 'popbag-minimal'); ?>" data-popbag-swiper-prev>
						<svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
					</button>
					<button type="button" class="flex h-10 w-10 items-center justify-center rounded-full border border-[#003745]/15 bg-white text-[#003745]" aria-label="<?php echo esc_attr__('Next', 'popbag-minimal'); ?>" data-popbag-swiper-next>
						<svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
					</button>
				</div>
				<div class="swiper overflow-hidden">
					<div class="swiper-wrapper" style="padding-bottom: 18px;">
						<?php if (!empty($products)) : ?>
							<?php foreach ($products as $product) : ?>
								<div class="swiper-slide h-auto">
									<?php get_template_part('template-parts/product-card', null, ['product' => $product, 'show_badge' => true]); ?>
								</div>
							<?php endforeach; ?>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>
	</section>
	<?php
	return (string) ob_get_clean();
}


