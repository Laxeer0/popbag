<?php
defined('ABSPATH') || exit;

get_header('shop');

do_action('woocommerce_before_single_product');

if (post_password_required()) {
	echo get_the_password_form();
	return;
}
?>

<main class="bg-white">
	<div class="mx-auto max-w-6xl px-6 py-12">
		<?php while (have_posts()) : ?>
			<?php the_post(); ?>
			<div id="product-<?php the_ID(); ?>" <?php wc_product_class('grid gap-10 md:grid-cols-2', get_the_ID()); ?>>
				<div class="space-y-4">
					<div class="rounded-[16px] border border-[#003745]/10 bg-white shadow-sm">
						<?php do_action('woocommerce_before_single_product_summary'); ?>
					</div>
				</div>
				<div class="summary entry-summary space-y-6 rounded-[16px] border border-[#003745]/10 bg-white p-6 shadow-sm">
					<div class="space-y-2">
						<?php do_action('woocommerce_single_product_summary'); ?>
					</div>
				</div>
			</div>
		<?php endwhile; ?>
	</div>
	<div class="mx-auto max-w-6xl px-6 pb-12">
		<?php do_action('woocommerce_after_single_product_summary'); ?>
	</div>
</main>

<?php
get_footer('shop');

