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

    public function deleteSession($id) {
        $this->db->query("DELETE FROM properties WHERE session_id = :id"); $this->db->bind('id',$id); $this->db->execute();
        $this->db->query("DELETE FROM players WHERE session_id = :id"); $this->db->bind('id',$id); $this->db->execute();
        $this->db->query("DELETE FROM sessions WHERE id = :id"); $this->db->bind('id',$id); $this->db->execute();
    }

    public function lastInsertId() {
        return $this->db->lastInsertId();
    }
}
