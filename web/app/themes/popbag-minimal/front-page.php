<?php
if (!defined('ABSPATH')) {
	exit;
}

get_header();

$shop_url      = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : home_url('/shop');
$new_arrivals  = function_exists('popbag_get_new_arrivals') ? popbag_get_new_arrivals(6) : [];
$best_sellers  = function_exists('popbag_get_best_sellers') ? popbag_get_best_sellers(6) : [];
$size_terms    = function_exists('popbag_get_product_categories_cached') ? popbag_get_product_categories_cached(['number' => 3]) : [];
$collection_map = [
	'Small'  => 'small',
	'Medium' => 'medium',
	'Large'  => 'large',
];
?>

<main>
	<section class="relative overflow-hidden bg-white">
		<div class="mx-auto flex min-h-[70vh] max-w-6xl flex-col justify-center gap-10 px-6 py-16 md:flex-row md:items-center">
			<div class="max-w-2xl space-y-6">
				<p class="text-sm uppercase tracking-[0.3em] text-[#1F525E]">Minimal Premium</p>
				<h1 class="font-display text-5xl font-black leading-tight text-[#003745] md:text-6xl">FILL YOUR STYLE</h1>
				<p class="text-lg text-[#1F525E]">Borse essenziali, tagliate per una vita urbana elegante. Colori sobri, dettagli curati, spazio per tutto.</p>
				<div class="flex flex-wrap items-center gap-4">
					<a href="<?php echo esc_url($shop_url); ?>" class="rounded-full bg-[#FF2030] px-6 py-3 text-sm font-bold uppercase tracking-[0.18em] text-white shadow-sm transition hover:-translate-y-px hover:shadow-md">Shop</a>
					<a href="#sizes" class="rounded-full border border-[#003745] px-6 py-3 text-sm font-semibold uppercase tracking-[0.18em] text-[#003745] transition hover:-translate-y-px hover:border-[#003745]/60 hover:shadow-sm">Choose your size</a>
				</div>
			</div>
			<div class="relative w-full max-w-md self-end">
				<div class="aspect-[3/4] rounded-[16px] border border-[#003745]/10 bg-white shadow-sm"></div>
				<div class="absolute -left-6 -top-6 rounded-full border border-[#003745]/15 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-[0.2em] text-[#1F525E] shadow-sm">Minimal premium</div>
			</div>
		</div>
	</section>

	<section id="sizes" class="bg-white">
		<div class="mx-auto max-w-6xl px-6 py-16">
			<div class="flex items-end justify-between">
				<div>
					<p class="text-sm uppercase tracking-[0.3em] text-[#1F525E]">Collections</p>
					<h2 class="mt-2 text-3xl font-black text-[#003745]">Choose your size</h2>
				</div>
				<a href="<?php echo esc_url($shop_url); ?>" class="text-sm font-semibold text-[#003745] underline decoration-[#FF2030] decoration-2 underline-offset-4">Vedi tutto</a>
			</div>
			<div class="mt-10 grid gap-6 md:grid-cols-3">
				<?php
				foreach ($collection_map as $label => $slug) :
					$term_link = $shop_url;
					foreach ($size_terms as $term) {
						if ($term->slug === $slug) {
							$link = get_term_link($term);
							$term_link = is_wp_error($link) ? $shop_url : $link;
							break;
						}
					}
					?>
					<a href="<?php echo esc_url($term_link); ?>" class="group relative overflow-hidden rounded-[16px] border border-[#003745]/10 bg-[#F9E2B0]/60 p-6 transition hover:-translate-y-1 hover:shadow-md">
						<div class="flex items-center justify-between">
							<div>
								<p class="text-xs uppercase tracking-[0.2em] text-[#1F525E]">Collection</p>
								<h3 class="mt-2 text-2xl font-black text-[#003745] group-hover:underline"><?php echo esc_html($label); ?></h3>
								<p class="mt-2 text-sm text-[#1F525E]">Linee pulite, silhouette essenziale.</p>
							</div>
							<div class="flex h-14 w-14 items-center justify-center rounded-full border border-[#003745]/20 bg-white text-[#003745]">
								<svg class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
									<path d="M7 9v10h10V9"></path>
									<path d="M9 9a3 3 0 1 1 6 0"></path>
								</svg>
							</div>
						</div>
					</a>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

	<section class="bg-[#F9E2B0]">
		<div class="mx-auto max-w-6xl px-6 py-16">
			<div class="flex items-center justify-between">
				<div>
					<p class="text-sm uppercase tracking-[0.3em] text-[#1F525E]">New arrivals</p>
					<h2 class="mt-2 text-3xl font-black text-[#003745]">Appena arrivati</h2>
				</div>
				<a href="<?php echo esc_url($shop_url); ?>" class="text-sm font-semibold text-[#003745] underline decoration-[#FF2030] decoration-2 underline-offset-4">Shop</a>
			</div>
			<div class="mt-10 grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
				<?php
				if (!empty($new_arrivals)) :
					foreach ($new_arrivals as $product) :
						get_template_part('template-parts/product-card', null, ['product' => $product, 'show_badge' => true]);
					endforeach;
				else :
					?>
					<p class="text-[#1F525E]">Nessun prodotto disponibile.</p>
				<?php endif; ?>
			</div>
		</div>
	</section>

	<section class="bg-white">
		<div class="mx-auto max-w-6xl px-6 py-16">
			<div class="flex items-center justify-between">
				<div>
					<p class="text-sm uppercase tracking-[0.3em] text-[#1F525E]">Best sellers</p>
					<h2 class="mt-2 text-3xl font-black text-[#003745]">Scelti da chi ama il design</h2>
				</div>
				<a href="<?php echo esc_url($shop_url); ?>" class="text-sm font-semibold text-[#003745] underline decoration-[#FF2030] decoration-2 underline-offset-4">Vedi shop</a>
			</div>
			<div class="mt-10 grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
				<?php
				if (!empty($best_sellers)) :
					foreach ($best_sellers as $product) :
						get_template_part('template-parts/product-card', null, ['product' => $product, 'show_badge' => false]);
					endforeach;
				else :
					?>
					<p class="text-[#1F525E]">Ancora nessuna vendita registrata.</p>
				<?php endif; ?>
			</div>
		</div>
	</section>

	<section class="bg-[#003745] text-white">
		<div class="mx-auto max-w-6xl px-6 py-16">
			<div class="flex flex-col gap-8 md:flex-row md:items-center md:justify-between">
				<div>
					<p class="text-sm uppercase tracking-[0.3em] text-[#F9E2B0]">Materials / Benefits</p>
					<h2 class="mt-2 text-3xl font-black">Fatti per durare, disegnati per restare leggeri.</h2>
				</div>
				<a href="<?php echo esc_url($shop_url); ?>" class="rounded-full border border-white/30 px-5 py-3 text-sm font-semibold uppercase tracking-[0.18em] text-white transition hover:-translate-y-px hover:border-white hover:shadow-sm">Esplora materiali</a>
			</div>
			<div class="mt-10 grid gap-6 md:grid-cols-3">
				<div class="rounded-[16px] border border-white/10 bg-white/5 p-6 shadow-sm">
					<h3 class="text-lg font-bold">Pelle premium soft-touch</h3>
					<p class="mt-3 text-sm text-[#F9E2B0]">Texture morbida, resistenza quotidiana, colori stabili nel tempo.</p>
				</div>
				<div class="rounded-[16px] border border-white/10 bg-white/5 p-6 shadow-sm">
					<h3 class="text-lg font-bold">Spazio organizzato</h3>
					<p class="mt-3 text-sm text-[#F9E2B0]">Scomparti puliti, zip fluide, interni chiari per trovare tutto al volo.</p>
				</div>
				<div class="rounded-[16px] border border-white/10 bg-white/5 p-6 shadow-sm">
					<h3 class="text-lg font-bold">Leggerezza controllata</h3>
					<p class="mt-3 text-sm text-[#F9E2B0]">Bordo sottile, peso contenuto, bilanciamento studiato per la giornata.</p>
				</div>
			</div>
		</div>
	</section>
</main>

<?php
get_footer();

