<?php
class CardModel {
    private $db;
    public function __construct() { $this->db = new Database; }

    public function drawRandom($type) {
        $this->db->query('SELECT * FROM cards WHERE type = :type AND is_active = 1 ORDER BY RAND() LIMIT 1');
        $this->db->bind('type', $type);
        return $this->db->single();
    }
}
