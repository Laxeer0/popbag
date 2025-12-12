<?php
/**
 * Fallback loop.
 *
 * @package PoppinsTailwind
 */

get_header();
?>

<main class="mx-auto w-full max-w-3xl px-6 py-12">
    <?php if (have_posts()) : ?>
        <?php while (have_posts()) : ?>
            <?php the_post(); ?>
            <article id="post-<?php the_ID(); ?>" <?php post_class('prose prose-stone mb-16'); ?>>
                <h1><?php the_title(); ?></h1>
                <div><?php the_content(); ?></div>
            </article>
        <?php endwhile; ?>
    <?php else : ?>
        <p class="text-center text-stone-500"><?php esc_html_e('Non ci sono contenuti.', 'poppins-tailwind'); ?></p>
    <?php endif; ?>
</main>

<?php
get_footer();
