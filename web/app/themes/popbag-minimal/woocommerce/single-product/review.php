<?php
/**
 * Review comment template (theme-styled).
 *
 * Closing li is left out on purpose.
 *
 * @package WooCommerce\Templates
 * @version 2.6.0
 */

defined('ABSPATH') || exit;
?>

<li <?php comment_class('rounded-[16px] border border-[#003745]/10 bg-white p-5 shadow-sm'); ?> id="li-comment-<?php comment_ID(); ?>">
	<div id="comment-<?php comment_ID(); ?>" class="comment_container">
		<div class="flex items-start gap-4">
			<div class="shrink-0">
				<?php
				/**
				 * Hook: woocommerce_review_before.
				 *
				 * @hooked woocommerce_review_display_gravatar - 10
				 */
				do_action('woocommerce_review_before', $comment);
				?>
			</div>

			<div class="comment-text min-w-0 flex-1">
				<?php
				/**
				 * Hook: woocommerce_review_before_comment_meta.
				 *
				 * @hooked woocommerce_review_display_rating - 10
				 */
				do_action('woocommerce_review_before_comment_meta', $comment);

				/**
				 * Hook: woocommerce_review_meta.
				 *
				 * @hooked woocommerce_review_display_meta - 10
				 */
				do_action('woocommerce_review_meta', $comment);

				do_action('woocommerce_review_before_comment_text', $comment);

				/**
				 * Hook: woocommerce_review_comment_text.
				 *
				 * @hooked woocommerce_review_display_comment_text - 10
				 */
				do_action('woocommerce_review_comment_text', $comment);

				do_action('woocommerce_review_after_comment_text', $comment);
				?>
			</div>
		</div>
	</div>
