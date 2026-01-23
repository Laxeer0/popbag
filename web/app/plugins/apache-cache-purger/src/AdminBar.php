<?php

declare(strict_types=1);

namespace MUACP;

final class AdminBar
{
    public function __construct(private readonly Purger $purger)
    {
    }

    public function register(): void
    {
        add_action('admin_bar_menu', [$this, 'addNode'], 90);
    }

    public function addNode(\WP_Admin_Bar $adminBar): void
    {
        if (!$this->purger->isEnabled()) {
            return;
        }

        if (!current_user_can($this->purger->capability())) {
            return;
        }

        $postId = $this->resolveContextPostId();
        if ($postId <= 0) {
            return;
        }

        $post = get_post($postId);
        if (!$post instanceof \WP_Post) {
            return;
        }

        if (!in_array($post->post_type, ['post', 'page', 'product'], true)) {
            return;
        }

        $url = add_query_arg(
            [
                'action' => 'muacp_purge',
                'post_id' => $postId,
                '_wpnonce' => wp_create_nonce('muacp_purge_' . $postId),
                'redirect_to' => $this->currentUrlForRedirect(),
            ],
            admin_url('admin-post.php')
        );

        $adminBar->add_node(
            [
                'id' => 'muacp_purge_cache',
                'title' => 'Purge cache',
                'href' => $url,
                'meta' => [
                    'title' => 'Purge cache (htcacheclean)',
                ],
            ]
        );
    }

    private function resolveContextPostId(): int
    {
        if (!is_admin()) {
            if (function_exists('is_singular') && is_singular(['post', 'page', 'product'])) {
                return (int) get_queried_object_id();
            }

            return 0;
        }

        global $pagenow;
        if (!in_array((string) $pagenow, ['post.php', 'post-new.php'], true)) {
            return 0;
        }

        if (isset($_GET['post'])) {
            return absint((string) $_GET['post']);
        }

        return 0;
    }

    private function currentUrlForRedirect(): string
    {
        if (is_admin()) {
            $ref = wp_get_referer();
            if (is_string($ref) && $ref !== '') {
                return $ref;
            }

            return admin_url();
        }

        $scheme = is_ssl() ? 'https' : 'http';
        $host = isset($_SERVER['HTTP_HOST']) ? (string) $_SERVER['HTTP_HOST'] : '';
        $uri = isset($_SERVER['REQUEST_URI']) ? (string) $_SERVER['REQUEST_URI'] : '/';

        if ($host === '') {
            return home_url('/');
        }

        return $scheme . '://' . $host . $uri;
    }
}

