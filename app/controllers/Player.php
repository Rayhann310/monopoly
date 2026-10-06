<?php
class Player extends Controller {

    public function index($id = 1) {
        $data['judul'] = 'Layar Pemain';
        $data['player'] = $this->model('PlayerModel')->getPlayerById($id);
        $data['board'] = $this->model('BoardModel')->getBoard();

        if (!$data['player']) { die("Pemain tidak ditemukan!"); }
        
        $data['properties'] = $this->model('PropertyModel')->getPlayerProperties($data['player']['session_id'], $id);

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
        $newPos = (int)($_POST['new_position'] ?? 0);
        $dice   = (int)($_POST['dice'] ?? 0);

        if ($id <= 0 || $newPos < 0 || $newPos > 39) {
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
        // Check rowCount — if 0, another request already locked it
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

        $passGoBonus = (int)$settings->get('pass_go_bonus', 2000);
        $taxAmount   = (int)$settings->get('tax_amount',   2000);
        $luxuryTax   = (int)$settings->get('luxury_tax',   7500);
        $maxPropLevel = (int)$settings->get('max_property_level', 4);
        $housePrice   = (int)$settings->get('house_price', 150);

        $newMoney = (int)$player['money'];
        $action   = ['type' => 'move', 'position' => $newPos];

        // Pass Go: detect crossing position 0 correctly
        // A player passes Go if oldPos + dice > 39 (they wrapped around)
        $passedGo = ($oldPos + $dice) > 39 && $newPos !== 0;
        if ($passedGo) {
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
                    $action['type'] = 'tax';
                    $action['amount'] = -$deduct;
                    $action['msg'] = "Kena pajak! Bayar Rp " . number_format($deduct, 0, ',', '.');
                    break;

                case 'go_to_jail':
                    $newPos = 10; // Penjara
                    $action['type'] = 'jail';
                    $action['position'] = 10;
                    $action['msg'] = "Masuk Penjara!";
                    break;

                case 'chance':
                    $card = $this->model('CardModel')->drawRandom('kesempatan');
                    if ($card) {
                        $action = $this->applyCardEffect($card, $player, $newMoney, $newPos);
                        $newMoney = $action['new_money'];
                        $newPos   = $action['new_position'];
                        $this->model('SessionModel')->setActiveCard($sessionId, json_encode([
                            'type' => 'kesempatan',
                            'player_name' => $player['name'],
                            'text' => $card['text']
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
                            'type' => 'dana_umum',
                            'player_name' => $player['name'],
                            'text' => $card['text']
                        ]));
                    }
                    break;

                case 'property':
                case 'station':
                case 'utility':
                    $owner = $this->model('PropertyModel')->getOwner($sessionId, $newPos);
                    $cellImage = $cell['image_url'] ?? null;
                    if ($cellImage && strpos($cellImage, '/') === false) {
                        $cellImage = 'assets/static/cities/' . $cellImage;
                    }
                    if (!$owner) {
                        $action['type']  = 'buy';
                        $action['price'] = (int)($cell['price'] ?? 0);
                        $action['name']  = $cell['name'];
                        $action['image'] = $cellImage;
                        $action['msg']   = "Beli {$cell['name']} seharga Rp " . number_format($cell['price'], 0, ',', '.');
                    } elseif ($owner['owner_id'] == $id) {
                        $currentLevel = (int)$owner['houses'];
                        if ($cell['type'] === 'property' && $currentLevel < $maxPropLevel) {
                            $nextLevel = $currentLevel + 1;
                            $nextLevelKey = "level{$nextLevel}_price";
                            $upgradePrice = isset($cell[$nextLevelKey]) && $cell[$nextLevelKey] > 0
                                ? (int)$cell[$nextLevelKey]
                                : $housePrice;
                            $nextLevelName = $cell["level{$nextLevel}_name"] ?? "Level {$nextLevel}";
                            $action['type']          = 'upgrade';
                            $action['price']         = $upgradePrice;
                            $action['name']          = $cell['name'];
                            $action['image']         = $cellImage;
                            $action['current_level'] = $currentLevel;
                            $action['next_level_name'] = $nextLevelName;
                            $action['msg']           = "Tingkatkan {$cell['name']} ke {$nextLevelName} seharga Rp " . number_format($upgradePrice, 0, ',', '.');
                        } else {
                            $action['type'] = 'own';
                            $action['msg']  = "Ini propertimu sendiri" . ($cell['type'] === 'property' && $currentLevel == $maxPropLevel ? " (Level Maksimal)" : "") . ".";
                        }
                    } else {
                        $rent = $this->calcRent($cell, $owner, $sessionId, $dice);
                        $newMoney -= $rent;
                        $this->model('PlayerModel')->updateMoney($owner['owner_id'], (int)$owner['money'] + $rent);
                        $currentLevel = (int)$owner['houses'];
                        $levelName = $cell["level{$currentLevel}_name"] ?? "Level {$currentLevel}";
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
                case 'jail':
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
            'status'   => 'success',
            'position' => $newPos,
            'money'    => $newMoney,
            'action'   => $action
        ]);
    }

    private function applyCardEffect($card, $player, $currentMoney, $currentPos) {
        $type  = $card['effect_type'];
        $value = (int)$card['effect_value'];
        $newMoney = $currentMoney;
        $newPos   = $currentPos;

        switch ($type) {
            case 'money':
                if (stripos($card['text'], 'per rumah') !== false) {
                    // Hitung total rumah
                    $props = $this->model('PropertyModel')->getPlayerProperties($player['session_id'], $player['id']);
                    $totalHouses = 0;
                    foreach ($props as $p) {
                        $totalHouses += (int)$p['houses'];
                    }
                    $newMoney += ($value * $totalHouses); // value is negative
                } else {
                    $newMoney += $value;
                }
                break;
            case 'move':
                $newPos = $value >= 0 ? $value : max(0, ($currentPos + $value + 40) % 40);
                break;
            case 'jail':
                $newPos = 10;
                break;
            case 'free':
                // Store free jail card — future enhancement
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
            return $rents[min($count - 1, 3)];
        }
        // Standard property — check tier-based rent
        $houses = min((int)($owner['houses'] ?? 0), 5);
        $levelKey = "level{$houses}_rent";
        if (isset($cell[$levelKey]) && $cell[$levelKey] > 0) {
            return (int)$cell[$levelKey];
        }
        // Fallback multiplier formula
        $multiplier = [1, 5, 15, 45, 80, 125];
        return (int)round($basePrice * 0.1 * ($multiplier[min($houses, 5)] ?? 1));
    }

    public function apiBuy() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['status' => 'error', 'msg' => 'Method not allowed']); return;
        }

        $playerId  = (int)($_POST['player_id'] ?? 0);
        $cellIndex = (int)($_POST['cell_index'] ?? 0);

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
                    echo json_encode(['status' => 'error', 'msg' => 'Properti sudah level maksimal']); return;
                }
                // Price for NEXT level
                $nextLevel = $currentLevel + 1;
                $nextLevelKey = "level{$nextLevel}_price";
                $upgradePrice = isset($cell[$nextLevelKey]) && $cell[$nextLevelKey] > 0
                    ? (int)$cell[$nextLevelKey]
                    : $defaultHousePrice;
                $nextLevelName = $cell["level{$nextLevel}_name"] ?? "Level {$nextLevel}";

                if ((int)$player['money'] < $upgradePrice) {
                    echo json_encode(['status' => 'error', 'msg' => "Uang tidak cukup untuk upgrade ke {$nextLevelName}"]); return;
                }
                $newMoney = (int)$player['money'] - $upgradePrice;
                $this->model('PlayerModel')->updateMoney($playerId, $newMoney);
                $this->model('PropertyModel')->upgradeProperty($sessionId, $playerId, $cellIndex);
                echo json_encode(['status' => 'success', 'money' => $newMoney, 'msg' => "Berhasil tingkatkan {$cell['name']} ke {$nextLevelName}!"]);
                return;
            } else {
                echo json_encode(['status' => 'error', 'msg' => 'Properti sudah dimiliki']); return;
            }
        }

        $price = (int)$cell['price'];
        if ((int)$player['money'] < $price) {
            echo json_encode(['status' => 'error', 'msg' => 'Uang tidak cukup']); return;
        }

        $newMoney = (int)$player['money'] - $price;
        $this->model('PlayerModel')->updateMoney($playerId, $newMoney);
        $this->model('PropertyModel')->buyProperty($sessionId, $playerId, $cellIndex);

        echo json_encode(['status' => 'success', 'money' => $newMoney, 'msg' => "Berhasil membeli {$cell['name']}!"]);
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

        echo json_encode([
            'is_turn'     => (bool)$player['is_turn'],
            'has_rolled'  => (bool)$player['has_rolled'],
            'position'    => (int)$player['position'],
            'money'       => (int)$player['money'],
            'properties'  => $properties,
            'active_card' => $activeCard
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
