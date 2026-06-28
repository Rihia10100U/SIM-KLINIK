# SIM Klinik — Agent Guide

Laravel 13 + Livewire 4 clinic management system (queues, registration, medical records, pharmacy, billing, reports, kiosk).

## Quick start

```bash
composer setup          # full first-time setup
composer dev            # serve + queue + logs + vite concurrently
composer test           # config:clear + artisan test
```

- PHP 8.3+, MySQL (`sim_klinik`), node via Vite
- `.env` sets `APP_LOCALE=id`, `TIMEZONE=Asia/Jakarta`
- `.npmrc` sets `ignore-scripts=true` — always add `--ignore-scripts` to `npm install` calls
- Lint: `./vendor/bin/pint`
- Tests: SQLite `:memory:` (see `phpunit.xml` — sets `DB_CONNECTION=sqlite`, `QUEUE_CONNECTION=sync`, `SESSION_DRIVER=array`, `CACHE_STORE=array`)
- Known: `ExampleTest` fails with 302 (redirects to login for unauthenticated users) — expected

## Architecture

- **No Vue/React.** All UI is Livewire 4 full-page components (`#[Layout()]`) + Blade.
- `routes/web.php` maps each route to one Livewire class — no traditional controllers.
- **5 roles** (`App\Enums\Role`): `admin`, `resepsionis`, `dokter`, `petugas_rekam_medis`, `apoteker`
- Custom `role` middleware (`EnsureRole`), aliased via `bootstrap/app.php`: Middleware alias `'role'`
- Route groups use `->middleware('role:admin,resepsionis')` etc.
- Public kiosk routes (no auth) under `/kiosk/*`
- Routes for legacy components (`PanggilanPendaftaran`, `DataLayanan`, `KioskTunggu`) redirect elsewhere — do not create new routes pointing to them.

## Directory layout

```
app/
  Livewire/           # Livewire components
  Models/             # Eloquent models
  Enums/              # Role enum
  Services/           # ThermalPrinter
  Http/Middleware/    # EnsureRole
resources/
  views/
    livewire/         # Component views
    components/
      layouts/        # app.blade.php, kiosk.blade.php, guest.blade.php
      icon.blade.php  # Custom <x-icon> SVG component
database/
  seeders/            # Order: PoliSeeder+PasienSeeder → AntrianSeeder etc.
```

## Key packages

- `livewire/livewire:^4.3` — all interactive UI
- `mike42/escpos-php` — thermal printer
- `laravel/pint` — PHP CS fixer
- `laravel/pail` — real-time log viewer (part of `composer dev`)

## Thermal printer

Config in `config/printer.php`, driven by env:
```
PRINTER_CONNECTION=windows    # windows | network | usb | com | none
PRINTER_WINDOWS_NAME=POS-58
```
Service class: `App\Services\ThermalPrinter`. Returns `false` on failure (never throws) so kiosk flow isn't blocked.

## Database

- MySQL in `.env` for local dev, SQLite `:memory:` for tests
- `SESSION_DRIVER=database`, `QUEUE_CONNECTION=database`, `CACHE_STORE=database`
- `APP_MAINTENANCE_STORE=database`
- Seeder ordering matters: `PoliSeeder` + `PasienSeeder` must run before `AntrianSeeder` etc.

## Test users (password: `password`)

| Role | Email |
|------|-------|
| Admin | admin@simklinik.test |
| Resepsionis | resepsionis@simklinik.test |
| Dokter | dokter@simklinik.test |
| Petugas Rekam Medis | rekammedis@simklinik.test |
| Apoteker | apoteker@simklinik.test |

## Conventions

- Use `php artisan make:livewire ComponentName` for new pages
- Models in `App\Models`, Livewire components in `App\Livewire`, views in `resources/views/livewire/`
- Layouts: `components.layouts.app` (auth), `components.layouts.kiosk` (public), `components.layouts.guest` (login)
- Sidebar active nav: `request()->routeIs('route-name') ? 'active' : ''` in `app.blade.php`
- Sidebar links use `<x-icon name="..." />` — icons defined in `resources/views/components/icon.blade.php`
- Reusable CSS in `resources/css/app.css`: `.nav-link`, `.card`, `.badge`, `.btn`, `.btn-primary`, `.input-field`, `.search-input`
- `.print-ticket` class + `@media print` block for thermal ticket printing via `window.print()`
- Tailwind custom colors: `klinik-bg`, `klinik-blue-dark`, `klinik-green`, `klinik-green-dark`
- Modal pattern: overlay `fixed inset-0 bg-black/40 flex items-start justify-center z-50 p-4 overflow-y-auto` > content `bg-white rounded-2xl shadow-xl p-6 mt-8 mb-8 max-h-[calc(100vh-4rem)] overflow-y-auto`
- Icons are SVG paths in a Blade component — add new entries to the `$icons` array in `resources/views/components/icon.blade.php`
- **Known issue**: `kiosk.blade.php` loads `js/antrian-speaker.js` but that file does not exist; only `public/js/call-queue.js` does. Fix `<script>` src if adding speaker functionality.
