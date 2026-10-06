    <div class="board-area">
        <div class="monopoly-board">
            <?php 
            // Load city images from DB
            $cityImages = [];
            try {
                $imgDb = new Database;
                $imgDb->query("SELECT cell_index, image_url FROM board_properties");
                foreach ($imgDb->resultSet() as $r) {
                    $cityImages[(int)$r['cell_index']] = $r['image_url'];
                }
            } catch (Exception $e) { }

            $grid = array_fill(0, 11, array_fill(0, 11, null));
            for ($i = 0; $i <= 10; $i++) { $grid[10][10 - $i] = $i; }
            for ($i = 11; $i <= 19; $i++) { $grid[10 - ($i - 10)][0] = $i; }
            for ($i = 20; $i <= 30; $i++) { $grid[0][$i - 20] = $i; }
            for ($i = 31; $i <= 39; $i++) { $grid[$i - 30][10] = $i; }
            
            for ($row = 0; $row < 11; $row++) {
                for ($col = 0; $col < 11; $col++) {
                    $cellIndex = $grid[$row][$col];
                    if ($cellIndex !== null) {
                        $cell = $data['board'][$cellIndex];

                        // Determine orientation class
                        $isTopLeft     = ($row == 0 && $col == 0);
                        $isTopRight    = ($row == 0 && $col == 10);
                        $isBottomLeft  = ($row == 10 && $col == 0);
                        $isBottomRight = ($row == 10 && $col == 10);
                        $isCorner      = $isTopLeft || $isTopRight || $isBottomLeft || $isBottomRight;
                        $isTop         = ($row == 0 && !$isCorner);
                        $isBottom      = ($row == 10 && !$isCorner);
                        $isLeft        = ($col == 0 && !$isCorner);
                        $isRight       = ($col == 10 && !$isCorner);

                        $orientClass = '';
                        if ($isTop)    $orientClass = 'cell-top';
                        if ($isBottom) $orientClass = 'cell-bottom';
                        if ($isLeft)   $orientClass = 'cell-left';
                        if ($isRight)  $orientClass = 'cell-right';
                        if ($isCorner) $orientClass = 'cell-corner';

                        echo '<div class="cell ' . $orientClass . '" id="cell-'.$cellIndex.'">';
                        echo '<div class="cell-content">';

                        // Color bar / image
                        if ($cell['color'] != 'white') {
                            echo '<div class="color-bar c-'.$cell['color'].' flex items-center justify-center">';
                            // Title in header, white text
                            echo '<div class="name-in-color text-white font-black uppercase text-center leading-none flex items-center justify-center w-full h-full" style="font-size:clamp(0.4rem, 1vmin, 0.7rem);">';
                            echo $cell['name'];
                            echo '</div>';
                            echo '</div>';

                            echo '<div class="name flex items-center justify-center w-full h-full p-1 relative">';
                            if (isset($cityImages[$cellIndex]) && !empty($cityImages[$cellIndex])) {
                                $imgPath = $cityImages[$cellIndex];
                                if (strpos($imgPath, '/') === false) $imgPath = 'assets/static/cities/' . $imgPath;
                                echo '<img src="'.BASEURL.'/'.$imgPath.'" style="width:100%;height:100%;object-fit:cover;border-radius:4px;box-shadow:inset 0 0 5px rgba(0,0,0,0.2);">';
                            }
                            echo '</div>';
                        } else {
                            echo '<div class="spacer-bar"></div>';
                            echo '<div class="name">' . $cell['name'] . '</div>';
                        }

                        if (isset($cell['price'])) {
                            echo '<div class="price z-10">Rp ' . number_format($cell['price'], 0, ',', '.') . '</div>';
                        } else {
                            echo '<div class="price z-10"></div>';
                        }
                        echo '<div class="tokens absolute inset-0 pointer-events-none"></div>';
                        echo '</div></div>';
                    } else if ($row == 1 && $col == 1) {
                        // Center space
                        echo '<div class="center-space relative overflow-hidden">';
                        echo '<div class="absolute inset-0 bg-white/5 opacity-50 bg-[radial-gradient(#cbd5e1_1px,transparent_1px)] [background-size:20px_20px]"></div>';
                        
                        // Tumpukan Dana Umum
                        $nameDU = htmlspecialchars($data['settings']['name_dana_umum'] ?? 'DANA UMUM');
                        echo '<div class="absolute left-[10%] top-1/2 -translate-y-1/2 flex flex-col items-center opacity-80 hover:opacity-100 transition cursor-pointer z-10">';
                        echo '    <div class="w-[clamp(50px,12vmin,140px)] h-[clamp(80px,18vmin,200px)] bg-emerald-500 rounded-xl border-4 border-emerald-300 shadow-[2px_2px_0_#064e3b,-2px_-2px_0_white,0_10px_20px_rgba(0,0,0,0.4)] flex flex-col items-center justify-center transform -rotate-12 hover:-translate-y-2 transition-transform p-1">';
                        echo '        <i class="fa-solid fa-gem text-white/60 text-3xl md:text-6xl mb-3"></i>';
                        echo '        <div class="text-white font-black text-[0.55rem] md:text-xs text-center uppercase tracking-widest leading-tight break-words max-w-full">'.$nameDU.'</div>';
                        echo '    </div>';
                        echo '    <div class="w-[clamp(50px,12vmin,140px)] h-[clamp(80px,18vmin,200px)] bg-emerald-600 rounded-xl absolute top-1 left-1 -z-10 shadow-[5px_5px_15px_rgba(0,0,0,0.5)] transform -rotate-6"></div>';
                        echo '    <div class="w-[clamp(50px,12vmin,140px)] h-[clamp(80px,18vmin,200px)] bg-emerald-700 rounded-xl absolute top-2 left-2 -z-20 transform -rotate-3"></div>';
                        echo '</div>';
                        
                        // Tumpukan Kesempatan
                        $nameKes = htmlspecialchars($data['settings']['name_kesempatan'] ?? 'KESEMPATAN');
                        echo '<div class="absolute right-[10%] top-1/2 -translate-y-1/2 flex flex-col items-center opacity-80 hover:opacity-100 transition cursor-pointer z-10">';
                        echo '    <div class="w-[clamp(50px,12vmin,140px)] h-[clamp(80px,18vmin,200px)] bg-amber-500 rounded-xl border-4 border-amber-300 shadow-[-2px_2px_0_#78350f,2px_-2px_0_white,0_10px_20px_rgba(0,0,0,0.4)] flex flex-col items-center justify-center transform rotate-12 hover:-translate-y-2 transition-transform p-1">';
                        echo '        <i class="fa-solid fa-question text-white/60 text-4xl md:text-7xl mb-3"></i>';
                        echo '        <div class="text-white font-black text-[0.55rem] md:text-xs text-center uppercase tracking-widest leading-tight break-words max-w-full">'.$nameKes.'</div>';
                        echo '    </div>';
                        echo '    <div class="w-[clamp(50px,12vmin,140px)] h-[clamp(80px,18vmin,200px)] bg-amber-600 rounded-xl absolute top-1 right-1 -z-10 shadow-[-5px_5px_15px_rgba(0,0,0,0.5)] transform rotate-6"></div>';
                        echo '    <div class="w-[clamp(50px,12vmin,140px)] h-[clamp(80px,18vmin,200px)] bg-amber-700 rounded-xl absolute top-2 right-2 -z-20 transform rotate-3"></div>';
                        echo '</div>';

                        echo '<div class="center-content-wrapper relative z-20">';
                        echo '<h1 class="title-text font-black">MONOPOLY</h1>';
                        echo '<h2 class="subtitle-text font-bold">EDISI INDONESIA</h2>';
                        echo '<p id="center-turn-info" class="info-badge mt-4 text-lg font-bold shadow-lg"></p>';
                        echo '</div>';
                        echo '</div>';
                    }
                }
            }
            ?>
        </div>
    </div>
</div>

<!-- QR Modal for Joining -->
<div id="qr-modal" class="fixed inset-0 bg-black/90 z-[100] hidden items-center justify-center p-4 backdrop-blur-lg">
    <div class="bg-slate-900 border border-white/10 p-6 lg:p-10 rounded-3xl w-full max-w-5xl shadow-[0_0_50px_rgba(0,0,0,1)] relative">
        <button onclick="document.getElementById('qr-modal').classList.remove('flex'); document.getElementById('qr-modal').classList.add('hidden');" class="absolute top-4 right-4 text-slate-400 hover:text-white transition w-10 h-10 bg-white/10 rounded-full flex items-center justify-center">
            <i class="fa-solid fa-xmark text-xl"></i>
        </button>
        <h2 class="text-2xl lg:text-4xl font-black text-white mb-2 text-center drop-shadow-md">Gabung Pemain</h2>
        <p class="text-slate-400 text-center mb-10">Scan QR Code ini menggunakan HP untuk mengontrol lemparan dadu.</p>
        
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 lg:gap-8">
            <?php foreach($data['players'] as $p): ?>
                <div class="bg-gradient-to-br from-white/5 to-white/10 p-4 lg:p-6 rounded-2xl flex flex-col items-center border border-white/10 shadow-lg relative overflow-hidden group hover:border-white/30 transition">
                    <div class="absolute inset-0 bg-<?= $p['color'] ?>-500/20 blur-[50px] rounded-full scale-150 -z-10 group-hover:bg-<?= $p['color'] ?>-500/40 transition duration-500"></div>
                    
                    <h3 class="text-white font-black mb-4 bg-<?= $p['color'] ?>-500 px-4 py-1.5 rounded-full shadow-lg border border-white/20 whitespace-nowrap text-sm lg:text-base">
                        <?= htmlspecialchars($p['name']) ?>
                    </h3>
                    <?php if (!empty($p['is_turn'])): ?>
                    <div class="text-xs bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 px-2 py-1 rounded-full font-black mb-3 animate-pulse">
                        👑 GILIRAN INI
                    </div>
                    <?php endif; ?>
                    
                    <?php 
                        $playerUrl = BASEURL . "/player/index/" . $p['id']; 
                    ?>
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=<?= urlencode($playerUrl) ?>" 
                         class="w-32 h-32 lg:w-44 lg:h-44 rounded-xl mb-4 bg-white p-2 shadow-[0_10px_20px_rgba(0,0,0,0.5)] transform group-hover:scale-105 transition duration-300">
                    
                    <a href="<?= BASEURL ?>/player/index/<?= $p['id'] ?>" target="_blank" class="text-<?= $p['color'] ?>-400 text-sm hover:text-white transition font-bold flex items-center gap-2">
                        Buka di Tab <i class="fa-solid fa-arrow-up-right-from-square"></i>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Bank Negara Modal -->
<div id="bank-modal" class="fixed inset-0 bg-black/90 z-[100] hidden items-center justify-center p-4 backdrop-blur-lg">
    <div class="bg-slate-900 border border-emerald-500/30 p-6 lg:p-10 rounded-3xl w-full max-w-4xl shadow-[0_0_50px_rgba(16,185,129,0.2)] relative">
        <button onclick="document.getElementById('bank-modal').classList.remove('flex'); document.getElementById('bank-modal').classList.add('hidden');" class="absolute top-4 right-4 text-slate-400 hover:text-emerald-400 transition w-10 h-10 bg-white/10 rounded-full flex items-center justify-center">
            <i class="fa-solid fa-xmark text-xl"></i>
        </button>
        <h2 class="text-2xl lg:text-4xl font-black text-emerald-400 mb-2 text-center drop-shadow-md"><i class="fa-solid fa-building-columns mr-3"></i>BANK NEGARA</h2>
        <p class="text-emerald-400/60 text-center mb-10">Panel Pusat Keuangan Monopoly — Klik +/- untuk transaksi manual</p>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <?php foreach($data['players'] as $p): ?>
                <div class="bg-white/5 p-5 rounded-2xl border border-white/10 flex items-center justify-between hover:bg-white/10 transition">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-full bg-<?= $p['color'] ?>-500 flex items-center justify-center text-white font-bold text-xl shadow-[0_5px_15px_rgba(0,0,0,0.5)]">
                            <i class="fa-solid fa-user"></i>
                        </div>
                        <div>
                            <h3 class="text-white font-bold text-lg"><?= htmlspecialchars($p['name']) ?></h3>
                            <div id="bank-money-<?= $p['id'] ?>" class="text-emerald-400 font-mono font-bold text-xl">Rp <?= number_format($p['money'], 0, ',', '.') ?></div>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <button onclick="bankAdjust(<?= $p['id'] ?>, '<?= htmlspecialchars($p['name']) ?>', -1)" 
                            class="w-12 h-12 bg-red-500/20 text-red-400 hover:bg-red-500 hover:text-white rounded-xl transition font-black text-2xl flex items-center justify-center" title="Kurangi Uang">−</button>
                        <button onclick="bankAdjust(<?= $p['id'] ?>, '<?= htmlspecialchars($p['name']) ?>', 1)" 
                            class="w-12 h-12 bg-emerald-500/20 text-emerald-400 hover:bg-emerald-500 hover:text-white rounded-xl transition font-black text-2xl flex items-center justify-center" title="Tambah Uang">+</button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Inject PHP data ke JS -->
<script>
    const dbPlayers = <?= json_encode(array_values($data['players'])) ?>;
    const dbSessionId = <?= json_encode($data['session']['id'] ?? null) ?>;
    const BASEURL = '<?= BASEURL ?>';
    const POLLING_INTERVAL = <?= POLLING_INTERVAL ?>;
</script>
<script src="<?= BASEURL ?>/assets/js/game.js?v=<?= time() ?>"></script>
