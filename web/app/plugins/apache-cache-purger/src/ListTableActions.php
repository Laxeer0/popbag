<?php

declare(strict_types=1);

namespace MUACP;

final class ListTableActions
{
    public function __construct(private readonly Purger $purger)
    {
    }

    public function register(): void
    {
        add_filter('post_row_actions', [$this, 'addRowAction'], 20, 2);
        add_filter('page_row_actions', [$this, 'addRowAction'], 20, 2);
    }

    /**
     * @param array<string, string> $actions
     * @return array<string, string>
     */
    public function addRowAction(array $actions, \WP_Post $post): array
    {
        if (!$this->purger->isEnabled()) {
            return $actions;
        }

        if (!in_array($post->post_type, ['post', 'page', 'product'], true)) {
            return $actions;
        }

        if (!current_user_can($this->purger->capability())) {
            return $actions;
        }

        $redirectTo = wp_get_referer();
        if (!is_string($redirectTo) || $redirectTo === '') {
            $redirectTo = $post->post_type === 'post'
                ? admin_url('edit.php')
                : admin_url('edit.php?post_type=' . $post->post_type);
        }

        $url = add_query_arg(
            [
                'action' => 'muacp_purge',
                'post_id' => $post->ID,
                '_wpnonce' => wp_create_nonce('muacp_purge_' . (int) $post->ID),
                'redirect_to' => $redirectTo,
            ],
            admin_url('admin-post.php')
        );

        $actions['muacp_purge_cache'] = '<a href="' . esc_url($url) . '">Purge cache</a>';

        return $actions;
    }
}

