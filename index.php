<?php
// ============================================================
// MONOPOLY INDONESIA — Entry Point
// ============================================================

// Load Composer autoload (termasuk phpdotenv)
require_once __DIR__ . '/vendor/autoload.php';

// Load .env file
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad(); // safeLoad: tidak error jika .env tidak ada

// Mulai session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Bootstrap app
require_once __DIR__ . '/app/init.php';
