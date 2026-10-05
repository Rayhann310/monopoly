<?php include '../app/views/templates/admin_header.php'; ?>
<?php include '../app/views/templates/admin_sidebar.php'; ?>

<!-- === TAB: DATABASE === -->
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

<?php include '../app/views/templates/admin_footer.php'; ?>
