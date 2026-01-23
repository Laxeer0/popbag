<?php

declare(strict_types=1);

namespace MUACP;

final class WooCommerceHooks
{
    /**
     * @var array<int, true>
     */
    private static array $debounce = [];

    public function __construct(private readonly Purger $purger)
    {
    }

    public function register(): void
    {
        add_action('woocommerce_product_set_stock', [$this, 'onProductSetStock'], 20, 1);
        add_action('woocommerce_variation_set_stock', [$this, 'onVariationSetStock'], 20, 1);
        add_action('woocommerce_product_set_stock_status', [$this, 'onProductSetStockStatus'], 20, 3);

        // Fallback: alcuni flussi aggiornano prodotto senza passare dagli hook stock.
        add_action('woocommerce_update_product', [$this, 'onUpdateProduct'], 20, 1);
        add_action('save_post_product', [$this, 'onSaveProductPost'], 20, 3);
    }

    public function onProductSetStock(mixed $product): void
    {
        $this->purgeFromProductMixed($product);
    }

    public function onVariationSetStock(mixed $variation): void
    {
        $this->purgeFromProductMixed($variation);
    }

    public function onProductSetStockStatus(int $productId, string $stockStatus, mixed $product): void
    {
        $id = $this->resolvePurgeProductId($productId, $product);
        if ($id > 0) {
            $this->purgeProductId($id);
        }
    }

    public function onUpdateProduct(int $productId): void
    {
        $this->purgeProductId($this->resolveParentProductId($productId));
    }

    public function onSaveProductPost(int $postId, \WP_Post $post, bool $update): void
    {
        if (!$update) {
            return;
        }

        if (wp_is_post_autosave($postId) || wp_is_post_revision($postId)) {
            return;
        }

        if ($post->post_status !== 'publish') {
            return;
        }

        $this->purgeProductId($this->resolveParentProductId($postId));
    }

    private function purgeFromProductMixed(mixed $product): void
    {
        $productId = 0;

        if (is_object($product) && method_exists($product, 'get_id')) {
            $productId = (int) $product->get_id();
        } elseif (is_int($product)) {
            $productId = $product;
        } elseif (is_string($product) && ctype_digit($product)) {
            $productId = (int) $product;
        }

        $this->purgeProductId($this->resolveParentProductId($productId));
    }

    private function resolvePurgeProductId(int $productId, mixed $product): int
    {
        if ($productId > 0) {
            return $this->resolveParentProductId($productId);
        }

        if (is_object($product) && method_exists($product, 'get_id')) {
            return $this->resolveParentProductId((int) $product->get_id());
        }

        return 0;
    }

    private function resolveParentProductId(int $productId): int
    {
        if ($productId <= 0) {
            return 0;
        }

        if (!function_exists('wc_get_product')) {
            return $productId;
        }

        $wcProduct = wc_get_product($productId);
        if (!$wcProduct) {
            return $productId;
        }

        if (is_a($wcProduct, \WC_Product_Variation::class) && method_exists($wcProduct, 'get_parent_id')) {
            $parentId = (int) $wcProduct->get_parent_id();
            return $parentId > 0 ? $parentId : $productId;
        }

        return $productId;
    }

    private function purgeProductId(int $productId): void
    {
        if (!$this->purger->isEnabled()) {
            return;
        }

        if ($productId <= 0) {
            return;
        }

        if (isset(self::$debounce[$productId])) {
            return;
        }
        self::$debounce[$productId] = true;

        $this->purger->purgeProductForStock($productId);
    }
}

