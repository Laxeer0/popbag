<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Theme setup.
 */
function poppins_tailwind_setup(): void
{
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('woocommerce');
    register_nav_menus(
        [
            'primary' => __('Menu principale', 'poppins-tailwind'),
            'footer'  => __('Menu footer', 'poppins-tailwind'),
        ]
    );
}
add_action('after_setup_theme', 'poppins_tailwind_setup');

/**
 * Enqueue compiled assets.
 */
function poppins_tailwind_enqueue_assets(): void
{
    $theme = wp_get_theme();
    wp_enqueue_style(
        'poppins-tailwind',
        get_theme_file_uri('dist/css/main.css'),
        [],
        $theme->get('Version')
    );
}
add_action('wp_enqueue_scripts', 'poppins_tailwind_enqueue_assets');

/**
 * Utility: fallback menu.
 */
function poppins_tailwind_menu_fallback(): void
{
    echo '<ul class="flex gap-4 text-sm uppercase tracking-[0.2em]"><li><a href="' . esc_url(admin_url('nav-menus.php')) . '">' . esc_html__('Configura il menu', 'poppins-tailwind') . '</a></li></ul>';
}
