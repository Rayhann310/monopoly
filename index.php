<?php
// ============================================================
// MONOPOLY INDONESIA — Entry Point
// ============================================================

// Tampilkan error saat booting awal (mencegah blank screen jika ada error parah)
ini_set('display_errors', 1);
error_reporting(E_ALL);
echo "<!-- BOOTING MONOPOLY -->";

// 1. Load Composer autoload & phpdotenv JIKA ADA
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
    if (class_exists('Dotenv\Dotenv')) {
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
        $dotenv->safeLoad();
    }
} 
// 2. FALLBACK (Untuk hosting tanpa Composer)
else {
    if (file_exists(__DIR__ . '/.env')) {
        $lines = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0) continue;
            if (strpos($line, '=') !== false) {
                list($name, $value) = explode('=', $line, 2);
                $name = trim($name);
                $value = trim(trim($value), "\"'");
                if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
                    putenv(sprintf('%s=%s', $name, $value));
                    $_ENV[$name] = $value;
                    $_SERVER[$name] = $value;
                }
            }
        }
    }
}

// Mulai session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Bootstrap app
require_once __DIR__ . '/app/init.php';

// Run App
$app = new App();
