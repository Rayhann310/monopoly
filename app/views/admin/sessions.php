<?php include 'app/views/templates/admin_header.php'; ?>
<?php include 'app/views/templates/admin_sidebar.php'; ?>

<!-- === TAB: SESSIONS === -->
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

<?php include 'app/views/templates/admin_footer.php'; ?>
