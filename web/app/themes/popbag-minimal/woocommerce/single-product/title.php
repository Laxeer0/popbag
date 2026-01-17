<?php
/**
 * Product title (theme-styled) with back button + category label.
 *
 * @package WooCommerce\Templates
 * @version 3.6.0
 */

defined('ABSPATH') || exit;

global $product;

$back_url   = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : home_url('/shop');
$back_label = __('Indietro', 'popbag-minimal');

$cat_name = '';
$cat_url  = '';

if ($product instanceof WC_Product) {
	$terms = get_the_terms($product->get_id(), 'product_cat');
	if (is_array($terms) && !empty($terms) && !is_wp_error($terms)) {
		// Prefer the "deepest" category (child) to be more specific.
		usort($terms, static function ($a, $b) {
			if (!$a instanceof WP_Term || !$b instanceof WP_Term) {
				return 0;
			}
			return (int) $b->parent <=> (int) $a->parent;
		});

		$term = $terms[0] instanceof WP_Term ? $terms[0] : null;
		if ($term) {
			$cat_name = $term->name;
			$link = get_term_link($term);
			if (!is_wp_error($link)) {
				$cat_url  = (string) $link;
				$back_url = $cat_url;
			}
		}
	}
}
?>

<div class="mb-3 flex flex-wrap items-center gap-2 md:flex-nowrap">
	<a href="<?php echo esc_url($back_url); ?>" class="inline-flex items-center gap-2 rounded-full border border-[#003745]/15 bg-white px-4 py-2 text-xs font-bold uppercase tracking-[0.18em] text-[#003745] transition hover:-translate-y-px hover:shadow-sm">
		<svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" aria-hidden="true">
			<path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
		</svg>
		<span><?php echo esc_html($back_label); ?></span>
	</a>

	<?php if ($cat_name) : ?>
		<?php if ($cat_url) : ?>
			<a href="<?php echo esc_url($cat_url); ?>" class="ml-auto min-w-0 max-w-full truncate rounded-full border border-[#003745]/15 bg-[#003745]/5 px-4 py-2 text-xs font-bold uppercase tracking-[0.18em] text-[#003745] md:max-w-[320px]">
				<?php echo esc_html($cat_name); ?>
			</a>
		<?php else : ?>
			<span class="ml-auto min-w-0 max-w-full truncate rounded-full border border-[#003745]/15 bg-[#003745]/5 px-4 py-2 text-xs font-bold uppercase tracking-[0.18em] text-[#003745] md:max-w-[320px]">
				<?php echo esc_html($cat_name); ?>
			</span>
		<?php endif; ?>
	<?php endif; ?>
</div>

<h1 class="product_title entry-title text-3xl font-black text-[#003745] popbag-stroke-yellow">
	<?php the_title(); ?>
</h1>

