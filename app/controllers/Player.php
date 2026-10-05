<?php
class Player extends Controller {
    public function index($id = 1) {
        $data['judul'] = 'Layar Pemain';
        $data['player'] = $this->model('PlayerModel')->getPlayerById($id);
        $data['board'] = $this->model('BoardModel')->getBoard();
        
        // Cek jika pemain tidak ada
        if(!$data['player']) {
            die("Pemain tidak ditemukan!");
        }

        $this->view('templates/header_player', $data);
        $this->view('player/index', $data);
        $this->view('templates/footer_player', $data);
    }

    public function apiRoll() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'];
            $newPos = $_POST['new_position'];
            $this->model('PlayerModel')->updatePosition($id, $newPos);
            echo json_encode(['status' => 'success']);
        }
    }
}
