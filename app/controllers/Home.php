<?php
class Home extends Controller {

    // Board utama — butuh session_id
    public function index() {
        // Redirect ke setup jika tidak ada session
        header('Location: ' . BASEURL . '/setup');
        exit;
    }

    public function game($sessionId = null) {
        if (!$sessionId) { header('Location: ' . BASEURL . '/setup'); exit; }

        $session = $this->model('SessionModel')->getSessionById($sessionId);
        if (!$session) { header('Location: ' . BASEURL . '/setup'); exit; }

        // Deteksi apakah user adalah host
        $hostToken = $_GET['host'] ?? '';
        $isHost = ($hostToken !== '' && $hostToken === $session['host_token']);

        $data['judul'] = 'Monopoly - ' . htmlspecialchars($session['name']);
        $data['session'] = $session;
        $data['is_host'] = $isHost;
        $data['host_token'] = $hostToken;
        $data['board'] = $this->model('BoardModel')->getBoard();
        $data['players'] = $this->model('PlayerModel')->getPlayersBySession($sessionId);

        $this->view('templates/header', $data);
        $this->view('home/index', $data);
        $this->view('templates/footer', $data);
    }

    public function apiStatus($sessionId = null) {
        header('Content-Type: application/json');
        if (!$sessionId) { echo json_encode([]); return; }
        echo json_encode(array_values($this->model('PlayerModel')->getPlayersBySession($sessionId)));
    }

    public function apiAdjustMoney() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { echo json_encode(['status'=>'error']); return; }
        $playerId = (int)($_POST['player_id'] ?? 0);
        $amount = (int)($_POST['amount'] ?? 0);
        if (!$playerId) { echo json_encode(['status'=>'error','msg'=>'ID tidak valid']); return; }
        $player = $this->model('PlayerModel')->getPlayerById($playerId);
        if (!$player) { echo json_encode(['status'=>'error','msg'=>'Pemain tidak ditemukan']); return; }
        $newMoney = max(0, (int)$player['money'] + $amount);
        $this->model('PlayerModel')->updateMoney($playerId, $newMoney);
        echo json_encode(['status'=>'success','new_money'=>$newMoney]);
    }

    public function apiReset($sessionId = null) {
        if ($sessionId) {
            $db = new Database;
            $db->query("UPDATE players SET position = 0, money = 15000, is_turn = 0, has_rolled = 0 WHERE session_id = :sid");
            $db->bind('sid', $sessionId);
            $db->execute();
            // Set giliran ke pemain pertama
            $db->query("UPDATE players SET is_turn = 1 WHERE session_id = :sid ORDER BY id ASC LIMIT 1");
            $db->bind('sid', $sessionId);
            $db->execute();
            $db->query("DELETE FROM properties WHERE session_id = :sid");
            $db->bind('sid', $sessionId);
            $db->execute();
        }
        header('Location: ' . BASEURL . '/home/game/' . $sessionId);
        exit;
    }
}
