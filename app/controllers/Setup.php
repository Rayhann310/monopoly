<?php
class Setup extends Controller {

    // Halaman utama onboarding - daftar semua sesi
    public function index() {
        $data['judul'] = 'Monopoly Indonesia - Pilih Sesi';
        $data['sessions'] = $this->model('SessionModel')->getAllSessions();
        $this->view('templates/header_setup', $data);
        $this->view('setup/index', $data);
        $this->view('templates/footer', $data);
    }

    // Buat sesi baru
    public function create() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/setup'); exit;
        }
        $name = htmlspecialchars(trim($_POST['session_name'] ?? 'Permainan Baru'));
        $numPlayers = min(4, max(2, (int)($_POST['num_players'] ?? 4)));
        $colors = ['red', 'blue', 'green', 'yellow'];

        // Buat sesi dengan host_token unik
        $hostToken = bin2hex(random_bytes(16));
        $db = new Database;
        $db->query("INSERT INTO sessions (name, status, host_token) VALUES (:name, 'waiting', :token)");
        $db->bind('name', $name);
        $db->bind('token', $hostToken);
        $db->execute();
        $sessionId = $db->lastInsertId();

        // Ambil uang awal dari pengaturan
        $db->query("SELECT setting_value FROM game_settings WHERE setting_key = 'starting_money'");
        $moneyRow = $db->single();
        $startMoney = $moneyRow ? (int)$moneyRow['setting_value'] : 15000;

        // Insert pemain
        for ($i = 0; $i < $numPlayers; $i++) {
            $playerName = htmlspecialchars(trim($_POST['player_' . ($i+1)] ?? 'Pemain ' . ($i+1)));
            $db->query("INSERT INTO players (session_id, name, color, position, money, is_turn) VALUES (:sid, :name, :color, 0, :money, :turn)");
            $db->bind('sid', $sessionId);
            $db->bind('name', $playerName);
            $db->bind('color', $colors[$i]);
            $db->bind('money', $startMoney);
            $db->bind('turn', $i === 0 ? 1 : 0);
            $db->execute();
        }

        // Set sesi jadi 'playing'
        $db->query("UPDATE sessions SET status = 'playing' WHERE id = :id");
        $db->bind('id', $sessionId);
        $db->execute();

        header('Location: ' . BASEURL . '/home/game/' . $sessionId . '?host=' . $hostToken);
        exit;
    }

    // Hapus sesi
    public function delete($id) {
        $this->model('SessionModel')->deleteSession($id);
        header('Location: ' . BASEURL . '/setup');
        exit;
    }
}
