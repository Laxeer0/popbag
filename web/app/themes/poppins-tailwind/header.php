<?php
/**
 * Header template.
 *
 * @package PoppinsTailwind
 */

?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>
<body <?php body_class('bg-stone-50 text-stone-900 antialiased'); ?>>
<?php wp_body_open(); ?>

<header class="sticky top-0 z-20 border-b border-stone-200 bg-white/80 backdrop-blur">
    <div class="mx-auto flex w-full max-w-6xl items-center justify-between px-6 py-4">
        <div class="text-lg font-semibold tracking-[0.4em]">
            <a href="<?php echo esc_url(home_url('/')); ?>">POPPINS</a>
        </div>

        <nav class="text-sm font-medium uppercase tracking-[0.35em]" aria-label="<?php esc_attr_e('Menu principale', 'poppins-tailwind'); ?>">
            <?php
            if (has_nav_menu('primary')) {
                wp_nav_menu(
                    [
                        'theme_location' => 'primary',
                        'container'      => false,
                        'menu_class'     => 'flex gap-6',
                        'fallback_cb'    => 'poppins_tailwind_menu_fallback',
                    ]
                );
            } else {
                poppins_tailwind_menu_fallback();
            }
            ?>
        </nav>

        <div class="flex items-center gap-3 text-xs uppercase tracking-[0.3em]">
            <a class="rounded-full border border-stone-900 px-4 py-2 transition hover:bg-stone-900 hover:text-white" href="<?php echo esc_url(home_url('/shop')); ?>">
                <?php esc_html_e('Shop', 'poppins-tailwind'); ?>
            </a>
            <?php if (class_exists('WooCommerce')) : ?>
                <a class="flex items-center gap-2 rounded-full bg-stone-900 px-4 py-2 text-white" href="<?php echo esc_url(wc_get_cart_url()); ?>">
                    <?php esc_html_e('Bag', 'poppins-tailwind'); ?>
                    <span class="rounded-full bg-white/20 px-2 py-0.5 text-xs font-semibold">
                        <?php echo esc_html(WC()->cart ? WC()->cart->get_cart_contents_count() : 0); ?>
                    </span>
                </a>
            <?php endif; ?>
        </div>
    </div>
</header>
