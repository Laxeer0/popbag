<?php

declare(strict_types=1);

namespace MUACP;

final class Cli
{
    public function __construct(private readonly Purger $purger)
    {
    }

    public static function register(Purger $purger): void
    {
        if (!class_exists(\WP_CLI::class)) {
            return;
        }

        \WP_CLI::add_command('muacp', new self($purger));
    }

    /**
     * Purge cache Apache mod_cache_disk tramite htcacheclean.
     *
     * ## OPTIONS
     *
     * [--url=<url>]
     * : Purge singola URL.
     *
     * [--post_id=<id>]
     * : Purge permalink + home (+ tassonomie se presenti).
     *
     * [--product_id=<id>]
     * : Purge prodotto + tassonomie + home (+ shop se attivo).
     *
     * [--all]
     * : Purge home + feed (+ shop se attivo).
     *
     * [--force]
     * : PURGE TOTALE CacheRoot (htcacheclean -r). Da usare solo con Apache fermo.
     *
     * @param array<int, string> $args
     * @param array<string, mixed> $assocArgs
     */
    public function purge(array $args, array $assocArgs): void
    {
        $hasAction =
            !empty($assocArgs['url']) ||
            !empty($assocArgs['post_id']) ||
            !empty($assocArgs['product_id']) ||
            !empty($assocArgs['all']) ||
            !empty($assocArgs['force']);

        if (!$hasAction) {
            \WP_CLI::error('Specifica almeno una opzione: --url, --post_id, --product_id, --all, --force');
        }

        if (!$this->purger->isEnabled() && empty($assocArgs['force'])) {
            \WP_CLI::error('MUACP is disabled (MUACP_ENABLED=false). Usa --force solo se vuoi purge totale del CacheRoot.');
        }

        $anyError = false;

        if (!empty($assocArgs['force'])) {
            \WP_CLI::warning('Purge totale CacheRoot: assicurati che Apache NON sia in esecuzione.');
            $r = $this->purger->purgeCacheRootForce();
            $this->printResult($r);
            $anyError = $anyError || !$r['ok'];
        }

        if (!empty($assocArgs['all'])) {
            $results = $this->purger->purgeAll(includeShop: true);
            foreach ($results as $r) {
                $this->printResult($r);
                $anyError = $anyError || !$r['ok'];
            }
        }

        if (!empty($assocArgs['url'])) {
            $url = (string) $assocArgs['url'];
            $r = $this->purger->purgeUrl($url);
            $this->printResult($r);
            $anyError = $anyError || !$r['ok'];
        }

        if (!empty($assocArgs['post_id'])) {
            $postId = absint((string) $assocArgs['post_id']);
            if ($postId <= 0) {
                \WP_CLI::error('Invalid --post_id');
            }

            $results = $this->purger->purgePost($postId);
            foreach ($results as $r) {
                $this->printResult($r);
                $anyError = $anyError || !$r['ok'];
            }
        }

        if (!empty($assocArgs['product_id'])) {
            $productId = absint((string) $assocArgs['product_id']);
            if ($productId <= 0) {
                \WP_CLI::error('Invalid --product_id');
            }

            $results = $this->purger->purgeProductForStock($productId);
            foreach ($results as $r) {
                $this->printResult($r);
                $anyError = $anyError || !$r['ok'];
            }
        }

        if ($anyError) {
            \WP_CLI::error('Purge completato con errori.', false);
            exit(1);
        }

        \WP_CLI::success('Purge completato.');
    }

    /**
     * @param array{ok: bool, exit_code: int, stdout: string, stderr: string, cmd: string, url: string} $result
     */
    private function printResult(array $result): void
    {
        $status = $result['ok'] ? 'OK' : 'ERROR';
        \WP_CLI::log(sprintf('%s %s (exit=%d)', $status, $result['url'], (int) $result['exit_code']));

        if (!$result['ok'] && $result['stderr'] !== '') {
            \WP_CLI::log('stderr: ' . trim($result['stderr']));
        }
    }
}

