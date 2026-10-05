<?php
require 'app/config/config.php';
try {
    $pdo = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME, DB_USER, DB_PASS);
    $pdo->exec("ALTER TABLE cards ADD COLUMN image_url VARCHAR(255) NULL");
    echo "Success";
} catch (Exception $e) {
    echo "Already exists or error: " . $e->getMessage();
}
