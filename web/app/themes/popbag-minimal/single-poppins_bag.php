<?php
if (!defined('ABSPATH')) {
	exit;
}

get_header();

while (have_posts()) :
	the_post();
	$bag_post_id = get_the_ID();
	$bag = function_exists('popbag_get_bag_data') ? popbag_get_bag_data($bag_post_id) : ['capacity' => 1, 'limits' => [], 'slug' => '', 'label' => get_the_title(), 'post_id' => $bag_post_id];
	$products_ids = function_exists('poppins_get_products_for_bag_post') ? poppins_get_products_for_bag_post($bag_post_id) : [];

	$back_url = wp_get_referer();
	if (!$back_url) {
		$back_url = post_type_exists('poppins_bag') ? (string) get_post_type_archive_link('poppins_bag') : home_url('/');
	}

	// Prefill selection when coming from cart "Modifica bag" link.
	$prefill_ids = [];
	if (isset($_GET['selected'])) {
		$raw = sanitize_text_field((string) $_GET['selected']);
		$parts = array_filter(array_map('trim', explode(',', $raw)));
		$prefill_ids = array_values(array_filter(array_map('absint', $parts)));
	}
	?>

	<main class="bg-white">
		<div class="mx-auto max-w-6xl px-6 py-12">
			<header class="popbag-bag-header">
				<a class="popbag-bag-back" href="<?php echo esc_url($back_url); ?>">
					<span aria-hidden="true">←</span>
					<?php esc_html_e('Indietro', 'popbag-minimal'); ?>
				</a>

				<div class="popbag-bag-header__right">
					<h1 class="popbag-bag-title"><?php the_title(); ?></h1>
					<span class="popbag-bag-icon" aria-hidden="true">
						<?php if (has_post_thumbnail()) : ?>
							<?php the_post_thumbnail('thumbnail'); ?>
						<?php else : ?>
							<span style="width:100%;height:100%;display:block;"></span>
						<?php endif; ?>
					</span>
					<span class="popbag-bag-capacity">
						<?php
						printf(
							/* translators: %d: capacity */
							esc_html__('%d capi', 'popbag-minimal'),
							absint($bag['capacity'] ?? 1)
						);
						?>
					</span>
				</div>
			</header>

			<?php if (function_exists('woocommerce_output_all_notices')) : ?>
				<div class="mt-6"><?php woocommerce_output_all_notices(); ?></div>
			<?php endif; ?>

			<form method="post" class="mt-8">
				<?php wp_nonce_field('popbag_add_bag_to_cart', 'popbag_bag_nonce'); ?>

				<?php if ($products_ids) : ?>
					<div class="popbag-bag-grid" aria-label="<?php echo esc_attr__('Prodotti selezionabili', 'popbag-minimal'); ?>">
						<?php foreach ($products_ids as $product_id) :
							$product = function_exists('wc_get_product') ? wc_get_product($product_id) : null;
							if (!$product) {
								continue;
							}

							$image_html = $product->get_image('woocommerce_single', ['class' => '']);
							?>

							<input
								type="checkbox"
								name="popbag_bag_products[]"
								value="<?php echo esc_attr($product_id); ?>"
								class="popbag-product-checkbox"
								data-product-id="<?php echo esc_attr($product_id); ?>"
								<?php checked(in_array((int) $product_id, $prefill_ids, true)); ?>
								style="position:absolute;left:-9999px;"
							/>

							<button type="button" class="popbag-product-card" data-product-id="<?php echo esc_attr($product_id); ?>" aria-label="<?php echo esc_attr($product->get_name()); ?>">
								<span class="popbag-product-card__check" data-popbag-toggle aria-label="<?php echo esc_attr__('Seleziona/deseleziona', 'popbag-minimal'); ?>">✓</span>
								<div class="popbag-product-card__img">
									<?php echo wp_kses_post($image_html); ?>
								</div>
								<div class="popbag-product-card__body">
									<p class="popbag-product-card__title"><?php echo esc_html($product->get_name()); ?></p>
									<span class="popbag-product-card__badge"><?php echo wp_kses_post($product->get_price_html()); ?></span>
								</div>
							</button>

							<?php
							// Modal template (cloned by JS on click).
							$main_id = (int) $product->get_image_id();
							$gallery_ids = method_exists($product, 'get_gallery_image_ids') ? (array) $product->get_gallery_image_ids() : [];
							$all_image_ids = array_values(array_unique(array_filter(array_merge([$main_id], $gallery_ids))));

							$desc = $product->get_description();
							if (!$desc) {
								$desc = $product->get_short_description();
							}

							$attrs = [];
							foreach ($product->get_attributes() as $attribute) {
								if ($attribute->is_taxonomy()) {
									$terms = wc_get_product_terms($product_id, $attribute->get_name(), ['fields' => 'names']);
									$value = $terms ? implode(', ', $terms) : '';
									$label = wc_attribute_label($attribute->get_name());
								} else {
									$value = implode(', ', (array) $attribute->get_options());
									$label = $attribute->get_name();
								}
								$value = trim((string) $value);
								$label = trim((string) $label);
								if ($label !== '' && $value !== '') {
									$attrs[] = ['label' => $label, 'value' => $value];
								}
							}
							?>
							<template id="popbag-product-detail-<?php echo esc_attr($product_id); ?>" data-product-name="<?php echo esc_attr($product->get_name()); ?>">
								<div class="popbag-modal__content">
									<div>
										<div class="popbag-modal__gallery">
											<?php
											$first_url = '';
											if ($all_image_ids) {
												$first_url = (string) wp_get_attachment_image_url($all_image_ids[0], 'large');
											}
											if (!$first_url) {
												$first_url = (string) wp_get_attachment_image_url($main_id, 'large');
											}
											?>
											<img src="<?php echo esc_url($first_url); ?>" alt="<?php echo esc_attr($product->get_name()); ?>" data-popbag-main-image loading="lazy" decoding="async" />
										</div>

										<?php if (count($all_image_ids) > 1) : ?>
											<div class="popbag-modal__thumbs" aria-label="<?php echo esc_attr__('Galleria', 'popbag-minimal'); ?>">
												<?php foreach ($all_image_ids as $img_id) :
													$thumb = wp_get_attachment_image_url($img_id, 'thumbnail');
													$full = wp_get_attachment_image_url($img_id, 'large');
													if (!$thumb || !$full) {
														continue;
													}
													?>
													<button type="button" data-popbag-thumb="<?php echo esc_url($full); ?>" aria-label="<?php echo esc_attr__('Apri immagine', 'popbag-minimal'); ?>">
														<img src="<?php echo esc_url($thumb); ?>" alt="" loading="lazy" decoding="async" />
													</button>
												<?php endforeach; ?>
											</div>
										<?php endif; ?>
									</div>

									<div>
										<?php if ($desc) : ?>
											<div class="popbag-modal__desc">
												<?php echo wp_kses_post(wpautop($desc)); ?>
											</div>
										<?php endif; ?>

										<?php if ($attrs) : ?>
											<dl class="popbag-modal__attrs">
												<?php foreach ($attrs as $row) : ?>
													<dt><?php echo esc_html($row['label']); ?></dt>
													<dd><?php echo esc_html($row['value']); ?></dd>
												<?php endforeach; ?>
											</dl>
										<?php endif; ?>
									</div>
								</div>

								<div class="popbag-modal__cta">
									<button type="button" class="popbag-modal__select" data-popbag-modal-select>
										<?php esc_html_e('Seleziona', 'popbag-minimal'); ?>
									</button>
								</div>
							</template>
						<?php endforeach; ?>
					</div>

					<div class="popbag-bag-actions">
						<button type="submit" class="popbag-bag-submit">
							<?php esc_html_e('Aggiungi bag al carrello', 'popbag-minimal'); ?>
						</button>
					</div>
				<?php else : ?>
					<p class="text-[#1F525E]"><?php esc_html_e('No products are available for this bag yet.', 'popbag-minimal'); ?></p>
				<?php endif; ?>
			</form>

			<div id="popbag-product-modal" class="popbag-modal" hidden>
				<div class="popbag-modal__backdrop" data-popbag-modal-backdrop></div>
				<div class="popbag-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="popbag-product-modal-title">
					<div class="popbag-modal__head">
						<h2 id="popbag-product-modal-title" class="popbag-modal__title"></h2>
						<button type="button" class="popbag-modal__close" aria-label="<?php echo esc_attr__('Chiudi', 'popbag-minimal'); ?>" data-popbag-modal-close>×</button>
					</div>
					<div id="popbag-product-modal-body"></div>
				</div>
			</div>

			<div id="popbag-lightbox" class="popbag-lightbox" hidden>
				<div class="popbag-lightbox__backdrop" data-popbag-lightbox-close></div>
				<div class="popbag-lightbox__dialog" role="dialog" aria-modal="true" aria-label="<?php echo esc_attr__('Immagine prodotto', 'popbag-minimal'); ?>">
					<div class="popbag-lightbox__top">
						<div class="popbag-lightbox__controls" aria-label="<?php echo esc_attr__('Zoom', 'popbag-minimal'); ?>">
							<button type="button" class="popbag-lightbox__btn" data-popbag-zoom-out aria-label="<?php echo esc_attr__('Riduci zoom', 'popbag-minimal'); ?>">−</button>
							<span id="popbag-lightbox-zoom" class="popbag-lightbox__zoom">100%</span>
							<button type="button" class="popbag-lightbox__btn" data-popbag-zoom-in aria-label="<?php echo esc_attr__('Aumenta zoom', 'popbag-minimal'); ?>">+</button>
						</div>
						<button type="button" class="popbag-lightbox__close" aria-label="<?php echo esc_attr__('Chiudi', 'popbag-minimal'); ?>" data-popbag-lightbox-close>×</button>
					</div>
					<div class="popbag-lightbox__stage" data-popbag-lightbox-stage>
						<img id="popbag-lightbox-img" src="" alt="" loading="lazy" decoding="async" draggable="false" />
					</div>
					<p class="popbag-lightbox__hint"><?php esc_html_e('Suggerimento: usa la rotellina per zoomare.', 'popbag-minimal'); ?></p>
				</div>
			</div>
		</div>
	</main>

<?php endwhile; ?>

<?php
get_footer();



