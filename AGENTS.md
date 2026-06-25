# SIM Klinik — Agent Guide

Laravel 13 + Livewire 4 clinic management system (queues, registration, medical records, pharmacy, billing, reports, kiosk).

## Quick start

```bash
composer setup          # full first-time setup
composer dev            # serve + queue + logs + vite concurrently
composer test           # config:clear + artisan test
```

- PHP 8.3+, MySQL (`sim_klinik`), node via Vite
- `.env` sets Indonesian locale/timezone: `APP_LOCALE=id`, `TIMEZONE=Asia/Jakarta`
- `.npmrc` has `ignore-scripts=true` — add `--ignore-scripts` to `npm install` calls

## Architecture

- **No Vue/React.** All UI is Livewire 4 full-page components (`#[Layout()]`) + Blade.
- Routes in `routes/web.php` — each maps to a Livewire class, no traditional controllers.
- **5 roles** (enum `App\Enums\Role`): `admin`, `resepsionis`, `dokter`, `petugas_rekam_medis`, `apoteker`
- Custom `role` middleware (`EnsureRole`) registered in `bootstrap/app.php`
- Public kiosk routes (no auth) for patient self-service under `/kiosk/*`

## Key packages

- `livewire/livewire:^4.3` — all interactive UI
- `mike42/escpos-php` — thermal printer (kiosk & registration queue tickets)
- `laravel/pint` — PHP CS fixer (`./vendor/bin/pint`)
- `laravel/pail` — real-time log viewer (part of `composer dev`)

## Thermal printer

Config in `config/printer.php`, driven by env:
```
PRINTER_CONNECTION=windows    # windows | network | usb | none
PRINTER_WINDOWS_NAME=POS-58
```
Service class: `App\Services\ThermalPrinter`. Returns `false` on failure (never throws) so kiosk flow isn't blocked.

## Database

- MySQL configured in `.env` for local dev, SQLite `:memory:` for tests
- `SESSION_DRIVER=database`, `QUEUE_CONNECTION=database`, `CACHE_STORE=database`
- Seeder ordering matters: `PoliSeeder` + `PasienSeeder` must run before `AntrianSeeder` etc.

## Test users (all have password `password`)

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
- Sidebar active nav uses `request()->routeIs('route-name') ? 'active' : ''` in `app.blade.php`
- New sidebar links use `<x-icon name="..." />` — available icons defined in `resources/views/components/icon.blade.php`
- Reusable CSS classes in `resources/css/app.css`: `.nav-link`, `.card`, `.badge`
- `.print-ticket` class + `@media print` block for thermal ticket printing via `window.print()`
- Tailwind theme colors in `tailwind.config.js`: `klinik-bg`, `klinik-blue`. However, `klinik-green` and `klinik-green-dark` are used throughout views but **not defined** in the config — adding them to `tailwind.config.js` colors may be needed for correct rendering.
