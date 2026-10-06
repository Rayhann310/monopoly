<?php
class PropertyModel {
    private $db;
    public function __construct() { $this->db = new Database; }

    public function getOwner($sessionId, $cellIndex) {
        $this->db->query('SELECT p.*, pl.name as owner_name, pl.color as owner_color, pl.money as owner_money 
                          FROM properties p 
                          JOIN players pl ON p.owner_id = pl.id 
                          WHERE p.session_id = :sid AND p.cell_index = :ci LIMIT 1');
        $this->db->bind('sid', $sessionId);
        $this->db->bind('ci', $cellIndex);
        return $this->db->single();
    }

    public function buyProperty($sessionId, $ownerId, $cellIndex) {
        $this->db->query('INSERT INTO properties (session_id, cell_index, owner_id, houses) VALUES (:sid, :ci, :oid, 0)');
        $this->db->bind('sid', $sessionId);
        $this->db->bind('ci', $cellIndex);
        $this->db->bind('oid', $ownerId);
        return $this->db->execute();
    }

    public function upgradeProperty($sessionId, $ownerId, $cellIndex, $targetLevel = null) {
        if ($targetLevel !== null) {
            $this->db->query('UPDATE properties SET houses = :lvl WHERE session_id = :sid AND owner_id = :oid AND cell_index = :ci');
            $this->db->bind('lvl', (int)$targetLevel);
        } else {
            $this->db->query('UPDATE properties SET houses = houses + 1 WHERE session_id = :sid AND owner_id = :oid AND cell_index = :ci');
        }
        $this->db->bind('sid', $sessionId);
        $this->db->bind('oid', $ownerId);
        $this->db->bind('ci', $cellIndex);
        return $this->db->execute();
    }

    public function getPlayerProperties($sessionId, $playerId) {
        $this->db->query('SELECT * FROM properties WHERE session_id = :sid AND owner_id = :pid');
        $this->db->bind('sid', $sessionId);
        $this->db->bind('pid', $playerId);
        return $this->db->resultSet();
    }

    public function countGroupOwned($sessionId, $ownerId, $cellIndexes) {
        $in = implode(',', array_map('intval', $cellIndexes));
        $this->db->query("SELECT COUNT(*) as cnt FROM properties WHERE session_id = :sid AND owner_id = :oid AND cell_index IN ($in)");
        $this->db->bind('sid', $sessionId);
        $this->db->bind('oid', $ownerId);
        $row = $this->db->single();
        return (int)($row['cnt'] ?? 0);
    }
}
