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
        // Table Players
        $this->dbh->exec("CREATE TABLE IF NOT EXISTS players (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(50) NOT NULL,
            color VARCHAR(20) NOT NULL,
            position INT DEFAULT 0,
            money INT DEFAULT 1500,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");

        // Insert initial data
        $stmt = $this->dbh->query("SELECT COUNT(*) FROM players");
        if ($stmt->fetchColumn() == 0) {
            $this->dbh->exec("INSERT INTO players (name, color, position, money) VALUES 
                ('Pemain 1', 'red', 0, 1500),
                ('Pemain 2', 'blue', 0, 1500),
                ('Pemain 3', 'green', 0, 1500),
                ('Pemain 4', 'yellow', 0, 1500)
            ");
        }
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
}
