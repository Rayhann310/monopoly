<?php
class Player extends Controller {

    public function index($id = 1) {
        $data['judul'] = 'Layar Pemain';
        $data['player'] = $this->model('PlayerModel')->getPlayerById($id);
        $data['board'] = $this->model('BoardModel')->getBoard();

        if (!$data['player']) { die("Pemain tidak ditemukan!"); }
        
        $data['properties'] = $this->model('PropertyModel')->getPlayerProperties($data['player']['session_id'], $id);
        $data['settings'] = $this->model('SettingsModel')->getAll();
        $data['pejabat'] = $this->model('SessionModel')->getPejabat($data['player']['session_id']);

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
        $freeParkingOn   = (int)$settings->get('free_parking_enabled', 1);
        $freeParkingSeed = (int)$settings->get('free_parking_seed', 0);

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
                    
                    $action['type']        = 'tax';
                    $action['amount']      = -$deduct;
                    
                    $pejabat = $this->model('SessionModel')->getPejabat($sessionId);
                    if ($freeParkingOn && $pejabat) {
                        // Transfer tax to Pejabat
                        $this->model('PlayerModel')->addMoney($pejabat['id'], $deduct);
                        $action['msg'] = "Kena " . ($newPos == 38 ? 'Pajak Mewah' : 'Pajak Biasa') . "! Membayar Rp " . number_format($deduct, 0, ',', '.') . " ke Pejabat Negara (" . $pejabat['name'] . ").";
                    } else {
                        $action['msg'] = "Kena " . ($newPos == 38 ? 'Pajak Mewah' : 'Pajak Biasa') . "! Uang disita negara sebesar Rp " . number_format($deduct, 0, ',', '.');
                    }
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
                        // Override type to ensure card is always 'card' (not jail override from applyCardEffect)
                        $action['type'] = 'card';
                        $action['card_type']  = 'kesempatan';
                        $action['card_text']  = $card['text'];
                        $action['card_image'] = $card['image_url'] ?? null;
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
                        $action['type'] = 'card';
                        $action['card_type']  = 'dana_umum';
                        $action['card_text']  = $card['text'];
                        $action['card_image'] = $card['image_url'] ?? null;
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
                            $lvl = $currentLevel + 1;
                            $options = [];
                            if ($lvl <= $maxPropLevel) {
                                $stepKey = "level{$lvl}_price";
                                $stepPrice = isset($cell[$stepKey]) && $cell[$stepKey] > 0
                                    ? (int)$cell[$stepKey]
                                    : $housePrice;
                                $cumCost = $stepPrice; // Only 1 level allowed per upgrade

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

                case 'pejabat_negara':
                case 'free_parking': // fallback in case old board used
                    if ($freeParkingOn) {
                        $this->model('SessionModel')->setPejabat($sessionId, $id);
                        $action['type']        = 'become_pejabat';
                        $action['msg']          = "SELAMAT! Kamu sekarang adalah 👑 PEJABAT NEGARA. Semua pemain yang mendarat di petak Pajak akan membayar pajak langsung ke rekeningmu!";
                    } else {
                        $action['type'] = 'safe';
                        $action['msg']  = "Petak Pejabat Negara. Fitur dinonaktifkan di pengaturan.";
                    }
                    break;
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
            'status'      => 'success',
            'position'    => $newPos,
            'money'       => $newMoney,
            'laps'        => (int)($player['laps'] ?? 0),
            'in_jail'     => (bool)($player['in_jail'] ?? false),
            'jail_turns'  => (int)($player['jail_turns'] ?? 0),
            'pejabat'     => $this->model('SessionModel')->getPejabat($sessionId),
            'action'      => $action
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
                $db = new Database();
                $db->query("UPDATE players SET free_jail_cards = free_jail_cards + 1 WHERE id = :id");
                $db->bind('id', $player['id']);
                $db->execute();
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

        // Fetch pending trade offers
        $db = new Database();
        $db->query("SELECT t.*, p.name as from_name, b.name as cell_name, b.image_url 
                    FROM trade_offers t 
                    JOIN players p ON t.from_player_id = p.id
                    JOIN board_properties b ON t.cell_index = b.cell_index
                    WHERE t.session_id = :sid AND t.to_player_id = :pid AND t.status = 'pending'");
        $db->bind('sid', $player['session_id']);
        $db->bind('pid', $id);
        $pendingOffers = $db->resultSet();
        echo json_encode([
            'is_turn'          => (bool)$player['is_turn'],
            'has_rolled'       => (bool)$player['has_rolled'],
            'in_jail'          => (bool)($player['in_jail'] ?? false),
            'jail_turns'       => (int)($player['jail_turns'] ?? 0),
            'free_jail_cards'  => (int)($player['free_jail_cards'] ?? 0),
            'laps'             => (int)($player['laps'] ?? 0),
            'position'         => (int)$player['position'],
            'money'            => (int)$player['money'],
            'properties'       => $properties,
            'active_card'      => $activeCard,
            'name_dana_umum'   => $settings->get('name_dana_umum', 'Dana Umum'),
            'name_kesempatan'  => $settings->get('name_kesempatan', 'Kesempatan'),
            'allow_trade'      => (bool)$settings->get('allow_trade', 1),
            'jail_bribe_cost'  => (int)$settings->get('jail_bribe_cost', 5000),
            'pending_offers'   => $pendingOffers,
        ]);
    }

    public function apiBribeJail() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['status' => 'error', 'msg' => 'Method not allowed']); return;
        }

        $id = (int)($_POST['player_id'] ?? 0);
        $player = $this->model('PlayerModel')->getPlayerById($id);

        if (!$player) {
            echo json_encode(['status' => 'error', 'msg' => 'Pemain tidak ditemukan']); return;
        }
        if (!$player['is_turn']) {
            echo json_encode(['status' => 'error', 'msg' => 'Bukan giliran kamu!']); return;
        }
        if (empty($player['in_jail'])) {
            echo json_encode(['status' => 'error', 'msg' => 'Kamu tidak sedang di penjara!']); return;
        }
        if ($player['has_rolled']) {
            echo json_encode(['status' => 'error', 'msg' => 'Sudah melempar dadu, selesaikan aksi saat ini!']); return;
        }

        $bribeCost = (int)$this->model('SettingsModel')->get('jail_bribe_cost', 5000);
        if ((int)$player['money'] < $bribeCost) {
            echo json_encode(['status' => 'error', 'msg' => 'Uang tidak cukup untuk membayar suap!']); return;
        }

        $db = new Database();
        
        // Cek siapa Pejabat Negara
        $pejabatId = null;
        $db->query("SELECT p.id FROM players p WHERE p.session_id = :sid AND p.position = (SELECT cell_index FROM board_properties WHERE type='pejabat' LIMIT 1) LIMIT 1");
        $db->bind('sid', $player['session_id']);
        $row = $db->single();
        if ($row) $pejabatId = $row['id'];
        
        if ($pejabatId && $pejabatId != $id) {
            // Suap mengalir ke Pejabat Negara
            $db->query("UPDATE players SET money = money - :cost, in_jail = 0, jail_turns = 0 WHERE id = :id AND in_jail = 1");
            $db->bind('cost', $bribeCost);
            $db->bind('id', $id);
            $db->execute();
            
            $db->query("UPDATE players SET money = money + :cost WHERE id = :pid");
            $db->bind('cost', $bribeCost);
            $db->bind('pid', $pejabatId);
            $db->execute();
            
            $logMsg = "Menyuap Pejabat Negara Rp " . number_format($bribeCost, 0, ',', '.') . " untuk bebas penjara!";
        } else {
            // Suap mengalir ke pot parkir bebas jika aktif, kalau tidak hangus (ke Bank)
            $db->query("UPDATE players SET money = money - :cost, in_jail = 0, jail_turns = 0 WHERE id = :id AND in_jail = 1");
            $db->bind('cost', $bribeCost);
            $db->bind('id', $id);
            $db->execute();
            
            $freeParkingOn = (int)$this->model('SettingsModel')->get('free_parking_enabled', 1);
            if ($freeParkingOn) {
                $db->query("UPDATE sessions SET free_parking_pot = free_parking_pot + :cost WHERE id = :sid");
                $db->bind('cost', $bribeCost);
                $db->bind('sid', $player['session_id']);
                $db->execute();
            }
            $logMsg = "Membayar denda Bank Rp " . number_format($bribeCost, 0, ',', '.') . " untuk bebas penjara!";
        }

        // Log action
        $db->query("INSERT INTO game_log (session_id, player_id, action) VALUES (:sid, :pid, :act)");
        $db->bind('sid', $player['session_id']);
        $db->bind('pid', $id);
        $db->bind('act', $logMsg);
        $db->execute();

        echo json_encode([
            'status' => 'success',
            'money' => (int)$player['money'] - $bribeCost,
            'msg' => 'Berhasil menyuap! Silakan melempar dadu sekarang.'
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

    public function apiUseJailCard() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['status' => 'error', 'msg' => 'Method not allowed']); return;
        }

        $id = (int)($_POST['player_id'] ?? 0);
        $player = $this->model('PlayerModel')->getPlayerById($id);

        if (!$player) {
            echo json_encode(['status' => 'error', 'msg' => 'Pemain tidak ditemukan']); return;
        }
        if (!$player['is_turn']) {
            echo json_encode(['status' => 'error', 'msg' => 'Bukan giliran kamu!']); return;
        }
        if (empty($player['in_jail'])) {
            echo json_encode(['status' => 'error', 'msg' => 'Kamu tidak sedang di penjara!']); return;
        }
        if ((int)($player['free_jail_cards'] ?? 0) <= 0) {
            echo json_encode(['status' => 'error', 'msg' => 'Kamu tidak punya Kartu Bebas Penjara!']); return;
        }
        if ($player['has_rolled']) {
            echo json_encode(['status' => 'error', 'msg' => 'Sudah melempar dadu, selesaikan aksi saat ini!']); return;
        }

        $db = new Database();
        $db->query("UPDATE players SET free_jail_cards = free_jail_cards - 1, in_jail = 0, jail_turns = 0 WHERE id = :id AND free_jail_cards > 0");
        $db->bind('id', $id);
        $db->execute();
        
        if ($db->rowCount() > 0) {
            // Log action
            $db->query("INSERT INTO game_log (session_id, player_id, action) VALUES (:sid, :pid, :act)");
            $db->bind('sid', $player['session_id']);
            $db->bind('pid', $id);
            $db->bind('act', "Menggunakan Kartu Bebas Penjara!");
            $db->execute();

            echo json_encode([
                'status' => 'success', 
                'msg' => 'Berhasil menggunakan Kartu Bebas Penjara! Silakan melempar dadu sekarang.'
            ]);
        } else {
            echo json_encode(['status' => 'error', 'msg' => 'Gagal menggunakan kartu!']);
        }
    }

    public function apiGetOtherProperties($id = null) {
        header('Content-Type: application/json');
        if (!$id) return;
        $player = $this->model('PlayerModel')->getPlayerById($id);
        $db = new Database();
        $db->query("SELECT p.*, pl.name as owner_name, pl.color as owner_color, b.name as cell_name, b.color_group, b.image_url, b.price
                    FROM properties p
                    JOIN players pl ON p.owner_id = pl.id
                    JOIN board_properties b ON p.cell_index = b.cell_index
                    WHERE p.session_id = :sid AND p.owner_id != :pid");
        $db->bind('sid', $player['session_id']);
        $db->bind('pid', $id);
        echo json_encode(['status' => 'success', 'data' => $db->resultSet()]);
    }

    public function apiOfferTrade() {
        header('Content-Type: application/json');
        $fromId = (int)($_POST['from_player_id'] ?? 0);
        $toId = (int)($_POST['to_player_id'] ?? 0);
        $cellIndex = (int)($_POST['cell_index'] ?? 0);
        $amount = (int)($_POST['amount'] ?? 0);
        
        $player = $this->model('PlayerModel')->getPlayerById($fromId);
        if ($player['money'] < $amount) {
            echo json_encode(['status' => 'error', 'msg' => 'Uang tidak cukup untuk penawaran ini!']); return;
        }

        $db = new Database();
        $db->query("INSERT INTO trade_offers (session_id, from_player_id, to_player_id, cell_index, offer_amount) VALUES (:sid, :from, :to, :cell, :amt)");
        $db->bind('sid', $player['session_id']);
        $db->bind('from', $fromId);
        $db->bind('to', $toId);
        $db->bind('cell', $cellIndex);
        $db->bind('amt', $amount);
        $db->execute();
        
        // Log action
        $db->query("SELECT name FROM board_properties WHERE cell_index = :ci");
        $db->bind('ci', $cellIndex);
        $propName = $db->single()['name'] ?? 'Properti';
        
        $db->query("INSERT INTO game_log (session_id, player_id, action) VALUES (:sid, :pid, :act)");
        $db->bind('sid', $player['session_id']);
        $db->bind('pid', $fromId);
        $db->bind('act', "Mengajukan penawaran Rp " . number_format($amount,0,',','.') . " untuk $propName.");
        $db->execute();

        echo json_encode(['status' => 'success', 'msg' => 'Penawaran berhasil dikirim!']);
    }

    public function apiAcceptTrade() {
        header('Content-Type: application/json');
        $offerId = (int)($_POST['offer_id'] ?? 0);
        
        $db = new Database();
        $db->query("SELECT * FROM trade_offers WHERE id = :id AND status = 'pending'");
        $db->bind('id', $offerId);
        $offer = $db->single();
        if (!$offer) {
            echo json_encode(['status' => 'error', 'msg' => 'Penawaran tidak valid atau sudah diproses.']); return;
        }

        $fromId = $offer['from_player_id'];
        $toId = $offer['to_player_id']; 
        $amt = $offer['offer_amount'];
        $cell = $offer['cell_index'];
        
        $buyer = $this->model('PlayerModel')->getPlayerById($fromId);
        if ($buyer['money'] < $amt) {
            $db->query("UPDATE trade_offers SET status = 'rejected' WHERE id = :id");
            $db->bind('id', $offerId);
            $db->execute();
            echo json_encode(['status' => 'error', 'msg' => 'Pembeli tidak lagi memiliki uang yang cukup. Penawaran dibatalkan.']); return;
        }

        $db->query("UPDATE trade_offers SET status = 'accepted' WHERE id = :id");
        $db->bind('id', $offerId);
        $db->execute();
        
        $db->query("UPDATE properties SET owner_id = :new_owner WHERE session_id = :sid AND cell_index = :cell");
        $db->bind('new_owner', $fromId);
        $db->bind('sid', $offer['session_id']);
        $db->bind('cell', $cell);
        $db->execute();
        
        $db->query("UPDATE players SET money = money - :amt WHERE id = :id");
        $db->bind('amt', $amt);
        $db->bind('id', $fromId);
        $db->execute();
        
        $db->query("UPDATE players SET money = money + :amt WHERE id = :id");
        $db->bind('amt', $amt);
        $db->bind('id', $toId);
        $db->execute();

        $db->query("SELECT name FROM board_properties WHERE cell_index = :ci");
        $db->bind('ci', $cell);
        $propName = $db->single()['name'] ?? 'Properti';
        
        $db->query("INSERT INTO game_log (session_id, player_id, action) VALUES (:sid, :pid, :act)");
        $db->bind('sid', $offer['session_id']);
        $db->bind('pid', $toId);
        $db->bind('act', "Menerima tawaran Rp " . number_format($amt,0,',','.') . " untuk $propName dari " . $buyer['name'] . ".");
        $db->execute();

        echo json_encode(['status' => 'success', 'msg' => 'Penawaran diterima! Properti telah berpindah tangan.']);
    }

    public function apiRejectTrade() {
        header('Content-Type: application/json');
        $offerId = (int)($_POST['offer_id'] ?? 0);
        $db = new Database();
        $db->query("UPDATE trade_offers SET status = 'rejected' WHERE id = :id");
        $db->bind('id', $offerId);
        $db->execute();
        echo json_encode(['status' => 'success']);
    }

    public function apiSellToBank() {
        header('Content-Type: application/json');
        $playerId = (int)($_POST['player_id'] ?? 0);
        $cellIndex = (int)($_POST['cell_index'] ?? 0);
        $price = (int)($_POST['price'] ?? 0);

        $player = $this->model('PlayerModel')->getPlayerById($playerId);
        if (!$player) {
            echo json_encode(['status' => 'error', 'msg' => 'Pemain tidak valid.']); return;
        }

        $db = new Database();
        
        // Verify ownership
        $db->query("SELECT * FROM properties WHERE session_id = :sid AND owner_id = :pid AND cell_index = :cell");
        $db->bind('sid', $player['session_id']);
        $db->bind('pid', $playerId);
        $db->bind('cell', $cellIndex);
        $prop = $db->single();

        if (!$prop) {
            echo json_encode(['status' => 'error', 'msg' => 'Properti ini bukan milikmu.']); return;
        }

        // Sell
        $db->query("DELETE FROM properties WHERE id = :id");
        $db->bind('id', $prop['id']);
        $db->execute();

        $db->query("UPDATE players SET money = money + :price WHERE id = :pid");
        $db->bind('price', $price);
        $db->bind('pid', $playerId);
        $db->execute();

        // Log action
        $db->query("SELECT name FROM board_properties WHERE cell_index = :ci");
        $db->bind('ci', $cellIndex);
        $propName = $db->single()['name'] ?? 'Properti';
        
        $db->query("INSERT INTO game_log (session_id, player_id, action) VALUES (:sid, :pid, :act)");
        $db->bind('sid', $player['session_id']);
        $db->bind('pid', $playerId);
        $db->bind('act', "Menjual $propName ke Bank seharga Rp " . number_format($price, 0, ',', '.') . ".");
        $db->execute();

        echo json_encode(['status' => 'success', 'msg' => 'Properti berhasil dijual ke Bank.']);
    }
}
