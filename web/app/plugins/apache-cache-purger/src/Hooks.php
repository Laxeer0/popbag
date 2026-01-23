<?php

declare(strict_types=1);

namespace MUACP;

final class Hooks
{
    /**
     * @var array<string, true>
     */
    private static array $debounce = [];

    public function __construct(private readonly Purger $purger)
    {
    }

    public function register(): void
    {
        add_action('save_post', [$this, 'onSavePost'], 20, 3);
        add_action('transition_post_status', [$this, 'onTransitionPostStatus'], 20, 3);
    }

    public function onSavePost(int $postId, \WP_Post $post, bool $update): void
    {
        if (!$this->purger->isEnabled()) {
            return;
        }

        if (!self::shouldHandlePost($postId, $post)) {
            return;
        }

        // Update di contenuto già pubblicato.
        if ($post->post_status !== 'publish') {
            return;
        }

        $key = 'save:' . $postId;
        if (isset(self::$debounce[$key])) {
            return;
        }
        self::$debounce[$key] = true;

        $this->purger->purgePost($postId);
    }

    public function onTransitionPostStatus(string $newStatus, string $oldStatus, \WP_Post $post): void
    {
        if (!$this->purger->isEnabled()) {
            return;
        }

        $postId = (int) $post->ID;
        if (!self::shouldHandlePost($postId, $post)) {
            return;
        }

        // Solo quando diventa publish (prima pubblicazione).
        if ($newStatus !== 'publish' || $oldStatus === 'publish') {
            return;
        }

        $key = 'transition:' . $postId;
        if (isset(self::$debounce[$key])) {
            return;
        }
        self::$debounce[$key] = true;

        $this->purger->purgePost($postId);
    }

    private static function shouldHandlePost(int $postId, \WP_Post $post): bool
    {
        if (!in_array($post->post_type, ['post', 'page', 'product'], true)) {
            return false;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return false;
        }

        if (wp_is_post_autosave($postId) || wp_is_post_revision($postId)) {
            return false;
        }

        return true;
    }
}

