# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Orion is a BTS (Brevet de Technicien Supérieur) access control system with three components:

- **`Orion-main/`** — Symfony 8.0 web dashboard (PHP 8.4+, MySQL/MariaDB)
- **`api/`** — Standalone PHP REST API consumed by hardware
- **`esp32/`** — PlatformIO firmware for ESP32 microcontrollers
- **`tests/`** — Python test simulator for the API

## Commands

All commands below run from inside `Orion-main/`.

### Development

```bash
# Install dependencies
composer install

# Start dev server (requires Symfony CLI)
symfony serve
# Or built-in PHP server:
php -S localhost:8000 -t public/

# Clear cache
php bin/console cache:clear

# Install JS imports
php bin/console importmap:install
```

### Database

```bash
# Run all pending migrations
php bin/console doctrine:migrations:migrate

# Create a new migration from entity changes
php bin/console doctrine:migrations:make DescriptionHere
```

### Tests

```bash
# Run all PHPUnit tests
php bin/phpunit

# Run a single test file
php bin/phpunit tests/SomeTest.php

# Run API integration tests (from project root)
cd tests && python simulateur.py
```

### Debugging

```bash
php bin/console debug:router        # List all routes
php bin/console debug:container     # List services
```

## Architecture

### Symfony Web App (`Orion-main/`)

Standard Symfony 8.0 structure with:
- **Controllers**: `src/Controller/` — `HomeController`, `AdminController`, `SecurityController`
- **Entities**: `src/Entity/` — `Utilisateur`, `QRCode`, `Porte` (door), `Badge`, `Code`, `LogAccess`
- **Services**: `src/Service/` — `QrCodeGeneratorService`, `TokenGeneratorService` (JWT via firebase/php-jwt)
- **Security**: `src/Security/AppAuthenticator` — form-based login, loads user by email
- **Templates**: Twig in `templates/` (base layout + `admin/`, `home/`, `security/` subdirs)
- **Assets**: Symfony Asset Mapper with importmaps (no Webpack/Vite — assets in `public/assets/`)
- **Frontend JS**: Stimulus controllers + Turbo (Hotwired stack)

Access control enforces `/admin/*` requires `ROLE_ADMIN` (configured in `config/packages/security.yaml`).

### Standalone API (`api/`)

Three independent PHP endpoints, each enforcing:
- POST-only with Bearer token authentication
- Rate limiting (5 req/5 min; stricter for PIN)
- Input validation via regex
- Daily log files at `api/logs/orion_YYYY-MM-DD.log`

Shared security utilities in `api/config/security.php`. Business logic split into `api/functions/` (one file per access method: badge, qrcode, pin).

### Database

MariaDB 10.4, local default: `mysql://root@127.0.0.1:3306/orion`. Configure actual credentials in `Orion-main/.env.local` (not committed). Migrations tracked in `Orion-main/migrations/`.

### ESP32 Firmware (`esp32/`)

PlatformIO project. Use `pio run` to build. Wokwi simulator config in `esp32/wokwi.toml` + `esp32/diagram.json`.

## Key Conventions

- **Token generation**: `TokenGeneratorService` reads `APP_TOKEN_ENCRYPTION_KEY` from env — the same key must be configured in the API for hardware authentication to work.
- **QR codes**: Generated server-side via `endroid/qr-code`; stored as `QRCode` entities linked one-to-one to `Utilisateur`.
- **PIN security**: PINs are never logged in plaintext anywhere in the codebase — maintain this invariant.
- **Roles**: Stored as JSON array on `Utilisateur` and `Porte` entities; `ROLE_ADMIN` grants admin panel access.
