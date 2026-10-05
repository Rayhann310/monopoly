<?php
class SettingsModel {
    private $db;
    public function __construct() { $this->db = new Database; }

    public function getAll() {
        $this->db->query("SELECT * FROM game_settings ORDER BY id");
        $rows = $this->db->resultSet();
        $result = [];
        foreach ($rows as $r) $result[$r['setting_key']] = $r;
        return $result;
    }

    public function get($key, $default = null) {
        $this->db->query("SELECT setting_value FROM game_settings WHERE setting_key = :k");
        $this->db->bind('k', $key);
        $row = $this->db->single();
        return $row ? $row['setting_value'] : $default;
    }
}
