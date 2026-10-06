<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?= $data['judul']; ?></title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- SweetAlert2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Font Awesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;700;900&display=swap" rel="stylesheet">
    <style>
        /* Force Landscape Overlay */
        #portrait-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: linear-gradient(135deg, #f59e0b, #d97706);
            z-index: 9999;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: white;
            text-align: center;
            padding: 20px;
        }
        @media screen and (max-width: 896px) and (orientation: portrait) {
            #portrait-overlay { display: flex; }
            #main-nav, .game-wrapper { display: none !important; }
        }
        :root {
            --board-bg: #fef3c7;
            --cell-bg: #fffbeb;
            --cell-border: rgba(0,0,0,0.08);
        }

        body { 
            font-family: 'Outfit', sans-serif; 
            background: linear-gradient(135deg, #fde68a 0%, #fbbf24 30%, #f59e0b 60%, #d97706 100%);
            background-attachment: fixed;
            color: #1e293b; 
            overflow: hidden; 
            margin: 0;
            padding: 0;
            height: 100vh;
            width: 100vw;
        }
        
        .game-wrapper {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            width: 100vw;
            padding-top: 60px;
        }

        .board-area {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 16px;
            width: 100%;
            height: 100%;
            perspective: 1200px; /* Add 3D perspective */
        }
        
        /* ==========================================
           GET RICH STYLE BOARD
        ========================================== */
        .monopoly-board {
            display: grid;
            grid-template-columns: 1.5fr repeat(9, 1fr) 1.5fr;
            grid-template-rows: 1.5fr repeat(9, 1fr) 1.5fr;
            
            width: 85vmin;
            height: 85vmin;
            max-width: 800px;
            max-height: 800px;
            
            gap: 2px;
            padding: 10px;
            border-radius: 20px;
            position: relative;
            
            background: #fff7e0;
            border: 5px solid #f59e0b;
            box-shadow: 
                0 0 0 3px #fbbf24,
                0 0 0 8px #f59e0b,
                0 15px 30px rgba(0,0,0,0.4);
        }

        @media (max-width: 768px) {
            .monopoly-board {
                width: 92vmin;
                height: 92vmin;
            }
        }

        /* === CELLS === */
        .cell { 
            position: relative; 
            background-color: #fffef5;
            overflow: hidden;
            border-radius: 4px;
            box-shadow: 
                inset 1px 1px 0 rgba(255,255,255,0.8), 
                inset -1px -1px 0 rgba(0,0,0,0.15), 
                0 0 0 1px rgba(0,0,0,0.1);
            transition: transform 0.2s, filter 0.2s, box-shadow 0.2s;
        }
        .cell:hover {
            filter: brightness(1.1);
            transform: scale(1.05); /* Pop out on hover */
            box-shadow: 
                inset 0 0 0 2px rgba(251,191,36,1), 
                0 10px 20px rgba(0,0,0,0.3);
            z-index: 30;
        }

        .cell-corner {
            background: linear-gradient(135deg, #fff9e6 0%, #fde68a 100%);
            border-radius: 8px;
        }

        /* Inner cell content wrappers */
        .cell-content { 
            width: 100%; 
            height: 100%; 
            display: flex;
            box-sizing: border-box;
        }

        /* Color bar styling — Get Rich vivid saturated colors */
        .color-bar {
            position: relative;
            box-shadow: inset 0 0 0 1px rgba(0,0,0,0.12);
        }
        .name {
            font-size: clamp(0.38rem, 1.05vmin, 0.68rem);
            font-weight: 900;
            color: #1e293b;
            text-align: center;
            text-transform: uppercase;
            line-height: 1.1;
            display: flex;
            align-items: center;
            justify-content: center;
            letter-spacing: -0.01em;
        }
        .price {
            font-size: clamp(0.35rem, 0.9vmin, 0.62rem);
            font-weight: 800;
            color: #92400e;
            text-align: center;
            padding: 2px;
            background: rgba(251,191,36,0.25);
        }
        .spacer-bar { background: transparent; }

        /* === Get Rich vivid color palette === */
        .c-white  { background-color: transparent; }
        .c-blue   { background: linear-gradient(135deg, #2563eb, #1d4ed8); }
        .c-green  { background: linear-gradient(135deg, #16a34a, #15803d); }
        .c-red    { background: linear-gradient(135deg, #dc2626, #b91c1c); }
        .c-yellow { background: linear-gradient(135deg, #ca8a04, #a16207); }
        .c-orange { background: linear-gradient(135deg, #ea580c, #c2410c); }
        .c-pink   { background: linear-gradient(135deg, #db2777, #be185d); }
        .c-brown  { background: linear-gradient(135deg, #7c3aed, #6d28d9); }
        .c-cyan   { background: linear-gradient(135deg, #0891b2, #0e7490); }

        /* === BOTTOM & TOP CELLS (Vertical Layout) === */
        .cell-bottom .cell-content, .cell-top .cell-content {
            flex-direction: column;
        }
        .cell-top .price { order: 1; padding: 2px; }
        .cell-top .name { order: 2; flex-grow: 1; padding: 2px 3px; }
        .cell-top .color-bar, .cell-top .spacer-bar { order: 3; height: 28%; width: 100%; }
        .cell-bottom .color-bar, .cell-bottom .spacer-bar { order: 1; height: 28%; width: 100%; }
        .cell-bottom .name { order: 2; flex-grow: 1; padding: 2px 3px; }
        .cell-bottom .price { order: 3; padding: 2px; }

        /* === LEFT & RIGHT CELLS (Horizontal Layout) === */
        .cell-left .cell-content { flex-direction: row-reverse; }
        .cell-right .cell-content { flex-direction: row; }
        .cell-left .color-bar, .cell-left .spacer-bar,
        .cell-right .color-bar, .cell-right .spacer-bar {
            width: 28%; height: 100%;
        }
        .cell-left .name, .cell-right .name,
        .cell-left .name-in-color, .cell-right .name-in-color {
            flex-grow: 1;
            writing-mode: vertical-rl;
            text-orientation: mixed;
            transform: rotate(180deg);
            padding: 4px 2px;
        }
        .cell-right .name, .cell-right .name-in-color { transform: none; }
        .cell-left .price {
            writing-mode: vertical-rl;
            transform: rotate(180deg);
            padding: 4px 2px;
        }
        .cell-right .price {
            writing-mode: vertical-rl;
            transform: none;
            padding: 4px 2px;
        }

        /* CORNER CELLS */
        .cell-corner .cell-content {
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 6px;
        }
        .cell-corner .name {
            font-size: clamp(0.5rem, 1.3vmin, 0.85rem);
            font-weight: 900;
            color: #92400e;
        }
        .cell-corner .price, .cell-corner .color-bar, .cell-corner .spacer-bar {
            display: none;
        }

        /* Kesempatan & Dana Umum cells */
        .cell-top .spacer-bar[style], .cell-bottom .spacer-bar[style] {
            width: 100% !important; height: 28% !important;
        }
        .cell-left .spacer-bar[style], .cell-right .spacer-bar[style] {
            width: 28% !important; height: 100% !important;
        }

        /* === PLAYER TOKENS — Get Rich style glossy balls === */
        .player-token {
            width: clamp(14px, 2.8vmin, 24px); 
            height: clamp(14px, 2.8vmin, 24px); 
            border-radius: 50%; 
            position: absolute;
            bottom: 4px; 
            background-image: radial-gradient(circle at 30% 28%, rgba(255,255,255,1) 18%, rgba(255,255,255,0) 65%);
            box-shadow: 
                0 4px 10px rgba(0,0,0,0.75), 
                inset -2px -3px 6px rgba(0,0,0,0.35),
                inset 1px 1px 5px rgba(255,255,255,0.8);
            border: 2px solid rgba(255,255,255,1);
            transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        }
        
        .p1 { background-color: #ef4444; left: 10%; z-index: 4; }
        .p2 { background-color: #3b82f6; left: 45%; z-index: 3; }
        .p3 { background-color: #22c55e; left: 25%; bottom: 25%; z-index: 2; }
        .p4 { background-color: #eab308; right: 10%; z-index: 1; }
        
        /* === CENTER AREA — Get Rich vibrant card island === */
        .center-space {
            grid-column: 2 / 11;
            grid-row: 2 / 11;
            background: 
                radial-gradient(ellipse at 50% 40%, #0284c7 0%, #0369a1 40%, #075985 80%, #082f49 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            border-radius: 12px;
            box-shadow: inset 0 0 40px rgba(0,0,0,0.5);
            position: relative;
            overflow: hidden;
        }
        /* Decorative dots pattern */
        .center-space::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image: radial-gradient(circle, rgba(255,255,255,0.15) 1px, transparent 1px);
            background-size: 18px 18px;
            opacity: 0.5;
        }

        .center-content-wrapper {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            z-index: 20;
            position: relative;
            transform: scale(1.5);
        }

        .title-text { 
            font-size: clamp(1.8rem, 5vmin, 4.5rem); 
            font-weight: 900;
            background: linear-gradient(135deg, #92400e 0%, #d97706 40%, #f59e0b 60%, #b45309 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            letter-spacing: -1px;
            line-height: 1;
            filter: drop-shadow(0 3px 6px rgba(146,64,14,0.3));
            text-shadow: none;
        }
        
        .subtitle-text { 
            font-size: clamp(0.55rem, 1.4vmin, 1.1rem); 
            color: #b45309; 
            font-weight: 900;
            letter-spacing: 0.35em;
            border-top: 3px solid #f59e0b;
            border-bottom: 3px solid #f59e0b;
            padding: 0.4em 1em;
            margin-bottom: 0.8rem;
            text-transform: uppercase;
            background: rgba(255,255,255,0.4);
            border-radius: 4px;
        }

        .info-badge {
            margin-top: 12px;
            color: #78350f;
            font-weight: 700;
            background: rgba(255,255,255,0.7);
            padding: 8px 18px;
            border-radius: 30px;
            border: 2px solid #fbbf24;
            font-size: 0.82rem;
            box-shadow: 0 4px 12px rgba(251,191,36,0.4);
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: rgba(0,0,0,0.1); border-radius: 10px; }
        ::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.2); border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: rgba(255,255,255,0.4); }
    </style>
</head>
<body>

<!-- Portrait Overlay -->
<div id="portrait-overlay">
    <i class="fa-solid fa-mobile-screen fa-rotate-270 text-6xl mb-4 animate-bounce"></i>
    <h1 class="text-3xl font-black mb-2">Putar Perangkat Anda</h1>
    <p class="text-lg opacity-80">Game ini hanya dapat dimainkan dalam mode Landscape.</p>
</div>

<!-- Floating Hamburger to toggle Navbar -->
<button onclick="document.getElementById('main-nav').classList.toggle('-translate-y-[150%]')" class="absolute top-3 right-4 z-[60] w-12 h-12 bg-black/20 hover:bg-black/40 backdrop-blur-md border-2 border-white/40 rounded-xl flex items-center justify-center text-white transition shadow-[0_4px_15px_rgba(0,0,0,0.2)]">
    <i class="fa-solid fa-bars text-xl drop-shadow-md"></i>
</button>

<!-- Navbar -->
<nav id="main-nav" class="-translate-y-[150%] fixed top-2 md:top-4 left-1/2 -translate-x-1/2 w-[95%] max-w-[1200px] min-h-[60px] py-2 px-4 md:px-6 flex flex-col md:flex-row justify-between items-center gap-3 md:gap-0 z-50 bg-gradient-to-r from-amber-600/90 via-amber-500/90 to-yellow-500/90 backdrop-blur-md border-2 border-amber-300 shadow-[0_10px_30px_rgba(0,0,0,0.4)] transition-transform duration-500 rounded-2xl md:rounded-full">

<div class="text-white font-black text-lg md:text-2xl tracking-widest flex items-center gap-2 md:gap-3 drop-shadow-[0_2px_4px_rgba(0,0,0,0.5)]">
        <i class="fa-solid fa-dice text-yellow-200 text-2xl md:text-3xl"></i> <span class="hidden sm:inline">MONOPOLY</span><span class="sm:hidden">MONOPOLY</span>
        <?php if (!empty($data['session'])): ?>
        <span class="text-amber-100 font-bold text-xs md:text-base bg-black/20 px-2 md:px-3 py-0.5 md:py-1 rounded-full border border-white/20 shadow-inner max-w-[150px] truncate">SESI: <?= htmlspecialchars($data['session']['name']) ?></span>
        <?php if (!empty($data['is_host'])): ?>
        <span class="hidden sm:inline text-xs bg-gradient-to-r from-yellow-300 to-amber-300 text-amber-900 border-2 border-white/50 px-2.5 py-1.5 rounded-full font-black shadow-lg uppercase tracking-wider"><i class="fa-solid fa-crown mr-1"></i> HOST</span>
        <?php endif; ?>
        <?php endif; ?>
    </div>
    <div class="flex flex-wrap justify-center gap-2 md:gap-2.5">
        <a href="<?= BASEURL ?>/setup" class="px-3 py-1.5 md:px-4 md:py-2 text-sm md:text-base bg-black/20 hover:bg-black/40 border-2 border-white/30 rounded-xl text-white font-bold transition flex items-center gap-1.5 md:gap-2 shadow-lg hover:-translate-y-0.5 active:translate-y-0">
            <i class="fa-solid fa-arrow-left text-yellow-200"></i> <span class="hidden lg:inline">Lobby</span>
        </a>
        <?php if (!empty($data['is_host'])): ?>
        <a href="<?= BASEURL ?>/admin/stopSession/<?= $data['session']['id'] ?>" onclick="return confirm('Hentikan permainan sesi ini?')" class="px-3 py-1.5 md:px-4 md:py-2 text-sm md:text-base bg-rose-600/80 hover:bg-rose-600 border-2 border-rose-400 rounded-xl text-white font-bold transition flex items-center gap-1.5 md:gap-2 shadow-lg hover:-translate-y-0.5 active:translate-y-0">
            <i class="fa-solid fa-stop text-rose-200"></i> <span class="hidden lg:inline">Stop</span>
        </a>
        <a href="<?= BASEURL ?>/home/apiReset/<?= $data['session']['id'] ?? '' ?>" onclick="return confirm('Reset semua posisi & uang pemain?')" class="px-3 py-1.5 md:px-4 md:py-2 text-sm md:text-base bg-orange-600/80 hover:bg-orange-600 border-2 border-orange-400 rounded-xl text-white font-bold transition flex items-center gap-1.5 md:gap-2 shadow-lg hover:-translate-y-0.5 active:translate-y-0">
            <i class="fa-solid fa-rotate-left text-orange-200"></i> <span class="hidden lg:inline">Reset</span>
        </a>
        <?php endif; ?>
        <button onclick="refreshPage(this)" class="px-3 py-1.5 md:px-4 md:py-2 bg-black/20 hover:bg-black/40 border-2 border-white/30 rounded-xl text-white font-bold transition flex items-center justify-center shadow-lg hover:-translate-y-0.5 active:translate-y-0 w-9 md:w-11" title="Refresh Halaman">
            <i id="refresh-icon" class="fa-solid fa-arrows-rotate text-yellow-200"></i>
        </button>
        <button id="fullscreen-btn" onclick="toggleFullscreen()" class="px-3 py-1.5 md:px-4 md:py-2 bg-black/20 hover:bg-black/40 border-2 border-white/30 rounded-xl text-white font-bold transition flex items-center justify-center shadow-lg hover:-translate-y-0.5 active:translate-y-0 w-9 md:w-11" title="Fullscreen">
            <i id="fs-icon" class="fa-solid fa-expand text-yellow-200"></i>
        </button>
        <button onclick="document.getElementById('bank-modal').classList.remove('hidden'); document.getElementById('bank-modal').classList.add('flex');" class="px-3 py-1.5 md:px-5 md:py-2 text-sm md:text-base bg-gradient-to-r from-emerald-500 to-green-600 hover:from-emerald-400 hover:to-green-500 border-2 border-emerald-300 rounded-xl text-white font-black transition flex items-center gap-1.5 md:gap-2 shadow-[0_4px_15px_rgba(16,185,129,0.4)] hover:-translate-y-0.5 active:translate-y-0">
            <i class="fa-solid fa-building-columns text-emerald-100"></i> <span class="hidden sm:inline">Bank</span>
        </button>
        <button onclick="document.getElementById('qr-modal').classList.remove('hidden'); document.getElementById('qr-modal').classList.add('flex');" class="px-3 py-1.5 md:px-5 md:py-2 text-sm md:text-base bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-400 hover:to-indigo-500 border-2 border-blue-300 rounded-xl text-white font-black transition flex items-center gap-1.5 md:gap-2 shadow-[0_4px_15px_rgba(59,130,246,0.4)] hover:-translate-y-0.5 active:translate-y-0">
            <i class="fa-solid fa-qrcode text-blue-100"></i> <span class="hidden sm:inline">Gabung</span>
        </button>
    </div>
</nav>

<script>
function refreshPage(btn) {
    const icon = document.getElementById('refresh-icon');
    if (icon) {
        icon.style.transition = 'transform 0.6s ease';
        icon.style.transform = 'rotate(360deg)';
    }
    if (btn) btn.disabled = true;
    setTimeout(() => { window.location.reload(); }, 500);
}

function toggleFullscreen() {
    const icon = document.getElementById('fs-icon');
    if (!document.fullscreenElement) {
        document.documentElement.requestFullscreen().then(() => {
            icon.classList.replace('fa-expand', 'fa-compress');
        }).catch(() => {});
    } else {
        document.exitFullscreen().then(() => {
            icon.classList.replace('fa-compress', 'fa-expand');
        }).catch(() => {});
    }
}
document.addEventListener('fullscreenchange', () => {
    const icon = document.getElementById('fs-icon');
    if (icon) {
        if (document.fullscreenElement) {
            icon.classList.replace('fa-expand', 'fa-compress');
        } else {
            icon.classList.replace('fa-compress', 'fa-expand');
        }
    }
});
</script>

<div class="game-wrapper">
