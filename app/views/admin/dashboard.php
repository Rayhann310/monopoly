<?php include '../app/views/templates/admin_header.php'; ?>
<?php include '../app/views/templates/admin_sidebar.php'; ?>

<!-- === TAB: OVERVIEW === -->
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

<?php include '../app/views/templates/admin_footer.php'; ?>
