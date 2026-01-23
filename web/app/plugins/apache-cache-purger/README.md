## Apache Cache Purger (MUACP)

Plugin WordPress per purge selettivo della cache `mod_cache_disk` tramite `htcacheclean`.

### Installazione

- Cartella plugin: `web/app/plugins/apache-cache-purger/`
- Entry point: `web/app/plugins/apache-cache-purger/apache-cache-purger.php`

Esegui autoload Composer nel container/host:

```bash
cd web/app/plugins/apache-cache-purger
composer install --no-dev --prefer-dist --no-interaction --no-progress --optimize-autoloader
```

Poi **attiva il plugin** da wp-admin (Plugins).

### Configurazione via costanti (wp-config.php)

Default se non definite:

- `MUACP_CACHE_ROOT` (default `/var/cache/apache2/mod_cache_disk/EXAMPLE_COM`)
- `MUACP_HTCACHECLEAN_BIN` (default `/usr/sbin/htcacheclean`)
- `MUACP_ENABLED` (default `true`)
- `MUACP_CAPABILITY` (default `manage_options`)
- `MUACP_PURGE_SHOP` (default `true`)

### Sicurezza / sudoers minimizzato

Se il processo PHP non può eseguire `htcacheclean`, configura sudoers in modo restrittivo e usa `sudo -n`:

```sudoers
Defaults:www-data !requiretty
www-data ALL=(root) NOPASSWD: /usr/sbin/htcacheclean -p /var/cache/apache2/mod_cache_disk/EXAMPLE_COM *
```

Poi in `wp-config.php`:

```php
define('MUACP_HTCACHECLEAN_BIN', '/usr/bin/sudo -n /usr/sbin/htcacheclean');
define('MUACP_CACHE_ROOT', '/var/cache/apache2/mod_cache_disk/EXAMPLE_COM');
```

