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

# Testing — Backend (PHPUnit)
composer test                                          # Run all tests (clears config, then artisan test)
php artisan test                                        # Run all tests
./vendor/bin/phpunit tests/Feature/GemaraCaseTest.php  # Run single test file
./vendor/bin/phpunit --filter testMethodName           # Run single test method

# Testing — Frontend (Vitest unit + Playwright E2E)
npm test                 # Run Vitest unit tests once (resources/js/__tests__/)
npm run test:watch       # Vitest in watch mode
npm run test:e2e         # Run Playwright E2E tests (tests/e2e/); auto-starts `php artisan serve`
npm run test:e2e:ui      # Playwright in interactive UI mode

# Dependencies
composer install && npm install
```

### Testing notes
- **Backend** tests live in `tests/Feature/` and `tests/Unit/` (PHPUnit).
- **Frontend unit** tests live in `resources/js/__tests__/` (Vitest, config in `vitest.config.js`).
- **E2E** tests live in `tests/e2e/` (Playwright, config in `playwright.config.js`). The config auto-starts `php artisan serve` on `127.0.0.1:8000` and reuses an existing server if one is running. E2E tests need built assets (`npm run build`), a running MySQL database with seeded tractates (`php artisan db:seed`), and the Playwright browsers installed (`npx playwright install chromium`).

## Environments

| Environment | Location | Branch | Database | Purpose |
|---|---|---|---|---|
| Local dev | `/home/eds/learngemara` | feature branch | `learngemara` | active development |
| Local prod-parity | `~/learngemara-prod` | `production` | `learngemara_prod` | reproduce production bugs, A/B compare |
| Remote dev | dev.howtolearngemara.org | any (`./deploy.sh test <branch>`) | `howtolearn_dev` | integration testing on real hosting |
| Production | app.howtolearngemara.org | `master` (`./deploy.sh production`) | `howtolearn_laravel` | live |

### Knowing what is deployed

The `production` branch records the commit currently running in production.
`./deploy.sh production` fast-forwards and pushes it after a successful deploy, so
`git log production` answers "what is live" without SSHing to the server. Do not commit to
this branch by hand.

### Local prod-parity checkout

A git worktree pinned to `production`, for reproducing a production issue against the code that is
actually running while your main checkout is somewhere ahead.

```bash
git worktree add ~/learngemara-prod production
cd ~/learngemara-prod
cp /home/eds/learngemara/.env .env      # then set DB_DATABASE=learngemara_prod, APP_URL=http://localhost:8001
composer install && npm ci && npm run build
php artisan serve --port=8001
```

- Use `npm ci`, not `npm install` — the latter rewrites the `name` field in `package-lock.json`
  to match the directory, dirtying the worktree.
- Use built assets (`npm run build`), not the Vite dev server; that is what production serves.
- Refresh it after a production deploy: `git -C ~/learngemara-prod pull --ff-only`
- Never deploy from it. `deploy.sh` refuses to run from a linked worktree or while on `production`.
- Keep it on port 8001: `playwright.config.js` targets 8000 with `reuseExistingServer`, so a stray
  server on 8000 would silently absorb the E2E run.

### Copying production data down

```bash
./deploy.sh copydb          # production DB -> dev DB (both on the server)
./deploy.sh copydb local    # production DB -> local learngemara_prod
```

`copydb local` verifies real `COUNT(*)` per table against production afterwards and fails on any
mismatch — `mysqldump` has silently under-dumped on this host before. The `learngemara_prod`
database must be created once by a MySQL admin; the app user lacks `CREATE DATABASE` rights.

### Deterministic CSS builds

`resources/css/app.css` begins with `@import 'tailwindcss' source(none)` followed by explicit
`@source` lines. **Do not remove `source(none)`.** Tailwind v4 otherwise auto-scans every
non-gitignored file in the project, which made the production CSS depend on local state: compiled
Blade views in `storage/framework/views/` pulled in ~21 kB of Laravel's exception-renderer styles,
and stray Markdown files contributed utilities from ordinary English words. Two checkouts of the
same commit produced different CSS. With explicit sources they build byte-identically.

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
MySQL with three main tables: `users` (with google_id for OAuth), `tractates` (reference data for 37 tractates), `gemara_cases` (cases with dynamic condition fields like consequences, when, where, who, etc.).
