<?php

declare(strict_types=1);

namespace MUACP;

final class Bootstrap
{
    public static function run(): void
    {
        $purger = new Purger(
            enabled: self::constBool('MUACP_ENABLED', true),
            cacheRoot: self::constString('MUACP_CACHE_ROOT', '/var/cache/apache2/mod_cache_disk/EXAMPLE_COM'),
            htcachecleanBin: self::constString('MUACP_HTCACHECLEAN_BIN', '/usr/sbin/htcacheclean'),
            capability: self::constString('MUACP_CAPABILITY', 'manage_options'),
            purgeShop: self::constBool('MUACP_PURGE_SHOP', true),
        );

        if (!$purger->isEnabled()) {
            return;
        }

        (new Hooks($purger))->register();

        if (class_exists(\WooCommerce::class) || function_exists('WC')) {
            (new WooCommerceHooks($purger))->register();
        }

        if (is_admin()) {
            (new AdminActions($purger))->register();
            (new ListTableActions($purger))->register();
        }

        // Registra sempre: il check su visibilità/capability avviene nel callback e dopo che WP è pronto.
        (new AdminBar($purger))->register();

        if (\defined('WP_CLI') && (bool) \constant('WP_CLI')) {
            Cli::register($purger);
        }
    }

    private static function constBool(string $name, bool $default): bool
    {
        if (!defined($name)) {
            return $default;
        }

        return (bool) constant($name);
    }

    private static function constString(string $name, string $default): string
    {
        if (!defined($name)) {
            return $default;
        }

        $value = (string) constant($name);
        return $value !== '' ? $value : $default;
    }
}

