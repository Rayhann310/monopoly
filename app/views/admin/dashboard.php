<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard — Monopoly</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;700;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', sans-serif; background: #020617; color: white; min-height: 100vh; }
        .tab-btn.active { background: rgba(59,130,246,0.2); border-color: rgba(59,130,246,0.5); color: #60a5fa; }
        .tab-content { display: none; } .tab-content.active { display: block; }
    </style>
</head>
<body>

<?php if (isset($_GET['saved'])): ?>
<script>document.addEventListener('DOMContentLoaded', () => Swal.fire({title:'Tersimpan!',icon:'success',background:'#0f172a',color:'#fff',timer:1500,showConfirmButton:false}));</script>
<?php endif; ?>
<?php if (isset($_GET['repaired'])): ?>
<script>document.addEventListener('DOMContentLoaded', () => Swal.fire({title:'Database Diperbaiki!',text:'Semua tabel sudah dicek dan diperbaiki.',icon:'success',background:'#0f172a',color:'#fff'}));</script>
<?php endif; ?>

<!-- Sidebar + Main Layout -->
<div class="flex min-h-screen">

    <!-- Sidebar -->
    <aside class="w-64 bg-slate-950 border-r border-white/5 flex flex-col fixed h-full z-30">
        <div class="p-6 border-b border-white/5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-gradient-to-br from-red-500 to-rose-700 rounded-xl flex items-center justify-center">
                    <i class="fa-solid fa-shield-halved text-white"></i>
                </div>
                <div>
                    <div class="text-white font-black text-sm">Admin Panel</div>
                    <div class="text-slate-500 text-xs">Monopoly Indonesia</div>
                </div>
            </div>
        </div>

        <nav class="flex-1 p-4 space-y-1">
            <button onclick="showTab('overview')" class="tab-btn active w-full text-left px-4 py-3 rounded-xl border border-transparent text-slate-400 hover:text-white transition flex items-center gap-3 font-bold" id="tab-btn-overview">
                <i class="fa-solid fa-chart-pie w-5"></i> Overview
            </button>
            <button onclick="showTab('settings')" class="tab-btn w-full text-left px-4 py-3 rounded-xl border border-transparent text-slate-400 hover:text-white transition flex items-center gap-3 font-bold" id="tab-btn-settings">
                <i class="fa-solid fa-gear w-5"></i> Pengaturan Game
            </button>
            <button onclick="showTab('sessions')" class="tab-btn w-full text-left px-4 py-3 rounded-xl border border-transparent text-slate-400 hover:text-white transition flex items-center gap-3 font-bold" id="tab-btn-sessions">
                <i class="fa-solid fa-gamepad w-5"></i> Sesi Aktif
            </button>
            <button onclick="showTab('database')" class="tab-btn w-full text-left px-4 py-3 rounded-xl border border-transparent text-slate-400 hover:text-white transition flex items-center gap-3 font-bold" id="tab-btn-database">
                <i class="fa-solid fa-database w-5"></i> Database
            </button>
            <button onclick="showTab('password')" class="tab-btn w-full text-left px-4 py-3 rounded-xl border border-transparent text-slate-400 hover:text-white transition flex items-center gap-3 font-bold" id="tab-btn-password">
                <i class="fa-solid fa-key w-5"></i> Ganti Password
            </button>
        </nav>

        <div class="p-4 border-t border-white/5 space-y-2">
            <a href="<?= BASEURL ?>/admin/cards" class="w-full text-left px-4 py-3 rounded-xl border border-white/10 text-slate-400 hover:text-white transition flex items-center gap-3 font-bold">
                <i class="fa-solid fa-cards-blank w-5"></i> Kelola Kartu
            </a>
            <a href="<?= BASEURL ?>/setup" class="block px-4 py-3 rounded-xl text-slate-500 hover:text-white transition flex items-center gap-3 text-sm">
                <i class="fa-solid fa-house w-5"></i> Kembali ke Lobby
            </a>
            <a href="<?= BASEURL ?>/admin/logout" class="block px-4 py-3 rounded-xl text-red-500/70 hover:text-red-400 transition flex items-center gap-3 text-sm font-bold">
                <i class="fa-solid fa-right-from-bracket w-5"></i> Logout
            </a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="ml-64 flex-1 p-8">

        <!-- Header -->
        <div class="flex items-center justify-between mb-10">
            <div>
                <h1 class="text-3xl font-black text-white">Dashboard Admin</h1>
                <p class="text-slate-500">Halo, <span class="text-blue-400 font-bold"><?= htmlspecialchars($data['admin']) ?></span></p>
            </div>
            <div class="text-slate-600 text-sm"><?= date('l, d F Y — H:i') ?></div>
        </div>

        <!-- === TAB: OVERVIEW === -->
        <div id="tab-overview" class="tab-content active">
            <!-- Stats Cards -->
            <div class="grid grid-cols-3 gap-6 mb-10">
                <div class="bg-slate-900/80 border border-white/10 rounded-2xl p-6">
                    <div class="text-slate-500 text-sm font-bold uppercase tracking-widest mb-3">Total Sesi</div>
                    <div class="text-5xl font-black text-white"><?= $data['total_sessions'] ?></div>
                </div>
                <div class="bg-emerald-500/10 border border-emerald-500/30 rounded-2xl p-6">
                    <div class="text-emerald-400 text-sm font-bold uppercase tracking-widest mb-3"><i class="fa-solid fa-circle animate-pulse mr-1"></i>Sesi Aktif</div>
                    <div class="text-5xl font-black text-emerald-400"><?= $data['active_sessions'] ?></div>
                </div>
                <div class="bg-blue-500/10 border border-blue-500/30 rounded-2xl p-6">
                    <div class="text-blue-400 text-sm font-bold uppercase tracking-widest mb-3">Total Pemain</div>
                    <div class="text-5xl font-black text-blue-400"><?= $data['total_players'] ?></div>
                </div>
            </div>

            <!-- Quick Settings Preview -->
            <div class="bg-slate-900/80 border border-white/10 rounded-2xl p-6">
                <h3 class="font-black text-white text-xl mb-5"><i class="fa-solid fa-sliders text-blue-400 mr-2"></i>Pengaturan Aktif</h3>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                    <?php foreach ($data['settings'] as $key => $s): ?>
                    <div class="bg-slate-800 rounded-xl p-4 border border-white/5">
                        <div class="text-slate-500 text-xs font-bold uppercase mb-1"><?= htmlspecialchars($s['label']) ?></div>
                        <div class="text-white font-black text-xl">
                            <?= $s['type'] === 'number' ? 'Rp ' . number_format($s['setting_value'],0,',','.') : ($s['setting_value'] == 1 ? '✅ Aktif' : '❌ Nonaktif') ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- === TAB: SETTINGS === -->
        <div id="tab-settings" class="tab-content">
            <h2 class="text-2xl font-black text-white mb-6"><i class="fa-solid fa-gear text-blue-400 mr-2"></i>Pengaturan Permainan</h2>
            <form action="<?= BASEURL ?>/admin/settings" method="POST">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                    <?php foreach ($data['settings'] as $key => $s): ?>
                    <div class="bg-slate-900/80 border border-white/10 rounded-2xl p-6">
                        <label class="text-slate-400 text-sm font-bold uppercase tracking-widest block mb-3"><?= htmlspecialchars($s['label']) ?></label>
                        <?php if ($s['type'] === 'boolean'): ?>
                        <div class="flex items-center gap-3">
                            <input type="hidden" name="settings[<?= $key ?>]" value="0">
                            <input type="checkbox" name="settings[<?= $key ?>]" value="1" <?= $s['setting_value'] ? 'checked' : '' ?>
                                class="w-5 h-5 accent-blue-500">
                            <span class="text-white font-bold"><?= $s['setting_value'] ? 'Aktif' : 'Nonaktif' ?></span>
                        </div>
                        <?php else: ?>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-500 font-bold">Rp</span>
                            <input type="number" name="settings[<?= $key ?>]" value="<?= htmlspecialchars($s['setting_value']) ?>" min="0"
                                class="w-full bg-slate-800 border border-white/10 rounded-xl pl-12 pr-4 py-3 text-white font-black text-xl focus:outline-none focus:border-blue-500 transition">
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
                <button type="submit" class="px-10 py-4 bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 text-white font-black text-lg rounded-2xl transition shadow-[0_0_25px_rgba(59,130,246,0.3)]">
                    <i class="fa-solid fa-floppy-disk mr-2"></i> Simpan Pengaturan
                </button>
            </form>
        </div>

        <!-- === TAB: SESSIONS === -->
        <div id="tab-sessions" class="tab-content">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-black text-white"><i class="fa-solid fa-gamepad text-emerald-400 mr-2"></i>Semua Sesi</h2>
                <button onclick="location.reload()" class="px-4 py-2 bg-white/10 rounded-lg text-slate-400 hover:text-white transition text-sm font-bold">
                    <i class="fa-solid fa-arrows-rotate mr-1"></i> Refresh
                </button>
            </div>
            <div class="bg-slate-900/80 border border-white/10 rounded-2xl overflow-hidden">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-white/5 text-slate-500 text-xs uppercase tracking-widest">
                            <th class="px-6 py-4 text-left">Nama Sesi</th>
                            <th class="px-6 py-4 text-left">Pemain</th>
                            <th class="px-6 py-4 text-left">Status</th>
                            <th class="px-6 py-4 text-left">Waktu</th>
                            <th class="px-6 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($data['sessions'] as $s): ?>
                        <?php $sc = ['waiting'=>'yellow','playing'=>'emerald','finished'=>'slate'][$s['status']] ?? 'slate'; ?>
                        <tr class="border-b border-white/5 hover:bg-white/5 transition">
                            <td class="px-6 py-4 font-bold text-white"><?= htmlspecialchars($s['name']) ?></td>
                            <td class="px-6 py-4 text-slate-400"><i class="fa-solid fa-users mr-1"></i><?= $s['player_count'] ?></td>
                            <td class="px-6 py-4">
                                <span class="text-xs font-black px-3 py-1 rounded-full bg-<?= $sc ?>-500/20 text-<?= $sc ?>-400 border border-<?= $sc ?>-500/30 uppercase">
                                    <?= $s['status'] ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-slate-500 text-sm"><?= date('d/m H:i', strtotime($s['created_at'])) ?></td>
                            <td class="px-6 py-4">
                                <div class="flex gap-2 justify-end">
                                    <a href="<?= BASEURL ?>/home/game/<?= $s['id'] ?>" target="_blank" class="px-3 py-2 bg-blue-500/20 text-blue-400 hover:bg-blue-500/40 rounded-lg text-sm font-bold transition">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                    <?php if ($s['status'] === 'playing'): ?>
                                    <a href="<?= BASEURL ?>/admin/stopSession/<?= $s['id'] ?>" onclick="return confirm('Hentikan sesi ini?')" class="px-3 py-2 bg-orange-500/20 text-orange-400 hover:bg-orange-500/40 rounded-lg text-sm font-bold transition">
                                        <i class="fa-solid fa-stop"></i>
                                    </a>
                                    <?php endif; ?>
                                    <a href="<?= BASEURL ?>/admin/deleteSession/<?= $s['id'] ?>" onclick="return confirm('Hapus sesi ini permanen?')" class="px-3 py-2 bg-red-500/20 text-red-400 hover:bg-red-500/40 rounded-lg text-sm font-bold transition">
                                        <i class="fa-solid fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($data['sessions'])): ?>
                        <tr><td colspan="5" class="px-6 py-12 text-center text-slate-600">Belum ada sesi permainan.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- === TAB: DATABASE === -->
        <div id="tab-database" class="tab-content">
            <h2 class="text-2xl font-black text-white mb-6"><i class="fa-solid fa-database text-purple-400 mr-2"></i>Manajemen Database</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-slate-900/80 border border-white/10 rounded-2xl p-6">
                    <h3 class="font-black text-white text-lg mb-2"><i class="fa-solid fa-wrench text-emerald-400 mr-2"></i>Perbaiki Database</h3>
                    <p class="text-slate-500 text-sm mb-6">Jalankan self-healing: cek semua tabel, tambahkan kolom yang hilang, insert data default jika kosong.</p>
                    <a href="<?= BASEURL ?>/admin/repairDb" onclick="return confirm('Jalankan perbaikan database?')"
                        class="inline-flex items-center gap-2 px-6 py-3 bg-emerald-500/20 hover:bg-emerald-500/40 border border-emerald-500/30 text-emerald-400 font-bold rounded-xl transition">
                        <i class="fa-solid fa-rotate"></i> Jalankan Self-Heal
                    </a>
                </div>
                <div class="bg-slate-900/80 border border-white/10 rounded-2xl p-6">
                    <h3 class="font-black text-white text-lg mb-2"><i class="fa-solid fa-broom text-orange-400 mr-2"></i>Bersihkan Sesi Lama</h3>
                    <p class="text-slate-500 text-sm mb-6">Hapus semua sesi dengan status "finished" beserta data pemain dan propertinya.</p>
                    <a href="<?= BASEURL ?>/admin/cleanFinished" onclick="return confirm('Hapus semua sesi yang sudah selesai?')"
                        class="inline-flex items-center gap-2 px-6 py-3 bg-orange-500/20 hover:bg-orange-500/40 border border-orange-500/30 text-orange-400 font-bold rounded-xl transition">
                        <i class="fa-solid fa-broom"></i> Bersihkan Sesi Selesai
                    </a>
                </div>
                <div class="bg-slate-900/80 border border-white/10 rounded-2xl p-6 md:col-span-2">
                    <h3 class="font-black text-white text-lg mb-4"><i class="fa-solid fa-table text-blue-400 mr-2"></i>Tabel yang Dikelola</h3>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                        <?php
                        $tables = ['admins','game_settings','cards','sessions','players','properties','game_log'];
                        $icons = ['fa-user-shield','fa-sliders','fa-id-card','fa-gamepad','fa-users','fa-building','fa-list'];
                        foreach ($tables as $i => $t): ?>
                        <div class="bg-slate-800 rounded-xl px-4 py-3 flex items-center gap-3 border border-white/5">
                            <i class="fa-solid <?= $icons[$i] ?> text-blue-400/60"></i>
                            <span class="text-white font-mono text-sm font-bold"><?= $t ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- === TAB: PASSWORD === -->
        <div id="tab-password" class="tab-content">
            <h2 class="text-2xl font-black text-white mb-6"><i class="fa-solid fa-key text-yellow-400 mr-2"></i>Ganti Password Admin</h2>
            <div class="max-w-md bg-slate-900/80 border border-white/10 rounded-2xl p-8">
                <form method="POST" action="<?= BASEURL ?>/admin/changePassword">
                    <div class="mb-5">
                        <label class="text-slate-400 text-sm font-bold uppercase tracking-widest block mb-2">Password Baru</label>
                        <input type="password" name="new_password" required minlength="6" placeholder="Min. 6 karakter"
                            class="w-full bg-slate-800 border border-white/10 rounded-xl px-4 py-3 text-white font-bold focus:outline-none focus:border-blue-500 transition">
                    </div>
                    <button type="submit" class="w-full py-3 bg-gradient-to-r from-yellow-500 to-orange-500 text-white font-black rounded-xl">
                        <i class="fa-solid fa-save mr-2"></i> Simpan Password
                    </button>
                </form>
            </div>
        </div>

    </main>
</div>

<script>
function showTab(name) {
    document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.getElementById('tab-' + name).classList.add('active');
    document.getElementById('tab-btn-' + name).classList.add('active');
}
</script>
</body>
</html>
