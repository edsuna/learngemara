# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

LearnGemara is a Laravel 13 TALL stack application (Tailwind CSS, Alpine.js, Livewire, Laravel) for creating and sharing Talmudic learning cases. It supports bilingual Hebrew/English UI, Google OAuth, and public/private case sharing.

## Common Commands

```bash
# Development
composer dev             # Run server + queue + logs + Vite together (concurrently)
php artisan serve        # Start local dev server only
npm run dev              # Start Vite dev server only

# Production
npm run build            # Minified production asset build (Vite)

# First-time setup (installs deps, copies .env, generates key, migrates, builds)
composer setup

# Database
php artisan migrate      # Run migrations

# Testing
composer test                                          # Run all tests (clears config, then artisan test)
php artisan test                                        # Run all tests
./vendor/bin/phpunit tests/Feature/GemaraCaseTest.php  # Run single test file
./vendor/bin/phpunit --filter testMethodName           # Run single test method

# Dependencies
composer install && npm install
```

## Architecture

### Backend
- **Controllers:** `app/Http/Controllers/` — `GemaraCaseController` handles CRUD, `Home` serves the landing page
- **Livewire Components:** `app/Livewire/` — `GemaraCases` (list with search/filter/pagination), `TopBar` (navigation), plus `Auth/` (auth components)
- **Models:** `app/Models/` — `User`, `GemaraCase`, `Tractate`
- **Routes:** `routes/web.php` (pages + form submissions), `routes/api.php` (JSON endpoints for tractates)

### Frontend
- **Build tool:** Vite (`vite.config.js`), via `laravel-vite-plugin`. Entry inputs: `resources/css/app.css` and `resources/js/app.js`.
- **Key JS modules** (in `resources/js/`):
  - `gemara_case.js` — Main SPA logic for case create/edit forms, exposes `window.gemaraCase()`
  - `language.js` — Hebrew/English toggle state
  - `arrows.js` — SVG diagram drawing
- **Blade templates:** `resources/views/` with layouts in `layouts/`, Livewire views in `livewire/`
- **Styles:** Tailwind CSS v4, configured via the `@tailwindcss/vite` plugin (no `tailwind.config.js`); entry stylesheet `resources/css/app.css`

### Data Flow
Forms submit via AJAX (Alpine.js → POST/PUT to GemaraCaseController → JSON response). Livewire handles the case listing page with real-time filtering. The `/api/tractates` endpoint provides tractate data for form dropdowns.

### Authentication
Email/password auth via Livewire components plus Google OAuth via Socialite (`/auth/google`, `/auth/callback`).

### Database
MySQL with three main tables: `users` (with google_id for OAuth), `tractates` (reference data for 63 tractates), `gemara_cases` (cases with dynamic condition fields like consequences, when, where, who, etc.).
