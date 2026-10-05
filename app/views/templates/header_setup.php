<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $data['judul']; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;700;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', sans-serif; background: #020617; color: white; min-height: 100vh; }
        .card-glow { box-shadow: 0 0 40px rgba(59,130,246,0.15); }
        .session-card:hover { transform: translateY(-4px); transition: all 0.3s; }
        @keyframes float { 0%,100% { transform: translateY(0); } 50% { transform: translateY(-10px); } }
        .float { animation: float 4s ease-in-out infinite; }
    </style>
</head>
<body>
