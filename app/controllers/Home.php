<?php
class Home extends Controller {
    public function index() {
        $data['judul'] = 'Monopoly - Edisi Kota Indonesia';
        $data['board'] = $this->model('BoardModel')->getBoard();
        $data['players'] = $this->model('PlayerModel')->getAllPlayers();
        
        $this->view('templates/header', $data);
        $this->view('home/index', $data);
        $this->view('templates/footer', $data);
    }

    public function apiStatus() {
        header('Content-Type: application/json');
        echo json_encode($this->model('PlayerModel')->getAllPlayers());
    }

    public function apiReset() {
        $db = new Database;
        $db->query("UPDATE players SET position = 0, money = 15000"); // Make everyone rich to fit Rupiah scale visually
        $db->execute();
        header('Location: ' . BASEURL);
        exit;
    }
}
