<?php
    $colorMap = [
        'red' => 'from-red-500 to-rose-600',
        'blue' => 'from-blue-500 to-indigo-600',
        'green' => 'from-emerald-400 to-green-600',
        'yellow' => 'from-amber-400 to-orange-500',
    ];
    $bgGrad = isset($colorMap[$data['player']['color']]) ? $colorMap[$data['player']['color']] : 'from-slate-500 to-slate-600';
?>

<style>
@keyframes shake {
    0% { transform: translate(1px, 1px) rotate(0deg); }
    10% { transform: translate(-1px, -2px) rotate(-1deg); }
    20% { transform: translate(-3px, 0px) rotate(1deg); }
    30% { transform: translate(3px, 2px) rotate(0deg); }
    40% { transform: translate(1px, -1px) rotate(1deg); }
    50% { transform: translate(-1px, 2px) rotate(-1deg); }
    60% { transform: translate(-3px, 1px) rotate(0deg); }
    70% { transform: translate(3px, 1px) rotate(-1deg); }
    80% { transform: translate(-1px, -1px) rotate(1deg); }
    90% { transform: translate(1px, 2px) rotate(0deg); }
    100% { transform: translate(1px, -2px) rotate(-1deg); }
}
.shake { animation: shake 0.3s; animation-iteration-count: infinite; }

/* Property card click effect */
.property-card {
    cursor: pointer;
    user-select: none;
    transition: transform 0.18s cubic-bezier(.34,1.56,.64,1), box-shadow 0.18s;
    position: relative;
    overflow: hidden;
}
.property-card:active { transform: scale(0.93); }
.property-card::after {
    content: '';
    position: absolute; inset: 0;
    background: radial-gradient(circle at var(--rx,50%) var(--ry,50%), rgba(255,255,255,0.22) 0%, transparent 65%);
    opacity: 0;
    transition: opacity 0.3s;
    pointer-events: none;
    border-radius: inherit;
}
.property-card:active::after { opacity: 1; }

/* Tap hint pulse on card */
@keyframes tapHint { 0%,100%{opacity:.6} 50%{opacity:1} }
.tap-hint { animation: tapHint 2s ease-in-out infinite; }

/* Modal slide-up */
@keyframes slideUp {
    from { transform: translateY(100%); opacity: 0; }
    to   { transform: translateY(0);    opacity: 1; }
}
.prop-modal-inner { animation: slideUp 0.28s cubic-bezier(.34,1.56,.64,1); }
</style>

<!-- Credit Card Container -->
<div class="px-5 pt-5 pb-2">
    <div id="player-credit-card" class="relative overflow-hidden rounded-3xl p-6 shadow-2xl border border-white/20 text-white transition-all duration-500 bg-gradient-to-br <?= $bgGrad ?> select-none"
         style="box-shadow: 0 20px 40px -15px rgba(0,0,0,0.7), inset 0 1px 1px rgba(255,255,255,0.4);">
        
        <!-- Background Decorative Watermark & Holographic Sheen -->
        <div class="absolute -right-8 -bottom-10 text-white/10 text-9xl pointer-events-none transform -rotate-12">
            <i class="fa-solid fa-gem"></i>
        </div>
        <div class="absolute inset-0 bg-gradient-to-tr from-transparent via-white/5 to-white/15 pointer-events-none"></div>

        <!-- Top Row: Chip, Contactless, & Monopoly VIP Logo -->
        <div class="relative z-10 flex items-center justify-between mb-4">
            <div class="flex items-center gap-3">
                <!-- EMV Smart Chip -->
                <div class="w-11 h-8 rounded-lg bg-gradient-to-br from-amber-200 via-amber-400 to-amber-600 border border-amber-200/80 shadow-md flex items-center justify-center relative overflow-hidden">
                    <div class="w-full h-[1px] bg-amber-800/40 absolute top-2.5"></div>
                    <div class="w-full h-[1px] bg-amber-800/40 absolute bottom-2.5"></div>
                    <div class="h-full w-[1px] bg-amber-800/40 absolute left-3"></div>
                    <div class="h-full w-[1px] bg-amber-800/40 absolute right-3"></div>
                    <div class="w-3.5 h-3 rounded border border-amber-800/50"></div>
                </div>
                <!-- Contactless Icon -->
                <i class="fa-solid fa-wifi rotate-90 text-white/70 text-lg"></i>
            </div>
            <!-- Bank / Game Branding -->
            <div class="flex items-center gap-1.5 bg-black/30 backdrop-blur-md px-3 py-1 rounded-full border border-white/15">
                <i class="fa-solid fa-crown text-amber-400 text-xs"></i>
                <span class="font-black text-[11px] tracking-wider uppercase">MONOPOLY VIP</span>
            </div>
        </div>

        <!-- Middle: Balance & Laps -->
        <div class="relative z-10 my-3">
            <div class="text-[10px] uppercase font-bold text-white/70 tracking-widest flex items-center gap-1.5">
                <span>Saldo Rekening</span>
                <?php if ((int)($data['player']['free_jail_cards'] ?? 0) > 0): ?>
                <span class="ml-2 bg-emerald-500/20 px-2 py-0.5 rounded-full text-[10px] font-bold text-emerald-300 border border-emerald-400/30" title="Kartu Bebas Penjara">
                    <i class="fa-solid fa-ticket-simple"></i> <?= $data['player']['free_jail_cards'] ?>
                </span>
                <?php endif; ?>
                <span id="lap-counter-badge" class="ml-auto bg-black/40 px-2.5 py-0.5 rounded-full text-[10px] font-mono text-amber-300 border border-amber-400/30">
                    <i class="fa-solid fa-flag-checkered mr-1"></i>Putaran <?= max(1, ((int)($data['player']['laps'] ?? 0)) + 1) ?>
                </span>
            </div>
            <div id="player-wallet-box" class="flex items-center gap-2 mt-1 relative transition-all duration-300">
                <i id="player-wallet-icon" class="fa-solid fa-wallet text-amber-300 text-xl transition-all duration-300"></i>
                <span id="player-money-val" class="text-3xl font-black font-mono tracking-tight drop-shadow-md">
                    Rp <?= number_format($data['player']['money'], 0, ',', '.') ?>
                </span>
                <span id="money-diff-badge" class="absolute -top-4 right-0 font-mono font-black text-xs px-2.5 py-0.5 rounded-full shadow-2xl transition-all duration-700 pointer-events-none opacity-0 scale-75"></span>
            </div>
        </div>

        <!-- Parking Pot Indicator -->
        <?php 
        $parkingPot = 0;
        // Fetch from session
        if (!empty($data['player']['session_id'])) {
            $db = new Database();
            $db->query("SELECT COALESCE(free_parking_pot,0) as pot FROM sessions WHERE id = :sid");
            $db->bind('sid', $data['player']['session_id']);
            $row = $db->single();
            $parkingPot = $row ? (int)$row['pot'] : 0;
        }
        ?>
        <div id="parking-pot-bar" class="relative z-10 mt-2 flex items-center gap-2 bg-black/25 rounded-xl px-3 py-1.5 border border-emerald-500/20 <?= $parkingPot > 0 ? '' : 'opacity-50' ?>" style="transition: opacity 0.5s">
            <span class="text-lg">🅿️</span>
            <div class="flex-1">
                <div class="text-[9px] font-bold uppercase tracking-wider text-emerald-400/70">Pot Parkir Bebas</div>
                <div id="parking-pot-val" class="text-sm font-black font-mono text-emerald-300">
                    <?= $parkingPot > 0 ? 'Rp ' . number_format($parkingPot, 0, ',', '.') : 'Kosong' ?>
                </div>
            </div>
            <?php if ($parkingPot > 0): ?>
            <div class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></div>
            <?php endif; ?>
        </div>


        <!-- Bottom Row: Cardholder Name, Virtual Card Number, & Brand Circles -->
        <div class="relative z-10 flex items-end justify-between mt-4 pt-2 border-t border-white/15">
            <div>
                <div class="text-[9px] uppercase tracking-wider text-white/60 font-bold">Pemegang Kartu</div>
                <div class="text-sm font-black tracking-wide uppercase drop-shadow"><?= htmlspecialchars($data['player']['name']) ?></div>
                <div class="text-[10px] font-mono tracking-widest text-white/70 mt-0.5">•••• •••• •••• <?= str_pad($data['player']['id'], 4, '0', STR_PAD_LEFT) ?></div>
            </div>
            <!-- Platinum Overlapping Circles -->
            <div class="flex items-center -space-x-3 opacity-80">
                <div class="w-7 h-7 rounded-full bg-rose-500/80 shadow-sm"></div>
                <div class="w-7 h-7 rounded-full bg-amber-400/80 shadow-sm"></div>
            </div>
        </div>
    </div>
</div>

<!-- Status Giliran / Status Penjara -->
<?php 
    $inJail = !empty($data['player']['in_jail']);
    $isTurn = !empty($data['player']['is_turn']);
    if ($inJail) {
        $badgeClass = 'bg-rose-950/70 border border-rose-500/50 shadow-rose-950/50';
    } elseif ($isTurn) {
        $badgeClass = 'bg-emerald-500/20 border border-emerald-500/40 shadow-emerald-950/50';
    } else {
        $badgeClass = 'bg-slate-800/60 border border-white/10';
    }
?>
<div id="turn-badge" class="mx-6 mt-3 rounded-2xl px-4 py-3 flex items-center gap-3 shadow-lg <?= $badgeClass ?>">
<?php if ($inJail): 
    $jailTurns = (int)($data['player']['jail_turns'] ?? 0);
?>
    <i class="fa-solid fa-handcuffs text-2xl text-rose-400 animate-pulse"></i>
    <div class="flex-1 w-full">
        <div class="text-rose-400 font-black text-base flex flex-wrap items-center gap-2">
            <span>TERTAHAN DI PENJARA!</span>
            <span class="text-xs bg-rose-500/30 text-rose-300 border border-rose-500/50 px-2 py-0.5 rounded-full font-bold">Percobaan <?= $jailTurns ?>/3</span>
        </div>
        <div class="text-rose-300/80 text-xs mt-0.5">Dadu KEMBAR untuk bebas • Putaran ke-4 otomatis bebas</div>
        
        <?php 
        $freeCards = (int)($data['player']['free_jail_cards'] ?? 0);
        if ($freeCards > 0 && $isTurn && empty($data['player']['has_rolled'])): ?>
            <button onclick="useJailCard()" class="mt-2 w-full bg-emerald-500 hover:bg-emerald-600 border border-emerald-400 text-white font-bold py-2 px-3 rounded-lg text-xs transition flex items-center justify-center gap-2 shadow-[0_0_15px_rgba(16,185,129,0.3)]">
                <i class="fa-solid fa-ticket-simple"></i> Pakai Kartu Bebas Penjara (Sisa: <?= $freeCards ?>)
            </button>
        <?php endif; ?>
    </div>
<?php elseif ($isTurn): ?>
    <i class="fa-solid fa-crown text-2xl text-amber-400 animate-pulse"></i>
    <div>
        <div class="text-emerald-400 font-black text-base">GILIRAN KAMU!</div>
        <div class="text-emerald-400/60 text-xs">Lempar dadu sekarang</div>
    </div>
<?php else: ?>
    <i class="fa-regular fa-clock text-2xl text-slate-500"></i>
    <div>
        <div class="text-slate-400 font-black text-base">MENUNGGU...</div>
        <div class="text-slate-600 text-xs">Bukan giliranmu saat ini</div>
    </div>
<?php endif; ?>
</div>

<!-- Dice Action Area -->
<div class="flex-1 flex flex-col items-center justify-center p-6 gap-5 mt-2">
    <!-- Dice Display -->
    <div class="flex gap-6 justify-center items-center h-32 w-full bg-slate-900/50 rounded-2xl border border-white/5 shadow-inner">
        <i id="mobile-die1" class="fa-solid fa-dice-one text-6xl text-<?= $data['player']['color'] ?>-500 drop-shadow-[0_0_15px_rgba(255,255,255,0.2)]"></i>
        <i id="mobile-die2" class="fa-solid fa-dice-one text-6xl text-<?= $data['player']['color'] ?>-500 drop-shadow-[0_0_15px_rgba(255,255,255,0.2)]"></i>
    </div>

    <!-- Roll Button -->
    <button id="mobile-roll-btn"
        <?= !$data['player']['is_turn'] ? 'disabled' : '' ?>
        class="w-full max-w-sm py-4 bg-gradient-to-r <?= $bgGrad ?> text-white rounded-2xl font-black text-xl shadow-[0_10px_30px_rgba(0,0,0,0.5)] active:scale-95 transition-transform border border-white/30 disabled:opacity-30 disabled:cursor-not-allowed disabled:active:scale-100">
        <i class="fa-solid fa-hand-sparkles mr-2"></i> LEMPAR DADU
    </button>

    <!-- End Turn Button (muncul setelah roll) -->
    <button id="end-turn-btn" class="hidden w-full max-w-sm py-4 bg-emerald-500 hover:bg-emerald-600 text-white rounded-2xl font-black text-xl shadow-[0_10px_30px_rgba(0,0,0,0.5)] active:scale-95 transition-transform border border-emerald-400/30">
        <i class="fa-solid fa-check mr-2"></i> SELESAI GILIRAN
    </button>
</div>

<!-- Properties Section - Premium Redesign -->
<div class="mt-auto px-3 pb-3">

    <!-- Section Header -->
    <div class="flex items-center justify-between mb-3 px-1">
        <div class="flex items-center gap-2">
            <div class="w-7 h-7 rounded-lg bg-blue-500/20 border border-blue-500/30 flex items-center justify-center">
                <i class="fa-solid fa-city text-blue-400 text-xs"></i>
            </div>
            <div>
                <h3 class="font-black text-white text-sm leading-none">Aset Properti</h3>
                <span class="text-[9px] text-slate-500 font-bold uppercase tracking-wider">
                    <?= count($data['properties']) ?> kota dimiliki
                </span>
            </div>
        </div>
        <?php if (!empty($data['properties'])): ?>
        <div class="flex items-center gap-1 text-slate-500 text-[10px] font-bold bg-slate-800/60 px-2 py-1 rounded-lg border border-white/5">
            <i class="fa-solid fa-hand-point-left text-[9px]"></i> Geser
        </div>
        <?php endif; ?>
    </div>

    <!-- Cards Slider -->
    <div class="flex gap-3 overflow-x-auto pb-2 snap-x snap-mandatory scrollbar-none" style="-webkit-overflow-scrolling:touch; scrollbar-width:none;">

        <?php if (empty($data['properties'])): ?>
        <!-- Empty State -->
        <div class="snap-center shrink-0 w-36 h-48 rounded-2xl border border-dashed border-white/10 bg-white/3 flex flex-col items-center justify-center gap-2 opacity-50">
            <div class="w-12 h-12 rounded-xl bg-slate-800 flex items-center justify-center">
                <i class="fa-solid fa-city text-slate-600 text-xl"></i>
            </div>
            <div class="text-center px-2">
                <div class="text-[11px] font-black text-slate-500 uppercase tracking-wide">Belum Ada</div>
                <div class="text-[9px] text-slate-600 mt-0.5">Beli properti saat mendarat</div>
            </div>
        </div>

        <?php else: ?>
        <?php foreach ($data['properties'] as $prop):
            $boardCell = null;
            foreach ($data['board'] as $idx => $cell) {
                if ($idx == $prop['cell_index']) { $boardCell = $cell; break; }
            }
            if (!$boardCell) continue;
            $color = $boardCell['color_group'] ?? 'slate';
            $houses = (int)$prop['houses'];
            $imgPath = '';
            if (!empty($boardCell['image_url'])) {
                $imgPath = (strpos($boardCell['image_url'], '/') === false)
                    ? BASEURL . '/assets_static/cities/' . $boardCell['image_url']
                    : BASEURL . '/' . $boardCell['image_url'];
            }
            $propData = json_encode([
                'cell_index'  => (int)$prop['cell_index'],
                'name'        => $boardCell['name'] ?? '',
                'color'       => $color,
                'price'       => (int)($boardCell['price'] ?? 0),
                'house_price' => (int)($boardCell['house_price'] ?? 0),
                'houses'      => $houses,
                'image'       => $imgPath,
                'level1_name' => $boardCell['level1_name'] ?? 'Rumah 1',
                'level2_name' => $boardCell['level2_name'] ?? 'Rumah 2',
                'level3_name' => $boardCell['level3_name'] ?? 'Rumah 3',
                'level4_name' => $boardCell['level4_name'] ?? 'Rumah 4',
                'level5_name' => $boardCell['level5_name'] ?? 'Hotel/Apartemen',
                'level0_rent' => (int)(($boardCell['price'] ?? 0) * 0.1),
                'level1_rent' => (int)($boardCell['level1_rent'] ?? 0),
                'level2_rent' => (int)($boardCell['level2_rent'] ?? 0),
                'level3_rent' => (int)($boardCell['level3_rent'] ?? 0),
                'level4_rent' => (int)($boardCell['level4_rent'] ?? 0),
                'level5_rent' => (int)($boardCell['level5_rent'] ?? 0),
                'type'        => $boardCell['type'] ?? 'property',
            ]);

            // Color mapping
            $colorPalette = [
                'blue'   => ['bg'=>'#1d4ed8','glow'=>'rgba(59,130,246,0.35)','text'=>'#93c5fd','border'=>'rgba(59,130,246,0.5)'],
                'green'  => ['bg'=>'#065f46','glow'=>'rgba(16,185,129,0.35)','text'=>'#6ee7b7','border'=>'rgba(16,185,129,0.5)'],
                'red'    => ['bg'=>'#991b1b','glow'=>'rgba(239,68,68,0.35)','text'=>'#fca5a5','border'=>'rgba(239,68,68,0.5)'],
                'yellow' => ['bg'=>'#92400e','glow'=>'rgba(245,158,11,0.35)','text'=>'#fde68a','border'=>'rgba(245,158,11,0.5)'],
                'purple' => ['bg'=>'#5b21b6','glow'=>'rgba(139,92,246,0.35)','text'=>'#c4b5fd','border'=>'rgba(139,92,246,0.5)'],
                'orange' => ['bg'=>'#9a3412','glow'=>'rgba(249,115,22,0.35)','text'=>'#fdba74','border'=>'rgba(249,115,22,0.5)'],
                'pink'   => ['bg'=>'#9d174d','glow'=>'rgba(236,72,153,0.35)','text'=>'#f9a8d4','border'=>'rgba(236,72,153,0.5)'],
                'teal'   => ['bg'=>'#115e59','glow'=>'rgba(20,184,166,0.35)','text'=>'#99f6e4','border'=>'rgba(20,184,166,0.5)'],
                'slate'  => ['bg'=>'#334155','glow'=>'rgba(100,116,139,0.25)','text'=>'#94a3b8','border'=>'rgba(100,116,139,0.4)'],
            ];
            $pal = $colorPalette[$color] ?? $colorPalette['slate'];
            $levelLabel = $houses === 0 ? 'Tanah' : ($houses === 5 ? 'Hotel' : "Rumah $houses");
            $levelColor = $houses === 0 ? '#64748b' : ($houses >= 5 ? '#f87171' : '#34d399');
        ?>

        <!-- Property Card -->
        <div class="snap-center shrink-0 w-36 rounded-2xl overflow-hidden cursor-pointer relative group transition-all duration-300 active:scale-95"
             style="box-shadow: 0 8px 24px <?= $pal['glow'] ?>, 0 0 0 1px <?= $pal['border'] ?>; background: linear-gradient(160deg, #1e293b 0%, #0f172a 100%);"
             onclick="showPropertyDetail(<?= htmlspecialchars($propData, ENT_QUOTES) ?>)">

            <!-- Color accent bar -->
            <div class="h-1.5 w-full" style="background: linear-gradient(90deg, <?= $pal['bg'] ?>, <?= $pal['text'] ?>)"></div>

            <!-- City Image -->
            <?php if ($imgPath): ?>
            <div class="w-full h-20 overflow-hidden relative">
                <img src="<?= $imgPath ?>" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500" loading="lazy">
                <div class="absolute inset-0" style="background: linear-gradient(to bottom, transparent 40%, #0f172a 100%)"></div>
                <!-- Level badge on image -->
                <div class="absolute bottom-2 right-2 text-[9px] font-black px-2 py-0.5 rounded-full" style="background:<?= $levelColor ?>22; border:1px solid <?= $levelColor ?>66; color:<?= $levelColor ?>">
                    <?= $levelLabel ?>
                </div>
            </div>
            <?php else: ?>
            <!-- No image placeholder -->
            <div class="w-full h-20 flex items-center justify-center" style="background: linear-gradient(135deg, <?= $pal['bg'] ?>33, <?= $pal['bg'] ?>11)">
                <i class="fa-solid fa-city text-2xl" style="color:<?= $pal['text'] ?>55"></i>
                <div class="absolute bottom-2 right-2 text-[9px] font-black px-2 py-0.5 rounded-full" style="background:<?= $levelColor ?>22; border:1px solid <?= $levelColor ?>66; color:<?= $levelColor ?>">
                    <?= $levelLabel ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Card Body -->
            <div class="p-2.5">
                <!-- City name -->
                <div class="font-black text-white text-[11px] leading-tight mb-1.5 line-clamp-2"><?= htmlspecialchars($boardCell['name']) ?></div>

                <!-- House icons -->
                <div class="flex gap-0.5 items-center mb-2 min-h-[14px]">
                    <?php if ($houses > 0):
                        for ($i = 0; $i < $houses; $i++):
                            $isHotel = ($i === 4);
                    ?>
                        <i class="fa-solid <?= $isHotel ? 'fa-hotel' : 'fa-house' ?> text-[10px]" style="color:<?= $isHotel ? '#f87171' : '#34d399' ?>"></i>
                        <?php if ($isHotel) break; ?>
                    <?php endfor; else: ?>
                        <span class="text-[9px] italic" style="color:<?= $pal['text'] ?>80">Tanah kosong</span>
                    <?php endif; ?>
                </div>

                <!-- Price & Tap hint -->
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-black" style="color:<?= $pal['text'] ?>">
                        <?php if ($boardCell['price']): ?>
                        Rp <?= number_format($boardCell['price'], 0, ',', '.') ?>
                        <?php else: ?>
                        &nbsp;
                        <?php endif; ?>
                    </span>
                    <div class="w-5 h-5 rounded-lg flex items-center justify-center" style="background:<?= $pal['bg'] ?>44">
                        <i class="fa-solid fa-arrow-up-right-from-square text-[8px]" style="color:<?= $pal['text'] ?>"></i>
                    </div>
                </div>
            </div>

            <!-- Glow overlay on hover -->
            <div class="absolute inset-0 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none" style="background: radial-gradient(circle at 50% 0%, <?= $pal['glow'] ?>, transparent 70%)"></div>
        </div>

        <?php endforeach; ?>
        <?php endif; ?>

        <!-- Spacer at end -->
        <div class="shrink-0 w-1"></div>
    </div>
</div>

<style>
.scrollbar-none::-webkit-scrollbar { display: none; }
</style>


<script>
    document.addEventListener('DOMContentLoaded', function() {
        const player = <?= json_encode($data['player']); ?>;
        const board = <?= json_encode($data['board']); ?>;
        const BASEURL = '<?= BASEURL ?>';
        const SETTINGS = {
            name_kesempatan: '<?= htmlspecialchars($data['settings']['name_kesempatan']['setting_value'] ?? 'Kesempatan') ?>',
            name_dana_umum:  '<?= htmlspecialchars($data['settings']['name_dana_umum']['setting_value'] ?? 'Dana Umum') ?>',
            allow_trade:     <?= !empty($data['settings']['allow_trade']['setting_value']) ? 'true' : 'false' ?>,
        };
        let currentPropsHash = JSON.stringify(<?= json_encode($data['properties'] ?? []) ?>);
        let hasRolled = <?= $data['player']['has_rolled'] ? 'true' : 'false' ?>;
        let isTurn = <?= $data['player']['is_turn'] ? 'true' : 'false' ?>;
        let lastShownCardText = null;
        let isRolling = false; // Guard against double-click/race condition
        let currentDisplayedMoney = parseInt(player.money) || 0;
        let moneyAnimFrame = null;

        function animateMoney(targetMoney, customDuration = 900) {
            targetMoney = parseInt(targetMoney);
            if (isNaN(targetMoney)) return;
            if (targetMoney === currentDisplayedMoney) return;

            const startVal = currentDisplayedMoney;
            const diff = targetMoney - startVal;
            const isIncrease = diff > 0;
            const duration = customDuration;
            const startTime = performance.now();

            // Floating Diff Badge & Visual Feedback
            const badge = document.getElementById('money-diff-badge');
            const walletBox = document.getElementById('player-wallet-box');
            const walletIcon = document.getElementById('player-wallet-icon');

            if (badge) {
                badge.textContent = (isIncrease ? '+' : '-') + ' Rp ' + Math.abs(diff).toLocaleString('id-ID');
                badge.className = 'absolute -top-3.5 -right-2 font-mono font-black text-xs px-2.5 py-0.5 rounded-full shadow-xl pointer-events-none transition-all duration-500 ' + 
                    (isIncrease ? 'bg-emerald-500 text-white shadow-emerald-500/50' : 'bg-rose-500 text-white shadow-rose-500/50');
                badge.style.opacity = '1';
                badge.style.transform = 'translateY(-6px) scale(1.05)';
                
                clearTimeout(badge._fadeTimer);
                badge._fadeTimer = setTimeout(() => {
                    badge.style.opacity = '0';
                    badge.style.transform = 'translateY(-16px) scale(0.85)';
                }, 1800);
            }

            if (walletBox) {
                walletBox.classList.remove('ring-2', 'ring-emerald-400', 'ring-rose-400');
                void walletBox.offsetWidth; // trigger reflow
                walletBox.classList.add('ring-2', isIncrease ? 'ring-emerald-400' : 'ring-rose-400');
                walletBox.style.transform = isIncrease ? 'scale(1.06)' : 'scale(0.96)';
                clearTimeout(walletBox._boxTimer);
                walletBox._boxTimer = setTimeout(() => {
                    walletBox.classList.remove('ring-2', 'ring-emerald-400', 'ring-rose-400');
                    walletBox.style.transform = '';
                }, 1000);
            }

            if (walletIcon) {
                walletIcon.style.transform = isIncrease ? 'scale(1.35) rotate(-12deg)' : 'scale(1.25) rotate(12deg)';
                walletIcon.style.color = isIncrease ? '#34d399' : '#f87171';
                clearTimeout(walletIcon._iconTimer);
                walletIcon._iconTimer = setTimeout(() => {
                    walletIcon.style.transform = '';
                    walletIcon.style.color = '';
                }, 1000);
            }

            if (moneyAnimFrame) {
                cancelAnimationFrame(moneyAnimFrame);
            }

            function step(currentTime) {
                const elapsed = currentTime - startTime;
                const progress = Math.min(elapsed / duration, 1);
                
                // easeOutCubic
                const ease = 1 - Math.pow(1 - progress, 3);
                const currentVal = Math.round(startVal + (diff * ease));
                
                const moneyEl = document.getElementById('player-money-val') || document.querySelector('.font-mono');
                if (moneyEl) {
                    moneyEl.textContent = 'Rp ' + currentVal.toLocaleString('id-ID');
                }

                if (progress < 1) {
                    moneyAnimFrame = requestAnimationFrame(step);
                } else {
                    currentDisplayedMoney = targetMoney;
                    player.money = targetMoney;
                    if (moneyEl) {
                        moneyEl.textContent = 'Rp ' + targetMoney.toLocaleString('id-ID');
                    }
                }
            }

            moneyAnimFrame = requestAnimationFrame(step);
        }

        function updateParkingPotDisplay(pot) {
            const el = document.getElementById('parking-pot-val');
            const bar = document.getElementById('parking-pot-bar');
            if (!el) return;
            if (pot > 0) {
                el.textContent = 'Rp ' + parseInt(pot).toLocaleString('id-ID');
                if (bar) { bar.style.opacity = '1'; }
            } else {
                el.textContent = 'Kosong';
                if (bar) { bar.style.opacity = '0.5'; }
            }
        }

        const rollBtn = document.getElementById('mobile-roll-btn');
        const endTurnBtn = document.getElementById('end-turn-btn');

        // Inisialisasi state tombol saat halaman dimuat
        if (!isTurn) {
            // Bukan giliran — kunci dadu
            rollBtn.disabled = true;
            rollBtn.classList.add('opacity-30');
            rollBtn.style.pointerEvents = 'none';
        } else if (hasRolled) {
            // Giliran kita tapi sudah roll — kunci dadu, tampilkan selesai
            rollBtn.disabled = true;
            rollBtn.classList.add('opacity-30');
            rollBtn.style.pointerEvents = 'none';
            endTurnBtn.classList.remove('hidden');
        }
        // else: giliran kita dan belum roll — tombol aktif (default)

        // Helper SweetAlert
        function showModal(title, text, icon, color) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title, html: text, icon,
                    background: '#0f172a', color: '#f1f5f9',
                    confirmButtonColor: color || '#3b82f6',
                    confirmButtonText: '<i class="fa fa-check"></i> Oke',
                    customClass: { popup: 'rounded-2xl border border-white/10 shadow-2xl' }
                });
            } else {
                const modal = document.getElementById('fallback-modal');
                document.getElementById('fm-title').innerText = title;
                document.getElementById('fm-text').innerHTML = text;
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            }
        }

        // Roll Dadu
        rollBtn.addEventListener('click', function() {
            if (this.disabled || isRolling || hasRolled) return;
            isRolling = true;
            this.disabled = true;
            this.style.pointerEvents = 'none';
            const btn = this;
            const d1el = document.getElementById('mobile-die1');
            const d2el = document.getElementById('mobile-die2');
            const icons = ['','fa-dice-one','fa-dice-two','fa-dice-three','fa-dice-four','fa-dice-five','fa-dice-six'];
            
            d1el.classList.add('shake');
            d2el.classList.add('shake');

            // Animasi putaran dadu acak cepat
            let rollInterval = setInterval(() => {
                const r1 = Math.floor(Math.random() * 6) + 1;
                const r2 = Math.floor(Math.random() * 6) + 1;
                d1el.className = `fa-solid ${icons[r1]} text-6xl text-slate-400 opacity-80`;
                d2el.className = `fa-solid ${icons[r2]} text-6xl text-slate-400 opacity-80`;
            }, 60);

            setTimeout(() => {
                clearInterval(rollInterval);
                d1el.classList.remove('shake');
                d2el.classList.remove('shake');

                const v1 = Math.floor(Math.random() * 6) + 1;
                const v2 = Math.floor(Math.random() * 6) + 1;
                const total = v1 + v2;
                d1el.className = `fa-solid ${icons[v1]} text-6xl text-${player.color}-500 drop-shadow-[0_0_15px_rgba(255,255,255,0.4)] transform scale-110 transition-transform`;
                d2el.className = `fa-solid ${icons[v2]} text-6xl text-${player.color}-500 drop-shadow-[0_0_15px_rgba(255,255,255,0.4)] transform scale-110 transition-transform`;
                
                setTimeout(() => {
                    d1el.classList.remove('scale-110');
                    d2el.classList.remove('scale-110');
                }, 200);

                player.position = (parseInt(player.position) + total) % 40;
                const landedCell = board[player.position];

                let title = `<i class='fa-solid fa-dice text-blue-400 mr-1'></i> Dadu: ${total}`;
                let text = `<b>Mendarat di:</b><br><span style="font-size:1.4rem;font-weight:900;color:#38bdf8">${landedCell.name}</span>`;
                let icon = 'success', color = '#38bdf8';

                function showCardAnimation(cardType, cardText, cardImg) {
                    cardText = cardText || 'Ambil kartu fisik dan ikuti instruksinya.';
                    const isKesempatan = cardType === 'kesempatan' || cardType === SETTINGS.name_kesempatan;
                    const cardLabel = isKesempatan ? SETTINGS.name_kesempatan : SETTINGS.name_dana_umum;
                    const cColor = isKesempatan ? '#f59e0b' : '#10b981';
                    const cIcon = isKesempatan ? 'fa-question' : 'fa-gem';
                    
                    const html = `
                    <style>
                    .mc-card-container { perspective: 1000px; width: 100%; height: 280px; margin-top: 10px; cursor: pointer; }
                    .mc-card { position: relative; width: 100%; height: 100%; text-align: center; transition: transform 0.8s cubic-bezier(0.34, 1.56, 0.64, 1); transform-style: preserve-3d; }
                    .mc-card.is-flipped { transform: rotateY(180deg); }
                    .mc-face { position: absolute; width: 100%; height: 100%; backface-visibility: hidden; border-radius: 16px; display: flex; flex-direction: column; justify-content: center; align-items: center; box-shadow: 0 15px 35px rgba(0,0,0,0.5); padding: 20px; border: 4px solid ${cColor}; }
                    .mc-front { background: radial-gradient(circle, #1e293b, #0f172a); }
                    .mc-back { background: white; color: #0f172a; transform: rotateY(180deg); }
                    .mc-front-icon { font-size: 80px; color: ${cColor}; text-shadow: 0 0 20px ${cColor}80; }
                    </style>
                    <div class="mc-card-container" onclick="this.querySelector('.mc-card').classList.add('is-flipped')">
                        <div class="mc-card">
                            <div class="mc-face mc-front">
                                <i class="fa-solid ${cIcon} mc-front-icon animate-pulse"></i>
                                <div style="color:white; margin-top:20px; font-weight:bold; font-size:1.2rem; letter-spacing:2px; text-transform:uppercase">${cardLabel}</div>
                                <div style="color:#94a3b8; font-size:0.8rem; margin-top:10px">Ketuk untuk membalik</div>
                            </div>
                            <div class="mc-face mc-back">
                                <div style="background:${cColor}; color:white; width:calc(100% + 40px); margin-top:-20px; padding:10px 15px; font-weight:900; text-transform:uppercase; font-size:1rem; border-top-left-radius: 10px; border-top-right-radius: 10px;">
                                    ${cardLabel}
                                </div>
                                <div style="flex-grow:1; display:flex; flex-direction:column; justify-content:center; align-items:center; padding:15px;">
                                    ${cardImg ? `<img src="${cardImg}" style="width:80px;height:80px;object-fit:cover;border-radius:10px;margin-bottom:12px;box-shadow:0 4px 12px rgba(0,0,0,0.2)">` : `<div style="width:70px;height:70px;border-radius:10px;background:${cColor}20;display:flex;align-items:center;justify-content:center;margin-bottom:12px;"><i class="fa-solid ${cIcon}" style="font-size:2rem;color:${cColor}"></i></div>`}
                                    <h3 style="font-weight:bold; font-size:1.05rem; color:#1e293b; line-height:1.4; text-align:center">${cardText}</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                    `;
                    
                    const swalPromise = Swal.fire({
                        html: html,
                        background: 'transparent',
                        showConfirmButton: true,
                        confirmButtonText: 'Oke, Sudah Dipahami',
                        confirmButtonColor: cColor,
                        backdrop: 'rgba(0,0,0,0.9)'
                    });
                    
                    setTimeout(() => {
                        const card = document.querySelector('.mc-card');
                        if(card && !card.classList.contains('is-flipped')) {
                            card.classList.add('is-flipped');
                        }
                    }, 1200);

                    return swalPromise;
                }

                // Kirim ke server — server yang handle semua logika
                fetch(BASEURL + '/player/apiRoll', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `id=${player.id}&die1=${v1}&die2=${v2}&dice=${total}`
                }).then(r => r.json()).then(res => {
                    if (res.status !== 'success') {
                        if (res.already_rolled) {
                            // Sudah roll di request lain — tetap kunci, tampilkan end turn
                            hasRolled = true;
                            isRolling = false;
                            btn.classList.add('opacity-30');
                            endTurnBtn.classList.remove('hidden');
                            endTurnBtn.disabled = false;
                        } else {
                            // Error biasa — kembalikan tombol
                            showModal('<i class="fa-solid fa-triangle-exclamation mr-1"></i> ' + (res.msg || 'Error'), '', 'warning', '#f59e0b');
                            btn.disabled = false;
                            btn.style.pointerEvents = '';
                            isRolling = false;
                        }
                        return;
                    }

                    hasRolled = true;
                    isRolling = false;
                    btn.classList.add('opacity-30');
                    const act = res.action || {};

                    // Update local position from server (e.g. jail redirect)
                    player.position = res.position;

                    // Update putaran (lap) pada kartu kredit
                    if (res.laps !== undefined) {
                        const lapBadge = document.getElementById('lap-counter-badge');
                        if (lapBadge) lapBadge.innerHTML = `<i class="fa-solid fa-flag-checkered mr-1"></i>Putaran ${(parseInt(res.laps || 0) + 1)}`;
                    }

                    // Update uang di UI dengan animasi count
                    animateMoney(res.money);

                    let jailBanner = '';
                    if (act.jail_auto_freed) {
                        jailBanner = `<div class="p-2.5 mb-3 bg-amber-500/20 border border-amber-500/40 rounded-xl text-amber-300 text-xs font-bold leading-relaxed text-center"><i class="fa-solid fa-lock-open mr-1"></i> ${act.msg_jail}</div>`;
                    } else if (act.jail_freed) {
                        jailBanner = `<div class="p-2.5 mb-3 bg-emerald-500/20 border border-emerald-500/40 rounded-xl text-emerald-300 text-xs font-bold leading-relaxed text-center"><i class="fa-solid fa-dice mr-1"></i> ${act.msg_jail}</div>`;
                    }

                    // Update parking pot display globally
                    if (res.parking_pot !== undefined) {
                        updateParkingPotDisplay(res.parking_pot);
                    }

                    // Show action modal
                    if (act.type === 'jail_stay') {
                        // Tertahan di penjara karena dadu tidak kembar
                        showModal('<i class="fa-solid fa-handcuffs text-rose-500 mr-1"></i> Tertahan di Penjara!', act.msg, 'warning', '#ef4444');
                    } else if (act.type === 'free_parking_win') {
                        Swal.fire({
                            title: `<span style="font-size:2rem">🅿️</span> PARKIR BEBAS!`,
                            html: `<div style="background:linear-gradient(135deg,#065f46,#064e3b);border-radius:16px;padding:20px;margin:10px 0;border:2px solid #10b981;">
                                <div style="font-size:2.5rem;font-weight:900;color:#34d399;font-family:monospace;">
                                    +Rp ${parseInt(act.amount).toLocaleString('id-ID')}
                                </div>
                                <div style="color:#6ee7b7;font-size:0.85rem;margin-top:6px;">Pot Parkir Bebas berhasil kamu ambil!</div>
                            </div>
                            <div style="color:#94a3b8;font-size:0.8rem;margin-top:8px;">Pot kini direset. Pajak berikutnya mulai mengisi pot lagi.</div>`,
                            background: '#0f172a', color: '#f1f5f9',
                            confirmButtonText: '<i class="fa-solid fa-coins mr-1"></i> Sip, Terima kasih!',
                            confirmButtonColor: '#10b981',
                        });
                    } else if (act.type === 'free_parking_empty') {
                        Swal.fire({
                            title: `<span style="font-size:2rem">🅿️</span> Parkir Bebas`,
                            html: `<div style="color:#64748b;font-size:1.2rem;margin:15px 0;">Pot Parkir Bebas masih <b style="color:#f1f5f9">kosong</b>.</div>
                            <div style="color:#94a3b8;font-size:0.85rem;">Nikmati istirahat — ketika pemain lain kena pajak, pot akan terisi!</div>`,
                            background: '#0f172a', color: '#f1f5f9',
                            confirmButtonText: '<i class="fa-solid fa-parking mr-1"></i> Oke!',
                            confirmButtonColor: '#3b82f6',
                        });
                    } else if (act.type === 'first_lap_info') {
                        // Informasi putaran pertama belum boleh beli properti
                        const imgHtml = act.image 
                            ? `<img src="${BASEURL}/${act.image}" style="width:100%;height:115px;object-fit:cover;border-radius:12px;margin-bottom:10px">`
                            : '';
                        Swal.fire({
                            title: `<i class="fa-solid fa-flag-checkered text-amber-400 mr-2"></i> Putaran Pertama!`,
                            html: `${jailBanner}${imgHtml}<div class="text-lg font-black text-white mb-1">${act.name}</div><div class="text-slate-300 text-sm leading-relaxed">${act.msg}</div>`,
                            background: '#0f172a', color: '#f1f5f9',
                            confirmButtonColor: '#f59e0b',
                            confirmButtonText: '<i class="fa-solid fa-check mr-1"></i> Mengerti'
                        });
                    } else if (act.type === 'card') {
                        const isKesempatan = act.card_type === 'kesempatan';
                        showCardAnimation(
                            isKesempatan ? 'Kesempatan' : 'Dana Umum',
                            act.card_text || 'Baca kartu fisikmu.',
                            act.card_image || null
                        ).then(() => {
                            fetch(BASEURL + '/player/apiClearCard', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                                body: `player_id=${player.id}`
                            });
                        });
                    } else if (act.type === 'buy') {
                        const imgHtml = act.image 
                            ? `<img src="${BASEURL}/${act.image}" style="width:100%;height:120px;object-fit:cover;border-radius:12px;margin-bottom:10px">`
                            : `<div style="height:80px;display:flex;align-items:center;justify-content:center;background:#1e293b;border-radius:12px;margin-bottom:10px"><i class="fa-solid fa-building" style="font-size:2.5rem;color:#475569"></i></div>`;
                        Swal.fire({
                            title: `<i class="fa-solid fa-building mr-1"></i> Beli Properti?`,
                            html: `${jailBanner}${imgHtml}<b>${act.name}</b><br><span style="font-size:1.3rem;font-weight:900;color:#10b981">Rp ${parseInt(act.price).toLocaleString('id-ID')}</span>`,
                            background: '#0f172a', color: '#f1f5f9',
                            showCancelButton: true,
                            confirmButtonText: '<i class="fa-solid fa-handshake mr-1"></i> Beli!',
                            cancelButtonText: 'Lewati',
                            confirmButtonColor: '#10b981',
                            cancelButtonColor: '#475569',
                        }).then(result => {
                            if (result.isConfirmed) {
                                fetch(BASEURL + '/player/apiBuy', {
                                    method: 'POST',
                                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                                    body: `player_id=${player.id}&cell_index=${res.position}`
                                }).then(r => r.json()).then(buyRes => {
                                    if (buyRes.status === 'success') {
                                        animateMoney(buyRes.money);
                                        showModal('<i class="fa-solid fa-check mr-1"></i> Berhasil!', buyRes.msg, 'success', '#10b981');
                                    } else {
                                        showModal('Gagal', buyRes.msg, 'error', '#ef4444');
                                    }
                                });
                            }
                        });
                    } else if (act.type === 'upgrade') {
                        // Card Kota: Opsi Tingkat Upgrade Langsung
                        let optionsHtml = '';
                        (act.upgrade_options || []).forEach(opt => {
                            const canAfford = opt.can_afford;
                            const iconLvl = opt.level === 5 ? 'fa-hotel text-amber-400' : 'fa-house text-emerald-400';
                            const tagMax = opt.is_max ? '<span class="bg-amber-500/20 text-amber-300 text-[10px] font-black px-1.5 py-0.5 rounded border border-amber-400/40 ml-1">MAX</span>' : '';
                            optionsHtml += `
                                <label class="flex items-center justify-between p-3 mb-2 rounded-xl border border-white/10 bg-slate-800/80 ${canAfford ? 'cursor-pointer hover:border-blue-400/80 hover:bg-slate-800' : 'opacity-40 cursor-not-allowed'} transition-all">
                                    <div class="flex items-center gap-3">
                                        <input type="radio" name="selected_upgrade_level" value="${opt.level}" ${canAfford ? '' : 'disabled'} class="w-4 h-4 text-blue-500 focus:ring-blue-500" ${opt.level === (act.current_level + 1) && canAfford ? 'checked' : ''}>
                                        <div class="text-left">
                                            <div class="font-black text-white text-sm flex items-center gap-1.5">
                                                <i class="fa-solid ${iconLvl}"></i>
                                                ${opt.name} ${tagMax}
                                            </div>
                                            <div class="text-[11px] text-emerald-400 font-mono">Sewa: Rp ${parseInt(opt.rent).toLocaleString('id-ID')}</div>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-xs font-black font-mono ${canAfford ? 'text-blue-400' : 'text-rose-400'}">
                                            Rp ${parseInt(opt.cost).toLocaleString('id-ID')}
                                        </div>
                                        ${!canAfford ? '<div class="text-[9px] text-rose-400 font-bold">Uang kurang</div>' : ''}
                                    </div>
                                </label>
                            `;
                        });

                        const imgHtml = act.image 
                            ? `<img src="${BASEURL}/${act.image}" style="width:100%;height:115px;object-fit:cover;border-radius:14px;margin-bottom:10px;border:1px solid rgba(255,255,255,0.1)">`
                            : '';

                        Swal.fire({
                            title: `<i class="fa-solid fa-city mr-1.5 text-blue-400"></i> ${act.name}`,
                            html: `
                                ${imgHtml}
                                <div class="text-xs text-slate-300 mb-3 flex items-center justify-between bg-slate-800/90 px-3 py-2 rounded-xl border border-white/10">
                                    <span>Status Saat Ini: <b class="text-white">${act.current_level_name}</b></span>
                                    <span>Tingkat: <b class="text-amber-400">${act.current_level}</b></span>
                                </div>
                                <div class="text-left text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Pilih Peningkatan:</div>
                                <div class="max-h-56 overflow-y-auto pr-1">
                                    ${optionsHtml}
                                </div>
                            `,
                            background: '#0f172a',
                            color: '#f1f5f9',
                            showCancelButton: true,
                            confirmButtonText: '<i class="fa-solid fa-arrow-up mr-1"></i> Upgrade Sekarang',
                            cancelButtonText: 'Lewati',
                            confirmButtonColor: '#3b82f6',
                            cancelButtonColor: '#475569',
                            preConfirm: () => {
                                const checked = document.querySelector('input[name="selected_upgrade_level"]:checked');
                                if (!checked) {
                                    Swal.showValidationMessage('Pilih tingkat yang ingin diupgrade!');
                                    return false;
                                }
                                return checked.value;
                            }
                        }).then(result => {
                            if (result.isConfirmed && result.value) {
                                const targetLvl = result.value;
                                fetch(BASEURL + '/player/apiBuy', {
                                    method: 'POST',
                                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                                    body: `player_id=${player.id}&cell_index=${res.position}&target_level=${targetLvl}`
                                }).then(r => r.json()).then(buyRes => {
                                    if (buyRes.status === 'success') {
                                        animateMoney(buyRes.money);
                                        showModal('<i class="fa-solid fa-check mr-1"></i> Berhasil!', buyRes.msg, 'success', '#3b82f6');
                                    } else {
                                        showModal('Gagal', buyRes.msg, 'error', '#ef4444');
                                    }
                                });
                            }
                        });
                    } else if (act.type === 'rent') {
                        const imgHtml = act.image 
                            ? `<img src="${BASEURL}/${act.image}" style="width:100%;height:120px;object-fit:cover;border-radius:12px;margin-bottom:10px">`
                            : `<div style="height:80px;display:flex;align-items:center;justify-content:center;background:#1e293b;border-radius:12px;margin-bottom:10px"><i class="fa-solid fa-building" style="font-size:2.5rem;color:#475569"></i></div>`;
                        Swal.fire({
                            title: `<i class="fa-solid fa-money-bill-wave mr-1 text-orange-500"></i> Bayar Sewa!`,
                            html: `${jailBanner}${imgHtml}
                                   <div style="font-size:1.1rem;font-weight:bold;color:#f1f5f9">${act.name}</div>
                                   <div style="color:#94a3b8;font-size:0.9rem;margin-bottom:10px">${act.level_name} milik <b>${act.owner}</b></div>
                                   <div style="font-size:1.4rem;font-weight:900;color:#ef4444">- Rp ${parseInt(act.amount).toLocaleString('id-ID')}</div>`,
                            background: '#0f172a', color: '#f1f5f9',
                            confirmButtonText: '<i class="fa-solid fa-check"></i> Mengerti',
                            confirmButtonColor: '#f97316'
                        });
                    } else if (act.type === 'tax') {
                        showModal('<i class="fa-solid fa-landmark mr-1"></i> Bayar Pajak!', act.msg, 'warning', '#f97316');
                    } else if (act.type === 'jail') {
                        showModal('<i class="fa-solid fa-handcuffs mr-1 text-rose-500"></i> DITANGKAP!', act.msg || 'Masuk penjara!', 'error', '#ef4444');
                    } else if (act.pass_go) {
                        showModal('<i class="fa-solid fa-star mr-1"></i> Melewati Start!', `Terima bonus <b class="text-yellow-400">Rp ${parseInt(act.pass_go_bonus).toLocaleString('id-ID')}</b>!`, 'success', '#eab308');
                    } else if (act.jail_auto_freed) {
                        showModal('<i class="fa-solid fa-lock-open text-amber-400 mr-1"></i> Bebas Otomatis dari Penjara!', act.msg_jail, 'info', '#f59e0b');
                    } else if (act.jail_freed) {
                        showModal('<i class="fa-solid fa-dice text-emerald-400 mr-1"></i> Dadu Kembar! Bebas dari Penjara', act.msg_jail, 'success', '#10b981');
                    } else {
                        showModal(title, text, 'info', color);
                    }

                    // Tampilkan tombol Selesai Giliran
                    endTurnBtn.classList.remove('hidden');
                    endTurnBtn.disabled = false;
                    endTurnBtn.innerHTML = '<i class="fa-solid fa-flag-checkered mr-2"></i>Selesai Giliran';
                    setTimeout(() => { endTurnBtn.scrollIntoView({ behavior: 'smooth', block: 'center' }); }, 300);

                }).catch(() => { btn.disabled = false; });

            }, 800);
        });

        // Selesai Giliran
        endTurnBtn.addEventListener('click', function() {
            const btn = endTurnBtn; // Simpan referensi — jangan pakai 'this' di dalam .then()
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-2"></i>Memproses...';

            fetch(BASEURL + '/player/apiEndTurn', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `id=${player.id}`
            })
            .then(r => r.json())
            .then(res => {
                if (res.status === 'success') {
                    btn.innerHTML = '<i class="fa-solid fa-check mr-2"></i>Selesai!';
                    hasRolled = false;
                    isTurn = false;
                    showModal('<i class="fa-solid fa-check mr-1"></i> Giliran Selesai!', 'Menunggu giliran berikutnya...', 'info', '#3b82f6');
                    setTimeout(() => {
                        btn.classList.add('hidden');
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fa-solid fa-flag-checkered mr-2"></i>Selesai Giliran';
                    }, 1200);
                } else {
                    // Gagal — kembalikan tombol
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fa-solid fa-flag-checkered mr-2"></i>Selesai Giliran';
                    showModal('<i class="fa-solid fa-triangle-exclamation mr-1"></i> Gagal', res.msg || 'Coba lagi.', 'warning', '#f59e0b');
                }
            })
            .catch(() => {
                // Network error — selalu kembalikan tombol
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-flag-checkered mr-2"></i>Selesai Giliran';
                showModal('<i class="fa-solid fa-wifi mr-1"></i> Koneksi Error', 'Periksa jaringan lalu coba lagi.', 'error', '#ef4444');
            });
        });

        // Polling status giliran (setiap 2 detik)
        setInterval(() => {
            fetch(BASEURL + '/player/apiStatus/' + player.id)
                .then(r => r.json())
                .then(status => {
                    const wasMyTurn = isTurn;
                    isTurn = status.is_turn;
                    const serverHasRolled = status.has_rolled;

                    // Update uang dengan animasi count jika berubah (misal menerima pembayaran sewa)
                    if (status.money !== undefined && parseInt(status.money) !== currentDisplayedMoney) {
                        animateMoney(status.money);
                    }

                    // Check if properties changed (bought new property or upgraded house)
                    if (status.properties) {
                        const newHash = JSON.stringify(status.properties);
                        if (newHash !== currentPropsHash) {
                            location.reload();
                            return;
                        }
                    }

                    // Show card popup to non-rolling players (spectators)
                    if (!isTurn && status.active_card) {
                        const cardText = status.active_card.text;
                        if (cardText !== lastShownCardText) {
                            lastShownCardText = cardText;
                            showCardAnimation(
                                status.active_card.type === 'kesempatan' ? 'Kesempatan' : 'Dana Umum',
                                cardText,
                                null
                            );
                        }
                    } else if (!status.active_card) {
                        lastShownCardText = null;
                    }

                    if (!wasMyTurn && isTurn) {
                        // Giliran baru dimulai!
                        hasRolled = false;
                        isRolling = false;
                        rollBtn.disabled = false;
                        rollBtn.classList.remove('opacity-30');
                        rollBtn.classList.remove('hidden'); // KEMBALIKAN TOMBOL
                        rollBtn.style.pointerEvents = 'auto'; // KEMBALIKAN POINTER EVENTS
                        endTurnBtn.classList.add('hidden');
                        showModal('<i class="fa-solid fa-dice mr-1"></i> Giliran Kamu!', 'Sekarang giliranmu! Lempar dadu.', 'success', '#22c55e');
                    }

                    // Update badge status
                    const badge = document.getElementById('turn-badge');
                    if (badge) {
                        if (status.in_jail) {
                            const jt = parseInt(status.jail_turns || 0);
                            badge.className = 'mx-6 mt-3 rounded-2xl px-4 py-3 flex items-center gap-3 shadow-lg bg-rose-950/70 border border-rose-500/50 shadow-rose-950/50';
                            badge.innerHTML = `<i class="fa-solid fa-handcuffs text-2xl text-rose-400 animate-pulse"></i><div><div class="text-rose-400 font-black text-base flex items-center gap-2"><span>TERTAHAN DI PENJARA!</span><span class="text-xs bg-rose-500/30 text-rose-300 border border-rose-500/50 px-2 py-0.5 rounded-full font-bold">Percobaan ${jt}/3</span></div><div class="text-rose-300/80 text-xs">Dadu KEMBAR untuk bebas • Putaran ke-4 otomatis bebas</div></div>`;
                            if (isTurn && !serverHasRolled) {
                                rollBtn.disabled = false;
                                rollBtn.classList.remove('opacity-30');
                            }
                        } else if (isTurn) {
                            badge.className = 'mx-6 mt-3 rounded-2xl px-4 py-3 flex items-center gap-3 shadow-lg bg-emerald-500/20 border border-emerald-500/40 shadow-emerald-950/50';
                            badge.innerHTML = '<i class="fa-solid fa-crown text-2xl text-amber-400 animate-pulse"></i><div><div class="text-emerald-400 font-black text-base">GILIRAN KAMU!</div><div class="text-emerald-400/60 text-xs">Lempar dadu sekarang</div></div>';
                        } else {
                            badge.className = 'mx-6 mt-3 rounded-2xl px-4 py-3 flex items-center gap-3 shadow-lg bg-slate-800/60 border border-white/10';
                            badge.innerHTML = '<i class="fa-regular fa-clock text-2xl text-slate-500"></i><div><div class="text-slate-400 font-black text-base">MENUNGGU...</div><div class="text-slate-600 text-xs">Bukan giliranmu saat ini</div></div>';
                            // Nonaktifkan tombol jika bukan giliran
                            rollBtn.disabled = true;
                            rollBtn.classList.add('opacity-30');
                        }
                    }

                    // Update putaran (laps) pada kartu kredit
                    const lapBadge = document.getElementById('lap-counter-badge');
                    if (lapBadge && status.laps !== undefined) {
                        lapBadge.innerHTML = `<i class="fa-solid fa-flag-checkered mr-1"></i>Putaran ${(parseInt(status.laps || 0) + 1)}`;
                    }
                }).catch(() => {});
        }, 2000);
    });
</script>

<!-- Fallback Modal -->
<div id="fallback-modal" class="hidden fixed inset-0 bg-black/90 z-[999] items-center justify-center p-6">
    <div class="bg-slate-900 border border-white/10 rounded-2xl p-8 w-full max-w-sm text-center shadow-2xl">
        <h2 id="fm-title" class="text-2xl font-black text-white mb-4"></h2>
        <p id="fm-text" class="text-slate-300 mb-6"></p>
        <button onclick="document.getElementById('fallback-modal').classList.add('hidden');document.getElementById('fallback-modal').classList.remove('flex');"
            class="px-8 py-3 bg-blue-500 text-white font-bold rounded-xl w-full">Oke</button>
    </div>
</div>

<!-- ===== PROPERTY DETAIL MODAL ===== -->
<div id="prop-detail-modal" class="hidden fixed inset-0 bg-black/80 backdrop-blur-sm z-[1000] items-end justify-center"
     onclick="if(event.target===this)closePropModal()">
    <div class="prop-modal-inner w-full max-w-sm bg-slate-900 border border-white/10 rounded-t-3xl shadow-2xl overflow-hidden pb-safe">

        <!-- Drag Handle -->
        <div class="flex justify-center pt-3 pb-1">
            <div class="w-10 h-1 rounded-full bg-white/20"></div>
        </div>

        <!-- Header: Image -->
        <div id="pm-img-wrap" class="w-full h-40 bg-slate-800 relative overflow-hidden">
            <img id="pm-img" src="" class="w-full h-full object-cover" style="display:none">
            <div id="pm-img-placeholder" class="absolute inset-0 flex items-center justify-center">
                <i class="fa-solid fa-city text-5xl text-slate-600"></i>
            </div>
            <!-- Color Overlay Bar -->
            <div id="pm-color-bar" class="absolute bottom-0 left-0 right-0 h-1.5"></div>
        </div>

        <!-- Body -->
        <div class="px-5 pt-4 pb-6">

            <!-- Name + Level Badge -->
            <div class="flex items-start justify-between gap-3 mb-3">
                <div>
                    <div class="text-[10px] uppercase tracking-wider text-slate-500 font-bold mb-0.5">Aset Properti</div>
                    <div id="pm-name" class="text-xl font-black text-white leading-tight"></div>
                </div>
                <div id="pm-level-badge" class="shrink-0 px-3 py-1 rounded-full text-xs font-black border text-center mt-1"></div>
            </div>

            <!-- Harga Beli + Harga Upgrade -->
            <div class="grid grid-cols-2 gap-2 mb-4">
                <div class="bg-slate-800/70 rounded-xl p-3 border border-white/5">
                    <div class="text-[10px] text-slate-500 uppercase tracking-wider font-bold mb-1">Harga Beli</div>
                    <div id="pm-price" class="text-sm font-black text-amber-400"></div>
                </div>
                <div class="bg-slate-800/70 rounded-xl p-3 border border-white/5">
                    <div class="text-[10px] text-slate-500 uppercase tracking-wider font-bold mb-1">Harga Upgrade</div>
                    <div id="pm-house-price" class="text-sm font-black text-blue-400"></div>
                </div>
            </div>

            <!-- Sewa Tiap Tingkat -->
            <div class="bg-slate-800/50 rounded-2xl border border-white/5 overflow-hidden mb-4">
                <div class="px-3 py-2 border-b border-white/5">
                    <span class="text-[10px] uppercase tracking-wider text-slate-500 font-bold">Tabel Sewa per Tingkat</span>
                </div>
                <div id="pm-rent-table" class="divide-y divide-white/5"></div>
            </div>

            <!-- Houses visual -->
            <div id="pm-houses-wrap" class="flex gap-1.5 items-center flex-wrap mb-4 hidden">
                <span class="text-[10px] text-slate-500 uppercase tracking-wider font-bold mr-1">Kondisi:</span>
                <div id="pm-houses-icons" class="flex gap-1 flex-wrap"></div>
            </div>

            <!-- Close Button -->
            <button onclick="closePropModal()"
                class="w-full py-3.5 bg-slate-700 hover:bg-slate-600 text-white font-black rounded-2xl transition active:scale-95">
                <i class="fa-solid fa-xmark mr-2"></i>Tutup
            </button>
        </div>
    </div>
</div>

<script>
const COLOR_HEX = {
    red:'#ef4444', blue:'#3b82f6', green:'#10b981', yellow:'#eab308',
    purple:'#a855f7', orange:'#f97316', pink:'#ec4899', teal:'#14b8a6',
    brown:'#a16207', cyan:'#06b6d4', slate:'#64748b', indigo:'#6366f1'
};

function showPropertyDetail(prop) {
    const modal = document.getElementById('prop-detail-modal');
    const accentColor = COLOR_HEX[prop.color] || '#64748b';

    // Image
    const img = document.getElementById('pm-img');
    const ph  = document.getElementById('pm-img-placeholder');
    if (prop.image) {
        img.src = prop.image;
        img.style.display = 'block';
        ph.style.display = 'none';
    } else {
        img.style.display = 'none';
        ph.style.display = 'flex';
    }
    document.getElementById('pm-color-bar').style.background = accentColor;

    // Name
    document.getElementById('pm-name').textContent = prop.name;

    // Level badge
    const levelBadge = document.getElementById('pm-level-badge');
    const levelNames = [prop.level1_name, prop.level2_name, prop.level3_name, prop.level4_name, prop.level5_name];
    const currentLevelName = prop.houses > 0 ? (levelNames[prop.houses - 1] || 'Level ' + prop.houses) : 'Tanah Kosong';
    levelBadge.textContent = currentLevelName;
    if (prop.houses === 0) {
        levelBadge.className = 'shrink-0 px-3 py-1 rounded-full text-xs font-black border text-center mt-1 bg-slate-700 text-slate-300 border-slate-500';
    } else if (prop.houses >= 5) {
        levelBadge.className = 'shrink-0 px-3 py-1 rounded-full text-xs font-black border text-center mt-1 bg-red-500/20 text-red-400 border-red-500/50';
    } else {
        levelBadge.className = 'shrink-0 px-3 py-1 rounded-full text-xs font-black border text-center mt-1 bg-emerald-500/20 text-emerald-400 border-emerald-500/50';
    }

    // Prices
    document.getElementById('pm-price').textContent = prop.price ? 'Rp ' + parseInt(prop.price).toLocaleString('id-ID') : '-';
    document.getElementById('pm-house-price').textContent = prop.house_price ? 'Rp ' + parseInt(prop.house_price).toLocaleString('id-ID') + ' /lvl' : '-';

    // Rent table
    const rentTable = document.getElementById('pm-rent-table');
    rentTable.innerHTML = '';
    const levels = [
        { label: 'Tanah Kosong', rent: prop.level0_rent, icon: 'fa-land-mine-on', houses: 0 },
        { label: prop.level1_name || 'Rumah 1',  rent: prop.level1_rent, icon: 'fa-house', houses: 1 },
        { label: prop.level2_name || 'Rumah 2',  rent: prop.level2_rent, icon: 'fa-house', houses: 2 },
        { label: prop.level3_name || 'Rumah 3',  rent: prop.level3_rent, icon: 'fa-house', houses: 3 },
        { label: prop.level4_name || 'Rumah 4',  rent: prop.level4_rent, icon: 'fa-house', houses: 4 },
        { label: prop.level5_name || 'Hotel',    rent: prop.level5_rent, icon: 'fa-hotel',  houses: 5 },
    ];
    levels.forEach(lvl => {
        if (!lvl.rent && lvl.houses > 0) return;
        const isCurrent = (lvl.houses === prop.houses);
        const div = document.createElement('div');
        div.className = 'flex items-center justify-between px-3 py-2' + (isCurrent ? ' bg-white/10' : '');
        const iconColor = lvl.houses === 5 ? 'text-red-400' : (lvl.houses > 0 ? 'text-emerald-400' : 'text-slate-500');
        const rentColor = isCurrent ? 'text-amber-300 font-black' : 'text-slate-300 font-bold';
        div.innerHTML = `
            <div class="flex items-center gap-2">
                <i class="fa-solid ${lvl.icon} text-xs ${iconColor}"></i>
                <span class="text-xs text-slate-300">${lvl.label}</span>
                ${isCurrent ? '<span class="text-[9px] bg-amber-500/20 text-amber-400 border border-amber-500/30 px-1.5 rounded-full font-black">AKTIF</span>' : ''}
            </div>
            <span class="text-xs ${rentColor}">Rp ${parseInt(lvl.rent||0).toLocaleString('id-ID')}</span>
        `;
        rentTable.appendChild(div);
    });

    // Houses icons
    if (prop.houses > 0) {
        const housesWrap = document.getElementById('pm-houses-wrap');
        housesWrap.classList.remove('hidden');
        const iconsDiv = document.getElementById('pm-houses-icons');
        iconsDiv.innerHTML = '';
        for (let i = 0; i < prop.houses; i++) {
            const isHotel = i === 4;
            const ic = document.createElement('i');
            ic.className = `fa-solid ${isHotel ? 'fa-hotel text-red-400' : 'fa-house text-emerald-400'} text-sm`;
            iconsDiv.appendChild(ic);
            if (isHotel) break;
        }
    } else {
        document.getElementById('pm-houses-wrap').classList.add('hidden');
    }

    // Show
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closePropModal() {
    const modal = document.getElementById('prop-detail-modal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

async function useJailCard() {
    const playerId = <?= (int)$data['player']['id'] ?>;
    const btn = document.querySelector('button[onclick="useJailCard()"]');
    if (btn) { btn.disabled = true; btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Memproses...'; }

    try {
        const res = await fetch('<?= BASEURL ?>/player/apiUseJailCard', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'player_id=' + playerId
        });
        const data = await res.json();

        if (data.status === 'success') {
            Swal.fire({
                icon: 'success',
                title: 'Bebas!',
                text: data.msg,
                background: '#0f172a',
                color: '#f8fafc',
                confirmButtonText: 'Lempar Dadu!',
                confirmButtonColor: '#10b981',
            }).then(() => location.reload());
        } else {
            Swal.fire({ icon: 'error', title: 'Gagal', text: data.msg, background: '#0f172a', color: '#f8fafc' });
            if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fa-solid fa-ticket-simple"></i> Pakai Kartu Bebas Penjara'; }
        }
    } catch(e) {
        Swal.fire({ icon: 'error', title: 'Error', text: 'Gagal menghubungi server', background: '#0f172a', color: '#f8fafc' });
        if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fa-solid fa-ticket-simple"></i> Pakai Kartu Bebas Penjara'; }
    }
}
</script>

