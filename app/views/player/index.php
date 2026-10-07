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
body { height: 100dvh; overflow: hidden; display: flex; flex-direction: column; background-color: #020617; } /* bg-slate-950 */
#player-container { flex: 1; display: flex; flex-direction: column; overflow-y: auto; overflow-x: hidden; scrollbar-width: none; -ms-overflow-style: none; }
#player-container::-webkit-scrollbar { display: none; }

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

<div id="player-container">
<!-- Top Action Bar -->
<div class="px-5 pt-4 pb-1 flex items-center justify-between shrink-0">
    <div class="flex items-center gap-2">
        <div class="w-8 h-8 rounded-full bg-slate-800 border border-white/10 flex items-center justify-center shadow-lg">
            <i class="fa-solid fa-user text-slate-400 text-xs"></i>
        </div>
        <span class="font-black text-white text-sm tracking-wide"><?= htmlspecialchars($data['player']['name']) ?></span>
    </div>
    <button onclick="showRulebook()" class="flex items-center gap-2 bg-indigo-500/20 hover:bg-indigo-500/30 text-indigo-300 px-3 py-1.5 rounded-full border border-indigo-400/30 transition-all active:scale-95">
        <i class="fa-solid fa-book-open text-[10px]"></i>
        <span class="text-[10px] font-black uppercase tracking-wider">Aturan</span>
    </button>
</div>

<!-- Credit Card Container -->
<div class="px-5 pt-2 pb-2">
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

        <!-- Pejabat Negara Indicator -->
        <div id="pejabat-bar" class="relative z-10 mt-2 flex items-center gap-2 bg-black/25 rounded-xl px-3 py-1.5 border <?= (!empty($data['pejabat']) && $data['pejabat']['id'] == $data['player']['id']) ? 'border-amber-500/50 bg-amber-500/10' : 'border-white/10' ?> transition-all duration-500">
            <span class="text-lg">👑</span>
            <div class="flex-1">
                <div class="text-[9px] font-bold uppercase tracking-wider text-amber-400/70">Status Pejabat Negara</div>
                <div id="pejabat-val" class="text-sm font-black text-amber-300">
                    <?php 
                    if (!empty($data['pejabat'])) {
                        echo ($data['pejabat']['id'] == $data['player']['id']) ? 'ANDA MENJABAT' : htmlspecialchars($data['pejabat']['name']);
                    } else {
                        echo 'KOSONG';
                    }
                    ?>
                </div>
            </div>
            <?php if (!empty($data['pejabat']) && $data['pejabat']['id'] == $data['player']['id']): ?>
            <div id="pejabat-pulse" class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"></div>
            <?php else: ?>
            <div id="pejabat-pulse" class="w-2 h-2 rounded-full bg-transparent"></div>
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
        $bribeCost = (int)($data['settings']['jail_bribe_cost']['setting_value'] ?? 5000);
        if ($isTurn && empty($data['player']['has_rolled'])): ?>
            <div class="flex gap-2 mt-2 w-full">
            <?php if ($freeCards > 0): ?>
                <button onclick="useJailCard()" class="flex-1 bg-emerald-500 hover:bg-emerald-600 border border-emerald-400 text-white font-bold py-2 px-2 rounded-lg text-[10px] transition leading-tight shadow-[0_0_15px_rgba(16,185,129,0.3)]">
                    <i class="fa-solid fa-ticket-simple mr-1"></i> Kartu (<?= $freeCards ?>)
                </button>
            <?php endif; ?>
                <button onclick="bribeJail()" class="flex-1 bg-amber-500 hover:bg-amber-600 border border-amber-400 text-white font-bold py-2 px-2 rounded-lg text-[10px] transition leading-tight shadow-[0_0_15px_rgba(245,158,11,0.3)]">
                    <i class="fa-solid fa-money-bill-wave mr-1"></i> Suap (Rp <?= number_format($bribeCost, 0, ',', '.') ?>)
                </button>
            </div>
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

    <!-- Action Buttons Row -->
    <div class="flex w-full max-w-sm gap-3">
        <!-- Roll Button -->
        <button id="mobile-roll-btn"
            <?= !$data['player']['is_turn'] ? 'disabled' : '' ?>
            class="flex-1 py-4 bg-gradient-to-br <?= $bgGrad ?> text-white rounded-[1.5rem] font-black text-lg md:text-xl tracking-wide shadow-[0_10px_40px_rgba(0,0,0,0.6)] active:scale-90 transition-all border border-white/20 disabled:opacity-30 disabled:cursor-not-allowed disabled:active:scale-100 relative overflow-hidden flex items-center justify-center">
            <div class="absolute inset-0 bg-white/20 transform -translate-x-full rounded-[1.5rem]"></div>
            <i class="fa-solid fa-dice mr-2 drop-shadow-md"></i> <span>LEMPAR</span>
        </button>

        <!-- End Turn Button -->
        <button id="end-turn-btn" class="hidden flex-1 py-4 bg-emerald-500 hover:bg-emerald-600 text-white rounded-[1.5rem] font-black text-lg md:text-xl tracking-wide shadow-[0_10px_30px_rgba(16,185,129,0.4)] active:scale-90 transition-all border border-emerald-400/30 flex items-center justify-center">
            <i class="fa-solid fa-check mr-2"></i> <span>SELESAI</span>
        </button>
    </div>
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
                    <span id="prop-count-badge"><?= count($data['properties']) ?></span> kota dimiliki
                </span>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <button onclick="openTradeModal()" class="flex items-center gap-1 text-white text-[10px] font-bold bg-indigo-500 hover:bg-indigo-600 px-3 py-1.5 rounded-lg border border-indigo-400/50 shadow-[0_0_15px_rgba(99,102,241,0.3)] transition-all">
                <i class="fa-solid fa-handshake"></i> Bursa
            </button>
            <div class="flex items-center gap-1 text-slate-500 text-[10px] font-bold bg-slate-800/60 px-2 py-1.5 rounded-lg border border-white/5" id="prop-swipe-hint" style="<?= empty($data['properties']) ? 'display:none;' : '' ?>">
                <i class="fa-solid fa-hand-point-left text-[9px]"></i> Geser
            </div>
        </div>
    </div>

    <div id="properties-slider-container">
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
                'level1_price' => (int)($boardCell['level1_price'] ?? 0),
                'level2_price' => (int)($boardCell['level2_price'] ?? 0),
                'level3_price' => (int)($boardCell['level3_price'] ?? 0),
                'level4_price' => (int)($boardCell['level4_price'] ?? 0),
                'level5_price' => (int)($boardCell['level5_price'] ?? 0),
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
    </div> <!-- /#properties-slider-container -->
</div>

<style>
.scrollbar-none::-webkit-scrollbar { display: none; }
</style>
</div> <!-- /#player-container -->

<script>
    function showRulebook() {
        const rulesHtml = `
            <div style="text-align: left; font-size: 0.9rem; line-height: 1.6; color: #cbd5e1; max-height: 60vh; overflow-y: auto; padding-right: 10px;" class="custom-scroll">
                
                <div style="margin-bottom: 20px;">
                    <div style="font-weight: 900; color: #f8fafc; font-size: 1.1rem; display: flex; align-items: center; gap: 8px; margin-bottom: 5px;">
                        <i class="fa-solid fa-flag-checkered text-blue-400"></i> Aturan Putaran Pertama
                    </div>
                    Kamu <b style="color: #60a5fa">tidak boleh membeli properti</b> sebelum berhasil melewati petak START (menyelesaikan 1 putaran penuh). Putaran pertama hanya untuk adaptasi!
                </div>

                <div style="margin-bottom: 20px;">
                    <div style="font-weight: 900; color: #f8fafc; font-size: 1.1rem; display: flex; align-items: center; gap: 8px; margin-bottom: 5px;">
                        <i class="fa-solid fa-crown text-amber-400"></i> Pejabat Negara
                    </div>
                    Pemain yang mendarat tepat di petak <b style="color: #fcd34d">Pejabat Negara</b> akan menjabat sebagai Pejabat Negara. Selama menjabat, <b>semua uang pajak</b> (Pajak Biasa & Mewah) dari semua pemain akan otomatis masuk ke <b style="color: #fcd34d">rekening Pejabat</b>! Posisi ini bisa <b style="color: #fb7185">direbut</b> jika pemain lain mendarat di petak yang sama.
                </div>

                <div style="margin-bottom: 20px;">
                    <div style="font-weight: 900; color: #f8fafc; font-size: 1.1rem; display: flex; align-items: center; gap: 8px; margin-bottom: 5px;">
                        <i class="fa-solid fa-house-chimney-crack text-amber-400"></i> Denda & Sewa
                    </div>
                    Jika kamu mendarat di properti milik lawan, saldo rekeningmu akan <b style="color: #fb7185">otomatis terpotong</b> untuk membayar sewa. Hati-hati jangan sampai bangkrut!
                </div>

                <div style="margin-bottom: 10px;">
                    <div style="font-weight: 900; color: #f8fafc; font-size: 1.1rem; display: flex; align-items: center; gap: 8px; margin-bottom: 5px;">
                        <i class="fa-solid fa-handcuffs text-rose-400"></i> Masuk Penjara
                    </div>
                    Masuk penjara menahanmu maksimal <b style="color: #f8fafc">3 putaran</b>. Kamu bisa keluar jika mendapat lemparan dadu kembar, memakai Kartu Bebas Penjara, atau membayar denda di putaran ke-3.
                </div>

            </div>
            <style>
                .custom-scroll::-webkit-scrollbar { width: 6px; }
                .custom-scroll::-webkit-scrollbar-track { background: rgba(255,255,255,0.05); border-radius: 10px; }
                .custom-scroll::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.2); border-radius: 10px; }
            </style>
        `;

        Swal.fire({
            title: '<div style="font-weight: 900; font-size: 1.5rem; letter-spacing: 1px;"><i class="fa-solid fa-book-open text-indigo-400 mr-2"></i> PANDUAN GAME</div>',
            html: rulesHtml,
            background: 'linear-gradient(145deg, #1e293b, #0f172a)',
            color: '#f8fafc',
            width: '24em',
            confirmButtonText: '<i class="fa-solid fa-check"></i> Mengerti!',
            confirmButtonColor: '#6366f1',
            customClass: {
                popup: 'rounded-3xl border border-white/10 shadow-2xl',
                title: 'border-b border-white/10 pb-4 mb-2'
            }
        });
    }

    // Global variables accessible by inline onclick handlers
    let player = <?= json_encode($data['player']); ?>;
    let board = <?= json_encode($data['board']); ?>;
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

    // Global helper
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



    function useJailCard() {
        if (!isTurn) { showModal('Bukan Giliran Kamu', 'Tunggu giliranmu!', 'warning'); return; }
        Swal.fire({
            title: '<i class="fa-solid fa-ticket-simple text-emerald-400 mr-2"></i> Pakai Kartu Bebas?',
            html: 'Gunakan <b class="text-emerald-400">Kartu Bebas Penjara</b> untuk langsung keluar dari penjara tanpa membayar.',
            background: '#0f172a', color: '#f1f5f9',
            showCancelButton: true,
            confirmButtonText: '<i class="fa-solid fa-ticket-simple mr-1"></i> Pakai Kartu',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#10b981',
            cancelButtonColor: '#475569',
            customClass: { popup: 'rounded-2xl border border-white/10 shadow-2xl' }
        }).then(result => {
            if (!result.isConfirmed) return;
            fetch(BASEURL + '/player/apiUseJailCard', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `player_id=${player.id}`
            }).then(r => r.json()).then(res => {
                if (res.status === 'success') {
                    showModal('<i class="fa-solid fa-door-open text-emerald-400 mr-1"></i> Bebas!', res.msg || 'Kartu digunakan! Kamu bebas.', 'success', '#10b981');
                } else {
                    showModal('<i class="fa-solid fa-triangle-exclamation mr-1"></i> Gagal', res.msg, 'error', '#ef4444');
                }
            }).catch(() => {
                showModal('Error', 'Koneksi gagal, coba lagi.', 'error', '#ef4444');
            });
        });
    }

    let pendingMoneyCheck = null;
    let animateMoney = function() {}; // placeholder, defined in DOMContentLoaded

    document.addEventListener('DOMContentLoaded', function() {
        animateMoney = function animateMoney(targetMoney, customDuration = 900) {
            targetMoney = parseInt(targetMoney);
            if (isNaN(targetMoney)) return;
            if (targetMoney === currentDisplayedMoney) return;

            // Jika ada popup yang terbuka, tunggu sampai ditutup
            if (typeof Swal !== 'undefined' && Swal.isVisible()) {
                if (pendingMoneyCheck) clearInterval(pendingMoneyCheck);
                pendingMoneyCheck = setInterval(() => {
                    if (!Swal.isVisible()) {
                        clearInterval(pendingMoneyCheck);
                        pendingMoneyCheck = null;
                        animateMoney(targetMoney, customDuration);
                    }
                }, 400);
                return;
            }

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

        function updatePejabatDisplay(pejabat) {
            const el = document.getElementById('pejabat-val');
            const bar = document.getElementById('pejabat-bar');
            const pulse = document.getElementById('pejabat-pulse');
            if (!el) return;
            if (pejabat) {
                if (pejabat.id == player.id) {
                    el.textContent = 'ANDA MENJABAT';
                    if (bar) { bar.classList.add('border-amber-500/50', 'bg-amber-500/10'); bar.classList.remove('border-white/10'); }
                    if (pulse) { pulse.classList.remove('bg-transparent'); pulse.classList.add('bg-amber-400', 'animate-pulse'); }
                } else {
                    el.textContent = pejabat.name;
                    if (bar) { bar.classList.remove('border-amber-500/50', 'bg-amber-500/10'); bar.classList.add('border-white/10'); }
                    if (pulse) { pulse.classList.add('bg-transparent'); pulse.classList.remove('bg-amber-400', 'animate-pulse'); }
                }
            } else {
                el.textContent = 'KOSONG';
                if (bar) { bar.classList.remove('border-amber-500/50', 'bg-amber-500/10'); bar.classList.add('border-white/10'); }
                if (pulse) { pulse.classList.add('bg-transparent'); pulse.classList.remove('bg-amber-400', 'animate-pulse'); }
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

        // Helper SweetAlert removed because it's now global

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

                // 🔊 Suara langkah kaki (Web Audio API — tidak perlu file)
                function playStepSound() {
                    try {
                        const ctx = new (window.AudioContext || window.webkitAudioContext)();
                        const osc = ctx.createOscillator();
                        const gain = ctx.createGain();
                        osc.connect(gain); gain.connect(ctx.destination);
                        osc.type = 'sine';
                        osc.frequency.setValueAtTime(220, ctx.currentTime);
                        osc.frequency.exponentialRampToValueAtTime(110, ctx.currentTime + 0.08);
                        gain.gain.setValueAtTime(0.18, ctx.currentTime);
                        gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.1);
                        osc.start(ctx.currentTime);
                        osc.stop(ctx.currentTime + 0.1);
                    } catch(e) {}
                }

                // Animasi token melangkah satu per satu
                const startPos = (parseInt(player.position) - total + 40) % 40;
                let stepPos = startPos;
                let stepsLeft = total;
                function doStep() {
                    if (stepsLeft <= 0) return;
                    stepPos = (stepPos + 1) % 40;
                    stepsLeft--;
                    playStepSound();
                    // Visual highlight cell
                    document.querySelectorAll('.cell').forEach(c => c.style.outline = '');
                    const cellEl = document.getElementById('cell-' + stepPos);
                    if (cellEl) {
                        cellEl.style.outline = '3px solid #38bdf8';
                        cellEl.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
                        setTimeout(() => { cellEl.style.outline = ''; }, 200);
                    }
                    if (stepsLeft > 0) setTimeout(doStep, 220);
                }
                if (total > 0) doStep();

                let title = `<i class='fa-solid fa-dice text-blue-400 mr-1'></i> Dadu: ${total}`;
                let text = `<b>Mendarat di:</b><br><span style="font-size:1.4rem;font-weight:900;color:#38bdf8">${landedCell.name}</span>`;
                let icon = 'success', color = '#38bdf8';

                function showCardAnimation(cardType, cardText, cardImg) {
                    cardText = cardText || 'Ambil kartu fisik dan ikuti instruksinya.';
                    const ct = String(cardType).toLowerCase();
                    const isKesempatan = ct === 'kesempatan' || ct === (SETTINGS.name_kesempatan || '').toLowerCase();
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

                    // Update pejabat display globally
                    if (res.pejabat !== undefined) {
                        updatePejabatDisplay(res.pejabat);
                    }

                    // Show action modal
                    if (act.type === 'jail_stay') {
                        // Tertahan di penjara karena dadu tidak kembar
                        showModal('<i class="fa-solid fa-handcuffs text-rose-500 mr-1"></i> Tertahan di Penjara!', act.msg, 'warning', '#ef4444');
                    } else if (act.type === 'become_pejabat') {
                        Swal.fire({
                            title: `<span style="font-size:2rem">👑</span> PEJABAT NEGARA!`,
                            html: `<div style="background:linear-gradient(135deg,#78350f,#451a03);border-radius:16px;padding:20px;margin:10px 0;border:2px solid #f59e0b;">
                                <div style="font-size:1.5rem;font-weight:900;color:#fcd34d;font-family:monospace;line-height:1.2;">
                                    KAMU SEKARANG ADALAH PEJABAT NEGARA!
                                </div>
                                <div style="color:#fde68a;font-size:0.95rem;margin-top:12px;">Semua uang Pajak (Pajak Biasa & Mewah) yang dibayarkan oleh pemain mana pun akan langsung masuk ke <b style="color:#fff">rekeningmu</b>!</div>
                            </div>
                            <div style="color:#94a3b8;font-size:0.8rem;margin-top:8px;">Posisi ini bisa direbut jika ada pemain lain yang mendarat di petak ini.</div>`,
                            background: '#0f172a', color: '#f1f5f9',
                            confirmButtonText: '<i class="fa-solid fa-crown mr-1"></i> Siap Bertugas!',
                            confirmButtonColor: '#f59e0b',
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
                        
                        let optionsHtml = '';
                        if (act.buy_options && act.buy_options.length > 0) {
                            optionsHtml = `<div class="text-left mt-3 bg-slate-800 rounded-xl p-3 border border-slate-700 max-h-48 overflow-y-auto">`;
                            act.buy_options.forEach(opt => {
                                const canAfford = opt.can_afford;
                                const iconLvl = opt.level === 0 ? 'fa-map-pin text-slate-300' : (opt.level === 5 ? 'fa-hotel text-amber-400' : 'fa-house text-emerald-400');
                                optionsHtml += `
                                    <label class="flex items-center justify-between p-3 mb-2 rounded-xl border border-white/10 bg-slate-800/80 ${canAfford ? 'cursor-pointer hover:border-emerald-400/80 hover:bg-slate-800' : 'opacity-40 cursor-not-allowed'} transition-all">
                                        <div class="flex items-center gap-3">
                                            <input type="radio" name="selected_buy_level" value="${opt.level}" ${canAfford ? '' : 'disabled'} class="w-4 h-4 text-emerald-500 focus:ring-emerald-500" ${opt.level === 0 && canAfford ? 'checked' : ''}>
                                            <div class="text-left">
                                                <div class="font-black text-white text-sm flex items-center gap-1.5">
                                                    <i class="fa-solid ${iconLvl}"></i>
                                                    ${opt.name}
                                                </div>
                                                <div class="text-[11px] text-emerald-400 font-mono">Sewa: Rp ${parseInt(opt.rent).toLocaleString('id-ID')}</div>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-xs font-black font-mono ${canAfford ? 'text-emerald-400' : 'text-rose-400'}">
                                                Rp ${parseInt(opt.cost).toLocaleString('id-ID')}
                                            </div>
                                            ${!canAfford ? '<div class="text-[9px] text-rose-400 font-bold">Uang kurang</div>' : ''}
                                        </div>
                                    </label>
                                `;
                            });
                            optionsHtml += `</div>`;
                        }

                        Swal.fire({
                            title: `<i class="fa-solid fa-building mr-1"></i> Beli Properti?`,
                            html: `${jailBanner}${imgHtml}<b>${act.name}</b><br><span style="font-size:1.3rem;font-weight:900;color:#10b981">Harga Dasar: Rp ${parseInt(act.price).toLocaleString('id-ID')}</span>${optionsHtml}`,
                            background: '#0f172a', color: '#f1f5f9',
                            showCancelButton: true,
                            confirmButtonText: '<i class="fa-solid fa-handshake mr-1"></i> Beli!',
                            cancelButtonText: 'Lewati',
                            confirmButtonColor: '#10b981',
                            cancelButtonColor: '#475569',
                        }).then(result => {
                            if (result.isConfirmed) {
                                let bodyParams = `player_id=${player.id}&cell_index=${res.position}`;
                                if (act.buy_options && act.buy_options.length > 0) {
                                    const checked = document.querySelector('input[name="selected_buy_level"]:checked');
                                    if (checked) {
                                        bodyParams += `&target_level=${checked.value}`;
                                    }
                                }
                                fetch(BASEURL + '/player/apiBuy', {
                                    method: 'POST',
                                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                                    body: bodyParams
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
                    endTurnBtn.innerHTML = '<i class="fa-solid fa-flag-checkered mr-2"></i> <span>SELESAI</span>';
                    setTimeout(() => { endTurnBtn.scrollIntoView({ behavior: 'smooth', block: 'center' }); }, 300);

                }).catch(() => { btn.disabled = false; });

            }, 800);
        });

        // Selesai Giliran
        endTurnBtn.addEventListener('click', function() {
            const btn = endTurnBtn; // Simpan referensi — jangan pakai 'this' di dalam .then()
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-2"></i> <span>...</span>';

            fetch(BASEURL + '/player/apiEndTurn', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `id=${player.id}`
            })
            .then(r => r.json())
            .then(res => {
                if (res.status === 'success') {
                    btn.innerHTML = '<i class="fa-solid fa-check mr-2"></i> <span>SELESAI!</span>';
                    hasRolled = false;
                    isTurn = false;
                    showModal('<i class="fa-solid fa-check mr-1"></i> Giliran Selesai!', 'Menunggu giliran berikutnya...', 'info', '#3b82f6');
                    setTimeout(() => {
                        btn.classList.add('hidden');
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fa-solid fa-flag-checkered mr-2"></i> <span>SELESAI</span>';
                    }, 1200);
                } else {
                    // Gagal — kembalikan tombol
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fa-solid fa-flag-checkered mr-2"></i> <span>SELESAI</span>';
                    showModal('<i class="fa-solid fa-triangle-exclamation mr-1"></i> Gagal', res.msg || 'Coba lagi.', 'warning', '#f59e0b');
                }
            })
            .catch(() => {
                // Network error — selalu kembalikan tombol
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-flag-checkered mr-2"></i> <span>SELESAI</span>';
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
                            currentPropsHash = newHash;
                            fetch(window.location.href)
                                .then(r => r.text())
                                .then(html => {
                                    const doc = new DOMParser().parseFromString(html, 'text/html');
                                    const newProps = doc.getElementById('properties-slider-container');
                                    if (newProps) {
                                        document.getElementById('properties-slider-container').innerHTML = newProps.innerHTML;
                                        document.getElementById('prop-count-badge').textContent = doc.getElementById('prop-count-badge').textContent;
                                        const hint = document.getElementById('prop-swipe-hint');
                                        if (status.properties.length > 0) hint.style.display = 'flex';
                                        else hint.style.display = 'none';
                                    }
                                });
                        }
                    }

                    // Show card popup to ALL players (active roller + spectators via polling)
                    if (status.active_card) {
                        const cardText = status.active_card.text;
                        if (cardText !== lastShownCardText) {
                            lastShownCardText = cardText;
                            const cType = status.active_card.type;
                            const cLabel = (cType === 'kesempatan') 
                                ? (SETTINGS.name_kesempatan || 'Kesempatan') 
                                : (SETTINGS.name_dana_umum || 'Dana Umum');
                            showCardAnimation(cLabel, cardText, null);
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
                        if (navigator.vibrate) navigator.vibrate([100, 50, 100]);
                        // showModal removed as per request for faster gameplay
                    }

                    // Process pending offers
                    if (status.pending_offers && status.pending_offers.length > 0) {
                        const offer = status.pending_offers[0];
                        if (!window.activeOfferId || window.activeOfferId !== offer.id) {
                            window.activeOfferId = offer.id;
                            let imgHtml = offer.image_url ? `<img src="${BASEURL}/${offer.image_url.includes('/') ? '' : 'assets_static/cities/'}${offer.image_url}" style="width:100%;height:100px;object-fit:cover;border-radius:12px;margin-bottom:10px">` : `<div style="height:80px;display:flex;align-items:center;justify-content:center;background:#1e293b;border-radius:12px;margin-bottom:10px"><i class="fa-solid fa-city" style="font-size:2.5rem;color:#475569"></i></div>`;
                            Swal.fire({
                                title: 'Penawaran Masuk!',
                                html: `${imgHtml}
                                    <div class="text-sm text-slate-300"><b>${offer.from_name}</b> ingin membeli properti <b>${offer.cell_name}</b> milikmu seharga:</div>
                                    <div class="text-3xl font-black text-amber-400 my-2">Rp ${parseInt(offer.offer_amount).toLocaleString('id-ID')}</div>`,
                                background: '#0f172a',
                                color: '#f8fafc',
                                showCancelButton: true,
                                confirmButtonText: 'Terima',
                                confirmButtonColor: '#10b981',
                                cancelButtonText: 'Tolak',
                                cancelButtonColor: '#ef4444',
                                allowOutsideClick: false
                            }).then(res => {
                                if (res.isConfirmed) {
                                    acceptTrade(offer.id);
                                } else {
                                    rejectTrade(offer.id);
                                }
                                window.activeOfferId = null;
                            });
                        }
                    } else {
                        if (window.activeOfferId && Swal.isVisible()) {
                            Swal.close();
                            window.activeOfferId = null;
                        }
                    }

                    // Update badge status
                    const badge = document.getElementById('turn-badge');
                    if (badge) {
                        if (status.in_jail) {
                            const jt = parseInt(status.jail_turns || 0);
                            badge.className = 'mx-6 mt-3 rounded-2xl px-4 py-3 flex items-center gap-3 shadow-lg bg-rose-950/70 border border-rose-500/50 shadow-rose-950/50';
                            let actionBtns = '';
                            if (isTurn && !serverHasRolled) {
                                actionBtns += '<div class="flex gap-2 mt-2 w-full">';
                                if (status.free_jail_cards > 0) {
                                    actionBtns += `<button onclick="useJailCard()" class="flex-1 bg-emerald-500 border border-emerald-400 text-white font-bold py-2 px-2 rounded-lg text-[10px] transition leading-tight shadow-[0_0_15px_rgba(16,185,129,0.3)]"><i class="fa-solid fa-ticket-simple mr-1"></i> Kartu (${status.free_jail_cards})</button>`;
                                }
                                actionBtns += `<button onclick="bribeJail()" class="flex-1 bg-amber-500 border border-amber-400 text-white font-bold py-2 px-2 rounded-lg text-[10px] transition leading-tight shadow-[0_0_15px_rgba(245,158,11,0.3)]"><i class="fa-solid fa-money-bill-wave mr-1"></i> Suap (Rp ${parseInt(status.jail_bribe_cost||5000).toLocaleString('id-ID')})</button>`;
                                actionBtns += '</div>';
                                rollBtn.disabled = false;
                                rollBtn.classList.remove('opacity-30');
                            }
                            badge.innerHTML = `<i class="fa-solid fa-handcuffs text-2xl text-rose-400 animate-pulse"></i><div class="flex-1 w-full"><div class="text-rose-400 font-black text-base flex flex-wrap items-center gap-2"><span>TERTAHAN DI PENJARA!</span><span class="text-xs bg-rose-500/30 text-rose-300 border border-rose-500/50 px-2 py-0.5 rounded-full font-bold">Percobaan ${jt}/3</span></div><div class="text-rose-300/80 text-xs mt-0.5">Dadu KEMBAR untuk bebas • Putaran ke-4 otomatis bebas</div>${actionBtns}</div>`;
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
                <div class="px-3 py-2 border-b border-white/5 grid grid-cols-[3.5fr_2fr_2fr] gap-1 items-center">
                    <span class="text-[9px] uppercase tracking-wider text-slate-500 font-bold">Tingkat</span>
                    <span class="text-[9px] uppercase tracking-wider text-slate-500 font-bold text-right" title="Total Biaya Beli + Upgrade">Biaya (Total)</span>
                    <span class="text-[9px] uppercase tracking-wider text-slate-500 font-bold text-right">Harga Sewa</span>
                </div>
                <div id="pm-rent-table" class="divide-y divide-white/5"></div>
            </div>

            <!-- Houses visual -->
            <div id="pm-houses-wrap" class="flex gap-1.5 items-center flex-wrap mb-4 hidden">
                <span class="text-[10px] text-slate-500 uppercase tracking-wider font-bold mr-1">Kondisi:</span>
                <div id="pm-houses-icons" class="flex gap-1 flex-wrap"></div>
            </div>

            <!-- Actions -->
            <div class="flex gap-2 mt-4">
                <button onclick="closePropModal()"
                    class="flex-1 py-3 bg-slate-700 hover:bg-slate-600 text-white font-black rounded-2xl transition active:scale-95 text-sm">
                    <i class="fa-solid fa-xmark mr-1"></i>Tutup
                </button>
                <button id="pm-sell-btn"
                    class="flex-1 py-3 bg-rose-500 hover:bg-rose-600 text-white font-black rounded-2xl transition active:scale-95 text-sm shadow-[0_0_15px_rgba(225,29,72,0.4)]">
                    <i class="fa-solid fa-building-circle-arrow-right mr-1"></i>Jual Bank
                </button>
            </div>
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
    
    let cumCost = parseInt(prop.price) || 0;
    let levels = [];
    
    if (prop.type === 'station') {
        levels = [
            { label: 'Punya 1 Stasiun', cost: cumCost, rent_str: 'Rp 200', icon: 'fa-train' },
            { label: 'Punya 2 Stasiun', cost: cumCost * 2, rent_str: 'Rp 400', icon: 'fa-train' },
            { label: 'Punya 3 Stasiun', cost: cumCost * 3, rent_str: 'Rp 800', icon: 'fa-train' },
            { label: 'Punya 4 Stasiun', cost: cumCost * 4, rent_str: 'Rp 1.600', icon: 'fa-train' }
        ];
    } else if (prop.type === 'utility') {
        levels = [
            { label: 'Punya 1 Perusahaan', cost: cumCost, rent_str: 'Angka Dadu × 40', icon: 'fa-lightbulb' },
            { label: 'Punya 2 Perusahaan', cost: cumCost * 2, rent_str: 'Angka Dadu × 100', icon: 'fa-lightbulb' }
        ];
    } else {
        levels.push({ label: 'Tanah Kosong', rent_str: 'Rp ' + parseInt(prop.level0_rent||0).toLocaleString('id-ID'), cost: cumCost, icon: 'fa-map-pin', houses: 0 });
        for (let i = 1; i <= 5; i++) {
            const stepCost = parseInt(prop[`level${i}_price`]) || parseInt(prop.house_price) || 0;
            cumCost += stepCost;
            if (prop[`level${i}_rent`]) {
                levels.push({
                    label: prop[`level${i}_name`] || (i===5 ? 'Hotel' : 'Rumah '+i),
                    rent_str: 'Rp ' + parseInt(prop[`level${i}_rent`]).toLocaleString('id-ID'),
                    cost: cumCost,
                    icon: i===5 ? 'fa-hotel' : 'fa-house',
                    houses: i
                });
            }
        }
    }

    levels.forEach(lvl => {
        if (!lvl.rent_str && lvl.houses > 0) return;
        const isCurrent = (prop.type === 'property' && lvl.houses === prop.houses);
        const div = document.createElement('div');
        div.className = 'grid grid-cols-[3.5fr_2fr_2fr] gap-1 items-center px-3 py-2' + (isCurrent ? ' bg-white/10' : '');
        
        let iconColor = 'text-slate-500';
        if (prop.type === 'property') {
            iconColor = lvl.houses === 5 ? 'text-rose-400' : (lvl.houses > 0 ? 'text-emerald-400' : 'text-slate-500');
        } else {
            iconColor = prop.type === 'station' ? 'text-orange-400' : 'text-yellow-400';
        }
        
        const textColor = isCurrent ? 'text-amber-300 font-black' : 'text-slate-300 font-bold';
        
        div.innerHTML = `
            <div class="flex items-center gap-1.5 overflow-hidden pr-1">
                <i class="fa-solid ${lvl.icon} text-[10px] ${iconColor}"></i>
                <span class="text-[11px] text-slate-300 truncate leading-none">${lvl.label}</span>
                ${isCurrent ? '<span class="text-[8px] bg-amber-500/20 text-amber-400 border border-amber-500/30 px-1 py-0.5 rounded-full font-black ml-1 flex-shrink-0">AKTIF</span>' : ''}
            </div>
            <span class="text-[11px] font-mono text-blue-400 text-right leading-none">Rp ${lvl.cost.toLocaleString('id-ID')}</span>
            <span class="text-[11px] font-mono ${textColor} text-right leading-none">${lvl.rent_str}</span>
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

    // Sell to Bank logic
    const sellBtn = document.getElementById('pm-sell-btn');
    
    // Hitung total uang yang telah dikeluarkan (Harga Tanah + Total Biaya Rumah saat ini)
    let totalInvested = parseInt(prop.price) || 0;
    for (let i = 1; i <= parseInt(prop.houses); i++) {
        totalInvested += parseInt(prop[`level${i}_price`]) || parseInt(prop.house_price) || 0;
    }
    const sellPrice = Math.floor(totalInvested / 2);
    
    sellBtn.onclick = () => sellToBank(prop.cell_index, prop.name, sellPrice);
    sellBtn.innerHTML = `<i class="fa-solid fa-building-circle-arrow-right mr-1"></i>Jual (Rp ${sellPrice.toLocaleString('id-ID')})`;

    // Show
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closePropModal() {
    const modal = document.getElementById('prop-detail-modal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

async function sellToBank(cellIndex, propName, sellPrice) {
    const { isConfirmed } = await Swal.fire({
        title: 'Jual Properti?',
        html: `Anda akan menjual <b>${propName}</b> ke Bank seharga <b>Rp ${sellPrice.toLocaleString('id-ID')}</b>.<br><br><span class="text-xs text-rose-400">Properti ini akan kembali menjadi milik Bank dan bisa dibeli oleh siapa saja yang mendarat di atasnya.</span>`,
        icon: 'warning',
        background: '#0f172a', color: '#f8fafc',
        showCancelButton: true,
        confirmButtonText: 'Ya, Jual',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#ef4444',
    });

    if (isConfirmed) {
        closePropModal();
        Swal.fire({ title: 'Menjual Properti...', background: '#0f172a', color: '#f8fafc', showConfirmButton: false });
        try {
            const r = await fetch(BASEURL + '/player/apiSellToBank', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `player_id=${player.id}&cell_index=${cellIndex}&price=${sellPrice}`
            });
            const res = await r.json();
            if (res.status === 'success') {
                Swal.fire({icon: 'success', title: 'Terjual!', text: res.msg, timer: 1500, background: '#0f172a', color: '#f8fafc'});
            } else {
                Swal.fire({icon: 'error', title: 'Gagal', text: res.msg, background: '#0f172a', color: '#f8fafc'});
            }
        } catch(e) {}
    }
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
            });
        } else {
            Swal.fire({ icon: 'error', title: 'Gagal', text: data.msg, background: '#0f172a', color: '#f8fafc' });
            if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fa-solid fa-ticket-simple"></i> Pakai Kartu Bebas Penjara'; }
        }
    } catch(e) {
        Swal.fire({ icon: 'error', title: 'Error', text: 'Gagal menghubungi server', background: '#0f172a', color: '#f8fafc' });
        if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fa-solid fa-ticket-simple mr-1"></i> Kartu (Sisa)'; }
    }
}

async function bribeJail() {
    const playerId = <?= (int)$data['player']['id'] ?>;
    const bribeCost = <?= (int)($data['settings']['jail_bribe_cost']['setting_value'] ?? 5000) ?>;
    
    const result = await Swal.fire({
        title: '<i class="fa-solid fa-money-bill-wave text-amber-400 mr-2"></i> Suap Petugas?',
        html: `Bayar <b class="text-amber-400">Rp ${bribeCost.toLocaleString('id-ID')}</b> untuk bebas sekarang.<br><small class="text-slate-400">Kamu masih bisa melempar dadu setelah ini.</small>`,
        background: '#0f172a', color: '#f1f5f9',
        showCancelButton: true,
        confirmButtonText: '<i class="fa-solid fa-money-bill-wave mr-1"></i> Bayar Suap',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#f59e0b',
        cancelButtonColor: '#475569',
        customClass: { popup: 'rounded-2xl border border-white/10 shadow-2xl' }
    });

    if (!result.isConfirmed) return;

    const btn = document.querySelector('button[onclick="bribeJail()"]');
    if (btn) { btn.disabled = true; btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Memproses...'; }

    try {
        const res = await fetch('<?= BASEURL ?>/player/apiBribeJail', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'player_id=' + playerId
        });
        const textData = await res.text();
        let data;
        try {
            data = JSON.parse(textData);
        } catch(e) {
            console.error("Server returned non-JSON:", textData);
            throw new Error("Invalid JSON");
        }

        if (data.status === 'success') {
            animateMoney(data.money);
            Swal.fire({
                icon: 'success',
                title: 'Berhasil Menyuap!',
                text: data.msg,
                background: '#0f172a',
                color: '#f8fafc',
                confirmButtonText: 'Lempar Dadu!',
                confirmButtonColor: '#10b981',
            });
        } else {
            Swal.fire({ icon: 'error', title: 'Gagal', text: data.msg, background: '#0f172a', color: '#f8fafc' });
            if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fa-solid fa-money-bill-wave mr-1"></i> Suap'; }
        }
    } catch(e) {
        Swal.fire({ icon: 'error', title: 'Error', text: 'Gagal menghubungi server', background: '#0f172a', color: '#f8fafc' });
        if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fa-solid fa-money-bill-wave mr-1"></i> Suap'; }
    }
}

async function openTradeModal() {
    if (!isTurn) {
        showModal('Belum Giliran', 'Kamu hanya bisa mengajukan penawaran saat giliranmu.', 'error');
        return;
    }
    Swal.fire({
        title: '<i class="fa-solid fa-circle-notch fa-spin"></i> Memuat Bursa...',
        background: '#0f172a', color: '#f8fafc', showConfirmButton: false, allowOutsideClick: false
    });
    
    try {
        const r = await fetch(BASEURL + '/player/apiGetOtherProperties/' + player.id);
        const res = await r.json();
        if (res.status === 'success') {
            if (!res.data || res.data.length === 0) {
                Swal.fire({icon: 'info', title: 'Bursa Kosong', text: 'Belum ada properti milik pemain lain yang bisa dibeli.', background: '#0f172a', color: '#f8fafc'});
                return;
            }
            let html = '<div class="flex flex-col gap-3 max-h-72 overflow-y-auto pr-1 text-left">';
            res.data.forEach(p => {
                const imgSrc = p.image_url
                    ? `${BASEURL}/${p.image_url.includes('/') ? '' : 'assets_static/cities/'}${p.image_url}`
                    : null;
                const img = imgSrc
                    ? `<img src="${imgSrc}" class="w-12 h-12 rounded-lg object-cover flex-shrink-0">`
                    : `<div class="w-12 h-12 rounded-lg bg-slate-700 flex items-center justify-center flex-shrink-0"><i class="fa-solid fa-city text-slate-400"></i></div>`;
                const safeOwner = p.owner_color ? `style="color:${p.owner_color}"` : '';
                html += `<div class="flex items-center gap-3 p-3 bg-slate-800/60 rounded-xl border border-white/8 cursor-pointer hover:bg-slate-700 transition-all active:scale-95"
                              onclick="submitTradeOffer(${p.cell_index}, ${p.owner_id}, '${(p.cell_name||'').replace(/'/g,"\\'")}', ${p.price||0})">
                            ${img}
                            <div class="flex-1 min-w-0">
                                <div class="font-bold text-white text-sm truncate">${p.cell_name || 'Properti'}</div>
                                <div class="text-xs text-slate-400">Pemilik: <span ${safeOwner} class="font-semibold">${p.owner_name || '-'}</span></div>
                            </div>
                            <div class="text-xs font-bold text-emerald-400 text-right flex-shrink-0">
                                Harga<br>Rp ${parseInt(p.price||0).toLocaleString('id-ID')}
                            </div>
                        </div>`;
            });
            html += '</div>';
            Swal.fire({
                title: '<i class="fa-solid fa-store text-indigo-400 mr-2"></i> Bursa Properti',
                html: html,
                background: '#0f172a', color: '#f8fafc',
                showCloseButton: true, showConfirmButton: false,
                customClass: { popup: 'rounded-2xl border border-white/10 shadow-2xl' }
            });
        } else {
            Swal.fire({icon: 'error', title: 'Gagal', text: res.msg || 'Gagal memuat bursa properti.', background: '#0f172a', color: '#f8fafc'});
        }
    } catch(e) {
        Swal.fire({icon: 'error', title: 'Error Koneksi', text: 'Tidak dapat terhubung ke server. Coba lagi.', background: '#0f172a', color: '#f8fafc'});
    }
}

async function submitTradeOffer(cellIndex, toPlayerId, propName, basePrice) {
    const { value: amount } = await Swal.fire({
        title: `Tawar ${propName}`,
        input: 'number',
        inputLabel: 'Masukkan Harga Penawaran (Rp)',
        inputValue: basePrice * 2,
        showCancelButton: true,
        confirmButtonText: 'Kirim Tawaran',
        cancelButtonText: 'Batal',
        background: '#0f172a', color: '#f8fafc',
        inputValidator: (val) => {
            if (!val || parseInt(val) <= 0) return 'Masukkan jumlah yang valid!';
            if (parseInt(val) > currentDisplayedMoney) return 'Uangmu tidak cukup!';
        }
    });

    if (amount) {
        Swal.fire({ title: 'Mengirim Penawaran...', background: '#0f172a', color: '#f8fafc', showConfirmButton: false });
        try {
            const r = await fetch(BASEURL + '/player/apiOfferTrade', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `from_player_id=${player.id}&to_player_id=${toPlayerId}&cell_index=${cellIndex}&amount=${amount}`
            });
            const res = await r.json();
            if (res.status === 'success') {
                Swal.fire({icon: 'success', title: 'Terkirim!', text: res.msg, timer: 1500, background: '#0f172a', color: '#f8fafc'});
            } else {
                Swal.fire({icon: 'error', title: 'Gagal', text: res.msg, background: '#0f172a', color: '#f8fafc'});
            }
        } catch(e) {}
    }
}

async function acceptTrade(offerId) {
    try {
        const r = await fetch(BASEURL + '/player/apiAcceptTrade', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `offer_id=${offerId}`
        });
        const res = await r.json();
        if (res.status === 'success') {
            Swal.fire({icon: 'success', title: 'Berhasil!', text: res.msg, timer: 2000, background: '#0f172a', color: '#f8fafc'});
        } else {
            Swal.fire({icon: 'error', title: 'Gagal', text: res.msg, background: '#0f172a', color: '#f8fafc'});
        }
    } catch(e) {}
}

async function rejectTrade(offerId) {
    try {
        await fetch(BASEURL + '/player/apiRejectTrade', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `offer_id=${offerId}`
        });
    } catch(e) {}
}
</script>

