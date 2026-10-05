<?php
class Database {
    private $host = DB_HOST;
    private $user = DB_USER;
    private $pass = DB_PASS;
    private $dbname = DB_NAME;
    private $dbh;
    private $stmt;

    public function __construct() {
        try {
            // Check & Create DB (Self Healing)
            $pdo = new PDO("mysql:host=" . $this->host, $this->user, $this->pass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            $stmt = $pdo->query("SELECT COUNT(*) FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '" . $this->dbname . "'");
            if (!$stmt->fetchColumn()) {
                $pdo->exec("CREATE DATABASE " . $this->dbname);
            }
            
            // Connect to created DB
            $this->dbh = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->dbname, $this->user, $this->pass);
            $this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            $this->checkAndCreateTables();
            
        } catch(PDOException $e) {
            die("Database Connection Error: " . $e->getMessage());
        }
    }

    private function checkAndCreateTables() {
        // Tabel sesi permainan (untuk multi-kelompok)
        $this->dbh->exec("CREATE TABLE IF NOT EXISTS sessions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            status ENUM('waiting','playing','finished') DEFAULT 'waiting',
            current_turn INT DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");

        // Tabel Players dengan session_id
        $this->dbh->exec("CREATE TABLE IF NOT EXISTS players (
            id INT AUTO_INCREMENT PRIMARY KEY,
            session_id INT NOT NULL DEFAULT 1,
            name VARCHAR(50) NOT NULL,
            color VARCHAR(20) NOT NULL,
            position INT DEFAULT 0,
            money INT DEFAULT 15000,
            is_turn TINYINT(1) DEFAULT 0,
            has_rolled TINYINT(1) DEFAULT 0,
            is_bankrupt TINYINT(1) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");

        // Tabel properti yang dimiliki pemain
        $this->dbh->exec("CREATE TABLE IF NOT EXISTS properties (
            id INT AUTO_INCREMENT PRIMARY KEY,
            session_id INT NOT NULL DEFAULT 1,
            cell_index INT NOT NULL,
            owner_id INT NOT NULL,
            houses INT DEFAULT 0
        )");
    }

    public function query($query) {
        $this->stmt = $this->dbh->prepare($query);
    }

    public function bind($param, $value, $type = null) {
        if (is_null($type)) {
            switch (true) {
                case is_int($value):
                    $type = PDO::PARAM_INT;
                    break;
                case is_bool($value):
                    $type = PDO::PARAM_BOOL;
                    break;
                case is_null($value):
                    $type = PDO::PARAM_NULL;
                    break;
                default:
                    $type = PDO::PARAM_STR;
            }
        }
        $this->stmt->bindValue($param, $value, $type);
    }

    public function execute() {
        $this->stmt->execute();
    }

    public function resultSet() {
        $this->execute();
        return $this->stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function single() {
        $this->execute();
        return $this->stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function lastInsertId() {
        return $this->dbh->lastInsertId();
    }
}
