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

## Tests

Feature tests viven en `tests/Feature/`. PHPUnit usa SQLite in-memory (`phpunit.xml`) — los tests son rápidos y autocontenidos.

```powershell
php artisan test                                # todo
php artisan test tests/Feature/ReservationFlowTest.php
php artisan test --filter=guest_creates_pending_reservation
```

Cobertura crítica: ReservationFlow (cotización, disponibilidad, store, cancel, expire, complete-past), PropertyAuthorization (intruder no puede ver/editar/borrar propiedades ajenas).

Notas:
- Las tablas geo (`paises`, `estados`, `municipios`, `colonias`) viven solo en MySQL real — los tests que dependen de ellas están `markTestSkipped`.
- `email_verified_at` no está en `User::$fillable` — al crear usuarios de test, asígnalo con `$user->email_verified_at = now(); $user->save();`.
- `transaction_types` y `property_status` requieren seed manual en setUp (FK constraints).

## Backups

`spatie/laravel-backup` instalado. Schedule:
- `backup:clean` diario 02:00 — limpia viejos según retention en `config/backup.php`.
- `backup:run --only-db` diario 02:30 — solo dump de MySQL.
- `backup:run` semanal domingo 03:30 — DB + archivos.

Para activar emails de notificación: configura `mail` driver y agrega la dirección en `config/backup.php` → `notifications.mail.to`.

## Error monitoring

Stub para Sentry (apagado por flag `SENTRY_ENABLED=false`). Para activar:
1. `composer require sentry/sentry-laravel`
2. Set `SENTRY_LARAVEL_DSN` en `.env`
3. Set `SENTRY_ENABLED=true`

## Feature flags

- `AI_DESCRIPTIONS_ENABLED=true|false` (default `false`) — gatea el botón "Generar con IA" en property-edit y el endpoint `POST /api/properties/ai-description`. Cuando está apagado, el endpoint retorna 404 y el botón no se renderiza. Para activarlo, también requiere `ANTHROPIC_API_KEY` en `.env`. El service vive en `app/Services/AIDescriptionService.php` (Anthropic SDK PHP, modelo `claude-opus-4-7`).

## SEO

- En el servidor responde el archivo estático `public/robots.txt`, que tapa la ruta `/robots.txt` de `routes/web.php`. Si cambias reglas, edita el archivo (ya incluye `Sitemap: https://bienescorp.com/sitemap.xml`).
- `GET /sitemap.xml` es dinámico: ruta en `routes/web.php` con cache de 1 h y lógica en `App\Services\SitemapBuilder`. No generes `public/sitemap.xml` en prod: taparía la ruta, y desde CLI las URLs salen del `APP_URL` del servidor (hoy apunta a localhost). `php artisan sitemap:generate` queda para uso local; `/deploy/run?action=sitemap` solo limpia la cache.
- El deploy (extractor zip) no borra archivos del servidor: quitar un archivo del repo no lo elimina en prod.
- Cada `/propiedad/{slug}` incluye JSON-LD (`RealEstateListing` o `Accommodation` con `aggregateRating` cuando hay reviews).

## Featured listings

- Campos `is_featured` (bool) y `featured_until` (datetime) en `properties` — **no están en `$fillable`** intencionalmente. Para tocarlos hay que usar `$property->forceFill([...])->save()`. El método `isFeaturedNow()` valida ambos.
- En `/propiedades` las destacadas se ordenan primero. La card recibe la clase `.card-featured` con borde coral y badge "Destacada".
- Endpoint `POST /propiedad/{slug}/destacar` (auth + dueño) toggle de 30 días.

## Vistas y movimiento de propiedades

`properties.views` es un contador acumulado sin fecha (se incrementa en cada carga de la ficha), así que por sí solo no sirve para medir el movimiento de un periodo. Encima de él:

- Tabla `property_view_snapshots` (una foto por propiedad por día, `unique(property_id, captured_on)`).
- `php artisan views:snapshot [--date=Y-m-d]` — guarda la foto del día. Corre solo, diario a la 01:00 (ver `app/Console/Kernel.php`).
- `php artisan views:report [--days=7]` — vistas nuevas por propiedad en el periodo, con promedio diario. Si no hay foto tan vieja, usa la más antigua y reporta el span real.
- `PropertyViewBaselineSeeder` — baseline del 2026-10-01 leído de las fichas públicas, para no empezar de cero.
- Sin SSH: `/deploy/run?token=...&action=views-snapshot|views-report&days=N|views-baseline`.

No hay Google Analytics ni otro tracking en el sitio; estas fotos son la única fuente de movimiento por fecha. Los prospectos del formulario viven aparte, en `leads` → `/cuenta/prospectos`.

### Origen de las visitas

`properties.views` no dice de dónde llegó la gente, así que no se podía separar Facebook del efecto de ser la propiedad más reciente del listado.

- Tabla `property_view_sources`: agregada por propiedad, origen y día (`unique(property_id, source, day)`), no una fila por visita.
- `App\Services\VisitSource` resuelve el origen. Prioridad: `?de=` → `?utm_source=` → host del `Referer` → `directo`. La marca manual va primero **a propósito**: Facebook manda mucho tráfico sin `Referer` desde su app, y si se dependiera del referer ese tráfico se contaría como directo.
- Al publicar en grupos hay que pegar la liga con `?de=facebook` para que se distinga.
- `php artisan views:sources [--days=7] [--property=ID]`, o `/deploy/run?action=views-sources&days=N`.
- Filtro ligero de bots por user agent; no pretende atrapar todo, solo que los rastreadores obvios no inflen un origen.

### Contactos por WhatsApp

Tabla `property_contact_clicks` (property_id, channel, created_at). La ficha dispara un `POST` a `/propiedad/{id}/contacto/{channel}` cuando alguien pica WhatsApp o correo; si falla, el enlace funciona igual. El conteo sale en `/cuenta/mis-propiedades`.

## Anti-spam

Los formularios tienen reCAPTCHA v2, pero igual entraba spam (las granjas de captchas lo resuelven barato). Encima va `<x-honeypot />` + el middleware `honeypot`: descarta el envío si el campo trampa viene lleno, si la marca de tiempo cifrada fue alterada, o si el formulario se mandó en menos de 3 segundos. **Al bot se le responde con el mismo mensaje de éxito que a una persona**, a propósito: con un error visible, quien lo opera lo nota y ajusta el script. Si no viene la marca de tiempo no se castiga, por si queda una vista vieja en caché.

## Gotchas

- `Property::isLand()` and `propertyTypes` cause N+1 if iterated without eager loading. Use `Property::with(['propertyTypes', 'status'])` in list queries.
- Blade JS interpolation: when injecting PHP values into JavaScript, use `@json($value)` — never bare `{{ $value }}` between JS tokens (it produces invalid JS for null/strings).
- File `<input type="file">` cannot have a default value; do not add `value="{{ old(...) }}"` to photo inputs.
- The reservation, availability and pricing migrations were added together on 2024-12-30; if you reset the DB, run `migrate:fresh` rather than picking individual migrations.
