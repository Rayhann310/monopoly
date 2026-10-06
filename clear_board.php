<?php
require_once 'app/core/Database.php';
$db = new Database();
$db->query("DELETE FROM board_properties");
$db->execute();
echo "board_properties cleared. " . $db->rowCount() . " rows deleted.";
