<?php include 'app/views/templates/admin_header.php'; ?>
<?php include 'app/views/templates/admin_sidebar.php'; ?>
<div class="max-w-7xl mx-auto px-4 py-8">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-black text-white"><i class="fa-solid fa-city text-cyan-400 mr-3"></i>Pengaturan Kota</h1>
            <p class="text-slate-500 text-sm mt-1">Upload gambar, atur nama, harga tanah, dan harga per tingkat rumah.</p>
        </div>
        <div class="text-right text-xs text-slate-500">
            <div class="text-slate-400 font-bold">Ukuran Gambar Ideal</div>
            <div>500x500 px atau 800x800 px (kotak 1:1)</div>
        </div>
    </div>
    <?php if (!empty($data['success'])): ?>
    <div class="mb-6 bg-emerald-500/20 border border-emerald-500/30 text-emerald-400 rounded-xl px-5 py-4 font-bold flex items-center gap-3">
        <i class="fa-solid fa-circle-check text-xl"></i> <?= htmlspecialchars($data['success']) ?>
    </div>
    <?php endif; ?>
    <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">
        <?php foreach ($data['board'] as $idx => $cell): ?>
        <?php if (!in_array($cell['type'], ['property', 'station', 'utility'])): continue; endif; ?>
        <?php
            $colorMap = [
                'blue'   => ['border' => 'border-t-blue-500'],
                'green'  => ['border' => 'border-t-emerald-500'],
                'red'    => ['border' => 'border-t-red-500'],
                'yellow' => ['border' => 'border-t-amber-400'],
                'orange' => ['border' => 'border-t-orange-500'],
                'pink'   => ['border' => 'border-t-pink-400'],
                'brown'  => ['border' => 'border-t-amber-800'],
                'cyan'   => ['border' => 'border-t-cyan-400'],
                'purple' => ['border' => 'border-t-purple-500'],
                'white'  => ['border' => 'border-t-slate-400'],
            ];
            $colorInfo = $colorMap[$cell['color']] ?? $colorMap['white'];
            $img = $data['images'][$idx] ?? null;
            $cp  = $data['custom_props'][$idx] ?? [];
            $isProperty = $cell['type'] === 'property';
            $defLevelNames = ['Kavling', 'Rumah', 'Rumah 2', 'Rumah 3', 'Hotel'];
            $basePrice = (int)($cp['price'] ?? $cell['price'] ?? 100);
            $defLevelPrices = [
                (int)round($basePrice * 0.5),
                (int)round($basePrice * 0.5),
                (int)round($basePrice * 0.75),
                (int)round($basePrice * 1.0),
                (int)round($basePrice * 1.5),
            ];
            $defLevelRents = [
                (int)round($basePrice * 0.05),
                (int)round($basePrice * 0.1),
                (int)round($basePrice * 0.25),
                (int)round($basePrice * 0.5),
                (int)round($basePrice * 1.0),
            ];
        ?>
        <div class="bg-slate-900/80 border border-white/10 border-t-4 <?= $colorInfo['border'] ?> rounded-2xl overflow-hidden flex flex-col">
            <div class="relative h-32 bg-slate-800 flex items-center justify-center overflow-hidden">
                <?php if ($img): ?>
                    <img src="<?= BASEURL ?>/<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($cell['name']) ?>" class="absolute inset-0 w-full h-full object-cover">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/70 to-transparent"></div>
                <?php else: ?>
                    <div class="text-center text-slate-600">
                        <i class="fa-solid fa-image text-3xl mb-1"></i>
                        <div class="text-xs">Belum ada gambar</div>
                    </div>
                <?php endif; ?>
                <div class="absolute bottom-2 left-2 right-2">
                    <div class="text-white font-black text-sm drop-shadow-lg"><?= htmlspecialchars($cell['name']) ?></div>
                    <div class="text-white/60 text-xs"><?= ucfirst($cell['type']) ?> - Rp <?= number_format((int)($cp['price'] ?? $cell['price'] ?? 0), 0, ',', '.') ?></div>
                </div>
            </div>
            <div class="p-4">
                <form action="<?= BASEURL ?>/admin/saveCityImage" method="POST" enctype="multipart/form-data" class="flex flex-col gap-3">
                    <input type="hidden" name="cell_index" value="<?= $idx ?>">
                    <div class="grid grid-cols-2 gap-2">
                        <label class="block col-span-2">
                            <span class="text-slate-400 text-xs font-bold uppercase tracking-wider block mb-1">Nama</span>
                            <input type="text" name="name" value="<?= htmlspecialchars($cp['name'] ?? $cell['name']) ?>"
                                   class="w-full bg-slate-800 border border-white/10 rounded-lg px-3 py-1.5 text-sm text-white focus:border-blue-500 focus:outline-none">
                        </label>
                        <label class="block">
                            <span class="text-slate-400 text-xs font-bold uppercase tracking-wider block mb-1">Harga Beli Tanah</span>
                            <input type="number" name="price" value="<?= htmlspecialchars($cp['price'] ?? $cell['price'] ?? '') ?>"
                                   class="w-full bg-slate-800 border border-white/10 rounded-lg px-3 py-1.5 text-sm text-white focus:border-blue-500 focus:outline-none">
                        </label>
                        <label class="block">
                            <span class="text-slate-400 text-xs font-bold uppercase tracking-wider block mb-1">Sewa Level 1</span>
                            <input type="number" name="level1_rent" value="<?= htmlspecialchars($cp['level1_rent'] ?? $defLevelRents[0]) ?>"
                                   class="w-full bg-slate-800 border border-white/10 rounded-lg px-3 py-1.5 text-sm text-white focus:border-blue-500 focus:outline-none">
                        </label>
                    </div>
                    <?php if ($isProperty): ?>
                    <div class="border border-white/10 rounded-xl overflow-hidden">
                        <div class="bg-slate-800/60 px-3 py-2 text-xs font-black text-slate-400 uppercase tracking-wider flex items-center gap-2">
                            <i class="fa-solid fa-layer-group text-blue-400"></i> Tingkatan Properti (Level 2-5)
                        </div>
                        <div class="p-3 flex flex-col gap-2">
                            <?php for ($lvl = 2; $lvl <= 5; $lvl++):
                                $lk = "level{$lvl}";
                                $defName  = $defLevelNames[$lvl - 1];
                                $defPrice = $defLevelPrices[$lvl - 1];
                                $defRent  = $defLevelRents[$lvl - 1];
                                $isHotel  = ($lvl === 5);
                            ?>
                            <div class="bg-slate-800/40 rounded-lg p-2">
                                <div class="flex items-center gap-1 mb-2">
                                    <i class="fa-solid <?= $isHotel ? 'fa-hotel text-red-400' : 'fa-house text-emerald-400' ?> text-xs"></i>
                                    <span class="text-xs font-bold text-slate-300">Level <?= $lvl ?> <?= $isHotel ? '(Hotel)' : '' ?></span>
                                </div>
                                <div class="grid grid-cols-3 gap-1">
                                    <label>
                                        <div class="text-slate-500 text-[10px] mb-0.5">Nama Tingkat</div>
                                        <input type="text" name="<?= $lk ?>_name"
                                               value="<?= htmlspecialchars($cp[$lk.'_name'] ?? $defName) ?>"
                                               placeholder="<?= $defName ?>"
                                               class="w-full bg-slate-900 border border-white/10 rounded px-2 py-1 text-xs text-white focus:border-blue-500 focus:outline-none">
                                    </label>
                                    <label>
                                        <div class="text-slate-500 text-[10px] mb-0.5">Harga Beli</div>
                                        <input type="number" name="<?= $lk ?>_price"
                                               value="<?= htmlspecialchars($cp[$lk.'_price'] ?? $defPrice) ?>"
                                               placeholder="<?= $defPrice ?>"
                                               class="w-full bg-slate-900 border border-white/10 rounded px-2 py-1 text-xs text-white focus:border-blue-500 focus:outline-none">
                                    </label>
                                    <label>
                                        <div class="text-slate-500 text-[10px] mb-0.5">Sewa</div>
                                        <input type="number" name="<?= $lk ?>_rent"
                                               value="<?= htmlspecialchars($cp[$lk.'_rent'] ?? $defRent) ?>"
                                               placeholder="<?= $defRent ?>"
                                               class="w-full bg-slate-900 border border-white/10 rounded px-2 py-1 text-xs text-white focus:border-blue-500 focus:outline-none">
                                    </label>
                                </div>
                            </div>
                            <?php endfor; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    <label class="block">
                        <span class="text-slate-400 text-xs font-bold uppercase tracking-wider block mb-1">
                            <i class="fa-solid fa-image mr-1"></i> <?= $img ? 'Ganti' : 'Upload' ?> Gambar (500x500 px)
                        </span>
                        <input type="file" name="image" accept="image/*"
                               class="w-full text-xs text-slate-400 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:bg-blue-500/20 file:text-blue-400 file:font-bold file:cursor-pointer hover:file:bg-blue-500/40 transition">
                    </label>
                    <div class="flex gap-2 mt-1">
                        <button type="submit" class="flex-1 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-lg text-sm transition flex items-center justify-center gap-2">
                            <i class="fa-solid fa-floppy-disk"></i> Simpan
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
