<?php
class Admin extends Controller {
    public function index() {
        header('Location: ' . BASEURL . '/admin/dashboard');
        exit;
    }

    private function requireLogin() {

        if (empty($_SESSION['admin_logged_in'])) {
            header('Location: ' . BASEURL . '/admin/login');
            exit;
        }
    }

    public function login() {
        // Jika sudah login, redirect ke dashboard
        if (!empty($_SESSION['admin_logged_in'])) {
            header('Location: ' . BASEURL . '/admin/dashboard'); exit;
        }

        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';
            $db = new Database;
            $db->query("SELECT * FROM admins WHERE username = :u LIMIT 1");
            $db->bind('u', $username);
            $admin = $db->single();
            if ($admin && password_verify($password, $admin['password_hash'])) {
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_username'] = $admin['username'];
                header('Location: ' . BASEURL . '/admin/dashboard'); exit;
            }
            $error = 'Username atau password salah!';
        }
        $data['judul'] = 'Admin Login';
        $data['error'] = $error;
        $this->view('admin/login', $data);
    }

    public function logout() {

        session_destroy();
        header('Location: ' . BASEURL . '/admin/login'); exit;
    }

    public function dashboard() {
        $this->requireLogin();
        $db = new Database;

        // Stats
        $db->query("SELECT COUNT(*) as total FROM sessions");
        $data['total_sessions'] = $db->single()['total'];

        $db->query("SELECT COUNT(*) as total FROM sessions WHERE status = 'playing'");
        $data['active_sessions'] = $db->single()['total'];

        $db->query("SELECT COUNT(*) as total FROM players");
        $data['total_players'] = $db->single()['total'];

        $db->query("SELECT s.*, COUNT(p.id) as player_count FROM sessions s LEFT JOIN players p ON p.session_id = s.id GROUP BY s.id ORDER BY s.created_at DESC LIMIT 10");
        $data['sessions'] = $db->resultSet();

        $data['settings'] = $this->model('SettingsModel')->getAll();
        $data['judul'] = 'Admin Dashboard';
        $data['admin'] = $_SESSION['admin_username'];

        $this->view('admin/dashboard', $data);
    }

    public function sessions() {
        $this->requireLogin();
        $db = new Database;
        $db->query("SELECT s.*, COUNT(p.id) as player_count FROM sessions s LEFT JOIN players p ON p.session_id = s.id GROUP BY s.id ORDER BY s.created_at DESC");
        $data['sessions'] = $db->resultSet();
        $data['judul'] = 'Sesi Aktif';
        $data['admin'] = $_SESSION['admin_username'];
        $this->view('admin/sessions', $data);
    }

    public function database() {
        $this->requireLogin();
        $data['judul'] = 'Database';
        $data['admin'] = $_SESSION['admin_username'];
        $this->view('admin/database', $data);
    }

    public function password() {
        $this->requireLogin();
        $data['judul'] = 'Ganti Password';
        $data['admin'] = $_SESSION['admin_username'];
        $this->view('admin/password', $data);
    }

    public function settings() {
        $this->requireLogin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $db = new Database;
            foreach ($_POST['settings'] as $key => $value) {
                $db->query("UPDATE game_settings SET setting_value = :val WHERE setting_key = :key");
                $db->bind('val', $value);
                $db->bind('key', $key);
                $db->execute();
            }
            header('Location: ' . BASEURL . '/admin/dashboard?saved=1'); exit;
        }
        $data['settings'] = $this->model('SettingsModel')->getAll();
        $data['judul'] = 'Pengaturan Game';
        $data['admin'] = $_SESSION['admin_username'];
        $this->view('admin/settings', $data);
    }

    public function cards() {
        $this->requireLogin();
        $data['judul'] = 'Kelola Kartu';
        $data['admin'] = $_SESSION['admin_username'];
        $db = new Database;
        $db->query("SELECT * FROM cards ORDER BY type, id");
        $data['cards'] = $db->resultSet();
        $this->view('admin/cards', $data);
    }

    public function saveCard() {
        $this->requireLogin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $db = new Database;
            $id = (int)($_POST['id'] ?? 0);
            $text = htmlspecialchars(trim($_POST['text'] ?? ''));
            $type = $_POST['type'] ?? 'kesempatan';
            $effectType = $_POST['effect_type'] ?? 'none';
            $effectValue = (int)($_POST['effect_value'] ?? 0);
            $isActive = isset($_POST['is_active']) ? 1 : 0;

            $image_url = '';
            if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                $filename = time() . '_' . rand(1000,9999) . '.' . $ext;
                $uploadDir = 'assets/static/cards/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $filename);
                $image_url = $filename;
            }

            if ($id > 0) {
                if ($image_url) {
                    $db->query("UPDATE cards SET text=:text, type=:type, effect_type=:et, effect_value=:ev, is_active=:ia, image_url=:img WHERE id=:id");
                    $db->bind('img', $image_url);
                } else {
                    $db->query("UPDATE cards SET text=:text, type=:type, effect_type=:et, effect_value=:ev, is_active=:ia WHERE id=:id");
                }

                $db->bind('id', $id);
            } else {
                $db->query("INSERT INTO cards (text, type, effect_type, effect_value, is_active, image_url) VALUES (:text,:type,:et,:ev,:ia,:img)");
                $db->bind('img', $image_url);
            }
            $db->bind('text', $text); $db->bind('type', $type);
            $db->bind('et', $effectType); $db->bind('ev', $effectValue); $db->bind('ia', $isActive);
            $db->execute();
        }
        header('Location: ' . BASEURL . '/admin/cards'); exit;
    }

    public function deleteCard($id) {
        $this->requireLogin();
        $db = new Database;
        $db->query("DELETE FROM cards WHERE id = :id");
        $db->bind('id', (int)$id);
        $db->execute();
        header('Location: ' . BASEURL . '/admin/cards'); exit;
    }

    public function properties() {
        $this->requireLogin();
        $data['judul'] = 'Gambar Kota';
        $data['admin'] = $_SESSION['admin_username'];
        $data['board'] = $this->model('BoardModel')->getBoard();

        // Load existing custom properties keyed by cell_index
        $db = new Database;
        $db->query("SELECT * FROM board_properties");
        $rows = $db->resultSet();
        $data['images'] = [];
        $data['custom_props'] = [];
        foreach ($rows as $r) {
            $data['images'][(int)$r['cell_index']] = $r['image_url'];
            $data['custom_props'][(int)$r['cell_index']] = [
                'name' => $r['name'],
                'price' => $r['price'],
                'house_price' => $r['house_price']
            ];
        }

        $data['success'] = $_GET['saved'] ?? null;
        $this->view('admin/properties', $data);
    }

    public function saveCityImage() {
        $this->requireLogin();
        $cellIndex = (int)($_POST['cell_index'] ?? -1);
        if ($cellIndex < 0 || $cellIndex > 39) {
            header('Location: ' . BASEURL . '/admin/properties'); exit;
        }

        $name = $_POST['name'] ?? '';
        $price = $_POST['price'] ?? '';

        $nameVal  = $name  === '' ? null : $name;
        $priceVal = $price === '' ? null : (int)$price;

        // Per-level names, prices, rents
        $levels = [];
        for ($i = 1; $i <= 5; $i++) {
            $ln = $_POST["level{$i}_name"]  ?? '';
            $lp = $_POST["level{$i}_price"] ?? '';
            $lr = $_POST["level{$i}_rent"]  ?? '';
            $levels["level{$i}_name"]  = $ln === '' ? null : $ln;
            $levels["level{$i}_price"] = $lp === '' ? null : (int)$lp;
            $levels["level{$i}_rent"]  = $lr === '' ? null : (int)$lr;
        }

        $db = new Database;
        $imageUrl = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
            $uploadDir = 'assets/static/cities/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg','jpeg','png','gif','webp'];
            if (in_array($ext, $allowed)) {
                $filename = 'city_' . $cellIndex . '_' . time() . '.' . $ext;
                $dest = $uploadDir . $filename;
                if (move_uploaded_file($_FILES['image']['tmp_name'], $dest)) { $imageUrl = $dest; }
            }
        }

        // Build upsert — always update all fields
        $setCols = 'name=:n2,price=:p2,level1_name=:l1n2,level2_name=:l2n2,level3_name=:l3n2,level4_name=:l4n2,level5_name=:l5n2,level1_price=:l1p2,level2_price=:l2p2,level3_price=:l3p2,level4_price=:l4p2,level5_price=:l5p2,level1_rent=:l1r2,level2_rent=:l2r2,level3_rent=:l3r2,level4_rent=:l4r2,level5_rent=:l5r2';
        $insCols = 'cell_index,name,price,level1_name,level2_name,level3_name,level4_name,level5_name,level1_price,level2_price,level3_price,level4_price,level5_price,level1_rent,level2_rent,level3_rent,level4_rent,level5_rent';
        $insVals = ':ci,:n,:p,:l1n,:l2n,:l3n,:l4n,:l5n,:l1p,:l2p,:l3p,:l4p,:l5p,:l1r,:l2r,:l3r,:l4r,:l5r';

        if ($imageUrl) {
            $setCols .= ',image_url=:img2';
            $insCols .= ',image_url';
            $insVals .= ',:img';
        }

        $sql = "INSERT INTO board_properties ($insCols) VALUES ($insVals) ON DUPLICATE KEY UPDATE $setCols";
        $db->query($sql);
        $db->bind('ci', $cellIndex);
        $db->bind('n', $nameVal);  $db->bind('n2', $nameVal);
        $db->bind('p', $priceVal); $db->bind('p2', $priceVal);
        for ($i = 1; $i <= 5; $i++) {
            $db->bind("l{$i}n",  $levels["level{$i}_name"]);  $db->bind("l{$i}n2", $levels["level{$i}_name"]);
            $db->bind("l{$i}p",  $levels["level{$i}_price"]); $db->bind("l{$i}p2", $levels["level{$i}_price"]);
            $db->bind("l{$i}r",  $levels["level{$i}_rent"]);  $db->bind("l{$i}r2", $levels["level{$i}_rent"]);
        }
        if ($imageUrl) { $db->bind('img', $imageUrl); $db->bind('img2', $imageUrl); }
        $db->execute();

        header('Location: ' . BASEURL . '/admin/properties?saved=Pengaturan kota berhasil disimpan!'); exit;
    }

    public function deleteCityImage($cellIndex) {
        $this->requireLogin();
        $cellIndex = (int)$cellIndex;
        $db = new Database;
        $db->query("SELECT image_url FROM board_properties WHERE cell_index = :ci");
        $db->bind('ci', $cellIndex);
        $row = $db->single();
        if ($row && file_exists($row['image_url'])) {
            unlink($row['image_url']);
        }
        $db->query("DELETE FROM board_properties WHERE cell_index = :ci");
        $db->bind('ci', $cellIndex);
        $db->execute();
        header('Location: ' . BASEURL . '/admin/properties'); exit;
    }

    public function repairDb() {
        $this->requireLogin();
        $db = new Database;
        $db->selfHeal(); // jalankan ulang self-heal
        header('Location: ' . BASEURL . '/admin/dashboard?repaired=1'); exit;
    }

    public function stopSession($id) {
        $this->requireLogin();
        $db = new Database;
        $db->query("UPDATE sessions SET status = 'finished' WHERE id = :id");
        $db->bind('id', (int)$id);
        $db->execute();
        $data['settings'] = $this->model('SettingsModel')->getAll();
        $data['judul'] = 'Pengaturan Game';
        $data['admin'] = $_SESSION['admin_username'];
        $this->view('admin/settings', $data);
    }

    public function deleteSession($id) {
        $this->requireLogin();
        $this->model('SessionModel')->deleteSession((int)$id);
        $data['settings'] = $this->model('SettingsModel')->getAll();
        $data['judul'] = 'Pengaturan Game';
        $data['admin'] = $_SESSION['admin_username'];
        $this->view('admin/settings', $data);
    }

    public function cleanFinished() {
        $this->requireLogin();
        $db = new Database;
        // Get finished session IDs
        $db->query("SELECT id FROM sessions WHERE status = 'finished'");
        $finished = $db->resultSet();
        foreach ($finished as $s) {
            $this->model('SessionModel')->deleteSession($s['id']);
        }
        header('Location: ' . BASEURL . '/admin/dashboard?repaired=1'); exit;
    }

    public function changePassword() {
        $this->requireLogin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $newPass = $_POST['new_password'] ?? '';
            if (strlen($newPass) >= 6) {
                $hash = password_hash($newPass, PASSWORD_DEFAULT);
                $db = new Database;
                $db->query("UPDATE admins SET password_hash = :hash WHERE username = :u");
                $db->bind('hash', $hash);
                $db->bind('u', $_SESSION['admin_username']);
                $db->execute();
            }
        }
        header('Location: ' . BASEURL . '/admin/dashboard?saved=1'); exit;
    }
}
