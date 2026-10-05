<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login — Monopoly Indonesia</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;700;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', sans-serif; background: #020617; min-height: 100vh; display: flex; align-items: center; justify-content: center; }
    </style>
</head>
<body>
<div class="w-full max-w-md px-4">
    <!-- Logo -->
    <div class="text-center mb-10">
        <div class="w-20 h-20 bg-gradient-to-br from-red-500 to-rose-700 rounded-2xl flex items-center justify-center shadow-[0_0_40px_rgba(239,68,68,0.4)] mx-auto mb-4">
            <i class="fa-solid fa-shield-halved text-white text-3xl"></i>
        </div>
        <h1 class="text-4xl font-black text-white">Admin Panel</h1>
        <p class="text-slate-500 mt-1">Monopoly Indonesia</p>
    </div>

    <!-- Form -->
    <div class="bg-slate-900 border border-white/10 rounded-3xl p-8 shadow-2xl">
        <?php if (!empty($data['error'])): ?>
        <div class="bg-red-500/20 border border-red-500/40 text-red-400 rounded-xl px-4 py-3 mb-6 flex items-center gap-3">
            <i class="fa-solid fa-circle-exclamation"></i>
            <?= htmlspecialchars($data['error']) ?>
        </div>
        <?php endif; ?>

        <form method="POST" action="<?= BASEURL ?>/admin/login">
            <div class="mb-5">
                <label class="text-slate-400 text-sm font-bold uppercase tracking-widest block mb-2">Username</label>
                <div class="relative">
                    <i class="fa-solid fa-user absolute left-4 top-1/2 -translate-y-1/2 text-slate-500"></i>
                    <input type="text" name="username" required placeholder="admin"
                        class="w-full bg-slate-800 border border-white/10 rounded-xl pl-11 pr-4 py-3 text-white font-bold focus:outline-none focus:border-blue-500 transition placeholder:text-slate-600">
                </div>
            </div>
            <div class="mb-8">
                <label class="text-slate-400 text-sm font-bold uppercase tracking-widest block mb-2">Password</label>
                <div class="relative">
                    <i class="fa-solid fa-lock absolute left-4 top-1/2 -translate-y-1/2 text-slate-500"></i>
                    <input type="password" name="password" required placeholder="••••••••"
                        class="w-full bg-slate-800 border border-white/10 rounded-xl pl-11 pr-4 py-3 text-white font-bold focus:outline-none focus:border-blue-500 transition placeholder:text-slate-600">
                </div>
            </div>
            <button type="submit" class="w-full py-4 bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 text-white font-black text-lg rounded-2xl transition hover:scale-[1.02] shadow-[0_0_25px_rgba(59,130,246,0.3)]">
                <i class="fa-solid fa-right-to-bracket mr-2"></i> Masuk
            </button>
        </form>
        <p class="text-center text-slate-600 text-sm mt-6">Default: <span class="text-slate-400 font-mono">admin / admin123</span></p>
    </div>

    <div class="text-center mt-6">
        <a href="<?= BASEURL ?>/setup" class="text-slate-600 hover:text-slate-400 transition text-sm">
            <i class="fa-solid fa-arrow-left mr-1"></i> Kembali ke Lobby
        </a>
    </div>
</div>
</body>
</html>
