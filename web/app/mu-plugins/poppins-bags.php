<?php

/**
 * Gestione dinamica delle bag (creazione da dashboard + assegnazione prodotti).
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Disabilita Gutenberg (block editor) per il post type delle bag.
 */
add_filter(
    'use_block_editor_for_post_type',
    static function (bool $use_block_editor, string $post_type): bool {
        if ($post_type === 'poppins_bag') {
            return false;
        }

        return $use_block_editor;
    },
    10,
    2,
);

/**
 * Registra il Custom Post Type "Bag".
 */
add_action(
    'init',
    static function (): void {
        $labels = [
            'name'               => __('Bag', 'poppins'),
            'singular_name'      => __('Bag', 'poppins'),
            'add_new'            => __('Aggiungi bag', 'poppins'),
            'add_new_item'       => __('Nuova bag', 'poppins'),
            'edit_item'          => __('Modifica bag', 'poppins'),
            'new_item'           => __('Nuova bag', 'poppins'),
            'view_item'          => __('Vedi bag', 'poppins'),
            'search_items'       => __('Cerca bag', 'poppins'),
            'not_found'          => __('Nessuna bag trovata', 'poppins'),
            'menu_name'          => __('Bag', 'poppins'),
        ];

        register_post_type(
            'poppins_bag',
            [
                'labels'        => $labels,
                'public'        => true,
                'show_ui'       => true,
                'show_in_menu'  => true,
                'has_archive'   => true,
                'rewrite'       => [
                    'slug'       => 'bags',
                    'with_front' => false,
                ],
                'capability_type' => 'post',
                'supports'      => ['title', 'editor', 'excerpt', 'thumbnail'],
                'menu_icon'     => 'dashicons-shopping-bag',
                'menu_position' => 25,
                'show_in_rest'  => true,
            ],
        );
    },
);

/**
 * Aggiunge le meta box per capienza e slug.
 */
add_action(
    'add_meta_boxes',
    static function (): void {
        add_meta_box(
            'poppins_bag_details',
            __('Dettagli bag', 'poppins'),
            function (WP_Post $post): void {
                $capacity        = (int) get_post_meta($post->ID, '_poppins_bag_capacity', true);
                $slug            = get_post_meta($post->ID, '_poppins_bag_slug', true);
                $category_limits = (array) get_post_meta($post->ID, '_poppins_bag_category_limits', true);
                $categories      = get_terms(
                    [
                        'taxonomy'   => 'product_cat',
                        'hide_empty' => false,
                    ],
                );
                wp_nonce_field('poppins_save_bag_details', 'poppins_bag_nonce');
                ?>
                <p>
                    <label for="poppins_bag_slug"><?php esc_html_e('Identificativo unico (es. bag_l)', 'poppins'); ?></label><br>
                    <input type="text" id="poppins_bag_slug" name="poppins_bag_slug" value="<?php echo esc_attr($slug); ?>" class="regular-text" required>
                </p>
                <p>
                    <label for="poppins_bag_capacity"><?php esc_html_e('Capienza massima (numero prodotti)', 'poppins'); ?></label><br>
                    <input type="number" min="1" id="poppins_bag_capacity" name="poppins_bag_capacity" value="<?php echo esc_attr($capacity ?: 1); ?>">
                </p>
                <?php if ($categories && !is_wp_error($categories)) : ?>
                    <hr>
                    <p><strong><?php esc_html_e('Limiti per categoria', 'poppins'); ?></strong></p>
                    <p class="description"><?php esc_html_e('Imposta il numero massimo di capi consentiti per ciascuna categoria nella bag. Lascia vuoto o 0 per nessun limite.', 'poppins'); ?></p>
                    <table class="widefat striped" style="max-width:640px;">
                        <thead>
                            <tr>
                                <th><?php esc_html_e('Categoria', 'poppins'); ?></th>
                                <th width="120"><?php esc_html_e('Max capi', 'poppins'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categories as $category) : ?>
                                <tr>
                                    <td><?php echo esc_html($category->name); ?></td>
                                    <td>
                                        <input type="number" min="0" name="poppins_bag_category_limits[<?php echo esc_attr($category->term_id); ?>]" value="<?php echo esc_attr($category_limits[$category->term_id] ?? ''); ?>" style="width:100%;">
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
                <?php
            },
            'poppins_bag',
            'normal',
            'default',
        );
    },
);

/**
 * Salva le meta delle bag.
 */
add_action(
    'save_post_poppins_bag',
    static function (int $post_id, WP_Post $post): void {
        if (!isset($_POST['poppins_bag_nonce']) || !wp_verify_nonce($_POST['poppins_bag_nonce'], 'poppins_save_bag_details')) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        $slug     = sanitize_title($_POST['poppins_bag_slug'] ?? '');
        $capacity = max(1, (int) ($_POST['poppins_bag_capacity'] ?? 1));
        $limits   = [];

        if (!empty($_POST['poppins_bag_category_limits']) && is_array($_POST['poppins_bag_category_limits'])) {
            foreach ($_POST['poppins_bag_category_limits'] as $term_id => $value) {
                $term_id = (int) $term_id;
                $limit   = max(0, (int) $value);
                if ($term_id > 0 && $limit > 0) {
                    $limits[$term_id] = $limit;
                }
            }
        }

        update_post_meta($post_id, '_poppins_bag_slug', $slug);
        update_post_meta($post_id, '_poppins_bag_capacity', $capacity);
        update_post_meta($post_id, '_poppins_bag_category_limits', $limits);
    },
    10,
    2,
);

/**
 * Recupera tutte le bag.
 *
 * @return array<string, array{label: string, capacity: int, post_id: int, limits: array}>
 */
function poppins_get_bags(): array
{
    $query = new WP_Query(
        [
            'post_type'      => 'poppins_bag',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
        ],
    );

    $bags = [];
    foreach ($query->posts as $bag_post) {
        $slug = get_post_meta($bag_post->ID, '_poppins_bag_slug', true);
        if (!$slug) {
            continue;
        }

        $bags[$slug] = [
            'label'    => $bag_post->post_title,
            'capacity' => (int) get_post_meta($bag_post->ID, '_poppins_bag_capacity', true) ?: 1,
            'post_id'  => $bag_post->ID,
            'limits'   => (array) get_post_meta($bag_post->ID, '_poppins_bag_category_limits', true),
        ];
    }

    return $bags;
}

/**
 * Tab di selezione bag nei prodotti WooCommerce.
 */
add_filter(
    'woocommerce_product_data_tabs',
    static function (array $tabs): array {
        $tabs['poppins_bags'] = [
            'label'  => __('Bag selezionabili', 'poppins'),
            'target' => 'poppins_bags_product_data',
            'class'  => ['hide_if_grouped', 'hide_if_external'],
        ];

        return $tabs;
    },
);

add_action(
    'woocommerce_product_data_panels',
    static function (): void {
        global $post;

        $bags = poppins_get_bags();
        $selected = (array) get_post_meta($post->ID, '_poppins_bags_available', true);
        ?>
        <div id="poppins_bags_product_data" class="panel woocommerce_options_panel hidden">
            <div class="options_group">
                <p><?php esc_html_e('Rendi il prodotto disponibile nelle bag selezionate.', 'poppins'); ?></p>
                <?php if (!$bags) : ?>
                    <p><?php esc_html_e('Crea una bag dal menu “Bag” per iniziare.', 'poppins'); ?></p>
                <?php else : ?>
                    <ul style="margin-left:1.25rem;">
                        <?php foreach ($bags as $slug => $bag) : ?>
                            <li style="margin-bottom:0.25rem;">
                                <label>
                                    <input type="checkbox" name="poppins_bags_available[]" value="<?php echo esc_attr($slug); ?>" <?php checked(in_array($slug, $selected, true)); ?>>
                                    <strong><?php echo esc_html($bag['label']); ?></strong>
                                    <small style="color:#777;">
                                        <?php
                                        printf(
                                            /* translators: %d: capacity */
                                            __('Capienza: %d prodotti', 'poppins'),
                                            absint($bag['capacity']),
                                        );
                            ?>
                                    </small>
                                </label>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
        <?php
    },
);

add_action(
    'woocommerce_admin_process_product_object',
    static function ($product): void {
        $bags = array_map('sanitize_text_field', (array) ($_POST['poppins_bags_available'] ?? []));

        $available = [];
        foreach (poppins_get_bags() as $slug => $data) {
            if (in_array($slug, $bags, true)) {
                $available[] = $slug;
            }
        }

        $product->update_meta_data('_poppins_bags_available', $available);
    },
);

/**
 * Helpers pubblici.
 */
function poppins_product_is_available_for_bag(int $product_id, string $bag_slug): bool
{
    $available = (array) get_post_meta($product_id, '_poppins_bags_available', true);

    return in_array($bag_slug, $available, true);
}

function poppins_get_products_for_bag(string $bag_slug): array
{
    $query = new WP_Query(
        [
            'post_type'      => 'product',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
            'fields'         => 'ids',
            'meta_query'     => [
                [
                    'key'     => '_poppins_bags_available',
                    'value'   => '"' . $bag_slug . '"',
                    'compare' => 'LIKE',
                ],
            ],
        ],
    );

    return $query->posts;
}

/**
 * Restituisce i prodotti associati a una bag partendo dall'ID post.
 *
 * @param int $bag_post_id
 * @return int[]
 */
function poppins_get_products_for_bag_post(int $bag_post_id): array
{
    $slug = get_post_meta($bag_post_id, '_poppins_bag_slug', true);
    if (!$slug) {
        return [];
    }

    return poppins_get_products_for_bag($slug);
}

/**
 * Restituisce i limiti per categoria per una bag.
 *
 * @return array<int, int>
 */
function poppins_get_bag_category_limits(string $bag_slug): array
{
    $bags = poppins_get_bags();
    if (!isset($bags[$bag_slug])) {
        return [];
    }

    return (array) ($bags[$bag_slug]['limits'] ?? []);
}
