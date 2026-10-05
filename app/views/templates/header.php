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
            background: #020617;
            background-image: 
                radial-gradient(circle at 15% 50%, rgba(59, 130, 246, 0.15), transparent 25%),
                radial-gradient(circle at 85% 30%, rgba(236, 72, 153, 0.15), transparent 25%);
            color: white; 
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
        
        /* The Board - White Background */
        .monopoly-board {
            display: grid;
            grid-template-columns: repeat(11, 1fr);
            grid-template-rows: repeat(11, 1fr);
            
            width: 90vmin;
            height: 90vmin;
            max-width: 900px;
            max-height: 900px;
            
            background: #ffffff;
            gap: 3px;
            padding: 10px;
            border-radius: 16px;
            
            box-shadow: 
                0 0 0 1px rgba(255,255,255,0.2),
                0 30px 60px rgba(0,0,0,0.8),
                inset 0 0 0 8px #e2e8f0;
            
            transform: scale(0.97);
        }

        /* Cells - White board style */
        .cell { 
            position: relative; 
            background-color: #ffffff;
            border-radius: 4px;
            box-shadow: inset 0 0 0 1px #e2e8f0;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .cell:hover {
            transform: scale(1.15);
            box-shadow: 0 10px 25px rgba(0,0,0,0.3);
            z-index: 20;
        }

        .cell-content { 
            width: 100%; 
            height: 100%; 
            border-radius: 4px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: space-between;
            background: #ffffff;
        }

        .name { 
            font-size: clamp(0.35rem, 1.2vmin, 0.7rem); 
            font-weight: 900; 
            padding: 3px 2px; 
            color: #1e293b; 
            line-height: 1.2;
            text-align: center;
        }
        
        .price { 
            font-size: clamp(0.35rem, 1.1vmin, 0.6rem); 
            color: #475569;
            font-weight: 700; 
            padding-bottom: 4px;
        }

        .color-bar { 
            height: 28%; 
            width: 100%; 
        }
        
        .c-white { background-color: transparent; }
        .c-blue { background-color: #3b82f6; }
        .c-green { background-color: #22c55e; }
        .c-red { background-color: #ef4444; }
        .c-yellow { background-color: #eab308; }
        .c-orange { background-color: #f97316; }
        .c-pink { background-color: #ec4899; }
        .c-brown { background-color: #8b5cf6; } 
        .c-cyan { background-color: #06b6d4; }

        /* Tokens */
        .player-token {
            width: clamp(14px, 2.8vmin, 24px); 
            height: clamp(14px, 2.8vmin, 24px); 
            border-radius: 50%; 
            position: absolute;
            bottom: 4px; 
            background-image: radial-gradient(circle at 35% 35%, rgba(255,255,255,0.9) 10%, rgba(255,255,255,0) 60%);
            box-shadow: 0 4px 6px rgba(0,0,0,0.8), inset -2px -2px 6px rgba(0,0,0,0.6);
            border: 1px solid rgba(255,255,255,0.8);
            transition: all 0.5s ease-in-out;
        }
        
        .p1 { background-color: #ef4444; left: 10%; z-index: 4; }
        .p2 { background-color: #3b82f6; left: 45%; z-index: 3; }
        .p3 { background-color: #22c55e; left: 25%; bottom: 25%; z-index: 2; }
        .p4 { background-color: #eab308; right: 10%; z-index: 1; }
        
        /* Center Area - White board style */
        .center-space {
            grid-column: 2 / 11;
            grid-row: 2 / 11;
            background: #f8fafc;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            border-radius: 8px;
            border: 2px solid #e2e8f0;
        }

        .center-content-wrapper {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            padding: 30px 50px;
        }

        .title-text { 
            font-size: clamp(2rem, 7vmin, 5rem); 
            color: #0f172a;
            font-weight: 900;
        }
        
        .subtitle-text { 
            font-size: clamp(0.8rem, 2.5vmin, 1.5rem); 
            letter-spacing: 0.4em; 
            color: #ef4444;
            background: #fff0f0;
            padding: 8px 24px;
            border-radius: 30px;
            margin-top: 16px;
            border: 2px solid #fecaca;
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
    </div>
    <div class="flex gap-3">
        <a href="<?= BASEURL ?>/home/apiReset" class="px-4 py-2 bg-red-500/20 hover:bg-red-500/40 border border-red-500/50 rounded-lg text-red-400 font-bold transition flex items-center gap-2">
            <i class="fa-solid fa-rotate-left"></i> Reset Game
        </a>
        <button onclick="document.getElementById('bank-modal').classList.remove('hidden'); document.getElementById('bank-modal').classList.add('flex');" class="px-4 py-2 bg-emerald-500/20 hover:bg-emerald-500/40 border border-emerald-500/50 rounded-lg text-emerald-400 font-bold transition flex items-center gap-2">
            <i class="fa-solid fa-building-columns"></i> Bank Negara
        </button>
        <button onclick="document.getElementById('qr-modal').classList.remove('hidden'); document.getElementById('qr-modal').classList.add('flex');" class="px-4 py-2 bg-blue-500 hover:bg-blue-600 rounded-lg text-white font-bold transition flex items-center gap-2 shadow-[0_0_15px_rgba(59,130,246,0.5)]">
            <i class="fa-solid fa-qrcode"></i> Gabung
        </button>
    </div>
</nav>

<div class="game-wrapper">
