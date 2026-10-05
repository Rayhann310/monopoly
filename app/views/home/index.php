    <div class="board-area">
        <div class="monopoly-board">
            <?php 
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
                        echo '<div class="cell" id="cell-'.$cellIndex.'">';
                        echo '<div class="cell-content">';
                        if ($cell['color'] != 'white') {
                            echo '<div class="color-bar c-'.$cell['color'].'"></div>';
                        } else {
                            echo '<div class="h-[25%] w-full"></div>';
                        }
                        echo '<div class="name flex-grow flex items-center justify-center">' . $cell['name'] . '</div>';
                        if (isset($cell['price'])) {
                            echo '<div class="price">Rp ' . number_format($cell['price'], 0, ',', '.') . '</div>';
                        } else {
                            echo '<div class="price"></div>';
                        }
                        echo '<div class="tokens absolute inset-0 pointer-events-none"></div>';
                        echo '</div></div>';
                    } else if ($row == 1 && $col == 1) {
                        // Center space
                        echo '<div class="center-space">';
                        echo '<div class="center-content-wrapper">';
                        echo '<h1 class="title-text font-black"><i class="fa-solid fa-building text-red-500 mr-3"></i>MONOPOLY</h1>';
                        echo '<h2 class="subtitle-text font-bold">EDISI INDONESIA</h2>';
                        echo '<p id="center-turn-info" class="info-badge mt-4 text-lg font-bold"></p>';
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
<script src="<?= BASEURL ?>/assets/js/game.js"></script>
