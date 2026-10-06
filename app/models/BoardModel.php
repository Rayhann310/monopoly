<?php
class BoardModel {
    private $board = [
        ['name' => 'Start', 'type' => 'start', 'color' => 'white'],
        ['name' => 'Jakarta', 'price' => 400, 'type' => 'property', 'color' => 'blue'],
        ['name' => 'Dana Umum', 'type' => 'community_chest', 'color' => 'white'],
        ['name' => 'Surabaya', 'price' => 350, 'type' => 'property', 'color' => 'blue'],
        ['name' => 'Pajak', 'price' => 200, 'type' => 'tax', 'color' => 'white'],
        ['name' => 'Stasiun Gambir', 'price' => 200, 'type' => 'station', 'color' => 'white'],
        ['name' => 'Bandung', 'price' => 300, 'type' => 'property', 'color' => 'green'],
        ['name' => 'Kesempatan', 'type' => 'chance', 'color' => 'white'],
        ['name' => 'Yogyakarta', 'price' => 280, 'type' => 'property', 'color' => 'green'],
        ['name' => 'Semarang', 'price' => 260, 'type' => 'property', 'color' => 'green'],
        ['name' => 'Penjara', 'type' => 'jail', 'color' => 'white'],
        ['name' => 'Medan', 'price' => 240, 'type' => 'property', 'color' => 'red'],
        ['name' => 'PLN', 'price' => 150, 'type' => 'utility', 'color' => 'white'],
        ['name' => 'Palembang', 'price' => 220, 'type' => 'property', 'color' => 'red'],
        ['name' => 'Padang', 'price' => 200, 'type' => 'property', 'color' => 'red'],
        ['name' => 'Stasiun Pasar Turi', 'price' => 200, 'type' => 'station', 'color' => 'white'],
        ['name' => 'Makassar', 'price' => 180, 'type' => 'property', 'color' => 'yellow'],
        ['name' => 'Dana Umum', 'type' => 'community_chest', 'color' => 'white'],
        ['name' => 'Manado', 'price' => 160, 'type' => 'property', 'color' => 'yellow'],
        ['name' => 'Kendari', 'price' => 140, 'type' => 'property', 'color' => 'yellow'],
        ['name' => 'Pejabat Negara', 'type' => 'pejabat_negara', 'color' => 'white'],
        ['name' => 'Denpasar', 'price' => 120, 'type' => 'property', 'color' => 'orange'],
        ['name' => 'Kesempatan', 'type' => 'chance', 'color' => 'white'],
        ['name' => 'Mataram', 'price' => 100, 'type' => 'property', 'color' => 'orange'],
        ['name' => 'Kupang', 'price' => 100, 'type' => 'property', 'color' => 'orange'],
        ['name' => 'Stasiun Tugu', 'price' => 200, 'type' => 'station', 'color' => 'white'],
        ['name' => 'Banjarmasin', 'price' => 80, 'type' => 'property', 'color' => 'pink'],
        ['name' => 'Balikpapan', 'price' => 60, 'type' => 'property', 'color' => 'pink'],
        ['name' => 'PDAM', 'price' => 150, 'type' => 'utility', 'color' => 'white'],
        ['name' => 'Pontianak', 'price' => 60, 'type' => 'property', 'color' => 'pink'],
        ['name' => 'Masuk Penjara', 'type' => 'go_to_jail', 'color' => 'white'],
        ['name' => 'Ambon', 'price' => 50, 'type' => 'property', 'color' => 'brown'],
        ['name' => 'Ternate', 'price' => 50, 'type' => 'property', 'color' => 'brown'],
        ['name' => 'Dana Umum', 'type' => 'community_chest', 'color' => 'white'],
        ['name' => 'Jayapura', 'price' => 40, 'type' => 'property', 'color' => 'brown'],
        ['name' => 'Stasiun Bandung', 'price' => 200, 'type' => 'station', 'color' => 'white'],
        ['name' => 'Kesempatan', 'type' => 'chance', 'color' => 'white'],
        ['name' => 'Sorong', 'price' => 40, 'type' => 'property', 'color' => 'cyan'],
        ['name' => 'Pajak Mewah', 'price' => 100, 'type' => 'tax', 'color' => 'white'],
        ['name' => 'Manokwari', 'price' => 40, 'type' => 'property', 'color' => 'cyan']
    ];

    public function getBoard() {
        $db = new Database();
        
        // Fetch custom names & prices for cities
        $db->query("SELECT * FROM board_properties");
        $customs = $db->resultSet();
        $customMap = [];
        foreach ($customs as $c) {
            $customMap[(int)$c['cell_index']] = $c;
        }

        // Fetch dynamic game settings for Tax, Luxury Tax, and Start Bonus
        $db->query("SELECT setting_key, setting_value FROM game_settings WHERE setting_key IN ('tax_amount', 'luxury_tax', 'pass_go_bonus')");
        $sets = $db->resultSet();
        $gameSettings = [
            'tax_amount' => 2000,
            'luxury_tax' => 7500,
            'pass_go_bonus' => 2000
        ];
        foreach ($sets as $s) {
            $gameSettings[$s['setting_key']] = (int)$s['setting_value'];
        }

        $board = $this->board;
        foreach ($board as $idx => &$cell) {
            $cell['index'] = $idx;
            
            // Override with custom city props if available
            if (isset($customMap[$idx])) {
                $c = $customMap[$idx];
                if (!empty($c['name']))      $cell['name']      = $c['name'];
                if (!empty($c['price']))     $cell['price']     = (int)$c['price'];
                if (!empty($c['image_url'])) $cell['image_url'] = $c['image_url'];
                // Tier levels
                for ($lvl = 1; $lvl <= 5; $lvl++) {
                    if (!empty($c["level{$lvl}_name"]))  $cell["level{$lvl}_name"]  = $c["level{$lvl}_name"];
                    if (!empty($c["level{$lvl}_price"])) $cell["level{$lvl}_price"] = (int)$c["level{$lvl}_price"];
                    if (!empty($c["level{$lvl}_rent"]))  $cell["level{$lvl}_rent"]  = (int)$c["level{$lvl}_rent"];
                }
            }

            // Apply dynamic settings for Taxes and Start
            if ($cell['type'] === 'tax') {
                if ($idx == 38) { // Pajak Mewah
                    $cell['price'] = $gameSettings['luxury_tax'];
                } else { // Pajak Biasa
                    $cell['price'] = $gameSettings['tax_amount'];
                }
            } else if ($cell['type'] === 'start') {
                $cell['price'] = $gameSettings['pass_go_bonus'];
            }
        }
        return $board;
    }
}

