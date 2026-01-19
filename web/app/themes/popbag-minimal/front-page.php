<?php
if (!defined('ABSPATH')) {
	exit;
}

get_header();

$shop_url      = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : home_url('/shop');
?>

<main>
	<section class="relative overflow-hidden bg-white">
		<div class="mx-auto flex max-w-6xl flex-col justify-center gap-10 px-6 pb-12 pt-6 md:flex-row md:items-center md:py-16">
			<div class="max-w-2xl space-y-4 text-center md:text-left">
				<h1 class="m-0 font-display text-5xl font-black leading-tight text-[#003745] popbag-stroke-yellow md:text-6xl">Crea la tua Bag</h1>
				<p class="m-0 text-lg text-[#1F525E]">Scegli una bag, seleziona i capi disponibili e aggiungila al carrello: paghi solo il prezzo della bag e trovi al suo interno l’elenco dei capi scelti.</p>
				<div class="flex flex-wrap items-center justify-center gap-3 pt-2 md:justify-start">
					<a href="<?php echo esc_url(post_type_exists('poppins_bag') ? (string) get_post_type_archive_link('poppins_bag') : home_url('/bags/')); ?>" class="rounded-full bg-[#FF2030] px-6 py-3 text-sm font-bold uppercase tracking-[0.18em] text-white shadow-sm transition hover:-translate-y-px hover:shadow-md">Scopri le bag</a>
					<a href="<?php echo esc_url($shop_url); ?>" class="rounded-full border border-[#003745] px-6 py-3 text-sm font-semibold uppercase tracking-[0.18em] text-[#003745] transition hover:-translate-y-px hover:border-[#003745]/60 hover:shadow-sm">Vedi lo shop</a>
				</div>
			</div>
			<div class="relative w-full max-w-md self-center md:self-end">
				<div class="overflow-hidden rounded-[16px] border border-[#003745]/10 bg-white shadow-sm">
					<img
						src="<?php echo esc_url(function_exists('popbag_asset_uri') ? popbag_asset_uri('assets/images/hero.png') : get_theme_file_uri('assets/images/hero.png')); ?>"
						alt="<?php echo esc_attr__('Hero bag', 'popbag-minimal'); ?>"
						class="aspect-[3/4] h-full w-full object-cover"
						loading="eager"
						decoding="async"
					/>
				</div>
			</div>
		</div>
	</section>

	<?php
	// Bags section (CPT poppins_bag) right after hero.
	$bags_url = post_type_exists('poppins_bag') ? (string) get_post_type_archive_link('poppins_bag') : home_url('/bags/');
	$bags = function_exists('popbag_get_bag_posts') ? popbag_get_bag_posts(12) : [];
	?>
	<section class="bg-white" style="background: rgba(255, 32, 48, 1);">
		<div class="mx-auto max-w-6xl px-6 py-16">
			<div class="flex flex-wrap items-end justify-between gap-4">
				<div>
					<?php if (!empty($bags)) : ?>
						<p class="text-xl font-extrabold uppercase tracking-[0.3em] text-[#F9E2B0] popbag-stroke-blue">Bag</p>
						<h2 class="mt-0 text-4xl font-black text-[#003745] popbag-stroke-yellow md:text-5xl">Le nostre bag</h2>
					<?php else : ?>
						<p class="text-xl font-extrabold uppercase tracking-[0.3em] text-[#F9E2B0] popbag-stroke-blue">Bag</p>
						<h2 class="mt-0 text-4xl font-black text-[#003745] popbag-stroke-yellow md:text-5xl">Bag</h2>
					<?php endif; ?>
				</div>
				<a href="<?php echo esc_url($bags_url); ?>" class="text-sm font-semibold text-[#FF2030] underline decoration-[#FF2030] decoration-2 underline-offset-4">Vedi tutto</a>
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
						<?php if (!empty($bags)) : ?>
							<?php foreach ($bags as $bag_post) : ?>
								<?php if (!$bag_post instanceof WP_Post) { continue; } ?>
								<?php
								// Ensure template parts relying on global $post behave correctly.
								global $post;
								$post = $bag_post;
								setup_postdata($post);
								?>
								<div class="swiper-slide h-auto">
									<?php get_template_part('template-parts/bag-card'); ?>
								</div>
							<?php endforeach; ?>
							<?php wp_reset_postdata(); ?>
						<?php else : ?>
							<div class="swiper-slide h-auto">
								<div class="rounded-[16px] border border-[#003745]/10 bg-[#003745]/5 p-6 text-sm text-[#1F525E]">
									<?php esc_html_e('No bags available yet.', 'popbag-minimal'); ?>
								</div>
							</div>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>
	</section>

	<?php
	// Category swipers after Bags.
	if (function_exists('popbag_shortcode_product_swiper')) {
		$term_link = static function (string $slug) use ($shop_url): string {
			$term = get_term_by('slug', $slug, 'product_cat');
			if (!$term || is_wp_error($term)) {
				return (string) $shop_url;
			}
			$link = get_term_link($term);
			return is_wp_error($link) ? (string) $shop_url : (string) $link;
		};

		$render_missing_category = static function (string $title, string $slug): void {
			?>
			<section class="bg-white">
				<div class="mx-auto max-w-6xl px-6 py-16">
					<h2 class="mt-1 text-3xl font-black text-[#003745] popbag-stroke-yellow"><?php echo esc_html($title); ?></h2>
					<div class="mt-6 rounded-[16px] border border-[#003745]/10 bg-[#003745]/5 p-5 text-sm text-[#1F525E]">
						<?php
						printf(
							/* translators: %s: category slug */
							esc_html__('Categoria non trovata (slug: %s). Creala in Prodotti → Categorie e assegna prodotti pubblicati.', 'popbag-minimal'),
							esc_html($slug)
						);
						?>
					</div>
				</div>
			</section>
			<?php
		};

		$render_empty_category = static function (string $title, string $slug, string $cta_url): void {
			?>
			<section class="bg-white">
				<div class="mx-auto max-w-6xl px-6 py-16">
					<div class="flex flex-wrap items-end justify-between gap-4">
						<div>
							<h2 class="mt-1 text-3xl font-black text-[#003745] popbag-stroke-yellow"><?php echo esc_html($title); ?></h2>
						</div>
						<a href="<?php echo esc_url($cta_url); ?>" class="text-sm font-semibold text-[#FF2030] underline decoration-[#FF2030] decoration-2 underline-offset-4">Vedi categoria</a>
					</div>
					<div class="mt-6 rounded-[16px] border border-[#003745]/10 bg-[#003745]/5 p-5 text-sm text-[#1F525E]">
						<?php
						printf(
							/* translators: %s: category slug */
							esc_html__('Nessun prodotto pubblicato/visibile nella categoria (slug: %s).', 'popbag-minimal'),
							esc_html($slug)
						);
						?>
					</div>
				</div>
			</section>
			<?php
		};

		$render_category_swiper = static function (string $title, string $slug) use ($term_link, $shop_url, $render_missing_category, $render_empty_category): void {
			$term = get_term_by('slug', $slug, 'product_cat');
			if (!$term || is_wp_error($term)) {
				$render_missing_category($title, $slug);
				return;
			}

			$products = function_exists('popbag_get_products_by_category_slug') ? popbag_get_products_by_category_slug($slug, 12) : [];
			$cta_url  = $term_link($slug);

			if (empty($products)) {
				$render_empty_category($title, $slug, $cta_url);
				return;
			}

			echo popbag_shortcode_product_swiper([
				'title'     => $title,
				'subtitle'  => '',
				'source'    => 'category',
				'category'  => $slug,
				'limit'     => 12,
				'cta_label' => 'Vedi categoria',
				'cta_url'   => $cta_url ? $cta_url : (string) $shop_url,
			]);
		};

		$homepage_categories = [
			['Levi’s', 'levis'],
			['Felpe', 'felpe'],
			['Giubbotti', 'giubbotti'],
			['Pantaloni', 'pantaloni'],
			['Maglieria', 'maglieria'],
			['Tute', 'tute'],
			['Profumi', 'profumi'],
		];

		foreach ($homepage_categories as [$title, $slug]) {
			$render_category_swiper($title, $slug);
		}
	}
	?>
</main>

<?php
get_footer();

