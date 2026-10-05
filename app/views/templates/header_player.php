<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?= $data['judul']; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;700;900&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background: #020617;
            color: white;
            overflow-x: hidden;
            margin: 0; padding: 0;
        }

        /* Swiper / Horizontal Scroll for Cards */
        .card-slider {
            display: flex;
            overflow-x: auto;
            scroll-snap-type: x mandatory;
            gap: 15px;
            padding: 10px 20px;
            -webkit-overflow-scrolling: touch;
        }
        .card-slider::-webkit-scrollbar { display: none; }
        
        .property-card {
            min-width: 140px;
            scroll-snap-align: start;
            background: linear-gradient(135deg, #1e293b, #0f172a);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 12px;
            padding: 15px;
            text-align: center;
            box-shadow: 0 10px 20px rgba(0,0,0,0.5);
        }
        
        .dice-3d {
            font-size: 5rem;
            color: white;
            text-shadow: 0 10px 20px rgba(0,0,0,0.5);
            transition: transform 0.1s;
        }
        
        .shake {
            animation: shake 0.5s infinite;
        }

        @keyframes shake {
            0% { transform: translate(1px, 1px) rotate(0deg); }
            10% { transform: translate(-1px, -2px) rotate(-10deg); }
            20% { transform: translate(-3px, 0px) rotate(10deg); }
            30% { transform: translate(3px, 2px) rotate(0deg); }
            40% { transform: translate(1px, -1px) rotate(10deg); }
            50% { transform: translate(-1px, 2px) rotate(-10deg); }
            60% { transform: translate(-3px, 1px) rotate(0deg); }
            70% { transform: translate(3px, 1px) rotate(-10deg); }
            80% { transform: translate(-1px, -1px) rotate(10deg); }
            90% { transform: translate(1px, 2px) rotate(0deg); }
            100% { transform: translate(1px, -2px) rotate(-10deg); }
        }
    </style>
</head>
<body class="flex flex-col min-h-screen pb-6">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
