<?php
class PlayerModel {
    private $db;
    public function __construct() { $this->db = new Database; }

    public function getAllPlayers() {
        $this->db->query('SELECT * FROM players ORDER BY id ASC');
        return $this->db->resultSet();
    }

    public function getPlayersBySession($sessionId) {
        $this->db->query('SELECT * FROM players WHERE session_id = :sid ORDER BY id ASC');
        $this->db->bind('sid', $sessionId);
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

    public function addMoney($id, $amount) {
        $this->db->query('UPDATE players SET money = money + :amount WHERE id = :id');
        $this->db->bind('amount', $amount);
        $this->db->bind('id', $id);
        $this->db->execute();
    }

    public function setJail($id, $inJail, $jailTurns = 0) {
        $this->db->query('UPDATE players SET in_jail = :j, jail_turns = :jt WHERE id = :id');
        $this->db->bind('j', (int)$inJail);
        $this->db->bind('jt', (int)$jailTurns);
        $this->db->bind('id', $id);
        $this->db->execute();
    }

    public function updateLaps($id, $laps) {
        $this->db->query('UPDATE players SET laps = :laps WHERE id = :id');
        $this->db->bind('laps', (int)$laps);
        $this->db->bind('id', $id);
        $this->db->execute();
    }

    public function getCurrentTurn($sessionId) {
        $this->db->query('SELECT * FROM players WHERE session_id = :sid AND is_turn = 1 LIMIT 1');
        $this->db->bind('sid', $sessionId);
        return $this->db->single();
    }

    public function markRolled($id) {
        $this->db->query('UPDATE players SET has_rolled = 1 WHERE id = :id');
        $this->db->bind('id', $id);
        $this->db->execute();
    }

    public function nextTurn($sessionId) {
        // Dapatkan semua pemain dalam sesi
        $this->db->query('SELECT id FROM players WHERE session_id = :sid AND is_bankrupt = 0 ORDER BY id ASC');
        $this->db->bind('sid', $sessionId);
        $players = $this->db->resultSet();
        if (empty($players)) return;

        // Cari yang sedang giliran
        $this->db->query('SELECT id FROM players WHERE session_id = :sid AND is_turn = 1 LIMIT 1');
        $this->db->bind('sid', $sessionId);
        $current = $this->db->single();

        $ids = array_column($players, 'id');
        $currentIdx = array_search($current['id'] ?? 0, $ids);
        $nextIdx = ($currentIdx + 1) % count($ids);
        $nextId = $ids[$nextIdx];

        // Reset semua, set next
        $this->db->query('UPDATE players SET is_turn = 0, has_rolled = 0 WHERE session_id = :sid');
        $this->db->bind('sid', $sessionId);
        $this->db->execute();

        $this->db->query('UPDATE players SET is_turn = 1 WHERE id = :id');
        $this->db->bind('id', $nextId);
        $this->db->execute();
    }
}
