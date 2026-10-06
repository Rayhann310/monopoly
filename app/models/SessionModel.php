<?php
class SessionModel {
    private $db;
    public function __construct() { $this->db = new Database; }

    public function getAllSessions() {
        $this->db->query("SELECT s.*, COUNT(p.id) as player_count FROM sessions s LEFT JOIN players p ON p.session_id = s.id GROUP BY s.id ORDER BY s.created_at DESC");
        return $this->db->resultSet();
    }

    public function getSessionById($id) {
        $this->db->query("SELECT * FROM sessions WHERE id = :id");
        $this->db->bind('id', $id);
        return $this->db->single();
    }

    public function createSession($name) {
        $this->db->query("INSERT INTO sessions (name, status) VALUES (:name, 'waiting')");
        $this->db->bind('name', $name);
        $this->db->execute();
        $sessionId = $this->db->lastInsertId();
        return $sessionId;
    }

    public function startSession($id) {
        $this->db->query("UPDATE sessions SET status = 'playing' WHERE id = :id");
        $this->db->bind('id', $id);
        $this->db->execute();
        // Set giliran pertama ke player pertama di sesi ini
        $this->db->query("UPDATE players SET is_turn = 1 WHERE session_id = :sid AND id = (SELECT MIN(id) FROM (SELECT id FROM players WHERE session_id = :sid2) t)");
        $this->db->bind('sid', $id);
        $this->db->bind('sid2', $id);
        $this->db->execute();
    }

    public function setActiveCard($id, $cardJson) {
        $this->db->query("UPDATE sessions SET active_card = :card WHERE id = :id");
        $this->db->bind('card', $cardJson);
        $this->db->bind('id', $id);
        $this->db->execute();
    }

    public function clearActiveCard($id) {
        $this->db->query("UPDATE sessions SET active_card = NULL WHERE id = :id");
        $this->db->bind('id', $id);
        $this->db->execute();
    }

    public function deleteSession($id) {
        $this->db->query("DELETE FROM properties WHERE session_id = :id"); $this->db->bind('id',$id); $this->db->execute();
        $this->db->query("DELETE FROM players WHERE session_id = :id"); $this->db->bind('id',$id); $this->db->execute();
        $this->db->query("DELETE FROM sessions WHERE id = :id"); $this->db->bind('id',$id); $this->db->execute();
    }

    public function lastInsertId() {
        return $this->db->lastInsertId();
    }

    public function getParkingPot($sessionId) {
        $this->db->query("SELECT COALESCE(free_parking_pot, 0) as pot FROM sessions WHERE id = :id");
        $this->db->bind('id', $sessionId);
        $row = $this->db->single();
        return $row ? (int)$row['pot'] : 0;
    }

    public function addToParkingPot($sessionId, $amount) {
        $this->db->query("UPDATE sessions SET free_parking_pot = COALESCE(free_parking_pot, 0) + :amt WHERE id = :id");
        $this->db->bind('amt', abs((int)$amount));
        $this->db->bind('id', $sessionId);
        $this->db->execute();
    }

    public function collectParkingPot($sessionId, $seed = 0) {
        $pot = $this->getParkingPot($sessionId);
        // Reset pot to seed value (or 0)
        $this->db->query("UPDATE sessions SET free_parking_pot = :seed WHERE id = :id");
        $this->db->bind('seed', (int)$seed);
        $this->db->bind('id', $sessionId);
        $this->db->execute();
        return $pot;
    }
}
