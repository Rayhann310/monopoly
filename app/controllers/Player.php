<?php
class Player extends Controller {

    public function index($id = 1) {
        $data['judul'] = 'Layar Pemain';
        $data['player'] = $this->model('PlayerModel')->getPlayerById($id);
        $data['board'] = $this->model('BoardModel')->getBoard();

        if (!$data['player']) { die("Pemain tidak ditemukan!"); }

        $this->view('templates/header_player', $data);
        $this->view('player/index', $data);
        $this->view('templates/footer_player', $data);
    }

    public function apiRoll() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['status' => 'error', 'msg' => 'Method not allowed']); return;
        }

        $id = (int)($_POST['id'] ?? 0);
        $newPos = (int)($_POST['new_position'] ?? 0);

        if ($id <= 0 || $newPos < 0 || $newPos > 39) {
            echo json_encode(['status' => 'error', 'msg' => 'Data tidak valid']); return;
        }

        $player = $this->model('PlayerModel')->getPlayerById($id);
        if (!$player) {
            echo json_encode(['status' => 'error', 'msg' => 'Pemain tidak ditemukan']); return;
        }

        if (!$player['is_turn']) {
            echo json_encode(['status' => 'error', 'msg' => 'Bukan giliran kamu!']); return;
        }

        if ($player['has_rolled']) {
            echo json_encode(['status' => 'error', 'msg' => 'Sudah melempar dadu!']); return;
        }

        $oldPos = $player['position'];
        
        // Pass Go logic
        $settings = $this->model('SettingsModel');
        $passGoBonus = (int)$settings->get('pass_go_bonus', 2000);
        $taxAmount = (int)$settings->get('tax_amount', 2000);
        $luxuryTax = (int)$settings->get('luxury_tax', 7500);

        $newMoney = (int)$player['money'];
        if ($newPos < $oldPos) {
            // Passed Go (completed a lap)
            $newMoney += $passGoBonus;
        }

        // Determine landing logic based on hardcoded board cells (for now)
        // Cell 4 is Tax, Cell 38 is Luxury Tax
        if ($newPos == 4) {
            $newMoney -= $taxAmount;
        } else if ($newPos == 38) {
            $newMoney -= $luxuryTax;
        } else if ($newPos == 30) {
            // Go to Jail
            $newPos = 10;
        }

        $this->model('PlayerModel')->updatePosition($id, $newPos);
        $this->model('PlayerModel')->updateMoney($id, $newMoney);
        $this->model('PlayerModel')->markRolled($id);

        echo json_encode(['status' => 'success', 'position' => $newPos, 'money' => $newMoney]);
    }

    public function apiEndTurn() {
        header('Content-Type: application/json');
        $id = (int)($_POST['id'] ?? 0);
        $player = $this->model('PlayerModel')->getPlayerById($id);
        if (!$player || !$player['is_turn']) {
            echo json_encode(['status' => 'error', 'msg' => 'Bukan giliran kamu']); return;
        }
        $this->model('PlayerModel')->nextTurn($player['session_id']);
        echo json_encode(['status' => 'success']);
    }

    public function apiStatus($id = null) {
        header('Content-Type: application/json');
        if (!$id) { echo json_encode(['status' => 'error']); return; }
        $player = $this->model('PlayerModel')->getPlayerById($id);
        echo json_encode(['is_turn' => (bool)$player['is_turn'], 'has_rolled' => (bool)$player['has_rolled'], 'position' => (int)$player['position'], 'money' => (int)$player['money']]);
    }
}
