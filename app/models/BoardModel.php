<?php
class BoardModel {
    private $board = [
        ['name' => 'Start',             'type' => 'start',           'color' => 'white'],
        ['name' => 'Jakarta',           'price' => 4000, 'type' => 'property',        'color' => 'blue',
         'level1_rent'=>200, 'level2_name'=>'Rumah','level2_price'=>2000,'level2_rent'=>600,
         'level3_name'=>'Rumah 2','level3_price'=>3000,'level3_rent'=>1400,
         'level4_name'=>'Rumah 3','level4_price'=>4000,'level4_rent'=>2000,
         'level5_name'=>'Hotel','level5_price'=>6000,'level5_rent'=>5000],
        ['name' => 'Dana Umum',         'type' => 'community_chest', 'color' => 'white'],
        ['name' => 'Surabaya',          'price' => 3500, 'type' => 'property',        'color' => 'blue',
         'level1_rent'=>175, 'level2_name'=>'Rumah','level2_price'=>1750,'level2_rent'=>500,
         'level3_name'=>'Rumah 2','level3_price'=>2625,'level3_rent'=>1100,
         'level4_name'=>'Rumah 3','level4_price'=>3500,'level4_rent'=>1750,
         'level5_name'=>'Hotel','level5_price'=>5250,'level5_rent'=>4250],
        ['name' => 'Pajak',             'price' => 200,  'type' => 'tax',             'color' => 'white'],
        ['name' => 'Stasiun Gambir',    'price' => 2000, 'type' => 'station',         'color' => 'white'],
        ['name' => 'Bandung',           'price' => 3000, 'type' => 'property',        'color' => 'green',
         'level1_rent'=>150, 'level2_name'=>'Rumah','level2_price'=>1500,'level2_rent'=>450,
         'level3_name'=>'Rumah 2','level3_price'=>2250,'level3_rent'=>1000,
         'level4_name'=>'Rumah 3','level4_price'=>3000,'level4_rent'=>1500,
         'level5_name'=>'Hotel','level5_price'=>4500,'level5_rent'=>3500],
        ['name' => 'Kesempatan',        'type' => 'chance',          'color' => 'white'],
        ['name' => 'Yogyakarta',        'price' => 2800, 'type' => 'property',        'color' => 'green',
         'level1_rent'=>140, 'level2_name'=>'Rumah','level2_price'=>1400,'level2_rent'=>420,
         'level3_name'=>'Rumah 2','level3_price'=>2100,'level3_rent'=>900,
         'level4_name'=>'Rumah 3','level4_price'=>2800,'level4_rent'=>1400,
         'level5_name'=>'Hotel','level5_price'=>4200,'level5_rent'=>3200],
        ['name' => 'Semarang',          'price' => 2600, 'type' => 'property',        'color' => 'green',
         'level1_rent'=>130, 'level2_name'=>'Rumah','level2_price'=>1300,'level2_rent'=>390,
         'level3_name'=>'Rumah 2','level3_price'=>1950,'level3_rent'=>850,
         'level4_name'=>'Rumah 3','level4_price'=>2600,'level4_rent'=>1300,
         'level5_name'=>'Hotel','level5_price'=>3900,'level5_rent'=>2800],
        ['name' => 'Penjara',           'type' => 'jail',            'color' => 'white'],
        ['name' => 'Medan',             'price' => 2400, 'type' => 'property',        'color' => 'red',
         'level1_rent'=>120, 'level2_name'=>'Rumah','level2_price'=>1200,'level2_rent'=>360,
         'level3_name'=>'Rumah 2','level3_price'=>1800,'level3_rent'=>800,
         'level4_name'=>'Rumah 3','level4_price'=>2400,'level4_rent'=>1200,
         'level5_name'=>'Hotel','level5_price'=>3600,'level5_rent'=>2500],
        ['name' => 'PLN',               'price' => 1500, 'type' => 'utility',         'color' => 'white'],
        ['name' => 'Palembang',         'price' => 2200, 'type' => 'property',        'color' => 'red',
         'level1_rent'=>110, 'level2_name'=>'Rumah','level2_price'=>1100,'level2_rent'=>330,
         'level3_name'=>'Rumah 2','level3_price'=>1650,'level3_rent'=>720,
         'level4_name'=>'Rumah 3','level4_price'=>2200,'level4_rent'=>1100,
         'level5_name'=>'Hotel','level5_price'=>3300,'level5_rent'=>2200],
        ['name' => 'Padang',            'price' => 2000, 'type' => 'property',        'color' => 'red',
         'level1_rent'=>100, 'level2_name'=>'Rumah','level2_price'=>1000,'level2_rent'=>300,
         'level3_name'=>'Rumah 2','level3_price'=>1500,'level3_rent'=>650,
         'level4_name'=>'Rumah 3','level4_price'=>2000,'level4_rent'=>1000,
         'level5_name'=>'Hotel','level5_price'=>3000,'level5_rent'=>2000],
        ['name' => 'Stasiun Pasar Turi','price' => 2000, 'type' => 'station',         'color' => 'white'],
        ['name' => 'Makassar',          'price' => 1800, 'type' => 'property',        'color' => 'yellow',
         'level1_rent'=>90,  'level2_name'=>'Rumah','level2_price'=>900,'level2_rent'=>270,
         'level3_name'=>'Rumah 2','level3_price'=>1350,'level3_rent'=>580,
         'level4_name'=>'Rumah 3','level4_price'=>1800,'level4_rent'=>900,
         'level5_name'=>'Hotel','level5_price'=>2700,'level5_rent'=>1750],
        ['name' => 'Dana Umum',         'type' => 'community_chest', 'color' => 'white'],
        ['name' => 'Manado',            'price' => 1600, 'type' => 'property',        'color' => 'yellow',
         'level1_rent'=>80,  'level2_name'=>'Rumah','level2_price'=>800,'level2_rent'=>240,
         'level3_name'=>'Rumah 2','level3_price'=>1200,'level3_rent'=>500,
         'level4_name'=>'Rumah 3','level4_price'=>1600,'level4_rent'=>800,
         'level5_name'=>'Hotel','level5_price'=>2400,'level5_rent'=>1500],
        ['name' => 'Kendari',           'price' => 1400, 'type' => 'property',        'color' => 'yellow',
         'level1_rent'=>70,  'level2_name'=>'Rumah','level2_price'=>700,'level2_rent'=>210,
         'level3_name'=>'Rumah 2','level3_price'=>1050,'level3_rent'=>440,
         'level4_name'=>'Rumah 3','level4_price'=>1400,'level4_rent'=>700,
         'level5_name'=>'Hotel','level5_price'=>2100,'level5_rent'=>1250],
        ['name' => 'Pejabat Negara',    'type' => 'pejabat_negara',  'color' => 'white'],
        ['name' => 'Denpasar',          'price' => 1200, 'type' => 'property',        'color' => 'orange',
         'level1_rent'=>60,  'level2_name'=>'Rumah','level2_price'=>600,'level2_rent'=>180,
         'level3_name'=>'Rumah 2','level3_price'=>900,'level3_rent'=>380,
         'level4_name'=>'Rumah 3','level4_price'=>1200,'level4_rent'=>600,
         'level5_name'=>'Hotel','level5_price'=>1800,'level5_rent'=>1100],
        ['name' => 'Kesempatan',        'type' => 'chance',          'color' => 'white'],
        ['name' => 'Mataram',           'price' => 1000, 'type' => 'property',        'color' => 'orange',
         'level1_rent'=>50,  'level2_name'=>'Rumah','level2_price'=>500,'level2_rent'=>150,
         'level3_name'=>'Rumah 2','level3_price'=>750,'level3_rent'=>320,
         'level4_name'=>'Rumah 3','level4_price'=>1000,'level4_rent'=>500,
         'level5_name'=>'Hotel','level5_price'=>1500,'level5_rent'=>900],
        ['name' => 'Kupang',            'price' => 1000, 'type' => 'property',        'color' => 'orange',
         'level1_rent'=>50,  'level2_name'=>'Rumah','level2_price'=>500,'level2_rent'=>150,
         'level3_name'=>'Rumah 2','level3_price'=>750,'level3_rent'=>320,
         'level4_name'=>'Rumah 3','level4_price'=>1000,'level4_rent'=>500,
         'level5_name'=>'Hotel','level5_price'=>1500,'level5_rent'=>900],
        ['name' => 'Stasiun Tugu',      'price' => 2000, 'type' => 'station',         'color' => 'white'],
        ['name' => 'Banjarmasin',       'price' => 800,  'type' => 'property',        'color' => 'pink',
         'level1_rent'=>40,  'level2_name'=>'Rumah','level2_price'=>400,'level2_rent'=>120,
         'level3_name'=>'Rumah 2','level3_price'=>600,'level3_rent'=>250,
         'level4_name'=>'Rumah 3','level4_price'=>800,'level4_rent'=>400,
         'level5_name'=>'Hotel','level5_price'=>1200,'level5_rent'=>750],
        ['name' => 'Balikpapan',        'price' => 600,  'type' => 'property',        'color' => 'pink',
         'level1_rent'=>30,  'level2_name'=>'Rumah','level2_price'=>300,'level2_rent'=>90,
         'level3_name'=>'Rumah 2','level3_price'=>450,'level3_rent'=>200,
         'level4_name'=>'Rumah 3','level4_price'=>600,'level4_rent'=>300,
         'level5_name'=>'Hotel','level5_price'=>900,'level5_rent'=>550],
        ['name' => 'PDAM',              'price' => 1500, 'type' => 'utility',         'color' => 'white'],
        ['name' => 'Pontianak',         'price' => 600,  'type' => 'property',        'color' => 'pink',
         'level1_rent'=>30,  'level2_name'=>'Rumah','level2_price'=>300,'level2_rent'=>90,
         'level3_name'=>'Rumah 2','level3_price'=>450,'level3_rent'=>200,
         'level4_name'=>'Rumah 3','level4_price'=>600,'level4_rent'=>300,
         'level5_name'=>'Hotel','level5_price'=>900,'level5_rent'=>550],
        ['name' => 'Masuk Penjara',     'type' => 'go_to_jail',      'color' => 'white'],
        ['name' => 'Ambon',             'price' => 500,  'type' => 'property',        'color' => 'brown',
         'level1_rent'=>25,  'level2_name'=>'Rumah','level2_price'=>250,'level2_rent'=>75,
         'level3_name'=>'Rumah 2','level3_price'=>375,'level3_rent'=>160,
         'level4_name'=>'Rumah 3','level4_price'=>500,'level4_rent'=>250,
         'level5_name'=>'Hotel','level5_price'=>750,'level5_rent'=>450],
        ['name' => 'Ternate',           'price' => 500,  'type' => 'property',        'color' => 'brown',
         'level1_rent'=>25,  'level2_name'=>'Rumah','level2_price'=>250,'level2_rent'=>75,
         'level3_name'=>'Rumah 2','level3_price'=>375,'level3_rent'=>160,
         'level4_name'=>'Rumah 3','level4_price'=>500,'level4_rent'=>250,
         'level5_name'=>'Hotel','level5_price'=>750,'level5_rent'=>450],
        ['name' => 'Dana Umum',         'type' => 'community_chest', 'color' => 'white'],
        ['name' => 'Jayapura',          'price' => 400,  'type' => 'property',        'color' => 'brown',
         'level1_rent'=>20,  'level2_name'=>'Rumah','level2_price'=>200,'level2_rent'=>60,
         'level3_name'=>'Rumah 2','level3_price'=>300,'level3_rent'=>130,
         'level4_name'=>'Rumah 3','level4_price'=>400,'level4_rent'=>200,
         'level5_name'=>'Hotel','level5_price'=>600,'level5_rent'=>375],
        ['name' => 'Stasiun Bandung',   'price' => 2000, 'type' => 'station',         'color' => 'white'],
        ['name' => 'Kesempatan',        'type' => 'chance',          'color' => 'white'],
        ['name' => 'Sorong',            'price' => 400,  'type' => 'property',        'color' => 'cyan',
         'level1_rent'=>20,  'level2_name'=>'Rumah','level2_price'=>200,'level2_rent'=>60,
         'level3_name'=>'Rumah 2','level3_price'=>300,'level3_rent'=>130,
         'level4_name'=>'Rumah 3','level4_price'=>400,'level4_rent'=>200,
         'level5_name'=>'Hotel','level5_price'=>600,'level5_rent'=>375],
        ['name' => 'Pajak Mewah',       'price' => 100,  'type' => 'tax',             'color' => 'white'],
        ['name' => 'Manokwari',         'price' => 400,  'type' => 'property',        'color' => 'cyan',
         'level1_rent'=>20,  'level2_name'=>'Rumah','level2_price'=>200,'level2_rent'=>60,
         'level3_name'=>'Rumah 2','level3_price'=>300,'level3_rent'=>130,
         'level4_name'=>'Rumah 3','level4_price'=>400,'level4_rent'=>200,
         'level5_name'=>'Hotel','level5_price'=>600,'level5_rent'=>375],
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

