<p align="center">
  <a href="https://roots.io/bedrock/">
    <img alt="Bedrock" src="https://cdn.roots.io/app/uploads/logo-bedrock.svg" height="100">
  </a>
</p>

<p align="center">
  <a href="https://packagist.org/packages/roots/bedrock"><img alt="Packagist Installs" src="https://img.shields.io/packagist/dt/roots/bedrock?label=projects%20created&colorB=2b3072&colorA=525ddc&style=flat-square"></a>
  <a href="https://packagist.org/packages/roots/wordpress"><img alt="roots/wordpress Packagist Downloads" src="https://img.shields.io/packagist/dt/roots/wordpress?label=roots%2Fwordpress%20downloads&logo=roots&logoColor=white&colorB=2b3072&colorA=525ddc&style=flat-square"></a>
  <img src="https://img.shields.io/badge/dynamic/json.svg?url=https://raw.githubusercontent.com/roots/bedrock/master/composer.json&label=wordpress&logo=roots&logoColor=white&query=$.require[%22roots/wordpress%22]&colorB=2b3072&colorA=525ddc&style=flat-square">
  <a href="https://github.com/roots/bedrock/actions/workflows/ci.yml"><img alt="Build Status" src="https://img.shields.io/github/actions/workflow/status/roots/bedrock/ci.yml?branch=master&logo=github&label=CI&style=flat-square"></a>
  <a href="https://twitter.com/rootswp"><img alt="Follow Roots" src="https://img.shields.io/badge/follow%20@rootswp-1da1f2?logo=twitter&logoColor=ffffff&message=&style=flat-square"></a>
  <a href="https://github.com/sponsors/roots"><img src="https://img.shields.io/badge/sponsor%20roots-525ddc?logo=github&style=flat-square&logoColor=ffffff&message=" alt="Sponsor Roots"></a>
</p>

<p align="center">WordPress boilerplate with Composer, easier configuration, and an improved folder structure</p>

<p align="center">
  <a href="https://roots.io/bedrock/">Website</a> &nbsp;&nbsp; <a href="https://roots.io/bedrock/docs/installation/">Documentation</a> &nbsp;&nbsp; <a href="https://github.com/roots/bedrock/releases">Releases</a> &nbsp;&nbsp; <a href="https://discourse.roots.io/">Community</a>
</p>

## Support us

We're dedicated to pushing modern WordPress development forward through our open source projects, and we need your support to keep building. You can support our work by purchasing [Radicle](https://roots.io/radicle/), our recommended WordPress stack, or by [sponsoring us on GitHub](https://github.com/sponsors/roots). Every contribution directly helps us create better tools for the WordPress ecosystem.

### Sponsors

<a href="https://carrot.com/"><img src="https://cdn.roots.io/app/uploads/carrot.svg" alt="Carrot" width="120" height="90"></a> <a href="https://wordpress.com/"><img src="https://cdn.roots.io/app/uploads/wordpress.svg" alt="WordPress.com" width="120" height="90"></a> <a href="https://www.itineris.co.uk/"><img src="https://cdn.roots.io/app/uploads/itineris.svg" alt="Itineris" width="120" height="90"></a> <a href="https://kinsta.com/?kaid=OFDHAJIXUDIV"><img src="https://cdn.roots.io/app/uploads/kinsta.svg" alt="Kinsta" width="120" height="90"></a>

## Overview

Bedrock is a WordPress boilerplate for developers that want to manage their projects with Git and Composer. Much of the philosophy behind Bedrock is inspired by the [Twelve-Factor App](http://12factor.net/) methodology, including the [WordPress specific version](https://roots.io/twelve-factor-wordpress/).

- Better folder structure
- Dependency management with [Composer](https://getcomposer.org)
- Easy WordPress configuration with environment specific files
- Environment variables with [Dotenv](https://github.com/vlucas/phpdotenv)
- Autoloader for mu-plugins (use regular plugins as mu-plugins)

## Getting Started

See the [Bedrock installation documentation](https://roots.io/bedrock/docs/installation/).

## Ambiente locale Poppins (Docker)

1. Duplica `.env.example` in `.env` (già incluso) e verifica che i valori corrispondano alla configurazione Docker (`DB_HOST=db`, `DB_NAME=poppins_db`, ecc.). Aggiorna le chiavi prima della messa in produzione.
2. Costruisci e avvia i container:

   ```bash
   docker compose up -d --build
   ```

3. Se stai partendo da un clone nuovo, installa le dipendenze PHP direttamente nel container:

   ```bash
   docker compose run --rm php composer install
   ```

4. Completa l’installazione di WordPress visitando `http://localhost:8080/wp/wp-admin/install.php` (le credenziali del database sono definite in `.env` e replicate anche nel servizio `db` di Docker Compose).

Comandi utili:

- Arresto dei container: `docker compose down`
- Pulizia database (attenzione, cancella i dati): `docker compose down -v`
- Accesso alla shell PHP-FPM: `docker compose exec php bash`
- Importare nuove dipendenze: `docker compose run --rm php composer require ...`

Il database MariaDB utilizza un volume nominato `db_data`, conservando i dati tra un riavvio e l’altro.

## Produzione

- Costruisci l’immagine ottimizzata (nginx + PHP-FPM) tramite `Dockerfile.deploy`:

  ```bash
  docker build -f Dockerfile.deploy -t poppins-app:latest .
  ```

- Oppure utilizza direttamente `docker-compose.prod.yml` su un host con Docker:

  ```bash
  docker compose -f docker-compose.prod.yml up -d --build
  ```

  Il servizio `app` serve l’applicazione su `:80` e monta un volume `uploads_data` per i media; il database è esterno al cluster Docker e le credenziali vengono lette da variabili (`DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASSWORD`) definite in `.env` o tramite secrets esterni.

- La pipeline Jenkins (vedi `Jenkinsfile`) esegue `composer install`, lint con Pint, build dell’immagine di deploy e push verso il registry definito in `DOCKER_REGISTRY`. Imposta l’ID credenziali Docker in `REGISTRY_CREDENTIALS` e, se vuoi avviare automaticamente l’ambiente via Compose, esporta `DEPLOY_ON_BUILD=true` sul job.

## Tema Tailwind

- Il tema `Poppins Tailwind` vive in `web/app/themes/poppins-tailwind`. Include un toolchain Tailwind (vedi `package.json`) e file sorgenti CSS in `resources/css/main.css`.
- Per lavorare sugli stili:

  ```bash
  cd web/app/themes/poppins-tailwind
  npm install          # solo la prima volta
  npm run dev          # watch in locale
  npm run build        # genera dist/css/main.css minificato
  ```

- Il tema enqueua automaticamente `dist/css/main.css` (prodotto dalla build) e offre template minimal (front-page, header, footer) già ottimizzati per l’ecommerce.
- Attivalo da WP Admin (Aspetto → Temi) oppure modifica `.env`/WP CLI per selezionare `poppins-tailwind`.

## Stay Connected

- Join us on Discord by [sponsoring us on GitHub](https://github.com/sponsors/roots)
- Participate on [Roots Discourse](https://discourse.roots.io/)
- Follow [@rootswp on Twitter](https://twitter.com/rootswp)
- Read the [Roots Blog](https://roots.io/blog/)
- Subscribe to the [Roots Newsletter](https://roots.io/newsletter/)
