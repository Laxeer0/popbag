<?php
if (!defined('ABSPATH')) {
	exit;
}
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo('charset'); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class('bg-[#F9E2B0] text-[#003745]'); ?>>
<?php wp_body_open(); ?>

<header class="sticky top-0 z-40 border-b border-[#003745]/10 bg-gradient-to-bl from-white to-[#FF2030]">
	<div class="mx-auto max-w-6xl px-6 py-2 md:py-3">
		<div class="grid grid-cols-[1fr_auto_1fr] items-center gap-3 md:grid-cols-[auto_1fr_auto]">
			<!-- Left (desktop): logo -->
			<div class="hidden md:flex md:items-center">
				<?php
				if (function_exists('popbag_render_site_logo')) {
					popbag_render_site_logo('', 'h-12 w-auto lg:h-14');
				}
				?>
			</div>

			<!-- Center: mobile logo / desktop menu -->
			<div class="min-w-0 justify-self-center md:justify-self-stretch">
				<div class="md:hidden">
					<?php
					if (function_exists('popbag_render_site_logo')) {
						popbag_render_site_logo('justify-self-center', 'h-12 w-auto');
					}
					?>
				</div>
				<nav class="hidden md:flex md:items-center md:justify-center">
					<?php
					wp_nav_menu([
						'theme_location' => 'primary',
						'container'      => false,
						'menu_class'     => 'flex flex-wrap items-center justify-center gap-x-6 gap-y-2 text-sm font-semibold uppercase tracking-[0.12em]',
						'fallback_cb'    => false,
					]);
					?>
				</nav>
			</div>

			<!-- Right: CTAs + (mobile) hamburger -->
			<?php $cart_url = function_exists('wc_get_cart_url') ? wc_get_cart_url() : home_url('/cart'); ?>
			<div class="flex items-center justify-self-end gap-2 md:gap-3">
				<form role="search" method="get" class="relative" action="<?php echo esc_url(home_url('/')); ?>">
					<label class="sr-only" for="popbag-search">Search</label>
					<input id="popbag-search" class="hidden" type="search" name="s" />
					<button type="submit" class="flex h-10 w-10 items-center justify-center rounded-full border border-[#003745]/15 bg-white text-[#003745] transition hover:-translate-y-px hover:border-[#003745]/30 hover:shadow-sm">
						<span aria-hidden="true">
							<svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
								<circle cx="11" cy="11" r="7"></circle>
								<path d="m16 16 4 4"></path>
							</svg>
						</span>
						<span class="sr-only"><?php esc_html_e('Search', 'popbag-minimal'); ?></span>
					</button>
				</form>
				<a href="<?php echo esc_url($cart_url); ?>" class="relative flex h-10 w-10 items-center justify-center rounded-full border border-[#003745]/15 bg-white text-[#003745] transition hover:-translate-y-px hover:border-[#003745]/30 hover:shadow-sm">
					<svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
						<path d="M3 4h2l1.6 9.2a1 1 0 0 0 1 .8h7.8a1 1 0 0 0 1-.8L17 7H6"></path>
						<circle cx="9" cy="19" r="1"></circle>
						<circle cx="15" cy="19" r="1"></circle>
					</svg>
					<?php if (function_exists('WC')) : ?>
						<span class="absolute -right-1 -top-1 inline-flex min-w-[1.4rem] items-center justify-center rounded-full bg-[#FF2030] px-1.5 text-xs font-bold text-white shadow-sm">
							<?php echo esc_html(WC()->cart->get_cart_contents_count()); ?>
						</span>
					<?php endif; ?>
					<span class="sr-only"><?php esc_html_e('View cart', 'popbag-minimal'); ?></span>
				</a>

				<button
					type="button"
					class="md:hidden flex h-10 w-10 items-center justify-center rounded-full border border-[#003745]/15 bg-white text-[#003745] transition hover:-translate-y-px hover:border-[#003745]/30 hover:shadow-sm"
					aria-label="<?php echo esc_attr__('Open menu', 'popbag-minimal'); ?>"
					aria-expanded="false"
					aria-controls="popbag-mobile-menu"
					data-popbag-menu-toggle
				>
					<svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
						<path stroke-linecap="round" d="M4 7h16M4 12h16M4 17h16"></path>
					</svg>
				</button>
			</div>
		</div>
	</div>

	<!-- Mobile menu overlay -->
	<div class="md:hidden">
		<div class="fixed inset-0 z-50 hidden bg-black/40" data-popbag-menu-backdrop></div>
		<div
			id="popbag-mobile-menu"
			class="fixed right-0 top-0 z-50 hidden h-full w-[85vw] max-w-sm border-l border-[#003745]/10 bg-white shadow-2xl"
			role="dialog"
			aria-modal="true"
			aria-label="<?php echo esc_attr__('Menu', 'popbag-minimal'); ?>"
			data-popbag-menu-panel
		>
			<div class="flex items-center justify-between border-b border-[#003745]/10 px-5 py-4">
				<span class="text-sm font-extrabold uppercase tracking-[0.12em] text-[#003745]"><?php echo esc_html(get_bloginfo('name')); ?></span>
				<button
					type="button"
					class="flex h-10 w-10 items-center justify-center rounded-full border border-[#003745]/15 bg-white text-[#003745]"
					aria-label="<?php echo esc_attr__('Close menu', 'popbag-minimal'); ?>"
					aria-expanded="true"
					data-popbag-menu-toggle
				>
					<svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
						<path stroke-linecap="round" d="M6 6l12 12M18 6 6 18"></path>
					</svg>
				</button>
			</div>
			<nav class="px-5 py-4">
				<?php
				wp_nav_menu([
					'theme_location' => 'primary',
					'container'      => false,
					'menu_class'     => 'flex flex-col gap-3 text-base font-semibold uppercase tracking-[0.12em]',
					'fallback_cb'    => false,
				]);
				?>
			</nav>
		</div>
	</div>
</header>

