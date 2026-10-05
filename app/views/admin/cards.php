<?php include '../app/views/templates/admin_header.php'; ?>
<?php include '../app/views/templates/admin_sidebar.php'; ?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Kelola Kartu — Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;700;900&display=swap" rel="stylesheet">
    <style>body{font-family:'Outfit',sans-serif;background:#020617;color:white;min-height:100vh;}</style>
</head>
<body class="p-6">
    <div class="max-w-5xl mx-auto">
        <div class="flex items-center justify-between mb-8">
            <div>
                <a href="<?= BASEURL ?>/admin/dashboard" class="text-slate-500 hover:text-white text-sm mb-2 block"><i class="fa-solid fa-arrow-left mr-1"></i> Dashboard</a>
                <h1 class="text-3xl font-black"><i class="fa-solid fa-cards-blank text-amber-400 mr-3"></i>Kelola Kartu</h1>
            </div>
            <button onclick="document.getElementById('add-modal').classList.remove('hidden');document.getElementById('add-modal').classList.add('flex');"
                class="px-6 py-3 bg-blue-500 hover:bg-blue-600 text-white font-bold rounded-xl transition">
                <i class="fa-solid fa-plus mr-2"></i> Tambah Kartu
            </button>
        </div>

        <!-- Tabs -->
        <div class="flex gap-3 mb-6">
            <button onclick="filterCards('all')" id="f-all" class="filter-btn px-4 py-2 bg-white/20 text-white font-bold rounded-lg">Semua</button>
            <button onclick="filterCards('kesempatan')" id="f-kesempatan" class="filter-btn px-4 py-2 bg-white/10 text-slate-400 font-bold rounded-lg"><i class="fa-solid fa-question text-amber-400 mr-2"></i>Kesempatan</button>
            <button onclick="filterCards('dana_umum')" id="f-dana_umum" class="filter-btn px-4 py-2 bg-white/10 text-slate-400 font-bold rounded-lg"><i class="fa-solid fa-gem text-emerald-400 mr-2"></i>Dana Umum</button>
        </div>

        <!-- Cards List -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <?php foreach ($data['cards'] as $card): ?>
            <div class="card-item bg-slate-900/80 border border-white/10 rounded-2xl p-5 <?= $card['is_active'] ? '' : 'opacity-40' ?>"
                 data-type="<?= $card['type'] ?>">
                <div class="flex items-start justify-between mb-3">
                    <span class="text-xs font-black px-3 py-1 rounded-full <?= $card['type']==='kesempatan' ? 'bg-amber-500/20 text-amber-400' : 'bg-emerald-500/20 text-emerald-400' ?>">
                        <?php if($card['type'] === 'kesempatan'): ?>
                            <i class="fa-solid fa-question mr-1"></i> KESEMPATAN
                        <?php else: ?>
                            <i class="fa-solid fa-gem mr-1"></i> DANA UMUM
                        <?php endif; ?>
                    </span>
                    <?php if (!$card['is_active']): ?>
                    <span class="text-xs text-red-400 font-bold">NONAKTIF</span>
                    <?php endif; ?>
                </div>
                <p class="text-white font-bold mb-3 leading-relaxed"><?= htmlspecialchars($card['text']) ?></p>
                <div class="flex items-center justify-between">
                    <div class="text-xs text-slate-500">
                        Efek: <span class="text-slate-300 font-bold"><?= $card['effect_type'] ?></span>
                        <?php if ($card['effect_value'] != 0): ?>
                        | Nilai: <span class="<?= $card['effect_value'] > 0 ? 'text-emerald-400' : 'text-red-400' ?> font-bold">
                            <?= $card['effect_value'] > 0 ? '+' : '' ?>Rp <?= number_format(abs($card['effect_value']),0,',','.') ?>
                        </span>
                        <?php endif; ?>
                    </div>
                    <div class="flex gap-2">
                        <button onclick="editCard(<?= htmlspecialchars(json_encode($card)) ?>)"
                            class="px-3 py-1.5 bg-blue-500/20 text-blue-400 hover:bg-blue-500/40 rounded-lg text-sm font-bold transition">
                            <i class="fa-solid fa-pen"></i>
                        </button>
                        <a href="<?= BASEURL ?>/admin/deleteCard/<?= $card['id'] ?>" onclick="return confirm('Hapus kartu ini?')"
                            class="px-3 py-1.5 bg-red-500/20 text-red-400 hover:bg-red-500/40 rounded-lg text-sm font-bold transition">
                            <i class="fa-solid fa-trash"></i>
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Add/Edit Card Modal -->
    <div id="add-modal" class="hidden fixed inset-0 bg-black/90 z-50 items-center justify-center p-4">
        <div class="bg-slate-900 border border-white/10 rounded-2xl p-8 w-full max-w-lg">
            <h2 class="text-2xl font-black mb-6" id="modal-title"><i class="fa-solid fa-plus-circle text-blue-400 mr-2"></i>Tambah Kartu</h2>
            <form method="POST" action="<?= BASEURL ?>/admin/saveCard" enctype="multipart/form-data">
                <input type="hidden" name="id" id="card-id" value="0">
                <div class="mb-4">
                    <label class="text-slate-400 text-sm font-bold uppercase mb-2 block">Tipe Kartu</label>
                    <select name="type" id="card-type" class="w-full bg-slate-800 border border-white/10 rounded-xl px-4 py-3 text-white font-bold focus:outline-none focus:border-blue-500">
                        <option value="kesempatan">Kesempatan</option>
                        <option value="dana_umum">Dana Umum</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label class="block text-slate-400 font-bold mb-2 text-sm uppercase tracking-wider">Gambar Kartu (Opsional)</label>
                    <input type="file" name="image" id="card_image" accept="image/*" class="w-full bg-slate-800 border border-white/10 rounded-xl px-4 py-3 text-slate-400 focus:outline-none focus:border-blue-500">
                    <p class="text-xs text-slate-500 mt-1">Format: JPG/PNG. Kosongkan jika tidak ingin mengubah.</p>
                </div>
                <div class="mb-4">
                    <label class="text-slate-400 text-sm font-bold uppercase mb-2 block">Teks Kartu</label>
                    <textarea name="text" id="card-text" required rows="3" placeholder="Isi instruksi kartu..."
                        class="w-full bg-slate-800 border border-white/10 rounded-xl px-4 py-3 text-white font-bold focus:outline-none focus:border-blue-500 resize-none"></textarea>
                </div>
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="text-slate-400 text-sm font-bold uppercase mb-2 block">Jenis Efek</label>
                        <select name="effect_type" id="card-effect" class="w-full bg-slate-800 border border-white/10 rounded-xl px-4 py-3 text-white font-bold focus:outline-none focus:border-blue-500">
                            <option value="none">Tidak Ada</option>
                            <option value="money">Uang (+ / -)</option>
                            <option value="move">Pindah Posisi</option>
                            <option value="jail">Masuk Penjara</option>
                            <option value="free">Bebas Penjara</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-slate-400 text-sm font-bold uppercase mb-2 block">Nilai</label>
                        <input type="number" name="effect_value" id="card-value" value="0" placeholder="contoh: 2000 atau -1500"
                            class="w-full bg-slate-800 border border-white/10 rounded-xl px-4 py-3 text-white font-bold focus:outline-none focus:border-blue-500">
                    </div>
                </div>
                <div class="mb-6 flex items-center gap-3">
                    <input type="checkbox" name="is_active" id="card-active" value="1" checked class="w-5 h-5 accent-blue-500">
                    <label for="card-active" class="text-white font-bold">Kartu Aktif</label>
                </div>
                <div class="flex gap-3">
                    <button type="submit" class="flex-1 py-3 bg-gradient-to-r from-blue-500 to-indigo-600 text-white font-black rounded-xl">
                        <i class="fa-solid fa-save mr-2"></i> Simpan
                    </button>
                    <button type="button" onclick="closeModal()" class="px-6 py-3 bg-slate-700 text-slate-300 font-bold rounded-xl">Batal</button>
                </div>
            </form>
        </div>
    </div>

<script>
function filterCards(type) {
    document.querySelectorAll('.filter-btn').forEach(b => { b.className = 'filter-btn px-4 py-2 bg-white/10 text-slate-400 font-bold rounded-lg'; });
    document.getElementById('f-'+type).className = 'filter-btn px-4 py-2 bg-white/20 text-white font-bold rounded-lg';
    document.querySelectorAll('.card-item').forEach(c => {
        c.style.display = (type === 'all' || c.dataset.type === type) ? 'block' : 'none';
    });
}
function editCard(card) {
    document.getElementById('modal-title').innerHTML = '<i class="fa-solid fa-pen text-blue-400 mr-2"></i>Edit Kartu';
    document.getElementById('card-id').value = card.id;
    document.getElementById('card-type').value = card.type;
    document.getElementById('card-text').value = card.text;
    document.getElementById('card-effect').value = card.effect_type;
    document.getElementById('card-value').value = card.effect_value;
    document.getElementById('card-active').checked = card.is_active == 1;
    document.getElementById('add-modal').classList.remove('hidden');
    document.getElementById('add-modal').classList.add('flex');
}
function closeModal() {
    document.getElementById('add-modal').classList.add('hidden');
    document.getElementById('add-modal').classList.remove('flex');
    document.getElementById('card-id').value = 0;
}
</script>
</body>
</html>

<?php include '../app/views/templates/admin_footer.php'; ?>
