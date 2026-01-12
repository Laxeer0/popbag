<?php
if (!defined('ABSPATH')) {
	exit;
}

$permalink = get_permalink();
$title = get_the_title();
?>

<article class="group h-full rounded-[16px] border border-[#003745]/10 bg-white p-4 shadow-sm transition hover:-translate-y-1 hover:shadow-lg">
	<a href="<?php echo esc_url($permalink); ?>" class="flex h-full flex-col gap-4">
		<div class="flex items-center justify-center overflow-hidden rounded-[14px] border border-[#003745]/10 bg-[#003745]/5 p-6">
			<?php if (has_post_thumbnail()) : ?>
				<?php the_post_thumbnail('thumbnail', ['class' => 'h-16 w-16 object-contain transition duration-300 group-hover:scale-105']); ?>
			<?php else : ?>
				<?php $ph = function_exists('popbag_asset_uri') ? popbag_asset_uri('assets/images/placeholder-bag.svg') : ''; ?>
				<?php if ($ph) : ?>
					<img src="<?php echo esc_url($ph); ?>" alt="<?php echo esc_attr($title); ?>" class="h-16 w-16 object-contain opacity-70 transition duration-300 group-hover:scale-105" loading="lazy" decoding="async" />
				<?php endif; ?>
			<?php endif; ?>
		</div>

		<div class="flex items-start justify-between gap-4">
			<h2 class="text-lg font-black text-[#003745] underline-offset-4 transition group-hover:underline">
				<?php echo esc_html($title); ?>
			</h2>
			<?php if (function_exists('popbag_get_bag_data')) :
				$bag = popbag_get_bag_data(get_the_ID());
				?>
				<span class="text-xs font-semibold uppercase tracking-[0.18em] text-[#1F525E]">
					<?php
					printf(
						/* translators: %d: capacity */
						esc_html__('%d capi', 'popbag-minimal'),
						absint($bag['capacity'] ?? 1)
					);
					?>
				</span>
			<?php endif; ?>
		</div>

		<?php if (get_the_excerpt()) : ?>
			<p class="text-sm text-[#1F525E]"><?php echo esc_html(get_the_excerpt()); ?></p>
		<?php endif; ?>
	</a>
</article>



