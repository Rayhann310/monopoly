<?php
// ============================================================
// MONOPOLY INDONESIA — Entry Point
// ============================================================

// Tampilkan error saat booting awal (mencegah blank screen jika ada error parah)
ini_set('display_errors', 1);
error_reporting(E_ALL);
// Mulai try-catch untuk menangkap FATAL ERROR tersembunyi
try {
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

    // === SETUP WIZARD: Jika .env tidak ada, tampilkan form setup ===
    if (!file_exists(__DIR__ . '/.env')) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['setup_action'])) {
            $envContent = "APP_NAME=\"Monopoly Indonesia\"\n"
                . "APP_ENV=production\n"
                . "APP_DEBUG=false\n"
                . "APP_URL=" . trim($_POST['app_url'] ?? 'http://localhost/monopoly') . "\n\n"
                . "DB_HOST=" . trim($_POST['db_host'] ?? 'localhost') . "\n"
                . "DB_PORT=" . trim($_POST['db_port'] ?? '3306') . "\n"
                . "DB_NAME=" . trim($_POST['db_name'] ?? 'monopoly_db') . "\n"
                . "DB_USER=" . trim($_POST['db_user'] ?? 'root') . "\n"
                . "DB_PASS=" . trim($_POST['db_pass'] ?? '') . "\n\n"
                . "ADMIN_DEFAULT_PASSWORD=" . trim($_POST['admin_pass'] ?? 'admin123') . "\n"
                . "POLLING_INTERVAL=2000\n";
            file_put_contents(__DIR__ . '/.env', $envContent);
            header('Location: ' . trim($_POST['app_url'] ?? '/'));
            exit;
        }
        $proto = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $guessUrl = $proto . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');
        if (strpos($_SERVER['REQUEST_URI'] ?? '', '/monopoly') !== false) {
            $guessUrl .= '/monopoly';
        }
        include __DIR__ . '/app/views/setup_wizard.php';
        exit;
    }

    // === PERSISTENSI SESI (Untuk Docker & VPS) ===
    $sessionSavePath = __DIR__ . '/storage/sessions';
    if (!is_dir($sessionSavePath)) {
        @mkdir($sessionSavePath, 0777, true);
    }
    if (is_dir($sessionSavePath) && is_writable($sessionSavePath)) {
        ini_set('session.save_path', $sessionSavePath);
    }
    // Pertahankan sesi login agar awet (7 hari)
    ini_set('session.gc_maxlifetime', 604800);
    ini_set('session.cookie_lifetime', 604800);
    session_set_cookie_params([
        'lifetime' => 604800,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax'
    ]);

    // Mulai session
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Bootstrap app
    require_once __DIR__ . '/app/init.php';

    // Run App
    $app = new App();

} catch (\Throwable $e) {
    // Tangkap SEMUA error (Fatal Error, Parse Error, Exception, dll)
    echo "<div style='font-family: monospace; padding: 20px; background: #222; color: #ff5555; border-radius: 8px; margin: 20px;'>";
    echo "<h2>🚨 SISTEM CRASH (FATAL ERROR)</h2>";
    echo "<b>Pesan:</b> " . $e->getMessage() . "<br><br>";
    echo "<b>File:</b> " . $e->getFile() . " (Baris " . $e->getLine() . ")<br><br>";
    echo "<b>Trace:</b><br><pre style='color: #aaa'>" . $e->getTraceAsString() . "</pre>";
    echo "</div>";
    exit;
}
