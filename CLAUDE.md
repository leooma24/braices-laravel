# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project

Laravel 10 (PHP 8.1+) real-estate marketplace in Spanish (Mexico). Users publish properties, browse listings, and reserve short-stay properties. Payments via PayPal and MercadoPago. Admin role manages users, banners and packages. Auth supports email + Facebook (Socialite). Roles via `spatie/laravel-permission`.

UI is server-rendered Blade with Bootstrap; assets bundled with Vite. Local dev runs on Laragon (Windows). The user is Spanish-speaking — keep flash messages, validation errors, and UI strings in Spanish.

## Commands

Development is on Windows + Laragon, but commands are standard Laravel. Run from `c:\laragon\www\braices-laravel`.

```powershell
# Install
composer install
npm install

# Run dev assets watcher (Vite)
npm run dev

# Build assets for production
npm run build

# Database
php artisan migrate
php artisan migrate:fresh --seed   # destructive, drops all tables

# Tests (PHPUnit)
php artisan test
php artisan test --filter=SomeTest      # single class
php artisan test --filter=SomeTest::testMethodName

# Lint / format (Pint - Laravel's PHP CS Fixer wrapper)
./vendor/bin/pint
./vendor/bin/pint --test                # check only, no changes

# Useful one-offs
php artisan route:list
php artisan tinker
php artisan storage:link
```

The Laravel app key + DB credentials live in `.env` (copy from `.env.example` if missing).

## Architecture

### Routing model

All routes are in [routes/web.php](routes/web.php) — there is no `api.php` controller surface in active use. URLs are Spanish (`/propiedades`, `/cuenta/perfil`, `/administrador/...`). Public + auth + admin route groups are defined in that single file.

Three middleware groups gate access:
- `auth` + `verified` — logged-in user area (`/cuenta/...`, `/propiedad/{id}/guardar`)
- `role:admin` (Spatie) — admin panel (`/administrador/...`)
- `validate.property` — custom middleware that runs before property create/update (see below)

### Property validation pipeline (non-obvious)

Property forms do **not** use a single `FormRequest`. Instead:

1. The `validate.property` middleware [app/Http/Middleware/ValidatePropertyRequest.php](app/Http/Middleware/ValidatePropertyRequest.php) reads `property_type_id`, looks up the type, and asks [PropertyRequestFactory](app/Http/Requests/PropertyRequestFactory.php) for the appropriate request class.
2. The factory returns `LandRequest` when the type name contains `"terreno"`, otherwise `PropertyRequest` — so validation rules differ for land vs. built properties.
3. The middleware runs `Validator::make()` with the chosen rules; if it fails, it redirects back with errors.
4. The middleware then calls `$next($request)` — the controller receives the original `Illuminate\Http\Request`, **not** a typed FormRequest. Controllers therefore use `$request->all()` / `$request->only()` and rely on `Property`'s `$fillable` to limit mass assignment. Sensitive fields (`property_status_id`, `user_id`, `slug`) must be unset before `fill()` and set explicitly.

When adding new property fields:
- Add to `Property::$fillable` if user-settable, otherwise leave it out.
- Add validation in `PropertyRequest` and/or `LandRequest`.
- Database column goes via `add_new_fields_to_properties_table` style migration.

### Property publishing flow

`PropertyController::create` ([app/Http/Controllers/PropertyController.php](app/Http/Controllers/PropertyController.php)) requires the user to have a `userPackage` with `remaining_listings > 0`. The package counter is decremented after the property is saved. New properties are forced to `property_status_id = 1` (active) — never trust this value from the request.

`Property::propertyTypes()` is a many-to-many through `property_property_type`. There is also a legacy `Property::type()` `hasOne` based on a deprecated `property_type_id` column that was kept for backward compatibility (see migration `2024_09_23_184000_remove_property_type_id_from_properties.php` — it's intentionally a no-op).

The `Property::photo_main` accessor returns a public URL (`asset('images/'.$value)`). When you need the filesystem path (e.g., for GD image processing in `getImageProperty`), use `$property->getRawOriginal('photo_main')` and resolve via `public_path()`.

### Reservation system (in progress, Dec 2024)

New domain on top of `Property`:
- `Reservation` — booking with `check_in_date` / `check_out_date` (use `date`, not `timestamp`, to avoid MySQL multi-`CURRENT_TIMESTAMP` issues).
- `PropertyAvailability` (table `property_availability`, singular) and `Pricing` (table `pricing`, singular) — both models declare `protected $table` because the default plural would be wrong.
- `Amenity` ↔ `Property` via `property_amenities` pivot.
- `Review`, `Favorite`, `PropertyRule` round out the model.

Properties only show in `/reservaciones` when `is_reservable = true`. The night-count for the total is computed from `?check_in=&check_out=` query params via Carbon.

### Payments

Two providers wired in: `PaypalController` (auth-only `/pagos/paypal` route) and `MercadoPagoController` (webhook + `getProduct`). Package purchases trigger a `Payment` record and bump the user's `UserPackage.remaining_listings`.

### Helpers / conventions

- [app/Helpers/SlugHelper.php](app/Helpers/SlugHelper.php) — `SlugHelper::createUniqueSlug($title, ModelClass)` is the canonical way to slug new properties / users. Use it instead of `Str::slug` directly when uniqueness matters.
- Image uploads land in `public/images/`. The repo has a `public/.gitignore` (untracked) that, if committed as `*` + `!.gitignore`, would block all future tracked files in `public/` — be careful when staging it.
- Many routes are duplicated by accident in `web.php` (e.g., `/propiedad/{slug}` is registered twice). Don't add a third — fix the duplicate if you touch the area.

## Gotchas

- `Property::isLand()` and `propertyTypes` cause N+1 if iterated without eager loading. Use `Property::with(['propertyTypes', 'status'])` in list queries.
- Blade JS interpolation: when injecting PHP values into JavaScript, use `@json($value)` — never bare `{{ $value }}` between JS tokens (it produces invalid JS for null/strings).
- File `<input type="file">` cannot have a default value; do not add `value="{{ old(...) }}"` to photo inputs.
- The reservation, availability and pricing migrations were added together on 2024-12-30; if you reset the DB, run `migrate:fresh` rather than picking individual migrations.
