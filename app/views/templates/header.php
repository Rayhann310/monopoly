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
        :root {
            --board-bg: #0f172a;
            --cell-bg: #1e293b;
            --cell-border: rgba(255, 255, 255, 0.05);
        }

        body { 
            font-family: 'Outfit', sans-serif; 
            background-color: #f8fafc;
            background-image: 
                radial-gradient(circle at 15% 50%, rgba(59, 130, 246, 0.08), transparent 30%),
                radial-gradient(circle at 85% 30%, rgba(16, 185, 129, 0.08), transparent 30%),
                linear-gradient(rgba(0,0,0,0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(0,0,0,0.03) 1px, transparent 1px);
            background-size: 100% 100%, 100% 100%, 40px 40px, 40px 40px;
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
            padding-top: 60px; /* Offset for navbar */
        }

        .board-area {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            width: 100%;
            height: 100%;
        }
        
        /* The Board - Premium Look */
        .monopoly-board {
            display: grid;
            /* 1.4fr untuk sudut (lebih besar), 1fr untuk sisi */
            grid-template-columns: 1.4fr repeat(9, 1fr) 1.4fr;
            grid-template-rows: 1.4fr repeat(9, 1fr) 1.4fr;
            
            width: 95vmin;
            height: 95vmin;
            max-width: 950px;
            max-height: 950px;
            
            background: #cbd5e1; /* Grid gap color */
            gap: 2px;
            padding: 10px;
            border-radius: 12px;
            position: relative;
            
            border: 4px solid #fff;
            box-shadow: 
                0 0 0 8px #d1d5db,
                0 30px 60px -12px rgba(0,0,0,0.5),
                inset 0 0 20px rgba(0,0,0,0.1);
            background-color: #f1f5f9;
        }

        /* Cells */
        .cell { 
            position: relative; 
            background-color: #ffffff;
            overflow: hidden;
            box-shadow: inset 0 0 0 1px rgba(0,0,0,0.05);
            transition: background-color 0.2s, box-shadow 0.2s;
        }
        .cell:hover {
            background-color: #f8fafc;
            box-shadow: inset 0 0 0 2px rgba(16, 185, 129, 0.8);
            z-index: 20;
        }

        /* Corner cells need to be distinct */
        .cell-corner {
            background-color: #f8fafc;
        }
        
        /* Inner cell content wrappers */
        .cell-content { 
            width: 100%; 
            height: 100%; 
            display: flex;
            box-sizing: border-box;
        }

        /* Base styles for parts */
        .color-bar {
            background-color: transparent;
            position: relative;
            box-shadow: inset 0 0 0 1px rgba(0,0,0,0.1);
        }
        .name {
            font-size: clamp(0.45rem, 1.15vmin, 0.75rem);
            font-weight: 800;
            color: #1e293b;
            text-align: center;
            text-transform: uppercase;
            line-height: 1.1;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .price {
            font-size: clamp(0.4rem, 1vmin, 0.7rem);
            font-weight: 700;
            color: #475569;
            text-align: center;
            padding: 2px;
        }
        .spacer-bar {
            background: transparent;
        }

        /* === BOTTOM & TOP CELLS (Vertical Layout) === */
        .cell-bottom .cell-content, .cell-top .cell-content {
            flex-direction: column;
        }
        
        /* Top cells: Price at top, Image in middle, Color bar at bottom */
        .cell-top .price { order: 1; padding: 2px; }
        .cell-top .name { order: 2; flex-grow: 1; padding: 2px 4px; }
        .cell-top .color-bar, .cell-top .spacer-bar { order: 3; height: 25%; width: 100%; }

        /* Bottom cells: Color bar at the top, Image in middle, Price at bottom */
        .cell-bottom .color-bar, .cell-bottom .spacer-bar { order: 1; height: 25%; width: 100%; }
        .cell-bottom .name { order: 2; flex-grow: 1; padding: 2px 4px; }
        .cell-bottom .price { order: 3; padding: 2px; }

        /* === LEFT & RIGHT CELLS (Horizontal Layout) === */
        /* To make text face center, we rotate the text itself */
        .cell-left .cell-content {
            flex-direction: row-reverse; /* Color bar on right */
        }
        .cell-right .cell-content {
            flex-direction: row; /* Color bar on left */
        }
        
        .cell-left .color-bar, .cell-left .spacer-bar,
        .cell-right .color-bar, .cell-right .spacer-bar {
            width: 25%;
            height: 100%;
        }
        
        .cell-left .name, .cell-right .name,
        .cell-left .name-in-color, .cell-right .name-in-color {
            flex-grow: 1;
            writing-mode: vertical-rl;
            text-orientation: mixed; /* Let browser handle orientation */
            transform: rotate(180deg); /* Reads bottom-to-top, letters facing center */
            padding: 4px 2px;
        }
        .cell-right .name, .cell-right .name-in-color {
            transform: none; /* Reads top-to-bottom, letters facing center */
        }
        
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

        /* Container inside left/right to hold name and price side-by-side vertically */
        .lr-text-container {
            display: flex;
            flex-direction: row; /* Because writing-mode is vertical, row acts visually like column */
            flex-grow: 1;
            justify-content: center;
            align-items: center;
        }
        
        /* CORNER CELLS */
        .cell-corner .cell-content {
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 8px;
        }
        .cell-corner .name {
            font-size: clamp(0.55rem, 1.4vmin, 0.9rem);
            transform: rotate(-45deg); /* Diagonal text for corners */
            width: 140%;
        }
        .cell-corner .price, .cell-corner .color-bar, .cell-corner .spacer-bar {
            display: none;
        }        }
        
        .c-white { background-color: transparent; }
        .c-blue { background-color: #3b82f6; }
        .c-green { background-color: #22c55e; }
        .c-red { background-color: #ef4444; }
        .c-yellow { background-color: #eab308; }
        .c-orange { background-color: #f97316; }
        .c-pink { background-color: #ec4899; }
        .c-brown { background-color: #8b5cf6; } 
        .c-cyan { background-color: #06b6d4; }

        /* Kesempatan & Dana Umum: auto-orientation so icon appears on the center-facing side */
        .cell-top .spacer-bar[style], .cell-bottom .spacer-bar[style] {
            width: 100% !important;
            height: 25% !important;
        }
        .cell-left .spacer-bar[style], .cell-right .spacer-bar[style] {
            width: 25% !important;
            height: 100% !important;
        }

        /* Tokens - 3D glossy balls */
        .player-token {
            width: clamp(14px, 2.8vmin, 24px); 
            height: clamp(14px, 2.8vmin, 24px); 
            border-radius: 50%; 
            position: absolute;
            bottom: 4px; 
            background-image: radial-gradient(circle at 30% 30%, rgba(255,255,255,0.95) 15%, rgba(255,255,255,0) 65%);
            box-shadow: 
                0 4px 8px rgba(0,0,0,0.7), 
                inset -2px -2px 6px rgba(0,0,0,0.4),
                inset 1px 1px 4px rgba(255,255,255,0.6);
            border: 1.5px solid rgba(255,255,255,0.9);
            transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        }
        
        .p1 { background-color: #ef4444; left: 10%; z-index: 4; }
        .p2 { background-color: #3b82f6; left: 45%; z-index: 3; }
        .p3 { background-color: #22c55e; left: 25%; bottom: 25%; z-index: 2; }
        .p4 { background-color: #eab308; right: 10%; z-index: 1; }
        
        /* Center Area - Premium Semi-3D */
        .center-space {
            grid-column: 2 / 11;
            grid-row: 2 / 11;
            background: radial-gradient(circle at center, #f8fafc 0%, #e2e8f0 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            border-radius: 16px;
            box-shadow: inset 0 0 30px rgba(0,0,0,0.04);
            position: relative;
        }

        .center-content-wrapper {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            transform: translateZ(5px);
            z-index: 20;
        }

        .title-text { 
            font-size: clamp(2rem, 5.5vmin, 5rem); 
            font-weight: 900;
            background: linear-gradient(135deg, #0f172a 0%, #334155 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            letter-spacing: -1.5px;
            margin-bottom: 0.5rem;
            line-height: 1;
            filter: drop-shadow(0px 4px 6px rgba(0,0,0,0.15));
        }
        
        .subtitle-text { 
            font-size: clamp(0.6rem, 1.5vmin, 1.2rem); 
            color: #ef4444; 
            font-weight: 800;
            letter-spacing: 0.4em;
            border-top: 3px solid #ef4444;
            border-bottom: 3px solid #ef4444;
            padding: 0.5em 0;
            margin-bottom: 1rem;
            text-transform: uppercase;
        }

        .info-badge {
            margin-top: 24px;
            color: #64748b;
            font-weight: 600;
            background: #f1f5f9;
            padding: 10px 20px;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            font-size: 0.85rem;
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: rgba(0,0,0,0.1); border-radius: 10px; }
        ::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.2); border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: rgba(255,255,255,0.4); }
    </style>
</head>
<body>

<!-- Floating Hamburger to toggle Navbar -->
<button onclick="document.getElementById('main-nav').classList.toggle('-translate-y-full')" class="absolute top-3 right-4 z-[60] w-10 h-10 bg-white/10 hover:bg-white/20 backdrop-blur border border-white/20 rounded-lg flex items-center justify-center text-white transition shadow-lg">
    <i class="fa-solid fa-bars"></i>
</button>

<!-- Navbar -->
<nav id="main-nav" class="absolute top-0 left-0 w-full h-[60px] px-6 pr-16 flex justify-between items-center z-50 bg-black/40 backdrop-blur-md border-b border-white/10 shadow-lg transition-transform duration-500">
    <div class="text-white font-black text-xl tracking-widest flex items-center gap-3">
        <i class="fa-solid fa-city text-red-500"></i> MONOPOLY
        <?php if (!empty($data['session'])): ?>
        <span class="text-slate-500 font-normal text-base">— <?= htmlspecialchars($data['session']['name']) ?></span>
        <?php if (!empty($data['is_host'])): ?>
        <span class="text-xs bg-amber-500/20 text-amber-400 border border-amber-500/30 px-2 py-1 rounded-lg font-bold"><i class="fa-solid fa-crown mr-1"></i> HOST</span>
        <?php endif; ?>
        <?php endif; ?>
    </div>
    <div class="flex gap-3">
        <a href="<?= BASEURL ?>/setup" class="px-4 py-2 bg-white/10 hover:bg-white/20 border border-white/20 rounded-lg text-white font-bold transition flex items-center gap-2">
            <i class="fa-solid fa-arrow-left"></i> Lobby
        </a>
        <?php if (!empty($data['is_host'])): ?>
        <a href="<?= BASEURL ?>/admin/stopSession/<?= $data['session']['id'] ?>" onclick="return confirm('Hentikan permainan sesi ini?')" class="px-4 py-2 bg-orange-500/20 hover:bg-orange-500/40 border border-orange-500/50 rounded-lg text-orange-400 font-bold transition flex items-center gap-2">
            <i class="fa-solid fa-stop"></i> Stop
        </a>
        <a href="<?= BASEURL ?>/home/apiReset/<?= $data['session']['id'] ?? '' ?>" onclick="return confirm('Reset semua posisi & uang pemain?')" class="px-4 py-2 bg-red-500/20 hover:bg-red-500/40 border border-red-500/50 rounded-lg text-red-400 font-bold transition flex items-center gap-2">
            <i class="fa-solid fa-rotate-left"></i> Reset
        </a>
        <?php endif; ?>
        <button onclick="refreshPage(this)" class="px-4 py-2 bg-white/10 hover:bg-white/20 border border-white/20 rounded-lg text-white font-bold transition flex items-center gap-2" title="Refresh Halaman">
            <i id="refresh-icon" class="fa-solid fa-arrows-rotate"></i>
        </button>
        <button id="fullscreen-btn" onclick="toggleFullscreen()" class="px-4 py-2 bg-white/10 hover:bg-white/20 border border-white/20 rounded-lg text-white font-bold transition flex items-center gap-2" title="Fullscreen">
            <i id="fs-icon" class="fa-solid fa-expand"></i>
        </button>
        <button onclick="document.getElementById('bank-modal').classList.remove('hidden'); document.getElementById('bank-modal').classList.add('flex');" class="px-4 py-2 bg-emerald-500/20 hover:bg-emerald-500/40 border border-emerald-500/50 rounded-lg text-emerald-400 font-bold transition flex items-center gap-2">
            <i class="fa-solid fa-building-columns"></i> Bank
        </button>
        <button onclick="document.getElementById('qr-modal').classList.remove('hidden'); document.getElementById('qr-modal').classList.add('flex');" class="px-4 py-2 bg-blue-500 hover:bg-blue-600 rounded-lg text-white font-bold transition flex items-center gap-2 shadow-[0_0_15px_rgba(59,130,246,0.5)]">
            <i class="fa-solid fa-qrcode"></i> Gabung
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
