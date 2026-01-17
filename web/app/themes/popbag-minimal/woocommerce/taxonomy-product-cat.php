<?php
/**
 * Product category archive (theme-styled).
 *
 * @package WooCommerce\Templates
 */

defined('ABSPATH') || exit;

get_header('shop');

$term = get_queried_object();
$term = $term instanceof WP_Term ? $term : null;

$shop_url = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : home_url('/shop');

$thumb_url = '';
if ($term) {
	$thumb_id = (int) get_term_meta($term->term_id, 'thumbnail_id', true);
	if ($thumb_id) {
		$maybe = wp_get_attachment_image_url($thumb_id, 'large');
		if ($maybe) {
			$thumb_url = (string) $maybe;
		}
	}
}
?>

<main class="bg-white">
	<div class="mx-auto max-w-6xl px-6 py-12">
		<?php if (function_exists('woocommerce_output_all_notices')) : ?>
			<div class="mb-6"><?php woocommerce_output_all_notices(); ?></div>
		<?php endif; ?>

		<?php if (function_exists('woocommerce_breadcrumb')) : ?>
			<div class="mb-6"><?php woocommerce_breadcrumb(); ?></div>
		<?php endif; ?>

		<header class="mb-10 flex flex-col gap-6 border-b border-[#003745]/10 pb-6">
			<div class="min-w-0 text-center md:text-left">
				<a class="inline-flex items-center gap-2 rounded-full border border-[#003745]/15 bg-white px-4 py-2 text-xs font-bold uppercase tracking-[0.18em] text-[#003745] transition hover:-translate-y-px hover:shadow-sm" href="<?php echo esc_url($shop_url); ?>">
					<svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" aria-hidden="true">
						<path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
					</svg>
					<span><?php esc_html_e('Indietro', 'popbag-minimal'); ?></span>
				</a>

				<h1 class="mt-4 text-3xl font-black text-[#003745] popbag-stroke-yellow md:text-4xl">
					<?php echo esc_html($term ? $term->name : woocommerce_page_title(false)); ?>
				</h1>

				<?php if ($term && term_description($term)) : ?>
					<div class="mt-3 max-w-2xl text-sm text-[#1F525E]">
						<?php echo wp_kses_post(term_description($term)); ?>
					</div>
				<?php endif; ?>
			</div>

			<?php if ($thumb_url) : ?>
				<div class="mx-auto w-full max-w-[260px] overflow-hidden rounded-[16px] border border-[#003745]/10 bg-white shadow-sm md:mx-0 md:max-w-[240px]">
					<img src="<?php echo esc_url($thumb_url); ?>" alt="<?php echo esc_attr($term ? $term->name : ''); ?>" class="aspect-square w-full object-cover" loading="lazy" decoding="async" />
				</div>
			<?php endif; ?>
		</header>

		<?php
		// Optional: show child categories for SEO/internal linking.
		if ($term) {
			$children = get_terms([
				'taxonomy'   => 'product_cat',
				'parent'     => $term->term_id,
				'hide_empty' => true,
			]);
			if (!is_wp_error($children) && !empty($children)) :
				?>
				<section class="mb-10">
					<p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#1F525E]"><?php esc_html_e('Sottocategorie', 'popbag-minimal'); ?></p>
					<div class="mt-4 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
						<?php foreach ($children as $child) :
							if (!$child instanceof WP_Term) {
								continue;
							}
							$link = get_term_link($child);
							if (is_wp_error($link)) {
								continue;
							}
							$child_thumb = '';
							$child_thumb_id = (int) get_term_meta($child->term_id, 'thumbnail_id', true);
							if ($child_thumb_id) {
								$maybe = wp_get_attachment_image_url($child_thumb_id, 'medium_large');
								if ($maybe) {
									$child_thumb = (string) $maybe;
								}
							}
							?>
							<a href="<?php echo esc_url($link); ?>" class="group rounded-[16px] border border-[#003745]/10 bg-white p-4 shadow-sm transition hover:-translate-y-px hover:shadow-lg">
								<div class="flex items-start justify-between gap-4">
									<div>
										<p class="m-0 text-lg font-black text-[#003745]"><?php echo esc_html($child->name); ?></p>
										<p class="mt-1 text-xs font-semibold uppercase tracking-[0.18em] text-[#1F525E]">
											<?php echo esc_html(sprintf(_n('%s prodotto', '%s prodotti', (int) $child->count, 'popbag-minimal'), number_format_i18n((int) $child->count))); ?>
										</p>
									</div>
									<?php if ($child_thumb) : ?>
										<div class="h-16 w-16 overflow-hidden rounded-[14px] border border-[#003745]/10 bg-[#003745]/5">
											<img src="<?php echo esc_url($child_thumb); ?>" alt="<?php echo esc_attr($child->name); ?>" class="h-full w-full object-cover transition group-hover:scale-105" loading="lazy" decoding="async" />
										</div>
									<?php endif; ?>
								</div>
							</a>
						<?php endforeach; ?>
					</div>
				</section>
				<?php
			endif;
		}
		?>

		<?php if (woocommerce_product_loop()) : ?>
			<?php $cart_url = function_exists('wc_get_cart_url') ? wc_get_cart_url() : home_url('/cart'); ?>
			<div class="mb-6 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
				<div class="text-xs font-semibold uppercase tracking-[0.18em] text-[#1F525E]">
					<?php esc_html_e('Prodotti', 'popbag-minimal'); ?>
				</div>
				<div class="flex flex-wrap items-center justify-center gap-3 md:justify-end">
					<?php woocommerce_catalog_ordering(); ?>
					<a class="inline-flex items-center justify-center rounded-full border border-[#003745]/15 bg-white px-4 py-2 text-xs font-bold uppercase tracking-[0.18em] text-[#003745] transition hover:-translate-y-px hover:border-[#FF2030] hover:bg-[#FF2030] hover:text-white hover:shadow-sm" href="<?php echo esc_url($cart_url); ?>">
						<svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" aria-hidden="true">
							<path stroke-linecap="round" stroke-linejoin="round" d="M3 4h2l1.6 9.2a1 1 0 0 0 1 .8h7.8a1 1 0 0 0 1-.8L17 7H6" />
							<path stroke-linecap="round" stroke-linejoin="round" d="M9 19h.01M15 19h.01" />
						</svg>
						<span><?php esc_html_e('Carrello', 'popbag-minimal'); ?></span>
					</a>
				</div>
			</div>

			<?php woocommerce_product_loop_start(); ?>

			<?php if (wc_get_loop_prop('total')) : ?>
				<?php while (have_posts()) : ?>
					<?php the_post(); ?>
					<?php wc_get_template_part('content', 'product'); ?>
				<?php endwhile; ?>
			<?php endif; ?>

			<?php woocommerce_product_loop_end(); ?>

			<?php do_action('woocommerce_after_shop_loop'); ?>
		<?php else : ?>
			<?php do_action('woocommerce_no_products_found'); ?>
		<?php endif; ?>
	</div>
</main>

<?php
get_footer('shop');

