<?php

declare(strict_types=1);

namespace MUACP;

final class AdminActions
{
    public function __construct(private readonly Purger $purger)
    {
    }

    public function register(): void
    {
        add_action('admin_post_muacp_purge', [$this, 'handlePurge']);
        add_action('admin_notices', [$this, 'renderNotice']);
    }

    public function handlePurge(): void
    {
        if (!$this->purger->isEnabled()) {
            wp_die('MUACP disabled', 403);
        }

        if (!current_user_can($this->purger->capability())) {
            wp_die('Forbidden', 403);
        }

        $postId = isset($_GET['post_id']) ? absint((string) $_GET['post_id']) : 0;
        if ($postId <= 0) {
            wp_die('Missing post_id', 400);
        }

        $nonce = isset($_GET['_wpnonce']) ? (string) $_GET['_wpnonce'] : '';
        if (!wp_verify_nonce($nonce, 'muacp_purge_' . $postId)) {
            wp_die('Invalid nonce', 403);
        }

        $post = get_post($postId);
        if (!$post instanceof \WP_Post) {
            wp_die('Invalid post', 404);
        }

        if (!in_array($post->post_type, ['post', 'page', 'product'], true)) {
            wp_die('Unsupported post type', 400);
        }

        $permalink = get_permalink($postId);
        $primaryUrl = is_string($permalink) ? $permalink : '';

        $this->purger->purgePost($postId);

        $redirectTo = isset($_GET['redirect_to']) ? (string) wp_unslash((string) $_GET['redirect_to']) : '';
        if ($redirectTo === '') {
            $redirectTo = (string) wp_get_referer();
        }

        $redirectTo = wp_validate_redirect($redirectTo, admin_url());

        $query = [
            'muacp_purged' => '1',
        ];
        if ($primaryUrl !== '') {
            $query['muacp_url'] = esc_url_raw($primaryUrl);
        }

        $target = add_query_arg($query, $redirectTo);
        wp_safe_redirect($target);
        exit;
    }

    public function renderNotice(): void
    {
        if (!isset($_GET['muacp_purged']) || (string) $_GET['muacp_purged'] !== '1') {
            return;
        }

        if (!current_user_can($this->purger->capability())) {
            return;
        }

        $url = '';
        if (isset($_GET['muacp_url'])) {
            $url = esc_url_raw((string) wp_unslash((string) $_GET['muacp_url']));
        }

        $message = $url !== ''
            ? sprintf('Cache purgata per: %s', esc_html($url))
            : 'Cache purgata.';

        echo '<div class="notice notice-success is-dismissible"><p>' . $message . '</p></div>';
    }
}

