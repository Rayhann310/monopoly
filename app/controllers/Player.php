<?php
class Player extends Controller {

    public function index($id = 1) {
        $data['judul'] = 'Layar Pemain';
        $data['player'] = $this->model('PlayerModel')->getPlayerById($id);
        $data['board'] = $this->model('BoardModel')->getBoard();

        if (!$data['player']) { die("Pemain tidak ditemukan!"); }
        
        $data['properties'] = $this->model('PropertyModel')->getPlayerProperties($data['player']['session_id'], $id);
        $data['settings'] = $this->model('SettingsModel')->getAll();

        $this->view('templates/header_player', $data);
        $this->view('player/index', $data);
        $this->view('templates/footer_player', $data);
    }

    public function apiRoll() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['status' => 'error', 'msg' => 'Method not allowed']); return;
        }

        $id     = (int)($_POST['id'] ?? 0);
        $die1   = (int)($_POST['die1'] ?? 0);
        $die2   = (int)($_POST['die2'] ?? 0);
        $dice   = (int)($_POST['dice'] ?? ($die1 + $die2));

        if ($die1 <= 0 || $die2 <= 0) {
            $die1 = max(1, min(6, (int)ceil($dice / 2)));
            $die2 = max(1, min(6, $dice - $die1));
        }
        $dice = $die1 + $die2;
        $isDouble = ($die1 === $die2);

        if ($id <= 0 || $dice < 2 || $dice > 12) {
            echo json_encode(['status' => 'error', 'msg' => 'Data tidak valid']); return;
        }

        $player = $this->model('PlayerModel')->getPlayerById($id);
        if (!$player) {
            echo json_encode(['status' => 'error', 'msg' => 'Pemain tidak ditemukan']); return;
        }
        if (!$player['is_turn']) {
            echo json_encode(['status' => 'error', 'msg' => 'Bukan giliran kamu!']); return;
        }
        if ($player['has_rolled']) {
            echo json_encode(['status' => 'error', 'msg' => 'Sudah melempar dadu!', 'already_rolled' => true]); return;
        }

        // === ATOMIC LOCK: SET has_rolled=1 WHERE has_rolled=0 (prevent race/double-roll) ===
        $db = new Database();
        $db->query("UPDATE players SET has_rolled = 1 WHERE id = :id AND has_rolled = 0 AND is_turn = 1");
        $db->bind('id', $id);
        $db->execute();
        $rowCount = $db->rowCount();
        if ($rowCount === 0) {
            echo json_encode(['status' => 'error', 'msg' => 'Sudah melempar dadu!', 'already_rolled' => true]); return;
        }
        // Re-fetch after lock
        $player = $this->model('PlayerModel')->getPlayerById($id);

        $oldPos    = (int)$player['position'];
        $sessionId = (int)$player['session_id'];
        $settings  = $this->model('SettingsModel');
        $board     = $this->model('BoardModel')->getBoard();

        $passGoBonus  = (int)$settings->get('pass_go_bonus', 2000);
        $taxAmount    = (int)$settings->get('tax_amount',   2000);
        $luxuryTax    = (int)$settings->get('luxury_tax',   7500);
        $maxPropLevel = (int)$settings->get('max_property_level', 4);
        $housePrice   = (int)$settings->get('house_price', 150);

        $newMoney = (int)$player['money'];
        $action   = ['type' => 'move'];

        // === ATURAN PENJARA: Jika pemain saat ini sedang di penjara ===
        if (!empty($player['in_jail'])) {
            $jailTurns = (int)($player['jail_turns'] ?? 0) + 1;

            if ($isDouble) {
                // Berhasil dapat dadu kembar! Bebas dari penjara dan melangkah
                $this->model('PlayerModel')->setJail($id, 0, 0);
                $player['in_jail'] = 0;
                $player['jail_turns'] = 0;
                $oldPos = 10;
                $newPos = (10 + $dice) % 40;
                $action['jail_freed'] = true;
                $action['jail_turn_count'] = $jailTurns;
                $action['msg_jail'] = "Dadu Kembar ({$die1} & {$die2})! Bebas dari penjara dan melangkah {$dice} petak!";
            } elseif ($jailTurns >= 4) {
                // Batas 3 putaran terlampaui! Pada putaran/lemparan ke-4 jika tidak kembar, otomatis bebas!
                $this->model('PlayerModel')->setJail($id, 0, 0);
                $player['in_jail'] = 0;
                $player['jail_turns'] = 0;
                $oldPos = 10;
                $newPos = (10 + $dice) % 40;
                $action['jail_auto_freed'] = true;
                $action['jail_turn_count'] = 4;
                $action['msg_jail'] = "Putaran ke-4 di penjara: Batas 3 putaran selesai! Anda otomatis BEBAS dari penjara dan melangkah {$dice} petak!";
            } else {
                // Masih dalam batas 3 putaran (percobaan 1, 2, atau 3) dan dadu tidak kembar — tetap tertahan di penjara!
                $this->model('PlayerModel')->setJail($id, 1, $jailTurns);
                $this->model('PlayerModel')->updatePosition($id, 10);
                $this->model('PlayerModel')->markRolled($id);
                echo json_encode([
                    'status'     => 'success',
                    'position'   => 10,
                    'money'      => $newMoney,
                    'in_jail'    => true,
                    'jail_turns' => $jailTurns,
                    'action'     => [
                        'type'       => 'jail_stay',
                        'die1'       => $die1,
                        'die2'       => $die2,
                        'jail_turns' => $jailTurns,
                        'msg'        => "Percobaan ke-{$jailTurns}/3 di penjara: Dadu tidak kembar ({$die1} & {$die2})! Anda tetap tertahan di penjara. Dadu kembar dibutuhkan untuk keluar (bebas otomatis di giliran ke-4)."
                    ]
                ]);
                return;
            }
        } else {
            // Pemain normal (tidak di penjara)
            $newPos = ($oldPos + $dice) % 40;
        }

        $action['position'] = $newPos;

        // === ATURAN PUTARAN (LAPS) & PASS START ===
        // Melewati atau mendarat di Start (posisi 0) menyelesaikan 1 putaran
        $passedGo = ($oldPos + $dice) >= 40;
        if ($passedGo) {
            $newLaps = (int)($player['laps'] ?? 0) + 1;
            $this->model('PlayerModel')->updateLaps($id, $newLaps);
            $player['laps'] = $newLaps;

            $newMoney += $passGoBonus;
            $action['pass_go'] = true;
            $action['pass_go_bonus'] = $passGoBonus;
        }

        $cell = $board[$newPos] ?? null;
        if ($cell && isset($cell['house_price'])) {
            $housePrice = (int)$cell['house_price'];
        }

        if ($cell) {
            switch ($cell['type']) {
                case 'tax':
                    $deduct = ($newPos == 38) ? $luxuryTax : $taxAmount;
                    $newMoney -= $deduct;
                    $action['type']   = 'tax';
                    $action['amount'] = -$deduct;
                    $action['msg']    = "Kena " . ($newPos == 38 ? 'Pajak Mewah' : 'Pajak Biasa') . "! Bayar Rp " . number_format($deduct, 0, ',', '.');
                    break;

                case 'go_to_jail':
                    $newPos = 10; // Masuk ke Penjara di petak 10
                    $this->model('PlayerModel')->setJail($id, 1, 0);
                    $action['type']     = 'jail';
                    $action['position'] = 10;
                    $action['msg']      = "Masuk Penjara! Anda harus melempar dadu kembar untuk keluar (batas 3 kali percobaan, putaran ke-4 otomatis bebas).";
                    break;

                case 'jail':
                    // Petak 10 saat langkah biasa = Hanya lewat / kunjungan penjara
                    $action['type'] = 'safe';
                    $action['msg']  = "Petak Penjara (Hanya Kunjungan/Lewat). Kamu tidak ditahan.";
                    break;

                case 'chance':
                    $card = $this->model('CardModel')->drawRandom('kesempatan');
                    if ($card) {
                        $action = $this->applyCardEffect($card, $player, $newMoney, $newPos);
                        $newMoney = $action['new_money'];
                        $newPos   = $action['new_position'];
                        $this->model('SessionModel')->setActiveCard($sessionId, json_encode([
                            'type'        => 'kesempatan',
                            'player_name' => $player['name'],
                            'text'        => $card['text']
                        ]));
                    }
                    break;

                case 'community_chest':
                    $card = $this->model('CardModel')->drawRandom('dana_umum');
                    if ($card) {
                        $action = $this->applyCardEffect($card, $player, $newMoney, $newPos);
                        $newMoney = $action['new_money'];
                        $newPos   = $action['new_position'];
                        $this->model('SessionModel')->setActiveCard($sessionId, json_encode([
                            'type'        => 'dana_umum',
                            'player_name' => $player['name'],
                            'text'        => $card['text']
                        ]));
                    }
                    break;

                case 'property':
                case 'station':
                case 'utility':
                    $owner = $this->model('PropertyModel')->getOwner($sessionId, $newPos);
                    $cellImage = $cell['image_url'] ?? null;
                    if ($cellImage && strpos($cellImage, '/') === false) {
                        $cellImage = 'assets_static/cities/' . $cellImage;
                    }
                    
                    if (!$owner) {
                        // === ATURAN PUTARAN PERTAMA ===
                        // Di putaran pertama (laps < 1), pemain belum boleh membeli properti apapun
                        if ((int)($player['laps'] ?? 0) < 1) {
                            $action['type']  = 'first_lap_info';
                            $action['name']  = $cell['name'];
                            $action['image'] = $cellImage;
                            $action['price'] = (int)($cell['price'] ?? 0);
                            $action['msg']   = "Putaran Pertama: Belum bisa membeli aset properti! Selesaikan minimal 1 putaran penuh melewati Start.";
                        } else {
                            $action['type']  = 'buy';
                            $action['price'] = (int)($cell['price'] ?? 0);
                            $action['name']  = $cell['name'];
                            $action['image'] = $cellImage;
                            $action['msg']   = "Beli {$cell['name']} seharga Rp " . number_format($cell['price'], 0, ',', '.');
                        }
                    } elseif ($owner['owner_id'] == $id) {
                        // Milik sendiri — Buka opsi upgrade sesuai tingkat properti
                        $currentLevel = (int)$owner['houses'];
                        if ($cell['type'] === 'property' && $currentLevel < $maxPropLevel) {
                            $options = [];
                            for ($lvl = $currentLevel + 1; $lvl <= $maxPropLevel; $lvl++) {
                                $cumCost = 0;
                                for ($step = $currentLevel + 1; $step <= $lvl; $step++) {
                                    $stepKey = "level{$step}_price";
                                    $stepPrice = isset($cell[$stepKey]) && $cell[$stepKey] > 0
                                        ? (int)$cell[$stepKey]
                                        : $housePrice;
                                    $cumCost += $stepPrice;
                                }

                                $lvlName = $cell["level{$lvl}_name"] ?? ($lvl === 5 ? 'Hotel / Apartemen' : "Rumah {$lvl}");
                                $lvlRent = isset($cell["level{$lvl}_rent"]) && $cell["level{$lvl}_rent"] > 0
                                    ? (int)$cell["level{$lvl}_rent"]
                                    : $this->calcRent($cell, ['houses' => $lvl, 'owner_id' => $id], $sessionId, $dice);

                                $options[] = [
                                    'level'      => $lvl,
                                    'name'       => $lvlName,
                                    'cost'       => $cumCost,
                                    'rent'       => $lvlRent,
                                    'can_afford' => ($newMoney >= $cumCost),
                                    'is_max'     => ($lvl === $maxPropLevel),
                                ];
                            }

                            $action['type']               = 'upgrade';
                            $action['name']               = $cell['name'];
                            $action['image']              = $cellImage;
                            $action['cell_index']         = $newPos;
                            $action['color_group']        = $cell['color'] ?? 'slate';
                            $action['current_level']      = $currentLevel;
                            $action['current_level_name'] = $currentLevel > 0 ? ($cell["level{$currentLevel}_name"] ?? "Rumah {$currentLevel}") : "Tanah Kosong";
                            $action['upgrade_options']    = $options;
                            $action['msg']                = "Upgrade aset {$cell['name']}";
                        } else {
                            $action['type'] = 'own';
                            $action['msg']  = "Ini propertimu sendiri" . ($cell['type'] === 'property' && $currentLevel >= $maxPropLevel ? " (Tingkat Maksimal)" : "") . ".";
                        }
                    } else {
                        // Milik pemain lain — Bayar sewa
                        $rent = $this->calcRent($cell, $owner, $sessionId, $dice);
                        $newMoney -= $rent;
                        // Tambah uang sewa ke pemilik properti secara realtime & aman
                        $this->model('PlayerModel')->addMoney($owner['owner_id'], $rent);
                        $currentLevel = (int)$owner['houses'];
                        $levelName = $cell["level{$currentLevel}_name"] ?? ($currentLevel === 5 ? 'Hotel / Apartemen' : "Level {$currentLevel}");
                        $action['type']          = 'rent';
                        $action['amount']        = $rent;
                        $action['owner']         = $owner['owner_name'];
                        $action['name']          = $cell['name'];
                        $action['image']         = $cellImage;
                        $action['current_level'] = $currentLevel;
                        $action['level_name']    = $currentLevel > 0 ? $levelName : 'Tanah Kosong';
                        $action['msg']           = "Bayar sewa Rp " . number_format($rent, 0, ',', '.') . " ke " . $owner['owner_name'];
                    }
                    break;

                case 'free_parking':
                case 'start':
                    $action['type'] = 'safe';
                    $action['msg']  = "Petak aman.";
                    break;
            }
        }

        $this->model('PlayerModel')->updatePosition($id, $newPos);
        $this->model('PlayerModel')->updateMoney($id, $newMoney);
        $this->model('PlayerModel')->markRolled($id);

        echo json_encode([
            'status'     => 'success',
            'position'   => $newPos,
            'money'      => $newMoney,
            'laps'       => (int)($player['laps'] ?? 0),
            'in_jail'    => (bool)($player['in_jail'] ?? false),
            'jail_turns' => (int)($player['jail_turns'] ?? 0),
            'action'     => $action
        ]);
    }

    private function applyCardEffect($card, $player, $currentMoney, $currentPos) {
        $type  = $card['effect_type'];
        $value = (int)$card['effect_value'];
        $passStartMoney = (int)($card['pass_start_money'] ?? 0);
        $newMoney = $currentMoney;
        $newPos   = $currentPos;

        switch ($type) {
            case 'money_bank':
            case 'money':
                if (stripos($card['text'], 'per rumah') !== false) {
                    $props = $this->model('PropertyModel')->getPlayerProperties($player['session_id'], $player['id']);
                    $totalHouses = 0;
                    foreach ($props as $p) {
                        $totalHouses += (int)$p['houses'];
                    }
                    $newMoney += ($value * $totalHouses);
                } else {
                    $newMoney += $value;
                }
                break;
            case 'money_players':
                $others = $this->model('PlayerModel')->getPlayersBySession($player['session_id']);
                $otherPlayersCount = 0;
                foreach ($others as $op) {
                    if ($op['id'] != $player['id']) {
                        $otherPlayersCount++;
                        $this->model('PlayerModel')->addMoney($op['id'], -$value);
                    }
                }
                $newMoney += ($value * $otherPlayersCount);
                break;
            case 'move_pos':
                $newPos = $value >= 0 ? $value : 0;
                // Check if passed start (assuming moving forward, so newPos < currentPos implies passed start)
                if ($newPos < $currentPos && $passStartMoney != 0) {
                    $newMoney += $passStartMoney;
                }
                break;
            case 'move_steps':
                $newPos = ($currentPos + $value + 40) % 40;
                if ($value > 0 && $newPos < $currentPos && $passStartMoney != 0) {
                    $newMoney += $passStartMoney;
                }
                break;
            case 'move':
                // Legacy support
                $newPos = $value >= 0 ? $value : max(0, ($currentPos + $value + 40) % 40);
                if ($value >= 0 && $newPos < $currentPos && $passStartMoney != 0) {
                    $newMoney += $passStartMoney;
                }
                break;
            case 'jail':
                $newPos = 10;
                $this->model('PlayerModel')->setJail($player['id'], 1, 0);
                break;
            case 'free':
                break;
        }

        return [
            'type'         => ($type === 'jail') ? 'jail' : 'card',
            'card_text'    => $card['text'],
            'card_type'    => $card['type'],
            'card_image'   => $card['image_url'] ?? null,
            'effect'       => $type,
            'amount'       => $value,
            'msg'          => $card['text'],
            'new_money'    => $newMoney,
            'new_position' => $newPos,
            'position'     => $newPos,
        ];
    }

    private function calcRent($cell, $owner, $sessionId, $dice = 6) {
        $basePrice = (int)($cell['price'] ?? 0);
        if ($cell['type'] === 'utility') {
            return $dice * 40;
        }
        if ($cell['type'] === 'station') {
            $stationCells = [5, 15, 25, 35];
            $count = $this->model('PropertyModel')->countGroupOwned($sessionId, $owner['owner_id'], $stationCells);
            $rents = [200, 400, 800, 1600];
            $idx = max(0, min($count - 1, count($rents) - 1));
            return $rents[$idx];
        }
        // Standard property — check tier-based rent
        $houses = max(0, min((int)($owner['houses'] ?? 0), 5));
        $levelKey = "level{$houses}_rent";
        if (isset($cell[$levelKey]) && $cell[$levelKey] > 0) {
            return (int)$cell[$levelKey];
        }
        // Fallback multiplier formula
        $multiplier = [1, 5, 15, 45, 80, 125];
        return (int)round($basePrice * 0.1 * ($multiplier[$houses] ?? 1));
    }

    public function apiBuy() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['status' => 'error', 'msg' => 'Method not allowed']); return;
        }

        $playerId    = (int)($_POST['player_id'] ?? 0);
        $cellIndex   = (int)($_POST['cell_index'] ?? 0);
        $targetLevel = isset($_POST['target_level']) ? (int)$_POST['target_level'] : null;

        $player = $this->model('PlayerModel')->getPlayerById($playerId);
        if (!$player || !$player['is_turn']) {
            echo json_encode(['status' => 'error', 'msg' => 'Bukan giliran kamu']); return;
        }

        $board     = $this->model('BoardModel')->getBoard();
        $cell      = $board[$cellIndex] ?? null;
        $sessionId = (int)$player['session_id'];

        if (!$cell || !isset($cell['price'])) {
            echo json_encode(['status' => 'error', 'msg' => 'Petak tidak bisa dibeli']); return;
        }

        $existing = $this->model('PropertyModel')->getOwner($sessionId, $cellIndex);
        $settings = $this->model('SettingsModel');
        $maxPropLevel = (int)$settings->get('max_property_level', 4);
        $defaultHousePrice = (int)$settings->get('house_price', 150);

        if ($existing) {
            if ($existing['owner_id'] == $playerId && $cell['type'] === 'property') {
                $currentLevel = (int)$existing['houses'];
                if ($currentLevel >= $maxPropLevel) {
                    echo json_encode(['status' => 'error', 'msg' => 'Properti sudah tingkat maksimal']); return;
                }

                $target = $targetLevel !== null ? $targetLevel : ($currentLevel + 1);
                if ($target <= $currentLevel || $target > $maxPropLevel) {
                    echo json_encode(['status' => 'error', 'msg' => 'Pilihan tingkat upgrade tidak valid']); return;
                }

                $totalCost = 0;
                for ($step = $currentLevel + 1; $step <= $target; $step++) {
                    $stepKey = "level{$step}_price";
                    $stepPrice = isset($cell[$stepKey]) && $cell[$stepKey] > 0
                        ? (int)$cell[$stepKey]
                        : $defaultHousePrice;
                    $totalCost += $stepPrice;
                }

                $targetName = $cell["level{$target}_name"] ?? ($target === 5 ? 'Hotel / Apartemen' : "Rumah {$target}");

                if ((int)$player['money'] < $totalCost) {
                    echo json_encode(['status' => 'error', 'msg' => "Uang tidak cukup untuk upgrade ke {$targetName} (Butuh Rp " . number_format($totalCost, 0, ',', '.') . ")"]); return;
                }

                $newMoney = (int)$player['money'] - $totalCost;
                $this->model('PlayerModel')->updateMoney($playerId, $newMoney);
                $this->model('PropertyModel')->upgradeProperty($sessionId, $playerId, $cellIndex, $target);
                echo json_encode([
                    'status' => 'success',
                    'money'  => $newMoney,
                    'msg'    => "Berhasil meningkatkan {$cell['name']} ke {$targetName}!"
                ]);
                return;
            } else {
                echo json_encode(['status' => 'error', 'msg' => 'Properti sudah dimiliki']); return;
            }
        }

        // Pembelian Baru — Cek aturan putaran pertama
        if ((int)($player['laps'] ?? 0) < 1) {
            echo json_encode(['status' => 'error', 'msg' => 'Putaran pertama belum selesai! Lewati Start terlebih dahulu untuk membeli properti.']);
            return;
        }

        $price = (int)$cell['price'];
        if ((int)$player['money'] < $price) {
            echo json_encode(['status' => 'error', 'msg' => 'Uang tidak cukup']); return;
        }

        $newMoney = (int)$player['money'] - $price;
        $this->model('PlayerModel')->updateMoney($playerId, $newMoney);
        $this->model('PropertyModel')->buyProperty($sessionId, $playerId, $cellIndex);

        echo json_encode([
            'status' => 'success',
            'money'  => $newMoney,
            'msg'    => "Berhasil membeli {$cell['name']}!"
        ]);
        return;
    }

    public function apiEndTurn() {
        header('Content-Type: application/json');
        $id     = (int)($_POST['id'] ?? 0);
        $player = $this->model('PlayerModel')->getPlayerById($id);
        if (!$player || !$player['is_turn']) {
            echo json_encode(['status' => 'error', 'msg' => 'Bukan giliran kamu']); return;
        }
        $this->model('PlayerModel')->nextTurn($player['session_id']);
        echo json_encode(['status' => 'success']);
    }

    public function apiStatus($id = null) {
        header('Content-Type: application/json');
        if (!$id) { echo json_encode(['status' => 'error']); return; }
        $player = $this->model('PlayerModel')->getPlayerById($id);
        
        // Fetch properties
        $properties = $this->model('PropertyModel')->getPlayerProperties($player['session_id'], $id);

        // Fetch active card
        $session = $this->model('SessionModel')->getSessionById($player['session_id']);
        $activeCard = ($session && !empty($session['active_card'])) ? json_decode($session['active_card'], true) : null;

        $settings = $this->model('SettingsModel');
        echo json_encode([
            'is_turn'          => (bool)$player['is_turn'],
            'has_rolled'       => (bool)$player['has_rolled'],
            'in_jail'          => (bool)($player['in_jail'] ?? false),
            'jail_turns'       => (int)($player['jail_turns'] ?? 0),
            'laps'             => (int)($player['laps'] ?? 0),
            'position'         => (int)$player['position'],
            'money'            => (int)$player['money'],
            'properties'       => $properties,
            'active_card'      => $activeCard,
            'name_dana_umum'   => $settings->get('name_dana_umum', 'Dana Umum'),
            'name_kesempatan'  => $settings->get('name_kesempatan', 'Kesempatan'),
            'allow_trade'      => (bool)$settings->get('allow_trade', 1),
        ]);
    }

    public function apiClearCard() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
        $playerId = (int)($_POST['player_id'] ?? 0);
        $player = $this->model('PlayerModel')->getPlayerById($playerId);
        if ($player) {
            $this->model('SessionModel')->clearActiveCard($player['session_id']);
            echo json_encode(['status' => 'success']);
        }
    }
}
