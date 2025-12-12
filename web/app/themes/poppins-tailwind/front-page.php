<?php
/**
 * Tailwind front page.
 *
 * @package PoppinsTailwind
 */

get_header();
?>

<main class="mx-auto flex w-full max-w-6xl flex-col gap-16 px-6 py-16">
    <section class="text-center">
        <p class="text-xs uppercase tracking-[0.6em] text-stone-500"><?php esc_html_e('Capsule FW25', 'poppins-tailwind'); ?></p>
        <h1 class="mt-6 text-4xl font-semibold tracking-tight text-stone-900 sm:text-6xl">
            <?php esc_html_e('Tailoring rilassato, anima contemporanea.', 'poppins-tailwind'); ?>
        </h1>
        <p class="mx-auto mt-4 max-w-2xl text-lg text-stone-600">
            <?php esc_html_e('Linee pulite, tessuti naturali e cromie neutre per un guardaroba senza stagione.', 'poppins-tailwind'); ?>
        </p>
        <div class="mt-8 flex flex-wrap justify-center gap-4">
            <a class="rounded-full bg-stone-900 px-6 py-3 text-sm font-semibold uppercase tracking-[0.3em] text-white" href="<?php echo esc_url(home_url('/shop')); ?>">
                <?php esc_html_e('Acquista ora', 'poppins-tailwind'); ?>
            </a>
            <a class="rounded-full border border-stone-900 px-6 py-3 text-sm font-semibold uppercase tracking-[0.3em]" href="#lookbook">
                <?php esc_html_e('Esplora lookbook', 'poppins-tailwind'); ?>
            </a>
        </div>
    </section>

    <section>
        <div class="flex flex-col gap-8 md:flex-row md:items-center">
            <div class="flex-1 space-y-4">
                <p class="text-xs uppercase tracking-[0.5em] text-stone-500"><?php esc_html_e('Manifesto', 'poppins-tailwind'); ?></p>
                <h2 class="text-3xl font-semibold">
                    <?php esc_html_e('Stile minimale, layering materico.', 'poppins-tailwind'); ?>
                </h2>
                <p class="text-stone-600">
                    <?php esc_html_e('Lavoriamo con filati selezionati e manifattura sartoriale per capi essenziali ma distintivi, pensati per mix & match quotidiani.', 'poppins-tailwind'); ?>
                </p>
                <ul class="grid gap-3 text-sm text-stone-700 sm:grid-cols-2">
                    <li class="rounded-2xl border border-stone-200 bg-white p-4 shadow-sm"><?php esc_html_e('Tessuti certificati e tracciabili', 'poppins-tailwind'); ?></li>
                    <li class="rounded-2xl border border-stone-200 bg-white p-4 shadow-sm"><?php esc_html_e('Palette neutre con accenti bronzo', 'poppins-tailwind'); ?></li>
                    <li class="rounded-2xl border border-stone-200 bg-white p-4 shadow-sm"><?php esc_html_e('Packaging plastic-free', 'poppins-tailwind'); ?></li>
                    <li class="rounded-2xl border border-stone-200 bg-white p-4 shadow-sm"><?php esc_html_e('Produzione limitata e responsabile', 'poppins-tailwind'); ?></li>
                </ul>
            </div>
            <div class="flex-1 rounded-[32px] bg-gradient-to-br from-stone-200 via-stone-100 to-white p-12 text-center shadow-inner">
                <p class="text-xs uppercase tracking-[0.5em] text-stone-500"><?php esc_html_e('Look del mese', 'poppins-tailwind'); ?></p>
                <p class="mt-6 text-2xl font-semibold text-stone-900"><?php esc_html_e('Blazer destrutturato + pantalone wide leg + camicia voile', 'poppins-tailwind'); ?></p>
                <p class="mt-4 text-sm text-stone-600"><?php esc_html_e('Disponibile in crema, cenere e bronzo.', 'poppins-tailwind'); ?></p>
            </div>
        </div>
    </section>

    <section id="lookbook">
        <p class="text-xs uppercase tracking-[0.5em] text-stone-500"><?php esc_html_e('Lookbook', 'poppins-tailwind'); ?></p>
        <h2 class="mt-4 text-3xl font-semibold"><?php esc_html_e('Moodboard urbano', 'poppins-tailwind'); ?></h2>
        <div class="mt-8 grid gap-6 md:grid-cols-2">
            <?php
            $lookbook = [
                ['title' => __('Soft tailoring', 'poppins-tailwind'), 'body' => __('Sagome fluide per riunioni e after-hours.', 'poppins-tailwind')],
                ['title' => __('Resort 25 preview', 'poppins-tailwind'), 'body' => __('Linee asimmetriche e toni sabbia.', 'poppins-tailwind')],
                ['title' => __('Night bloom', 'poppins-tailwind'), 'body' => __('Satin liquidi e gioielli minimali.', 'poppins-tailwind')],
                ['title' => __('Knit lounge', 'poppins-tailwind'), 'body' => __('Maglieria leggera monocromatica.', 'poppins-tailwind')],
            ];
            foreach ($lookbook as $card) :
                ?>
                <article class="rounded-[32px] border border-stone-200 bg-white/70 p-8 shadow-lg shadow-stone-200">
                    <h3 class="text-2xl font-semibold text-stone-900"><?php echo esc_html($card['title']); ?></h3>
                    <p class="mt-3 text-stone-600"><?php echo esc_html($card['body']); ?></p>
                </article>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="rounded-[32px] bg-white p-10 shadow-2xl shadow-stone-200">
        <div class="flex flex-col gap-8 md:flex-row md:items-center">
            <div class="flex-1 space-y-2">
                <p class="text-xs uppercase tracking-[0.5em] text-stone-500"><?php esc_html_e('Journal', 'poppins-tailwind'); ?></p>
                <h2 class="text-3xl font-semibold"><?php esc_html_e('Ricevi inviti privati e anteprime.', 'poppins-tailwind'); ?></h2>
                <p class="text-stone-600"><?php esc_html_e('Una newsletter al mese con editorials, trunk show e capsule limitate.', 'poppins-tailwind'); ?></p>
            </div>
            <form class="flex flex-1 flex-col gap-4 md:flex-row" action="#" method="post">
                <label class="sr-only" for="newsletter-email"><?php esc_html_e('Email', 'poppins-tailwind'); ?></label>
                <input class="flex-1 rounded-full border border-stone-300 px-5 py-3 text-stone-900 focus:border-stone-500 focus:outline-none" id="newsletter-email" type="email" name="newsletter-email" placeholder="<?php esc_attr_e('La tua email', 'poppins-tailwind'); ?>">
                <button class="rounded-full bg-stone-900 px-6 py-3 text-sm font-semibold uppercase tracking-[0.3em] text-white" type="submit">
                    <?php esc_html_e('Iscrivimi', 'poppins-tailwind'); ?>
                </button>
            </form>
        </div>
    </section>
</main>

<?php
get_footer();
