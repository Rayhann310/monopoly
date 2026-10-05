<?php include '../app/views/templates/admin_header.php'; ?>
<?php include '../app/views/templates/admin_sidebar.php'; ?>

<!-- === TAB: SETTINGS === -->
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

<?php include '../app/views/templates/admin_footer.php'; ?>
