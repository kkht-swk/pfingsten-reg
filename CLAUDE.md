# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a Symfony 7.4 (LTS 6.4+) PHP web application for managing team and player registrations for the SWK Pfingsten (Whitsun) tournament. The application provides German/English/Dutch multi-language support and sends email confirmations for registrations.

## Technology Stack

- **PHP**: 8.1+
- **Framework**: Symfony 7.4 (LTS)
- **Database**: MariaDB (via Doctrine ORM)
- **Templating**: Twig
- **Email**: Symfony Mailer (SMTP)
- **Asset Management**: Symfony Asset Mapper
- **Package Manager**: Composer

## Development Environment Setup

### Starting Services

Development uses Docker Compose for database and email services:

```bash
# Start MariaDB database and Mailpit (email testing)
docker compose up

# Start Symfony development server (separate terminal)
symfony server:start

# Open browser to local site
symfony open:local
```

### Stopping Services

```bash
symfony server:stop
docker compose stop
```

### Accessing Services

- **Web Application**: Auto-opened by `symfony open:local`
- **Mailpit (Email Testing)**: http://localhost:8025/
- **Adminer (Database Client)**: http://localhost:8080/

## Database Management

### Initial Setup

```bash
# Create database (skip if using Docker, as it auto-creates)
bin/console doctrine:database:create

# Run migrations to create tables
bin/console doctrine:migrations:migrate
```

### Development Workflow

After modifying entities in `src/Entity/`:

```bash
# Generate migration file
bin/console make:migration

# Apply migrations to database
bin/console doctrine:migrations:migrate
```

## Common Development Commands

### Package Management

```bash
# Install dependencies from composer.lock
composer install

# Update dependencies from composer.json
composer update
```

### Code Generation

```bash
# Create new entity
bin/console make:entity

# Create new controller
bin/console make:controller
```

### Cache Management

```bash
# Clear cache (useful when things behave unexpectedly)
bin/console cache:clear
```

## Production Deployment

### Build for Production

```bash
# Compile assets for production
bin/console asset-map:compile

# Optimize environment variables
composer dump-env prod

# Clear cache
bin/console cache:clear
```

### Package for Deployment

```bash
tar czvf ../pf.tgz assets/ bin/ composer.json composer.lock config/ importmap.php LICENSE migrations/ public/ src/ templates/ translations/ .env.prod.local
scp ../pf.tgz <server>:
```

On the production server:
1. Configure `.env.local` or `.env.prod.local` with `APP_ENV=prod`, database, and email server settings
2. Run `bin/console doctrine:database:create` (if database doesn't exist)
3. Run `bin/console doctrine:migrations:migrate`
4. Run `bin/console asset-map:compile`
5. Run `composer dump-env prod`
6. Run `bin/console cache:clear`

## Application Architecture

### Core Entities

The application has two main registration flows, each with its own entity structure:

1. **PlayerInfo** (`src/Entity/PlayerInfo.php`): Individual player registrations
   - One-to-one relationships with `Kontakt` (contact info) and `BankAccount`
   - Auto-generates unique `hashkey` for URL-based access
   - Validates: vorname, nachname, altersklasse (age category), nahrung (food preference)

2. **TeamInfo** (`src/Entity/TeamInfo.php`): Team registrations
   - One-to-one relationships with `Kontakt` and `BankAccount`
   - Auto-generates unique `hashkey` for URL-based access
   - Tracks: verein (club), altersklasse, ankunftszeit (arrival time)
   - Includes player/coach/guest counts (vegan/meat options)
   - Supports optional logo and team picture uploads
   - Calculates cost: 90 EUR per person (players + coaches + guests)
   - Has `lastSavedAt` timestamp for edit tracking

### Routing Structure

Routes are defined using PHP 8 attributes in controllers:

- `/` - Static homepage (DefaultController)
- `/player/*` - Player registration routes
  - `/player/register` - New player registration
  - `/player/summary/{hashkey}` - Confirmation page
  - `/player/list` - Admin: List all players (by age category)
  - `/player/csv` - Admin: Export players to CSV
  - `/player/delete/{id}` - Admin: Delete player

- `/{_locale}/team/*` - Team registration routes (multi-language: de/en/nl)
  - `/{_locale}/team/register` - New team registration
  - `/{_locale}/team/register/{hashkey}` - Edit existing team
  - `/{_locale}/team/summary/{hashkey}` - Confirmation page
  - `/{_locale}/team/master/{hashkey}` - Admin edit (no email sent)
  - `/{_locale}/team/list` - Admin: List all teams (by age category)
  - `/{_locale}/team/csv` - Admin: Export teams to CSV
  - `/{_locale}/team/delete/{id}` - Admin: Delete team

### Configuration

- **Global Config**: `src/Config.php` - Contains constants like `SWK_YEAR`
- **Services**: `config/services.yaml` - Defines `app.upload_dir` parameter for file uploads
- **Environment**: `.env` - Database and email server configuration
- **Upload Directory**: `public/uploads/` - Team logos and pictures are stored here

### Email System

Both controllers send confirmation emails using Symfony Mailer:
- Templates in `templates/email/` (HTML and text versions)
- Team emails include an edit link with the hashkey
- All emails BCC to `pfingsten@kkht.de`

### Form Validation

Uses Symfony Validator with attributes on entities:
- `#[Assert\NotBlank]` for required fields
- `#[Assert\NotNull]` and `#[Assert\GreaterThanOrEqual]` for numeric fields
- Error messages use translation keys (e.g., `error.vorname.notblank`)

### Debugging

XDebug is configured in `php.ini` for use with the Symfony development server. Start "Listen for XDebug" in your IDE and set breakpoints as needed.

## Important Notes

- All entities use `DateTimeImmutable` for timestamps
- Hashkeys are 64-character hex strings generated with `bin2hex(random_bytes(32))`
- Age categories (altersklasse) are: wU12, mU12, wU14, mU14
- File uploads use Symfony's SluggerInterface for safe filenames
- Database migrations are in `migrations/` directory
- Templates use Twig and are in `templates/` directory
- Translations are in `translations/` directory
