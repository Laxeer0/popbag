<?php
/**
 * Display single product reviews (comments) - theme styled.
 *
 * @package WooCommerce\Templates
 * @version 9.7.0
 */

defined('ABSPATH') || exit;

global $product;

if (!comments_open()) {
	return;
}
?>

<div id="reviews" class="woocommerce-Reviews">
	<div class="grid gap-8 lg:grid-cols-[1.2fr_0.8fr]">
		<div id="comments" class="rounded-[16px] border border-[#003745]/10 bg-white p-6 shadow-sm">
			<h2 class="woocommerce-Reviews-title text-xl font-black text-[#003745]">
				<?php
				$count = $product->get_review_count();
				if ($count && wc_review_ratings_enabled()) {
					/* translators: 1: reviews count 2: product name */
					$reviews_title = sprintf(
						esc_html(_n('%1$s review for %2$s', '%1$s reviews for %2$s', $count, 'woocommerce')),
						esc_html($count),
						'<span>' . get_the_title() . '</span>'
					);
					echo apply_filters('woocommerce_reviews_title', $reviews_title, $count, $product); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				} else {
					esc_html_e('Reviews', 'woocommerce');
				}
				?>
			</h2>

			<?php if (have_comments()) : ?>
				<ol class="commentlist mt-6 space-y-4">
					<?php
					wp_list_comments(apply_filters('woocommerce_product_review_list_args', [
						'callback' => 'woocommerce_comments',
					]));
					?>
				</ol>

				<?php if (get_comment_pages_count() > 1 && get_option('page_comments')) : ?>
					<nav class="woocommerce-pagination mt-6">
						<?php
						paginate_comments_links(apply_filters('woocommerce_comment_pagination_args', [
							'prev_text' => is_rtl() ? '&rarr;' : '&larr;',
							'next_text' => is_rtl() ? '&larr;' : '&rarr;',
							'type'      => 'list',
						]));
						?>
					</nav>
				<?php endif; ?>
			<?php else : ?>
				<p class="woocommerce-noreviews mt-6 text-sm text-[#1F525E]">
					<?php esc_html_e('There are no reviews yet.', 'woocommerce'); ?>
				</p>
			<?php endif; ?>
		</div>

		<?php if (get_option('woocommerce_review_rating_verification_required') === 'no' || wc_customer_bought_product('', get_current_user_id(), $product->get_id())) : ?>
			<div id="review_form_wrapper" class="rounded-[16px] border border-[#003745]/10 bg-white p-6 shadow-sm">
				<div id="review_form">
					<?php
					$commenter = wp_get_current_commenter();

					$name_email_required = (bool) get_option('require_name_email', 1);
					$label_class = 'block text-sm font-semibold text-[#003745]';
					$input_class = 'mt-1 w-full rounded-[14px] border border-[#003745]/15 bg-white px-4 py-3 text-[#003745] focus:border-[#003745]/40 focus:outline-none focus:ring-2 focus:ring-[#FF2030]/20';

					$comment_form = [
						/* translators: %s is product title */
						'title_reply'         => have_comments() ? esc_html__('Add a review', 'woocommerce') : sprintf(esc_html__('Be the first to review &ldquo;%s&rdquo;', 'woocommerce'), get_the_title()),
						/* translators: %s is product title */
						'title_reply_to'      => esc_html__('Leave a Reply to %s', 'woocommerce'),
						'title_reply_before'  => '<span id="reply-title" class="comment-reply-title block text-xl font-black text-[#003745]" role="heading" aria-level="3">',
						'title_reply_after'   => '</span>',
						'comment_notes_after' => '',
						'logged_in_as'        => '',
						'label_submit'        => esc_html__('Invia recensione', 'popbag-minimal'),
						'class_submit'        => popbag_button_classes('primary', 'md', 'w-full'),
						'fields'              => [],
						'comment_field'       => '',
					];

					// Name/email fields (if needed).
					$fields = [
						'author' => [
							'label'        => __('Name', 'woocommerce'),
							'type'         => 'text',
							'value'        => $commenter['comment_author'],
							'required'     => $name_email_required,
							'autocomplete' => 'name',
						],
						'email'  => [
							'label'        => __('Email', 'woocommerce'),
							'type'         => 'email',
							'value'        => $commenter['comment_author_email'],
							'required'     => $name_email_required,
							'autocomplete' => 'email',
						],
					];

					foreach ($fields as $key => $field) {
						$required = !empty($field['required']);
						$comment_form['fields'][$key] =
							'<p class="comment-form-' . esc_attr($key) . ' mt-4">' .
							'<label class="' . esc_attr($label_class) . '" for="' . esc_attr($key) . '">' . esc_html($field['label']) .
							($required ? ' <span class="required" aria-hidden="true">*</span>' : '') .
							'</label>' .
							'<input class="' . esc_attr($input_class) . '" id="' . esc_attr($key) . '" name="' . esc_attr($key) . '" type="' . esc_attr($field['type']) . '" autocomplete="' . esc_attr($field['autocomplete']) . '" value="' . esc_attr($field['value']) . '" size="30" ' . ($required ? 'required aria-required="true"' : '') . ' />' .
							'</p>';
					}

					$account_page_url = wc_get_page_permalink('myaccount');
					if ($account_page_url) {
						/* translators: %s opening and closing link tags respectively */
						$comment_form['must_log_in'] = '<p class="must-log-in mt-4 text-sm text-[#1F525E]">' . sprintf(esc_html__('You must be %1$slogged in%2$s to post a review.', 'woocommerce'), '<a class="font-semibold underline decoration-[#FF2030] decoration-2 underline-offset-4" href="' . esc_url($account_page_url) . '">', '</a>') . '</p>';
					}

					if (wc_review_ratings_enabled()) {
						$required_attr = wc_review_ratings_required() ? ' data-required="1"' : '';

						$comment_form['comment_field'] .=
							'<div class="comment-form-rating mt-4" data-popbag-rating-slider-wrap' . $required_attr . '>' .
							'<label class="' . esc_attr($label_class) . '" for="popbag-rating" id="comment-form-rating-label">' .
							esc_html__('Your rating', 'woocommerce') .
							(wc_review_ratings_required() ? ' <span class="required" aria-hidden="true">*</span>' : '') .
							'</label>' .
							'<p class="mt-2 text-xs uppercase tracking-[0.18em] text-[#1F525E]">' .
							esc_html__('Trascina per valutare (0 = non impostato)', 'popbag-minimal') .
							'</p>' .
							'<div class="mt-3 flex items-center gap-3">' .
							'<input type="range" min="0" max="5" step="1" value="0" id="popbag-rating" class="popbag-rating-slider" aria-describedby="comment-form-rating-label" />' .
							'<span class="popbag-rating-value rounded-full border border-[#003745]/15 bg-white px-3 py-1 text-xs font-bold uppercase tracking-[0.18em] text-[#003745]" aria-live="polite">0/5</span>' .
							'</div>' .
							'<select class="popbag-rating-select-hidden" name="rating" id="rating" ' . (wc_review_ratings_required() ? 'required aria-required="true"' : '') . '>' .
							'<option value="">' . esc_html__('Rate&hellip;', 'woocommerce') . '</option>' .
							'<option value="5">' . esc_html__('Perfect', 'woocommerce') . '</option>' .
							'<option value="4">' . esc_html__('Good', 'woocommerce') . '</option>' .
							'<option value="3">' . esc_html__('Average', 'woocommerce') . '</option>' .
							'<option value="2">' . esc_html__('Not that bad', 'woocommerce') . '</option>' .
							'<option value="1">' . esc_html__('Very poor', 'woocommerce') . '</option>' .
							'</select>' .
							'</div>';
					}

					$comment_form['comment_field'] .=
						'<p class="comment-form-comment mt-4">' .
						'<label class="' . esc_attr($label_class) . '" for="comment">' . esc_html__('Your review', 'woocommerce') . ' <span class="required" aria-hidden="true">*</span></label>' .
						'<textarea class="' . esc_attr($input_class) . '" id="comment" name="comment" cols="45" rows="6" required aria-required="true"></textarea>' .
						'</p>';

					comment_form(apply_filters('woocommerce_product_review_comment_form_args', $comment_form));
					?>
				</div>
			</div>
		<?php else : ?>
			<p class="woocommerce-verification-required rounded-[16px] border border-[#003745]/10 bg-[#003745]/5 p-5 text-sm text-[#1F525E]">
				<?php esc_html_e('Only logged in customers who have purchased this product may leave a review.', 'woocommerce'); ?>
			</p>
		<?php endif; ?>
	</div>
</div>

