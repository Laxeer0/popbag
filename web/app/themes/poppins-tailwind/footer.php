<?php
/**
 * Footer template.
 *
 * @package PoppinsTailwind
 */

?>
<footer class="mt-16 bg-stone-900 py-10 text-stone-100">
    <div class="mx-auto flex w-full max-w-6xl flex-col gap-6 px-6 md:flex-row md:items-center md:justify-between">
        <p class="text-sm">&copy; <?php echo esc_html(date_i18n('Y')); ?> Poppins — <?php esc_html_e('Tessuti responsabili, made in Italy.', 'poppins-tailwind'); ?></p>

        <?php if (has_nav_menu('footer')) : ?>
            <?php
            wp_nav_menu(
                [
                    'theme_location' => 'footer',
                    'container'      => false,
                    'menu_class'     => 'flex flex-wrap gap-4 text-xs uppercase tracking-[0.3em]',
                ]
            );
            ?>
        <?php endif; ?>
    </div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
