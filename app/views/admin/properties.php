<?php include 'app/views/templates/admin_header.php'; ?>
<?php include 'app/views/templates/admin_sidebar.php'; ?>

<div class="max-w-6xl mx-auto px-6 py-8">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-black text-white"><i class="fa-solid fa-city text-cyan-400 mr-3"></i>Gambar Kota</h1>
            <p class="text-slate-500 text-sm mt-1">Upload gambar untuk setiap petak kota di papan permainan.</p>
        </div>
    </div>

    <?php if (!empty($data['success'])): ?>
    <div class="mb-6 bg-emerald-500/20 border border-emerald-500/30 text-emerald-400 rounded-xl px-5 py-4 font-bold flex items-center gap-3">
        <i class="fa-solid fa-circle-check text-xl"></i> <?= htmlspecialchars($data['success']) ?>
    </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
        <?php foreach ($data['board'] as $idx => $cell): ?>
        <?php if (!in_array($cell['type'], ['property', 'station', 'utility'])): continue; endif; ?>
        <?php
            $colorClass = [
                'blue'   => 'border-t-blue-500',
                'green'  => 'border-t-emerald-500',
                'red'    => 'border-t-red-500',
                'yellow' => 'border-t-amber-400',
                'orange' => 'border-t-orange-500',
                'pink'   => 'border-t-pink-400',
                'brown'  => 'border-t-amber-800',
                'cyan'   => 'border-t-cyan-400',
                'purple' => 'border-t-purple-500',
                'white'  => 'border-t-slate-400',
            ][$cell['color']] ?? 'border-t-slate-400';
            $img = $data['images'][$idx] ?? null;
        ?>
        <div class="bg-slate-900/80 border border-white/10 border-t-4 <?= $colorClass ?> rounded-2xl overflow-hidden flex flex-col">
            <!-- Preview -->
            <div class="relative h-36 bg-slate-800 flex items-center justify-center overflow-hidden">
                <?php if ($img): ?>
                    <img src="<?= BASEURL ?>/<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($cell['name']) ?>"
                         class="absolute inset-0 w-full h-full object-cover">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
                <?php else: ?>
                    <div class="text-center text-slate-600">
                        <i class="fa-solid fa-image text-4xl mb-2"></i>
                        <div class="text-xs font-bold">Belum ada gambar</div>
                    </div>
                <?php endif; ?>
                <div class="absolute bottom-2 left-2 right-2">
                    <div class="text-white font-black text-sm drop-shadow-lg"><?= htmlspecialchars($cell['name']) ?></div>
                    <?php if (isset($cell['price'])): ?>
                    <div class="text-white/70 text-xs">Rp <?= number_format($cell['price'], 0, ',', '.') ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Upload Form -->
            <div class="p-4">
                <form action="<?= BASEURL ?>/admin/saveCityImage" method="POST" enctype="multipart/form-data" class="flex flex-col gap-3">
                    <input type="hidden" name="cell_index" value="<?= $idx ?>">
                    <label class="block">
                        <span class="text-slate-400 text-xs font-bold uppercase tracking-wider block mb-1">Upload Gambar</span>
                        <input type="file" name="image" accept="image/*" required
                               class="w-full text-xs text-slate-400 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:bg-blue-500/20 file:text-blue-400 file:font-bold file:cursor-pointer hover:file:bg-blue-500/40 transition">
                    </label>
                    <div class="flex gap-2">
                        <button type="submit" class="flex-1 py-2 bg-blue-500 hover:bg-blue-600 text-white font-bold rounded-lg text-sm transition">
                            <i class="fa-solid fa-upload mr-1"></i> Simpan
                        </button>
                        <?php if ($img): ?>
                        <a href="<?= BASEURL ?>/admin/deleteCityImage/<?= $idx ?>" onclick="return confirm('Hapus gambar ini?')"
                           class="py-2 px-3 bg-red-500/20 hover:bg-red-500/40 text-red-400 font-bold rounded-lg text-sm transition">
                            <i class="fa-solid fa-trash"></i>
                        </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<?php include 'app/views/templates/admin_footer.php'; ?>
