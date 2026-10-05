<?php
class PlayerModel {
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    public function getAllPlayers() {
        $this->db->query('SELECT * FROM players ORDER BY id ASC');
        return $this->db->resultSet();
    }

    public function getPlayerById($id) {
        $this->db->query('SELECT * FROM players WHERE id = :id');
        $this->db->bind('id', $id);
        return $this->db->single();
    }

    public function updatePosition($id, $newPosition) {
        $this->db->query('UPDATE players SET position = :position WHERE id = :id');
        $this->db->bind('position', $newPosition);
        $this->db->bind('id', $id);
        $this->db->execute();
    }

    public function updateMoney($id, $newMoney) {
        $this->db->query('UPDATE players SET money = :money WHERE id = :id');
        $this->db->bind('money', $newMoney);
        $this->db->bind('id', $id);
        $this->db->execute();
    }
}
