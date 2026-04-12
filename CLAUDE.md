# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

LearnGemara is a Laravel 8 TALL stack application (Tailwind CSS, Alpine.js, Livewire, Laravel) for creating and sharing Talmudic learning cases. It supports bilingual Hebrew/English UI, Google OAuth, and public/private case sharing.

## Common Commands

```bash
# Development
npm run dev              # Build frontend assets
npm run watch            # Build with file watching
php artisan serve        # Start local dev server

# Production
npm run prod             # Minified production build

# Database
php artisan migrate      # Run migrations

# Testing
./vendor/bin/phpunit                           # Run all tests
./vendor/bin/phpunit tests/Feature/GemaraCaseTest.php  # Run single test file
./vendor/bin/phpunit --filter testMethodName    # Run single test method

# Dependencies
composer install && npm install
```

## Architecture

### Backend
- **Controllers:** `app/Http/Controllers/` — `GemaraCaseController` handles CRUD, `Home` serves the landing page
- **Livewire Components:** `app/Http/Livewire/` — `GemaraCases` (list with search/filter/pagination), `TopBar` (navigation)
- **Models:** `app/Models/` — `User`, `GemaraCase`, `Tractate`
- **Routes:** `routes/web.php` (pages + form submissions), `routes/api.php` (JSON endpoints for tractates)

### Frontend
- **Entry point:** `resources/js/app.js` → compiled to `public/js/app.js` via webpack.mix.js
- **Key JS modules:**
  - `gemara_case.js` — Main SPA logic for case create/edit forms, exposes `window.gemaraCase()`
  - `language.js` — Hebrew/English toggle state
  - `arrows.js` — SVG diagram drawing
- **Blade templates:** `resources/views/` with layouts in `layouts/`, Livewire views in `livewire/`
- **Styles:** Tailwind CSS configured in `tailwind.config.js`, compiled from `resources/sass/app.scss`

### Data Flow
Forms submit via AJAX (Alpine.js → POST/PUT to GemaraCaseController → JSON response). Livewire handles the case listing page with real-time filtering. The `/api/tractates` endpoint provides tractate data for form dropdowns.

### Authentication
Email/password auth via Livewire components plus Google OAuth via Socialite (`/auth/google`, `/auth/callback`).

### Database
MySQL with three main tables: `users` (with google_id for OAuth), `tractates` (reference data for 63 tractates), `gemara_cases` (cases with dynamic condition fields like consequences, when, where, who, etc.).
