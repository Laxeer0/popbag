<?php

declare(strict_types=1);

/**
 * Plugin Name: Apache Cache Purger (htcacheclean)
 * Description: Purge selettivo della cache Apache mod_cache_disk tramite htcacheclean (contenuti + WooCommerce stock + UI + WP-CLI).
 * Author: Popbag
 * Version: 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

$autoload = __DIR__ . '/vendor/autoload.php';
if (is_readable($autoload)) {
    require_once $autoload;
}

if (class_exists(\MUACP\Bootstrap::class)) {
    \MUACP\Bootstrap::run();
}

