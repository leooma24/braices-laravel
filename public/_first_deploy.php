<?php
/**
 * Standalone deploy extractor — NO depende de Laravel ni rutas cacheadas.
 *
 * Se sube vía FTP junto con deploy.zip. Cuando lo invocas con el token
 * correcto, descomprime deploy.zip sobre /public_html/ y se auto-borra
 * (junto con el zip y consigo mismo).
 *
 * Esto resuelve el chicken-and-egg de subir DeployController por primera
 * vez: el endpoint /deploy/run requiere el código nuevo, este script no.
 *
 * URL: https://bienescorp.com/_first_deploy.php?token=<DEPLOY_TOKEN>
 *
 * Después del primer deploy exitoso este archivo NO existe en el server
 * (se auto-borró). Para deploys futuros, el endpoint Laravel maneja
 * todo.
 */

declare(strict_types=1);

header('Content-Type: text/plain; charset=utf-8');

// Lee DEPLOY_TOKEN del .env de prod directamente (sin bootstrap Laravel).
$envFile = __DIR__ . '/../.env';
if (!is_readable($envFile)) {
    http_response_code(500);
    echo "ERROR: no se puede leer .env en {$envFile}\n";
    exit;
}
$envRaw = file_get_contents($envFile);
preg_match('/^\s*DEPLOY_TOKEN\s*=\s*(.+?)\s*$/m', $envRaw, $m);
$configuredToken = isset($m[1]) ? trim($m[1], "\"' ") : '';

if ($configuredToken === '') {
    http_response_code(503);
    echo "DEPLOY_TOKEN no está configurado en el .env. Endpoint deshabilitado.\n";
    exit;
}

$providedToken = $_GET['token'] ?? $_POST['token'] ?? '';

if (!hash_equals($configuredToken, (string) $providedToken)) {
    http_response_code(403);
    echo "Forbidden.\n";
    exit;
}

// Validar que ZipArchive esté disponible.
if (!class_exists(ZipArchive::class)) {
    http_response_code(500);
    echo "ERROR: la extensión ZipArchive de PHP no está instalada.\n";
    exit;
}

// El script vive en /public_html/public/_first_deploy.php (web root).
// El zip se sube a /public_html/_deploy.zip (UN nivel arriba, fuera del web
// root — no se puede descargar vía HTTP).
$publicHtml = dirname(__DIR__);          // /public_html
$zipPath = $publicHtml . '/_deploy.zip';
$extractTo = $publicHtml;                // extrae sobre /public_html/

if (!is_file($zipPath)) {
    http_response_code(404);
    echo "ERROR: no se encontró {$zipPath}\n";
    exit;
}

echo "Extracting {$zipPath} -> {$extractTo}\n";
echo "Zip size: " . number_format(filesize($zipPath)) . " bytes\n\n";

$zip = new ZipArchive();
$open = $zip->open($zipPath);
if ($open !== true) {
    http_response_code(500);
    echo "ERROR: ZipArchive::open() falló con código {$open}\n";
    exit;
}

$fileCount = $zip->numFiles;
echo "Files in zip: {$fileCount}\n";

$ok = $zip->extractTo($extractTo);
$zip->close();

if (!$ok) {
    http_response_code(500);
    echo "ERROR: extractTo() falló\n";
    exit;
}

echo "Extracción completa.\n";

// Auto-cleanup: borrar el zip y el script extractor.
@unlink($zipPath);
@unlink(__FILE__);

echo "Cleanup: zip y extractor borrados.\n";
echo "OK.\n";
