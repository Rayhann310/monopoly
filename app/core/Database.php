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
            $pdo = new PDO("mysql:host=" . $this->host, $this->user, $this->pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
            $stmt = $pdo->query("SELECT COUNT(*) FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '" . $this->dbname . "'");
            if (!$stmt->fetchColumn()) {
                $pdo->exec("CREATE DATABASE `" . $this->dbname . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            }
            $this->dbh = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->dbname . ";charset=utf8mb4", $this->user, $this->pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
            $this->selfHeal();
        } catch(PDOException $e) {
            die("<div style='font-family:monospace;background:#1e1e1e;color:#ff6b6b;padding:30px;border-radius:10px;margin:20px;'><h2>⚠️ Database Error</h2><p>" . $e->getMessage() . "</p><p>Pastikan MySQL berjalan di XAMPP.</p></div>");
        }
    }

    public function selfHeal() {
        // === TABEL ADMIN ===
        $this->dbh->exec("CREATE TABLE IF NOT EXISTS admins (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            password_hash VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        // Default admin: admin / admin123
        $stmt = $this->dbh->query("SELECT COUNT(*) FROM admins");
        if ($stmt->fetchColumn() == 0) {
            $hash = password_hash('admin123', PASSWORD_DEFAULT);
            $this->dbh->exec("INSERT INTO admins (username, password_hash) VALUES ('admin', '$hash')");
        }

        // === TABEL PENGATURAN PERMAINAN ===
        $this->dbh->exec("CREATE TABLE IF NOT EXISTS game_settings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            setting_key VARCHAR(100) UNIQUE NOT NULL,
            setting_value TEXT NOT NULL,
            label VARCHAR(200) NOT NULL,
            type ENUM('number','text','boolean') DEFAULT 'text'
        )");
        // Default settings
        $defaults = [
            ['starting_money',  '15000', 'Uang Awal Pemain (Rp)', 'number'],
            ['pass_go_bonus',   '2000',  'Bonus Melewati Start (Rp)', 'number'],
            ['tax_amount',      '2000',  'Jumlah Pajak Biasa (Rp)', 'number'],
            ['luxury_tax',      '7500',  'Jumlah Pajak Mewah (Rp)', 'number'],
            ['max_players',     '4',     'Maksimal Pemain per Sesi', 'number'],
            ['allow_trade',     '1',     'Izinkan Tukar Properti antar Pemain', 'boolean'],
            ['name_dana_umum',  'DANA UMUM', 'Nama Kartu Dana Umum', 'text'],
            ['name_kesempatan', 'KESEMPATAN', 'Nama Kartu Kesempatan', 'text'],
            ['max_property_level', '4',   'Maksimal Tingkat Properti (Rumah/Hotel)', 'number'],
            ['house_price',     '150',   'Harga Beli Rumah/Tingkat Baru (Rp)', 'number']
        ];
        foreach ($defaults as $d) {
            $this->dbh->exec("INSERT IGNORE INTO game_settings (setting_key, setting_value, label, type) VALUES ('{$d[0]}','{$d[1]}','{$d[2]}','{$d[3]}')");
        }

        // === TABEL KARTU KESEMPATAN & DANA UMUM ===
        $this->dbh->exec("CREATE TABLE IF NOT EXISTS cards (
            id INT AUTO_INCREMENT PRIMARY KEY,
            type ENUM('kesempatan','dana_umum') NOT NULL,
            text TEXT NOT NULL,
            effect_type ENUM('money','move','jail','free','none') DEFAULT 'none',
            effect_value INT DEFAULT 0,
            is_active TINYINT(1) DEFAULT 1,
            image_url VARCHAR(255) NULL
        )");
        
        try {
            $this->dbh->query("SELECT image_url FROM cards LIMIT 1");
        } catch (PDOException $e) {
            $this->dbh->exec("ALTER TABLE cards ADD COLUMN image_url VARCHAR(255) NULL");
        }
        $cardCount = $this->dbh->query("SELECT COUNT(*) FROM cards")->fetchColumn();
        if ($cardCount == 0) {
            $this->dbh->exec("INSERT INTO cards (type, text, effect_type, effect_value) VALUES
                ('kesempatan','Maju ke Jakarta. Jika melewati Start, terima Rp 2.000.','move',39),
                ('kesempatan','Bank membayar dividen kepada Anda. Terima Rp 500.','money',500),
                ('kesempatan','Kena denda parkir ilegal. Bayar Rp 1.500.','money',-1500),
                ('kesempatan','Pergi ke penjara! Jangan melewati Start, jangan menerima Rp 2.000.','jail',10),
                ('kesempatan','Anda terpilih menjadi Ketua RT. Bayar setiap pemain Rp 500.','money',-500),
                ('kesempatan','Maju ke Bandung. Jika melewati Start, terima Rp 2.000.','move',24),
                ('kesempatan','Perbaikan rumah. Bayar Rp 2.500 per rumah yang dimiliki.','money',-2500),
                ('kesempatan','Anda mendapat hadiah ulang tahun dari setiap pemain Rp 500.','money',500),
                ('kesempatan','Maju mundur 3 langkah.','move',-3),
                ('kesempatan','Bebas dari penjara. Simpan kartu ini sampai dibutuhkan.','free',0),
                ('dana_umum','Terima warisan dari kakek. Terima Rp 5.000.','money',5000),
                ('dana_umum','Pajak penghasilan. Bayar Rp 2.000.','money',-2000),
                ('dana_umum','Dana pensiun cair! Terima Rp 1.000.','money',1000),
                ('dana_umum','Pergi ke penjara! Jangan melewati Start.','jail',10),
                ('dana_umum','Biaya rumah sakit. Bayar Rp 1.500.','money',-1500),
                ('dana_umum','Anda menang lomba kecantikan! Terima Rp 1.000.','money',1000),
                ('dana_umum','Subsidi pemerintah cair. Terima Rp 2.000.','money',2000),
                ('dana_umum','Bebas dari penjara. Simpan kartu ini sampai dibutuhkan.','free',0)
            ");
        }

        // === TABEL SESI ===
        $this->dbh->exec("CREATE TABLE IF NOT EXISTS sessions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            status ENUM('waiting','playing','finished') DEFAULT 'waiting',
            host_token VARCHAR(64) NOT NULL DEFAULT '',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        // Self-heal: tambah host_token jika belum ada
        try { $this->dbh->exec("ALTER TABLE sessions ADD COLUMN host_token VARCHAR(64) NOT NULL DEFAULT ''"); } catch(Exception $e) {}

        // === TABEL PEMAIN ===
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
        // Self-heal: tambah kolom baru ke players jika belum ada
        foreach (['is_turn TINYINT(1) DEFAULT 0','has_rolled TINYINT(1) DEFAULT 0','is_bankrupt TINYINT(1) DEFAULT 0','session_id INT NOT NULL DEFAULT 1'] as $col) {
            try { $this->dbh->exec("ALTER TABLE players ADD COLUMN $col"); } catch(Exception $e) {}
        }

        // === TABEL PROPERTI ===
        $this->dbh->exec("CREATE TABLE IF NOT EXISTS properties (
            id INT AUTO_INCREMENT PRIMARY KEY,
            session_id INT NOT NULL DEFAULT 1,
            cell_index INT NOT NULL,
            owner_id INT NOT NULL,
            houses INT DEFAULT 0
        )");

        // === TABEL GAME LOG ===
        $this->dbh->exec("CREATE TABLE IF NOT EXISTS game_log (
            id INT AUTO_INCREMENT PRIMARY KEY,
            session_id INT DEFAULT NULL,
            player_id INT DEFAULT NULL,
            action TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");

        // === TABEL BOARD PROPERTI (gambar kota) ===
        $this->dbh->exec("CREATE TABLE IF NOT EXISTS board_properties (
            id INT AUTO_INCREMENT PRIMARY KEY,
            cell_index INT NOT NULL UNIQUE,
            image_url VARCHAR(255) NULL,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )");

        return true;
    }

    public function query($query) { $this->stmt = $this->dbh->prepare($query); }
    public function bind($param, $value, $type = null) {
        if (is_null($type)) {
            switch (true) {
                case is_int($value): $type = PDO::PARAM_INT; break;
                case is_bool($value): $type = PDO::PARAM_BOOL; break;
                case is_null($value): $type = PDO::PARAM_NULL; break;
                default: $type = PDO::PARAM_STR;
            }
        }
        $this->stmt->bindValue($param, $value, $type);
    }
    public function execute() { return $this->stmt->execute(); }
    public function resultSet() { $this->execute(); return $this->stmt->fetchAll(PDO::FETCH_ASSOC); }
    public function single() { $this->execute(); return $this->stmt->fetch(PDO::FETCH_ASSOC); }
    public function lastInsertId() { return $this->dbh->lastInsertId(); }
    public function rowCount() { return $this->stmt->rowCount(); }
}
