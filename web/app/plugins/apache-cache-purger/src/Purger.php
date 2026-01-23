<?php

declare(strict_types=1);

namespace MUACP;

final class Purger
{
    private const DEFAULT_TIMEOUT_SECONDS = 2.5;

    public function __construct(
        private readonly bool $enabled,
        private readonly string $cacheRoot,
        private readonly string $htcachecleanBin,
        private readonly string $capability,
        private readonly bool $purgeShop,
    ) {
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function capability(): string
    {
        return $this->capability;
    }

    public function shouldPurgeShop(): bool
    {
        return $this->purgeShop;
    }

    /**
     * @return array{ok: bool, exit_code: int, stdout: string, stderr: string, cmd: string, url: string}
     */
    public function purgeUrl(string $url): array
    {
        $normalized = self::normalizeUrlForHtcacheclean($url);
        if ($normalized === '') {
            return [
                'ok' => false,
                'exit_code' => 1,
                'stdout' => '',
                'stderr' => 'Empty URL',
                'cmd' => '',
                'url' => '',
            ];
        }

        return $this->runHtcacheclean(urls: [$normalized]);
    }

    /**
     * Purge home + feed + (shop se attivo).
     *
     * @return array<int, array{ok: bool, exit_code: int, stdout: string, stderr: string, cmd: string, url: string}>
     */
    public function purgeAll(bool $includeShop = true): array
    {
        $urls = [
            home_url('/'),
            home_url('/feed/'),
            home_url('/comments/feed/'),
        ];

        if ($includeShop && $this->purgeShop && function_exists('wc_get_page_id')) {
            $shopId = (int) wc_get_page_id('shop');
            if ($shopId > 0) {
                $shopUrl = get_permalink($shopId);
                if (is_string($shopUrl) && $shopUrl !== '') {
                    $urls[] = $shopUrl;
                }
            }
        }

        return $this->purgeUrls($urls);
    }

    /**
     * Purge post/permalink + home + feed + tassonomie (+ shop se product e attivo).
     *
     * @return array<int, array{ok: bool, exit_code: int, stdout: string, stderr: string, cmd: string, url: string}>
     */
    public function purgePost(int $postId): array
    {
        $post = get_post($postId);
        if (!$post instanceof \WP_Post) {
            return [];
        }

        $urls = [];

        $permalink = get_permalink($postId);
        if (is_string($permalink) && $permalink !== '') {
            $urls[] = $permalink;
        }

        $urls[] = home_url('/');
        $urls[] = home_url('/feed/');
        $urls[] = home_url('/comments/feed/');

        $urls = array_merge($urls, self::getTaxonomyArchiveUrlsForPost($postId, $post->post_type));

        if ($post->post_type === 'product' && $this->purgeShop && function_exists('wc_get_page_id')) {
            $shopId = (int) wc_get_page_id('shop');
            if ($shopId > 0) {
                $shopUrl = get_permalink($shopId);
                if (is_string($shopUrl) && $shopUrl !== '') {
                    $urls[] = $shopUrl;
                }
            }
        }

        return $this->purgeUrls($urls);
    }

    /**
     * Purge per stock update: prodotto (+ tassonomie) + home (+ shop se attivo).
     *
     * @return array<int, array{ok: bool, exit_code: int, stdout: string, stderr: string, cmd: string, url: string}>
     */
    public function purgeProductForStock(int $productId): array
    {
        $urls = [];

        $permalink = get_permalink($productId);
        if (is_string($permalink) && $permalink !== '') {
            $urls[] = $permalink;
        }

        $urls = array_merge($urls, self::getTaxonomyArchiveUrlsForPost($productId, 'product'));
        $urls[] = home_url('/');

        if ($this->purgeShop && function_exists('wc_get_page_id')) {
            $shopId = (int) wc_get_page_id('shop');
            if ($shopId > 0) {
                $shopUrl = get_permalink($shopId);
                if (is_string($shopUrl) && $shopUrl !== '') {
                    $urls[] = $shopUrl;
                }
            }
        }

        return $this->purgeUrls($urls);
    }

    /**
     * FORCE: purge totale del CacheRoot (usa htcacheclean -r).
     * Da usare solo con Apache fermo.
     *
     * @return array{ok: bool, exit_code: int, stdout: string, stderr: string, cmd: string, url: string}
     */
    public function purgeCacheRootForce(): array
    {
        if (!$this->enabled) {
            return [
                'ok' => false,
                'exit_code' => 1,
                'stdout' => '',
                'stderr' => 'MUACP disabled',
                'cmd' => '',
                'url' => '(cacheRoot)',
            ];
        }

        $cmdParts = array_merge(
            $this->getBinParts(),
            ['-p', $this->sanitizeCacheRoot(), '-r'],
        );

        return $this->runCommand($cmdParts, urlForResult: '(cacheRoot)');
    }

    /**
     * @param string[] $urls
     * @return array<int, array{ok: bool, exit_code: int, stdout: string, stderr: string, cmd: string, url: string}>
     */
    public function purgeUrls(array $urls): array
    {
        $normalized = [];
        foreach ($urls as $u) {
            if (!is_string($u) || $u === '') {
                continue;
            }
            $n = self::normalizeUrlForHtcacheclean($u);
            if ($n !== '') {
                $normalized[] = $n;
            }
        }

        $normalized = array_values(array_unique($normalized));
        $results = [];
        foreach ($normalized as $n) {
            $results[] = $this->runHtcacheclean(urls: [$n]);
        }

        return $results;
    }

    /**
     * @param array<int, string> $urls Normalizzate (schema+host, query presente o ? finale).
     * @return array{ok: bool, exit_code: int, stdout: string, stderr: string, cmd: string, url: string}
     */
    private function runHtcacheclean(array $urls): array
    {
        if (!$this->enabled) {
            return [
                'ok' => false,
                'exit_code' => 1,
                'stdout' => '',
                'stderr' => 'MUACP disabled',
                'cmd' => '',
                'url' => $urls[0] ?? '',
            ];
        }

        $cmdParts = array_merge(
            $this->getBinParts(),
            ['-p', $this->sanitizeCacheRoot()],
            $urls,
        );

        return $this->runCommand($cmdParts, urlForResult: $urls[0] ?? '');
    }

    /**
     * @return array<int, string>
     */
    private function getBinParts(): array
    {
        // Il comando è definito lato server (wp-config.php). Può includere "sudo -n".
        $bin = trim($this->htcachecleanBin);
        if ($bin === '' || strpos($bin, "\0") !== false) {
            $bin = '/usr/sbin/htcacheclean';
        }

        $parts = preg_split('/\s+/', $bin) ?: [];
        $parts = array_values(array_filter(array_map('strval', $parts), static fn (string $p): bool => $p !== ''));

        return $parts ?: ['/usr/sbin/htcacheclean'];
    }

    private function sanitizeCacheRoot(): string
    {
        $cacheRoot = trim($this->cacheRoot);
        if ($cacheRoot === '' || strpos($cacheRoot, "\0") !== false) {
            return '/var/cache/apache2/mod_cache_disk/EXAMPLE_COM';
        }

        return $cacheRoot;
    }

    /**
     * @param array<int, string> $cmdParts
     * @return array{ok: bool, exit_code: int, stdout: string, stderr: string, cmd: string, url: string}
     */
    private function runCommand(array $cmdParts, string $urlForResult): array
    {
        $stdout = '';
        $stderr = '';
        $exit = 1;
        $cmdString = self::formatCommandForLog($cmdParts);

        $descriptors = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $process = @proc_open($cmdParts, $descriptors, $pipes);
        if (!is_resource($process)) {
            $this->debugLog('proc_open failed', ['cmd' => $cmdString]);
            return [
                'ok' => false,
                'exit_code' => 1,
                'stdout' => '',
                'stderr' => 'proc_open failed',
                'cmd' => $cmdString,
                'url' => $urlForResult,
            ];
        }

        fclose($pipes[0]);
        stream_set_blocking($pipes[1], false);
        stream_set_blocking($pipes[2], false);

        $start = microtime(true);
        $timedOut = false;

        while (true) {
            $status = proc_get_status($process);
            $stdout .= (string) stream_get_contents($pipes[1]);
            $stderr .= (string) stream_get_contents($pipes[2]);

            if (!$status['running']) {
                $exit = (int) $status['exitcode'];
                break;
            }

            if ((microtime(true) - $start) > self::DEFAULT_TIMEOUT_SECONDS) {
                $timedOut = true;
                @proc_terminate($process, 9);
                break;
            }

            usleep(50_000);
        }

        $stdout .= (string) stream_get_contents($pipes[1]);
        $stderr .= (string) stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);

        $close = proc_close($process);
        if ($close !== -1) {
            $exit = (int) $close;
        }

        $ok = (!$timedOut) && ($exit === 0);

        $this->debugLog('htcacheclean result', [
            'ok' => $ok,
            'exit' => $exit,
            'timed_out' => $timedOut,
            'url' => $urlForResult,
            'cmd' => $cmdString,
            'stderr' => $stderr,
        ]);

        return [
            'ok' => $ok,
            'exit_code' => $exit,
            'stdout' => $stdout,
            'stderr' => $timedOut ? 'timeout' : $stderr,
            'cmd' => $cmdString,
            'url' => $urlForResult,
        ];
    }

    /**
     * @param array<int, string> $cmdParts
     */
    private static function formatCommandForLog(array $cmdParts): string
    {
        $out = [];
        foreach ($cmdParts as $p) {
            $out[] = escapeshellarg($p);
        }

        return implode(' ', $out);
    }

    /**
     * Normalizza per htcacheclean:
     * - URL assoluta con schema+host
     * - include sempre "?" (anche se query string vuota)
     */
    public static function normalizeUrlForHtcacheclean(string $url): string
    {
        $url = trim($url);
        if ($url === '') {
            return '';
        }

        $home = home_url('/');
        $homeParts = wp_parse_url($home);
        $parts = wp_parse_url($url);

        if (!is_array($parts)) {
            $url = home_url($url);
            $parts = wp_parse_url($url);
        }

        if (!is_array($parts) || empty($homeParts) || !is_array($homeParts)) {
            return '';
        }

        $scheme = (string) ($parts['scheme'] ?? $homeParts['scheme'] ?? 'http');
        $host = (string) ($parts['host'] ?? $homeParts['host'] ?? '');
        if ($host === '') {
            return '';
        }

        $port = isset($parts['port']) ? (int) $parts['port'] : (isset($homeParts['port']) ? (int) $homeParts['port'] : 0);
        $path = (string) ($parts['path'] ?? '/');
        $query = array_key_exists('query', $parts) ? (string) $parts['query'] : null;

        if ($path === '') {
            $path = '/';
        }
        if ($path[0] !== '/') {
            $path = '/' . $path;
        }

        $out = $scheme . '://' . $host;
        if ($port > 0) {
            $out .= ':' . $port;
        }
        $out .= $path;

        if ($query === null || $query === '') {
            $out .= '?';
        } else {
            $out .= '?' . $query;
        }

        return $out;
    }

    /**
     * @return string[]
     */
    private static function getTaxonomyArchiveUrlsForPost(int $postId, string $postType): array
    {
        $urls = [];

        $taxonomies = get_object_taxonomies($postType, 'objects');
        if (!is_array($taxonomies)) {
            return [];
        }

        foreach ($taxonomies as $taxonomy) {
            if (!$taxonomy instanceof \WP_Taxonomy) {
                continue;
            }
            if (!$taxonomy->public) {
                continue;
            }

            $terms = wp_get_post_terms($postId, $taxonomy->name);
            if (!is_array($terms) || is_wp_error($terms)) {
                continue;
            }

            foreach ($terms as $term) {
                if (!$term instanceof \WP_Term) {
                    continue;
                }
                $link = get_term_link($term);
                if (is_string($link) && $link !== '' && !is_wp_error($link)) {
                    $urls[] = $link;
                }
            }
        }

        return array_values(array_unique($urls));
    }

    /**
     * @param array<string, mixed> $context
     */
    private function debugLog(string $message, array $context = []): void
    {
        if (!defined('WP_DEBUG_LOG') || !WP_DEBUG_LOG) {
            return;
        }

        $line = '[MUACP] ' . $message;
        if ($context) {
            $line .= ' ' . wp_json_encode($context);
        }

        error_log($line);
    }
}

