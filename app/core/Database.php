<?php
class Database {
    private $host = DB_HOST;
    private $user = DB_USER;
    private $pass = DB_PASS;
    private $dbname = DB_NAME;
    private $dbh;
    private $stmt;

    public function __construct() {
        $hosts = [$this->host];
        // Jika host adalah 'localhost', coba juga '127.0.0.1' sebagai fallback
        if ($this->host === 'localhost') {
            $hosts[] = '127.0.0.1';
        }

        $connected = false;
        $lastError = null;

        foreach ($hosts as $h) {
            try {
                // 1. Langsung coba koneksi ke database target (Sangat Cepat)
                $this->dbh = new PDO(
                    "mysql:host={$h};port=" . DB_PORT . ";dbname=" . $this->dbname . ";charset=utf8mb4",
                    $this->user, $this->pass,
                    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
                );
                
                // 2. Cek apakah tabel utama (admins) sudah ada. Jika belum, jalankan selfHeal
                $check = $this->dbh->query("SHOW TABLES LIKE 'admins'");
                if ($check && $check->rowCount() == 0) {
                    $this->selfHeal();
                }
                
                $connected = true;
                break;
            } catch(PDOException $e) {
                // Jika error karena database belum ada (SQLSTATE 42000 / 1049)
                if ($e->getCode() == 1049) {
                    try {
                        $pdo = new PDO("mysql:host={$h};port=" . DB_PORT . ";charset=utf8mb4", $this->user, $this->pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
                        $pdo->exec("CREATE DATABASE `" . $this->dbname . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                        
                        // Sambung ulang ke database yang baru dibuat
                        $this->dbh = new PDO(
                            "mysql:host={$h};port=" . DB_PORT . ";dbname=" . $this->dbname . ";charset=utf8mb4",
                            $this->user, $this->pass,
                            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
                        );
                        $this->selfHeal();
                        $connected = true;
                        break;
                    } catch(PDOException $ex) {
                        $lastError = $ex;
                    }
                } else {
                    $lastError = $e;
                }
            }
        }

        if (!$connected) {
            $msg  = htmlspecialchars($lastError->getMessage());
            $hint = '';
            if (str_contains($msg, '2002') || str_contains($msg, 'Operation not permitted') || str_contains($msg, 'Connection refused')) {
                $hint = '<p style="color:#fbbf24">💡 <b>Hint:</b> Buat file <code>.env</code> di root project dengan isi:<br>'
                      . '<code>DB_HOST=localhost<br>DB_USER=nama_user_db<br>DB_PASS=password_db<br>DB_NAME=nama_database</code></p>'
                      . '<p style="color:#94a3b8">Di hosting Hostinger/cPanel, host biasanya <code>localhost</code> atau <code>127.0.0.1</code>.</p>';
            }
            die("<div style='font-family:monospace;background:#1e1e1e;color:#ff6b6b;padding:30px;border-radius:10px;margin:20px;'>"
              . "<h2>⚠️ Database Connection Error</h2><p>{$msg}</p>{$hint}</div>");
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
            ['starting_money',       '15000', 'Uang Awal Pemain (Rp)', 'number'],
            ['pass_go_bonus',        '2000',  'Bonus Melewati Start (Rp)', 'number'],
            ['tax_amount',           '2000',  'Jumlah Pajak Biasa (Rp)', 'number'],
            ['luxury_tax',           '7500',  'Jumlah Pajak Mewah (Rp)', 'number'],
            ['max_players',          '4',     'Maksimal Pemain per Sesi', 'number'],
            ['allow_trade',          '1',     'Izinkan Tukar Properti antar Pemain', 'boolean'],
            ['name_dana_umum',       'DANA UMUM', 'Nama Kartu Dana Umum', 'text'],
            ['name_kesempatan',      'KESEMPATAN', 'Nama Kartu Kesempatan', 'text'],
            ['max_property_level',   '4',   'Maksimal Tingkat Properti (Rumah/Hotel)', 'number'],
            ['house_price',          '150', 'Harga Beli Rumah/Tingkat Baru (Rp)', 'number'],
            ['free_parking_enabled', '1',   'Aktifkan Pot Parkir Bebas (pajak masuk pot)', 'boolean'],
            ['free_parking_seed',    '0',   'Dana Awal Pot Parkir Bebas (Rp)', 'number'],
        ];
        foreach ($defaults as $d) {
            $this->dbh->exec("INSERT IGNORE INTO game_settings (setting_key, setting_value, label, type) VALUES ('{$d[0]}','{$d[1]}','{$d[2]}','{$d[3]}')");
        }

        // === TABEL KARTU KESEMPATAN & DANA UMUM ===
        $this->dbh->exec("CREATE TABLE IF NOT EXISTS cards (
            id INT AUTO_INCREMENT PRIMARY KEY,
            type ENUM('kesempatan','dana_umum') NOT NULL,
            text TEXT NOT NULL,
            effect_type VARCHAR(50) DEFAULT 'none',
            effect_value INT DEFAULT 0,
            pass_start_money INT DEFAULT 0,
            is_active TINYINT(1) DEFAULT 1,
            image_url VARCHAR(255) NULL,
            text_hash VARCHAR(64) NULL
        )");

        // Self-heal: add missing columns
        try { $this->dbh->query("SELECT image_url FROM cards LIMIT 1"); }
        catch (PDOException $e) { $this->dbh->exec("ALTER TABLE cards ADD COLUMN image_url VARCHAR(255) NULL"); }

        try { $this->dbh->query("SELECT pass_start_money FROM cards LIMIT 1"); }
        catch (PDOException $e) {
            $this->dbh->exec("ALTER TABLE cards ADD COLUMN pass_start_money INT DEFAULT 0 AFTER effect_value");
            $this->dbh->exec("ALTER TABLE cards MODIFY COLUMN effect_type VARCHAR(50) DEFAULT 'none'");
            // Migrate old effect_type data
            $this->dbh->exec("UPDATE cards SET effect_type = 'money_bank' WHERE effect_type = 'money' AND text NOT LIKE '%setiap pemain%'");
            $this->dbh->exec("UPDATE cards SET effect_type = 'money_players' WHERE effect_type = 'money' AND text LIKE '%setiap pemain%'");
            $this->dbh->exec("UPDATE cards SET effect_type = 'move_pos' WHERE effect_type = 'move' AND effect_value >= 0");
            $this->dbh->exec("UPDATE cards SET effect_type = 'move_steps' WHERE effect_type = 'move' AND effect_value < 0");
            $this->dbh->exec("UPDATE cards SET pass_start_money = 2000 WHERE effect_type = 'move_pos' AND text LIKE '%melewati Start%'");
        }
        try { $this->dbh->query("SELECT text_hash FROM cards LIMIT 1"); }
        catch (PDOException $e) {
            $this->dbh->exec("ALTER TABLE cards ADD COLUMN text_hash VARCHAR(64) NULL");
            // Backfill existing rows
            $this->dbh->exec("UPDATE cards SET text_hash = MD5(CONCAT(type,'|',text)) WHERE text_hash IS NULL");
            try { $this->dbh->exec("ALTER TABLE cards ADD UNIQUE KEY uq_card_hash (text_hash)"); } catch(Exception $ex) {}
        }
        // Ensure unique key exists
        try { $this->dbh->exec("ALTER TABLE cards ADD UNIQUE KEY uq_card_hash (text_hash)"); } catch(Exception $e) {}

        // Seed kartu — INSERT IGNORE: tidak pernah duplikat, tidak menimpa kartu custom
        $seedCards = [
            // ===== 25 KESEMPATAN =====
            ['kesempatan','Kontenmu viral di media sosial! Dapat sponsorship dadakan. Terima Rp 75.000.','money_bank',75000,0],
            ['kesempatan','Kena razia tilang di jalan tol. Bayar denda Rp 15.000.','money_bank',-15000,0],
            ['kesempatan','Liburan ke Bali! Maju ke Denpasar. Jika melewati Start terima Rp 2.000.','move_pos',21,2000],
            ['kesempatan','Tertangkap korupsi anggaran RT. Langsung masuk penjara!','jail',10,0],
            ['kesempatan','Menang undian berhadiah dari minimarket. Terima Rp 50.000.','money_bank',50000,0],
            ['kesempatan','Usaha warmie bangkrut. Bayar utang supplier Rp 30.000.','money_bank',-30000,0],
            ['kesempatan','Terpilih jadi ketua OSIS. Traktir semua pemain masing-masing Rp 10.000.','money_players',-10000,0],
            ['kesempatan','Dapat transfer salah dari orang asing. Terima Rp 25.000 dari setiap pemain.','money_players',25000,0],
            ['kesempatan','Google Maps error, nyasar 3 langkah ke belakang.','move_steps',-3,0],
            ['kesempatan','Bebas dari penjara. Simpan kartu ini sampai kamu butuhkan.','free',0,0],
            ['kesempatan','Saham perusahaanmu naik drastis. Terima dividen Rp 100.000.','money_bank',100000,0],
            ['kesempatan','Kena PHK mendadak. Bayar pesangon ke bank Rp 40.000.','money_bank',-40000,0],
            ['kesempatan','Menang lomba startup tingkat nasional. Terima hadiah Rp 200.000.','money_bank',200000,0],
            ['kesempatan','Pipa bocor di rumahmu. Biaya perbaikan Rp 20.000.','money_bank',-20000,0],
            ['kesempatan','Teman lamamu bayar utang lama. Terima Rp 60.000.','money_bank',60000,0],
            ['kesempatan','Denda tilang CCTV. Bayar Rp 12.000.','money_bank',-12000,0],
            ['kesempatan','Jual barang bekas di marketplace, laris manis. Terima Rp 35.000.','money_bank',35000,0],
            ['kesempatan','Kena sanksi pajak karena lapor telat. Bayar Rp 45.000.','money_bank',-45000,0],
            ['kesempatan','Maju ke Jakarta! Jika melewati Start terima Rp 2.000.','move_pos',1,2000],
            ['kesempatan','Bisnis dropship-mu untung bulan ini. Terima Rp 80.000.','money_bank',80000,0],
            ['kesempatan','Bayar iuran arisan yang sudah menunggak. Bayar Rp 18.000.','money_bank',-18000,0],
            ['kesempatan','Dapat bonus kinerja dari perusahaan. Terima Rp 150.000.','money_bank',150000,0],
            ['kesempatan','Ketahuan parkir sembarangan. Bayar Rp 8.000.','money_bank',-8000,0],
            ['kesempatan','Investasi reksa dana-mu cair dengan untung. Terima Rp 120.000.','money_bank',120000,0],
            ['kesempatan','Kena tipu jual beli online. Rugi Rp 55.000.','money_bank',-55000,0],

            // ===== 25 DANA UMUM =====
            ['dana_umum','THR akhir tahun cair dari pemerintah. Terima Rp 200.000.','money_bank',200000,0],
            ['dana_umum','Biaya rawat inap rumah sakit. Bayar Rp 75.000.','money_bank',-75000,0],
            ['dana_umum','Ulang tahunmu! Semua pemain kasih hadiah masing-masing Rp 15.000.','money_players',15000,0],
            ['dana_umum','Ketahuan nyontek saat ujian dadu. Langsung masuk penjara!','jail',10,0],
            ['dana_umum','Subsidi listrik dari pemerintah cair. Terima Rp 50.000.','money_bank',50000,0],
            ['dana_umum','Bayar pajak bumi dan bangunan tahunan. Denda Rp 60.000.','money_bank',-60000,0],
            ['dana_umum','Juara lomba 17-an tingkat kelurahan. Hadiah Rp 30.000.','money_bank',30000,0],
            ['dana_umum','Beasiswa pendidikan cair. Terima Rp 100.000.','money_bank',100000,0],
            ['dana_umum','TV rusak, beli baru. Bayar Rp 40.000.','money_bank',-40000,0],
            ['dana_umum','Bebas dari penjara. Simpan kartu ini sampai kamu butuhkan.','free',0,0],
            ['dana_umum','Warisan dari kakek jauh akhirnya cair. Terima Rp 300.000.','money_bank',300000,0],
            ['dana_umum','Tagihan listrik dan air bulan ini membengkak. Bayar Rp 25.000.','money_bank',-25000,0],
            ['dana_umum','Tabunganmu berbunga lebih dari biasanya. Terima Rp 45.000.','money_bank',45000,0],
            ['dana_umum','Biaya servis motor tahunan. Bayar Rp 18.000.','money_bank',-18000,0],
            ['dana_umum','Dana bantuan sosial dari kelurahan turun. Terima Rp 80.000.','money_bank',80000,0],
            ['dana_umum','Kena denda keterlambatan bayar cicilan. Bayar Rp 35.000.','money_bank',-35000,0],
            ['dana_umum','Panen investasi emas yang sudah lama ditanam. Terima Rp 175.000.','money_bank',175000,0],
            ['dana_umum','Kecelakaan kecil di parkiran. Bayar ganti rugi Rp 50.000.','money_bank',-50000,0],
            ['dana_umum','Traktir semua pemain makan, bayar masing-masing Rp 20.000.','money_players',-20000,0],
            ['dana_umum','Royalti dari konten lama yang masih ditonton. Terima Rp 65.000.','money_bank',65000,0],
            ['dana_umum','Biaya notaris urus sertifikat properti. Bayar Rp 30.000.','money_bank',-30000,0],
            ['dana_umum','Dana hibah RT untuk warga berprestasi. Terima Rp 90.000.','money_bank',90000,0],
            ['dana_umum','Bayar biaya kondangan yang menumpuk. Bayar Rp 22.000.','money_bank',-22000,0],
            ['dana_umum','Asuransi jiwa cair lebih cepat dari perkiraan. Terima Rp 250.000.','money_bank',250000,0],
            ['dana_umum','Renovasi genteng bocor sebelum musim hujan. Bayar Rp 55.000.','money_bank',-55000,0],
        ];
        $stmtCard = $this->dbh->prepare(
            "INSERT IGNORE INTO cards (type, text, effect_type, effect_value, pass_start_money, text_hash)
             VALUES (?, ?, ?, ?, ?, MD5(CONCAT(?, '|', ?)))"
        );
        foreach ($seedCards as $c) {
            $stmtCard->execute([$c[0], $c[1], $c[2], $c[3], $c[4], $c[0], $c[1]]);
        }


        // === TABEL SESI ===
        $this->dbh->exec("CREATE TABLE IF NOT EXISTS sessions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            status ENUM('waiting','playing','finished') DEFAULT 'waiting',
            host_token VARCHAR(64) NOT NULL DEFAULT '',
            active_card TEXT NULL DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        // Self-heal sessions columns
        try { $this->dbh->exec("ALTER TABLE sessions ADD COLUMN host_token VARCHAR(64) NOT NULL DEFAULT ''"); } catch(Exception $e) {}
        try { $this->dbh->exec("ALTER TABLE sessions ADD COLUMN active_card TEXT NULL DEFAULT NULL"); } catch(Exception $e) {}
        try { $this->dbh->exec("ALTER TABLE sessions ADD COLUMN free_parking_pot INT DEFAULT 0"); } catch(Exception $e) {}
        try { $this->dbh->exec("ALTER TABLE sessions ADD COLUMN pejabat_id INT NULL DEFAULT NULL"); } catch(Exception $e) {}

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
        foreach ([
            'is_turn TINYINT(1) DEFAULT 0',
            'has_rolled TINYINT(1) DEFAULT 0',
            'is_bankrupt TINYINT(1) DEFAULT 0',
            'session_id INT NOT NULL DEFAULT 1',
            'laps INT DEFAULT 0',
            'in_jail TINYINT(1) DEFAULT 0',
            'jail_turns INT DEFAULT 0',
            'free_jail_cards INT DEFAULT 0'
        ] as $col) {
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
            name VARCHAR(100) NULL,
            price INT NULL,
            house_price INT NULL,
            level1_name VARCHAR(50) NULL,
            level2_name VARCHAR(50) NULL,
            level3_name VARCHAR(50) NULL,
            level4_name VARCHAR(50) NULL,
            level5_name VARCHAR(50) NULL,
            level1_price INT NULL,
            level2_price INT NULL,
            level3_price INT NULL,
            level4_price INT NULL,
            level5_price INT NULL,
            level1_rent INT NULL,
            level2_rent INT NULL,
            level3_rent INT NULL,
            level4_rent INT NULL,
            level5_rent INT NULL,
            image_url VARCHAR(255) NULL,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )");
        // Self-heal: add missing columns if they don't exist
        foreach ([
            'name VARCHAR(100) NULL', 'price INT NULL', 'house_price INT NULL',
            'level1_name VARCHAR(50) NULL', 'level2_name VARCHAR(50) NULL',
            'level3_name VARCHAR(50) NULL', 'level4_name VARCHAR(50) NULL', 'level5_name VARCHAR(50) NULL',
            'level1_price INT NULL', 'level2_price INT NULL', 'level3_price INT NULL',
            'level4_price INT NULL', 'level5_price INT NULL',
            'level1_rent INT NULL', 'level2_rent INT NULL', 'level3_rent INT NULL',
            'level4_rent INT NULL', 'level5_rent INT NULL'
        ] as $col) {
            try { $this->dbh->exec("ALTER TABLE board_properties ADD COLUMN $col"); } catch(Exception $e) {}
        }

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
