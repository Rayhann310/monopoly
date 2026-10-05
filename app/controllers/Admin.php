<?php
class Admin extends Controller {

    private function requireLogin() {
        session_start();
        if (empty($_SESSION['admin_logged_in'])) {
            header('Location: ' . BASEURL . '/admin/login');
            exit;
        }
    }

    public function login() {
        session_start();
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
        session_start();
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
        header('Location: ' . BASEURL . '/admin/dashboard'); exit;
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

            if ($id > 0) {
                $db->query("UPDATE cards SET text=:text, type=:type, effect_type=:et, effect_value=:ev, is_active=:ia WHERE id=:id");
                $db->bind('id', $id);
            } else {
                $db->query("INSERT INTO cards (text, type, effect_type, effect_value, is_active) VALUES (:text,:type,:et,:ev,:ia)");
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
        header('Location: ' . BASEURL . '/admin/dashboard'); exit;
    }

    public function deleteSession($id) {
        $this->requireLogin();
        $this->model('SessionModel')->deleteSession((int)$id);
        header('Location: ' . BASEURL . '/admin/dashboard'); exit;
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
