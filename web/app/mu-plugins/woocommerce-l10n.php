<?php

/**
 * Temporary workaround for WooCommerce translations triggering _doing_it_wrong() on WP 6.7+.
 * Suppresses the notice until WooCommerce releases an upstream fix.
 */

add_filter(
    'doing_it_wrong_trigger_error',
    static function (bool $trigger, string $function_name, string $message): bool {
        if ('_load_textdomain_just_in_time' === $function_name && str_contains($message, '<code>woocommerce</code>')) {
            return false;
        }

        return $trigger;
    },
    10,
    3,
);
