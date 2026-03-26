<?php
/**
 * Blood Bank Management System
 * Front Controller — public/index.php
 */

declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));
define('APP_VERSION', '1.0.0');

// Base URL can be overridden by environment (Render) or computed for local subdirectory deployments
$scriptDir = dirname($_SERVER['SCRIPT_NAME'] ?? '');
$computedBase = rtrim(str_replace('/public', '', $scriptDir), '/');
$computedBase = $computedBase === '/' ? '' : $computedBase;
define('BASE_URL', getenv('BASE_URL') !== false ? getenv('BASE_URL') : $computedBase);

// ── Bootstrap ────────────────────────────────────────────────
session_start();

// Regenerate CSRF token if not set
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Autoload core classes
spl_autoload_register(function (string $class): void {
    $paths = [
        BASE_PATH . '/app/core/'             . $class . '.php',
        BASE_PATH . '/app/models/'       . $class . '.php',
        BASE_PATH . '/app/controllers/'  . $class . '.php',
        BASE_PATH . '/app/middleware/'   . $class . '.php',
    ];
    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
});

// ── Dispatch ─────────────────────────────────────────────────
$router = new Router();

require BASE_PATH . '/app/routes/web.php';

$method = $_SERVER['REQUEST_METHOD'];
$uri    = $_SERVER['REQUEST_URI'];

// Strip base path if app is in a subdirectory
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/';
$uri = str_replace('/BLOODBANK-SYSTEM', '', $uri);
$uri = str_replace('/public', '', $uri);
$uri = str_replace('/index.php', '', $uri);
if ($uri === '') $uri = '/';

$router->dispatch($method, $uri);