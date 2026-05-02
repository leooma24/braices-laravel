# Deploy a producción

Este proyecto se deploya vía FTP automatizado con GitHub Actions. Sin SSH.

## Arquitectura del deploy

```
local (push a master)
   │
   ▼
GitHub Actions (.github/workflows/deploy.yml)
   1. composer install --no-dev --optimize-autoloader
   2. Limpia archivos que no van a prod (.env, .git, tests, etc.)
   3. FTP sync hacia el servidor (sin tocar .env, storage/, public/images/)
   4. Llama GET /deploy/run?token=XXX&action=migrate
   5. Llama GET /deploy/run?token=XXX&action=cache
```

## Setup inicial (una sola vez)

### 1. Generar el `DEPLOY_TOKEN`

```powershell
php -r "echo bin2hex(random_bytes(32));"
```

Guárdalo. Lo vas a usar en 3 lugares.

### 2. Configurar el `.env` LOCAL

Pon el token recién generado:

```
DEPLOY_TOKEN=<el token>
```

### 3. Configurar el `.env` de PRODUCCIÓN

Conéctate por FTP al servidor y edita el `.env` que ya está allá.
Asegúrate de que tenga (mínimo):

```
APP_ENV=production
APP_DEBUG=false
APP_URL=https://bienescorp.com
APP_KEY=<la misma que ya tenías>

DB_HOST=<host de MySQL en prod>
DB_DATABASE=<base>
DB_USERNAME=<user>
DB_PASSWORD=<pass>

DEPLOY_TOKEN=<el MISMO token que pusiste en local>

# Las demás credenciales que ya tenías (MercadoPago, PayPal, Facebook, etc.)
```

**Nunca subas tu `.env` local al servidor.** El workflow está configurado para no tocarlo.

### 4. Configurar GitHub Secrets

En tu repo de GitHub: **Settings → Secrets and variables → Actions → New repository secret**.

Agrega estos 5 secrets:

| Nombre | Valor |
|---|---|
| `FTP_HOST` | tu servidor FTP (ej: `ftp.bienescorp.com`) |
| `FTP_USERNAME` | el `FTP_USER` de tu `.env` local |
| `FTP_PASSWORD` | el `FTP_PASSWORD` de tu `.env` local |
| `FTP_REMOTE_DIR` | el directorio remoto (ej: `/public_html/` o `/`) |
| `DEPLOY_TOKEN` | el mismo token de los pasos 1-3 |
| `APP_DEPLOY_URL` | URL pública (ej: `https://bienescorp.com`) — sin slash final |

### 5. Auditar el schema de producción ANTES del primer deploy

Esto valida que las migraciones idempotentes no van a romper nada.

**Opción A — desde tu máquina apuntando a la BD de prod (recomendado):**

```powershell
# Edita TEMPORALMENTE tu .env local con los datos de la BD de prod
# (DB_HOST, DB_DATABASE, DB_USERNAME, DB_PASSWORD).

php artisan schema:check

# Salida esperada:
#   "OK — el schema actual contiene todo lo que las migraciones necesitan"
#   o lista de columnas/tablas faltantes (las agregará migrate).

# Restaura tu .env local con los datos de tu base local cuando termines.
```

**Opción B — vía phpMyAdmin (manual):**

1. En phpMyAdmin de prod, exporta solo la estructura: `Export → Custom → Structure only`.
2. Compara contra la estructura local.

## Primer deploy (paso a paso)

1. **Backup de la BD de producción.** Manual desde phpMyAdmin:
   ```
   Export → Quick → SQL → Go
   ```
   Guarda el archivo. Es tu red de seguridad si algo sale mal.

2. **Push a master desde local.** El workflow se dispara automático:
   ```powershell
   git push origin master
   ```

3. **Monitorea en GitHub** → pestaña *Actions*. Tarda ~3-5 min.

4. **Verifica manualmente que el sitio sigue arriba**:
   ```
   https://bienescorp.com/
   https://bienescorp.com/propiedades
   ```

5. **Si necesitas correr migraciones manualmente** (el workflow lo hace solo, pero por si acaso):
   ```
   https://bienescorp.com/deploy/run?token=TU_TOKEN&action=migrate
   ```
   Verás el output como texto plano.

6. **Después del primer deploy exitoso**, opcionalmente vacía `DEPLOY_TOKEN` en el `.env` de prod para deshabilitar el endpoint hasta el siguiente deploy.

## Deploys subsecuentes

Cada `git push origin master` despliega automáticamente. El workflow:
- Sube solo archivos modificados (FTP sync incremental).
- Re-ejecuta migraciones (idempotentes, no rompe si ya están aplicadas).
- Regenera caches.

## Acciones disponibles en `/deploy/run`

| `action=` | Qué hace |
|---|---|
| `status` | Verifica que el endpoint funciona y muestra info de la app |
| `migrate` | Corre `php artisan migrate --force` |
| `cache` | `config:cache` + `route:cache` + `view:cache` |
| `clear` | `config:clear` + `route:clear` + `view:clear` + `cache:clear` |
| `storage-link` | Genera el symlink `public/storage → storage/app/public` |
| `sitemap` | Regenera `public/sitemap.xml` |

URL: `https://bienescorp.com/deploy/run?token=TU_TOKEN&action=migrate`

## Rollback

Si un deploy rompe algo:

1. **Revertir el código**: `git revert <hash> && git push origin master`. El workflow despliega la versión revertida.

2. **Revertir la BD**: importa el backup que hiciste antes del deploy desde phpMyAdmin (`Import → SQL`).

3. **Restaurar caches**:
   ```
   https://bienescorp.com/deploy/run?token=TU_TOKEN&action=clear
   https://bienescorp.com/deploy/run?token=TU_TOKEN&action=cache
   ```

## Cosas que NO se sobreescriben en cada deploy

El workflow las protege explícitamente:

- `.env` (config de prod, contiene credenciales reales)
- `storage/` (logs, sessions, framework cache)
- `public/images/` (fotos de propiedades subidas por usuarios)
- `public/photo-user/` (avatars)

## Después del primer deploy: cron del scheduler

Para que las tareas programadas (release-expired, sitemap, backups) corran:

**En cPanel** → *Cron Jobs*, agrega:

```
* * * * * cd /home/user/public_html && php artisan schedule:run >> /dev/null 2>&1
```

(Ajusta la ruta según donde esté instalado.)

Si tu hosting no tiene cron, puedes usar un servicio externo como **EasyCron** o **cron-job.org** apuntando a un endpoint web que dispare el scheduler. Pregúntame y te lo monto.

## Troubleshooting

### El workflow falla en "Sync vía FTP"
- Verifica los 4 secrets de FTP en GitHub.
- Algunos hostings requieren FTPS (FTP sobre TLS) o SFTP. Si es FTPS, agrega `protocol: ftps` al step. Si es SFTP, hay que cambiar la action.

### El endpoint `/deploy/run` regresa 503
- `DEPLOY_TOKEN` no está configurado en el `.env` de producción. Conéctate por FTP, edítalo, y vuelve a intentar.

### El endpoint regresa 403
- El token no coincide. Verifica que el `DEPLOY_TOKEN` en el `.env` de prod sea EXACTAMENTE el mismo que estás pasando en la URL.

### Migración falla con "table already exists"
- No debería pasar — todas las migraciones nuevas tienen `Schema::hasTable()` guard. Si pasa, copia el output completo y dímelo.

### "Class not found" después del deploy
- Falta el cache de autoload. Ejecuta:
  ```
  https://bienescorp.com/deploy/run?token=TU_TOKEN&action=clear
  https://bienescorp.com/deploy/run?token=TU_TOKEN&action=cache
  ```
