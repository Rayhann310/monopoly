<?php
// ============================================================
// CONFIG — Dibaca dari .env (via phpdotenv) dengan fallback
// ============================================================

// Helper: ambil dari $_ENV atau getenv, dengan fallback
function env(string $key, $default = null) {
    return $_ENV[$key] ?? getenv($key) ?: $default;
}

// === APP ===
define('APP_NAME',  env('APP_NAME', 'Monopoly Indonesia'));
define('APP_ENV',   env('APP_ENV', 'local'));
define('APP_DEBUG', filter_var(env('APP_DEBUG', true), FILTER_VALIDATE_BOOLEAN));
define('BASEURL',   rtrim(env('APP_URL', 'http://localhost/monopoly'), '/'));

// === DATABASE ===
define('DB_HOST', env('DB_HOST', 'localhost'));
define('DB_PORT', env('DB_PORT', '3306'));
define('DB_USER', env('DB_USER', 'root'));
define('DB_PASS', env('DB_PASS', ''));
define('DB_NAME', env('DB_NAME', 'monopoly_db'));

// === GAME ===
define('POLLING_INTERVAL', (int)env('POLLING_INTERVAL', 1500));
define('ADMIN_DEFAULT_PASSWORD', env('ADMIN_DEFAULT_PASSWORD', 'admin123'));

// === ERROR DISPLAY ===
if (APP_DEBUG) {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(0);
}
