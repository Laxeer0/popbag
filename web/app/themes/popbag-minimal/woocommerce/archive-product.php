<?php
defined('ABSPATH') || exit;

get_header('shop');
?>

<main class="bg-white">
	<div class="mx-auto max-w-6xl px-6 py-12">
		<?php if (function_exists('woocommerce_output_all_notices')) : ?>
			<div class="mb-6"><?php woocommerce_output_all_notices(); ?></div>
		<?php endif; ?>

		<?php if (function_exists('woocommerce_breadcrumb')) : ?>
			<div class="mb-6"><?php woocommerce_breadcrumb(); ?></div>
		<?php endif; ?>

		<header class="mb-10 flex flex-col gap-4 border-b border-[#003745]/10 pb-6 md:flex-row md:items-center md:justify-between">
			<div>
				<h1 class="text-3xl font-black text-[#003745] popbag-stroke-yellow"><?php woocommerce_page_title(); ?></h1>
				<?php if (apply_filters('woocommerce_show_page_title', true)) : ?>
					<p class="mt-1 text-xl font-extrabold uppercase tracking-[0.18em] text-[#F9E2B0] popbag-stroke-blue">Prodotti</p>
				<?php endif; ?>
			</div>
			<div class="flex items-center gap-4">
				<?php woocommerce_catalog_ordering(); ?>
			</div>
		</header>

		<?php if (woocommerce_product_loop()) : ?>
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



