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
<body <?php body_class('bg-white text-[#003745]'); ?>>
<?php wp_body_open(); ?>

<header class="sticky top-0 z-40 border-b border-[#003745]/10 bg-[#FF2030]">
	<div class="mx-auto max-w-6xl px-6 py-2 md:py-3">
		<div class="flex items-center gap-3 md:grid md:grid-cols-[auto_1fr_auto] md:gap-3">
			<!-- Left: actions (mobile) / Right (desktop): actions -->
			<?php $cart_url = function_exists('wc_get_cart_url') ? wc_get_cart_url() : home_url('/cart'); ?>
			<?php
			$myaccount_url = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('myaccount') : wp_login_url();
			$myaccount_url = $myaccount_url ? $myaccount_url : home_url('/');
			?>
			<div class="order-3 flex items-center justify-end gap-2 md:order-3 md:justify-self-end md:gap-3">
				<?php if (is_user_logged_in()) : ?>
					<a class="flex h-10 w-10 items-center justify-center rounded-full border border-[#003745]/15 bg-white text-[#003745] transition hover:-translate-y-px hover:border-[#003745]/30 hover:shadow-sm" href="<?php echo esc_url($myaccount_url); ?>" aria-label="<?php echo esc_attr__('My account', 'woocommerce'); ?>">
						<svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" aria-hidden="true">
							<path stroke-linecap="round" stroke-linejoin="round" d="M20 21a8 8 0 1 0-16 0"></path>
							<path stroke-linecap="round" stroke-linejoin="round" d="M12 12a4 4 0 1 0-4-4 4 4 0 0 0 4 4Z"></path>
						</svg>
						<span class="sr-only"><?php esc_html_e('My account', 'woocommerce'); ?></span>
					</a>
				<?php endif; ?>

				<?php if (!is_user_logged_in()) : ?>
					<a class="hidden md:inline-flex <?php echo esc_attr(popbag_button_classes('outline', 'sm', 'h-10')); ?>" href="<?php echo esc_url($myaccount_url); ?>">
						<?php esc_html_e('Login', 'woocommerce'); ?>
					</a>
					<a class="hidden md:inline-flex <?php echo esc_attr(popbag_button_classes('secondary', 'sm', 'h-10')); ?>" href="<?php echo esc_url($myaccount_url); ?>">
						<?php esc_html_e('Register', 'woocommerce'); ?>
					</a>
				<?php endif; ?>

				<a href="<?php echo esc_url($cart_url); ?>" class="relative flex h-10 w-10 items-center justify-center rounded-full border border-[#003745]/15 bg-white text-[#003745] transition hover:-translate-y-px hover:border-[#003745]/30 hover:shadow-sm">
					<svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
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
					<svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" aria-hidden="true">
						<path stroke-linecap="round" d="M4 7h16M4 12h16M4 17h16"></path>
					</svg>
				</button>
			</div>

			<!-- Left (desktop): logo -->
			<div class="order-2 hidden md:flex md:order-1 md:items-center">
				<?php
				if (function_exists('popbag_render_site_logo')) {
					popbag_render_site_logo('', 'h-12 w-auto lg:h-14');
				}
				?>
			</div>

			<!-- Center: mobile logo / desktop menu -->
			<div class="order-1 flex min-w-0 flex-1 justify-start md:order-2 md:flex-none md:justify-center">
				<div class="md:hidden">
					<?php
					if (function_exists('popbag_render_site_logo')) {
						popbag_render_site_logo('', 'h-12 w-auto');
					}
					?>
				</div>
				<nav class="popbag-primary-nav hidden md:flex md:items-center md:justify-center">
					<?php
					wp_nav_menu([
						'theme_location' => 'primary',
						'container'      => false,
						'menu_class'     => 'menu flex flex-wrap items-center justify-center gap-x-6 gap-y-2 text-sm font-semibold uppercase tracking-[0.12em]',
						'fallback_cb'    => false,
						'walker'         => class_exists('Popbag_Walker_Primary_Nav') ? new Popbag_Walker_Primary_Nav() : null,
					]);
					?>
				</nav>
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
					<svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" aria-hidden="true">
						<path stroke-linecap="round" d="M6 6l12 12M18 6 6 18"></path>
					</svg>
				</button>
			</div>
			<nav class="popbag-mobile-nav px-5 py-4">
				<?php if (!is_user_logged_in()) : ?>
					<div class="mb-4 grid grid-cols-2 gap-2">
						<a class="flex items-center justify-center gap-2 rounded-[14px] border border-[#003745]/10 bg-white px-4 py-2 text-sm font-semibold uppercase tracking-[0.12em] text-[#003745]" href="<?php echo esc_url($myaccount_url); ?>">
							<svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" aria-hidden="true">
								<path stroke-linecap="round" stroke-linejoin="round" d="M15 3h4v4"></path>
								<path stroke-linecap="round" stroke-linejoin="round" d="M10 14 21 3"></path>
								<path stroke-linecap="round" stroke-linejoin="round" d="M21 14v6a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h6"></path>
							</svg>
							<span><?php esc_html_e('Login', 'woocommerce'); ?></span>
						</a>
						<a class="flex items-center justify-center gap-2 rounded-[14px] bg-[#003745] px-4 py-2 text-sm font-semibold uppercase tracking-[0.12em] text-white" href="<?php echo esc_url($myaccount_url); ?>">
							<svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" aria-hidden="true">
								<path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14"></path>
								<path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14"></path>
							</svg>
							<span><?php esc_html_e('Register', 'woocommerce'); ?></span>
						</a>
					</div>
				<?php endif; ?>
				<?php
				wp_nav_menu([
					'theme_location' => 'primary',
					'container'      => false,
					'menu_class'     => 'menu flex flex-col gap-3 text-base font-semibold uppercase tracking-[0.12em]',
					'fallback_cb'    => false,
					'walker'         => class_exists('Popbag_Walker_Primary_Nav') ? new Popbag_Walker_Primary_Nav() : null,
				]);
				?>
			</nav>
		</div>
	</div>
</header>

