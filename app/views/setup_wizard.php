<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monopoly Setup Wizard</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-900 text-slate-200 min-h-screen flex items-center justify-center p-4">
    <div class="bg-slate-800 p-8 rounded-2xl shadow-2xl max-w-lg w-full border border-slate-700">
        <h2 class="text-3xl font-bold text-white mb-2 text-center text-emerald-400">🚀 Setup Monopoly</h2>
        <p class="text-slate-400 text-sm text-center mb-6">File konfigurasi <code>.env</code> tidak ditemukan. Silakan isi form di bawah ini untuk menghubungkan aplikasi dengan database.</p>
        
        <form method="POST" action="">
            <input type="hidden" name="setup_action" value="1">
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold mb-1">APP_URL (Base URL)</label>
                    <input type="text" name="app_url" class="w-full p-2 bg-slate-900 border border-slate-700 rounded-lg text-white" value="<?php echo htmlspecialchars($guessUrl); ?>" required>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold mb-1">DB_HOST</label>
                        <input type="text" name="db_host" class="w-full p-2 bg-slate-900 border border-slate-700 rounded-lg text-white" value="localhost" required>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-1">DB_PORT</label>
                        <input type="number" name="db_port" class="w-full p-2 bg-slate-900 border border-slate-700 rounded-lg text-white" value="3306" required>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">DB_NAME (Nama Database)</label>
                    <input type="text" name="db_name" class="w-full p-2 bg-slate-900 border border-slate-700 rounded-lg text-white" value="monopoly_db" required>
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">DB_USER (User Database)</label>
                    <input type="text" name="db_user" class="w-full p-2 bg-slate-900 border border-slate-700 rounded-lg text-white" value="root" required>
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">DB_PASS (Password Database)</label>
                    <input type="password" name="db_pass" class="w-full p-2 bg-slate-900 border border-slate-700 rounded-lg text-white" placeholder="(Kosongkan jika tidak ada password)">
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">Password Admin (Default)</label>
                    <input type="text" name="admin_pass" class="w-full p-2 bg-slate-900 border border-slate-700 rounded-lg text-white" value="admin123" required>
                </div>
            </div>

            <button type="submit" class="w-full mt-8 bg-emerald-600 hover:bg-emerald-500 text-white font-bold py-3 rounded-lg transition-colors">
                Simpan & Lanjutkan
            </button>
        </form>
    </div>
</body>
</html>
