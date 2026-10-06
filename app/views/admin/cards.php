<?php include 'app/views/templates/admin_header.php'; ?>
<?php include 'app/views/templates/admin_sidebar.php'; ?>

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
            <button onclick="openAddModal()"
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
                <?php if (!empty($card['image_url'])): ?>
                <div class="mb-3">
                    <img src="<?= BASEURL ?>/<?= htmlspecialchars($card['image_url']) ?>" alt="Gambar Kartu" class="w-16 h-16 object-cover rounded-xl border border-white/10 shadow-lg">
                </div>
                <?php endif; ?>
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
    <div id="add-modal" class="hidden fixed inset-0 z-50 items-center justify-center p-3 backdrop-blur-md" style="background:rgba(0,0,0,0.85)">
        <div class="bg-gradient-to-br from-slate-900 via-slate-900 to-slate-800 border border-white/10 rounded-3xl w-full max-w-2xl shadow-[0_30px_80px_rgba(0,0,0,0.9)] overflow-hidden max-h-[96svh] flex flex-col">
            
            <!-- Modal Header -->
            <div class="relative px-7 pt-6 pb-4 border-b border-white/10 flex items-center justify-between shrink-0">
                <div class="flex items-center gap-3">
                    <div id="modal-icon-wrap" class="w-11 h-11 rounded-2xl bg-blue-500/20 border border-blue-500/30 flex items-center justify-center">
                        <i id="modal-icon" class="fa-solid fa-plus-circle text-blue-400 text-lg"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-black text-white" id="modal-title">Tambah Kartu</h2>
                        <p class="text-xs text-slate-500 mt-0.5">Isi detail kartu di bawah ini</p>
                    </div>
                </div>
                <button type="button" onclick="closeModal()" class="w-9 h-9 rounded-xl bg-white/5 hover:bg-white/15 text-slate-400 hover:text-white transition flex items-center justify-center">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="overflow-y-auto flex-1">
                <form id="card-form" method="POST" action="<?= BASEURL ?>/admin/saveCard" enctype="multipart/form-data">
                    <input type="hidden" name="id" id="card-id" value="0">
                    <div class="px-7 py-5 space-y-5">

                        <!-- Tipe + Aktif -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="text-slate-400 text-xs font-bold uppercase tracking-widest mb-2 block">
                                    <i class="fa-solid fa-layer-group mr-1 text-blue-400"></i> Tipe Kartu
                                </label>
                                <select name="type" id="card-type" onchange="updateModalTheme()" class="w-full bg-slate-800/80 border border-white/10 rounded-xl px-4 py-3 text-white font-bold focus:outline-none focus:border-blue-400 focus:ring-1 focus:ring-blue-400/30 transition">
                                    <option value="kesempatan">🎯 Kesempatan</option>
                                    <option value="dana_umum">💎 Dana Umum</option>
                                </select>
                            </div>
                            <div>
                                <label class="text-slate-400 text-xs font-bold uppercase tracking-widest mb-2 block">
                                    <i class="fa-solid fa-toggle-on mr-1 text-emerald-400"></i> Status
                                </label>
                                <label class="w-full h-[50px] bg-slate-800/80 border border-white/10 rounded-xl px-4 flex items-center gap-3 cursor-pointer hover:bg-slate-700/50 transition">
                                    <input type="checkbox" name="is_active" id="card-active" value="1" checked class="w-5 h-5 accent-emerald-500 cursor-pointer">
                                    <span class="text-white font-bold text-sm">Kartu Aktif</span>
                                </label>
                            </div>
                        </div>

                        <!-- Teks Kartu -->
                        <div>
                            <label class="text-slate-400 text-xs font-bold uppercase tracking-widest mb-2 block">
                                <i class="fa-solid fa-align-left mr-1 text-amber-400"></i> Teks Kartu
                            </label>
                            <textarea name="text" id="card-text" required rows="3" placeholder="Isi instruksi yang tampil di kartu..."
                                class="w-full bg-slate-800/80 border border-white/10 rounded-xl px-4 py-3 text-white font-semibold text-sm focus:outline-none focus:border-amber-400 focus:ring-1 focus:ring-amber-400/30 resize-none transition placeholder:text-slate-600 leading-relaxed"></textarea>
                        </div>

                        <!-- Jenis Efek -->
                        <div>
                            <label class="text-slate-400 text-xs font-bold uppercase tracking-widest mb-2 block">
                                <i class="fa-solid fa-wand-magic-sparkles mr-1 text-purple-400"></i> Jenis Efek
                            </label>
                            <div class="grid grid-cols-3 sm:grid-cols-4 gap-2" id="effect-grid">
                                <button type="button" data-val="none"         class="effect-chip selected" onclick="selectEffect('none')"><i class="fa-solid fa-ban"></i><span>Tidak Ada</span></button>
                                <button type="button" data-val="money_bank"   class="effect-chip" onclick="selectEffect('money_bank')"><i class="fa-solid fa-building-columns"></i><span>Uang Bank</span></button>
                                <button type="button" data-val="money_players" class="effect-chip" onclick="selectEffect('money_players')"><i class="fa-solid fa-users"></i><span>Uang Semua Pemain</span></button>
                                <button type="button" data-val="move_pos"     class="effect-chip" onclick="selectEffect('move_pos')"><i class="fa-solid fa-map-pin"></i><span>Pindah ke Kota</span></button>
                                <button type="button" data-val="move_steps"   class="effect-chip" onclick="selectEffect('move_steps')"><i class="fa-solid fa-shoe-prints"></i><span>Maju/Mundur</span></button>
                                <button type="button" data-val="jail"         class="effect-chip" onclick="selectEffect('jail')"><i class="fa-solid fa-handcuffs"></i><span>Masuk Penjara</span></button>
                                <button type="button" data-val="free"         class="effect-chip" onclick="selectEffect('free')"><i class="fa-solid fa-ticket-simple"></i><span>Bebas Penjara</span></button>
                            </div>
                            <input type="hidden" name="effect_type" id="card-effect" value="none">
                        </div>

                        <!-- Nilai / Kota (conditional) -->
                        <div id="nilai-wrap">
                            <label class="text-slate-400 text-xs font-bold uppercase tracking-widest mb-2 block" id="label-nilai">
                                <i class="fa-solid fa-coins mr-1 text-amber-400"></i> <span id="label-nilai-text">Nilai (Rp)</span>
                            </label>
                            <input type="hidden" name="effect_value" id="real-card-value" value="0">
                            <input type="number" id="card-value-num" value="0" placeholder="contoh: 2000 atau -1500"
                                class="w-full bg-slate-800/80 border border-white/10 rounded-xl px-4 py-3 text-white font-bold focus:outline-none focus:border-blue-400 focus:ring-1 focus:ring-blue-400/30 transition">
                            <select id="card-value-select" class="w-full bg-slate-800/80 border border-white/10 rounded-xl px-4 py-3 text-white font-bold focus:outline-none focus:border-blue-400 focus:ring-1 focus:ring-blue-400/30 transition hidden">
                                <?php foreach ($data['board'] as $index => $cell): ?>
                                    <option value="<?= $index ?>"><?= $index ?> — <?= htmlspecialchars($cell['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <p id="nilai-hint" class="text-xs text-slate-500 mt-1.5">Gunakan angka positif untuk tambah, negatif untuk kurang.</p>
                        </div>

                        <!-- Bonus Melewati Start (conditional) -->
                        <div id="pass-start-wrap" class="hidden">
                            <label class="text-slate-400 text-xs font-bold uppercase tracking-widest mb-2 block">
                                <i class="fa-solid fa-flag-checkered mr-1 text-emerald-400"></i> Bonus Melewati Start (Opsional)
                            </label>
                            <input type="number" name="pass_start_money" id="card-pass-start" value="0" placeholder="Misal: 2000 (isi 0 untuk tidak ada bonus)"
                                class="w-full bg-slate-800/80 border border-white/10 rounded-xl px-4 py-3 text-white font-bold focus:outline-none focus:border-emerald-400 focus:ring-1 focus:ring-emerald-400/30 transition">
                            <p class="text-xs text-slate-500 mt-1.5">Pemain mendapat bonus ini jika pindah lokasi melewati petak Start.</p>
                        </div>

                        <!-- Gambar -->
                        <div>
                            <label class="block text-slate-400 text-xs font-bold uppercase tracking-widest mb-2">
                                <i class="fa-solid fa-image mr-1 text-rose-400"></i> Gambar Kartu <span class="text-slate-600 normal-case font-normal">(opsional)</span>
                            </label>
                            <input type="file" name="image" id="card_image" accept="image/*" 
                                class="w-full text-sm text-slate-400 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-blue-500/20 file:text-blue-400 file:font-bold file:cursor-pointer hover:file:bg-blue-500/30 transition bg-slate-800/80 border border-white/10 rounded-xl px-3 py-2">
                            <p class="text-xs text-slate-600 mt-1.5">Format JPG/PNG. Kosongkan untuk tidak mengubah gambar.</p>
                        </div>

                    </div>

                    <!-- Modal Footer -->
                    <div class="px-7 py-5 border-t border-white/10 flex gap-3 shrink-0 bg-slate-900/60">
                        <button type="button" onclick="closeModal()" class="px-6 py-3 bg-slate-700/80 hover:bg-slate-700 text-slate-300 font-bold rounded-xl transition">
                            <i class="fa-solid fa-xmark mr-1"></i> Batal
                        </button>
                        <button type="submit" class="flex-1 py-3 bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-400 hover:to-indigo-500 text-white font-black rounded-xl transition shadow-lg shadow-blue-900/40 flex items-center justify-center gap-2">
                            <i class="fa-solid fa-floppy-disk"></i> Simpan Kartu
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>
    .effect-chip {
        display: flex; flex-direction: column; align-items: center; justify-content: center;
        gap: 5px; padding: 10px 6px; border-radius: 12px; font-size: 10px; font-weight: 700;
        border: 1.5px solid rgba(255,255,255,0.08); background: rgba(255,255,255,0.04);
        color: #94a3b8; cursor: pointer; text-align: center; transition: all 0.18s;
        min-height: 64px; line-height: 1.2;
    }
    .effect-chip i { font-size: 16px; }
    .effect-chip:hover { background: rgba(255,255,255,0.1); color: #fff; border-color: rgba(255,255,255,0.2); }
    .effect-chip.selected { background: rgba(59,130,246,0.2); border-color: rgba(59,130,246,0.6); color: #60a5fa; box-shadow: 0 0 12px rgba(59,130,246,0.2); }
    .effect-chip[data-val="jail"].selected { background: rgba(239,68,68,0.2); border-color: rgba(239,68,68,0.6); color: #f87171; box-shadow: 0 0 12px rgba(239,68,68,0.2); }
    .effect-chip[data-val="free"].selected { background: rgba(16,185,129,0.2); border-color: rgba(16,185,129,0.6); color: #34d399; box-shadow: 0 0 12px rgba(16,185,129,0.2); }
    .effect-chip[data-val="money_bank"].selected,
    .effect-chip[data-val="money_players"].selected { background: rgba(245,158,11,0.2); border-color: rgba(245,158,11,0.6); color: #fbbf24; box-shadow: 0 0 12px rgba(245,158,11,0.2); }
    .effect-chip[data-val="move_pos"].selected,
    .effect-chip[data-val="move_steps"].selected { background: rgba(139,92,246,0.2); border-color: rgba(139,92,246,0.6); color: #a78bfa; box-shadow: 0 0 12px rgba(139,92,246,0.2); }
    #add-modal { animation: fadeInModal 0.18s ease; }
    #add-modal > div { animation: scaleInModal 0.22s cubic-bezier(.34,1.56,.64,1); }
    @keyframes fadeInModal { from { opacity:0 } to { opacity:1 } }
    @keyframes scaleInModal { from { transform:scale(0.93); opacity:0 } to { transform:scale(1); opacity:1 } }
    </style>


<script>
function filterCards(type) {
    document.querySelectorAll('.filter-btn').forEach(b => { b.className = 'filter-btn px-4 py-2 bg-white/10 text-slate-400 font-bold rounded-lg'; });
    document.getElementById('f-'+type).className = 'filter-btn px-4 py-2 bg-white/20 text-white font-bold rounded-lg';
    document.querySelectorAll('.card-item').forEach(c => {
        c.style.display = (type === 'all' || c.dataset.type === type) ? 'block' : 'none';
    });
}

function selectEffect(val) {
    document.getElementById('card-effect').value = val;
    document.querySelectorAll('.effect-chip').forEach(c => c.classList.remove('selected'));
    const chip = document.querySelector(`.effect-chip[data-val="${val}"]`);
    if (chip) chip.classList.add('selected');
    toggleEffectInput();
}

function toggleEffectInput() {
    const type       = document.getElementById('card-effect').value;
    const numInput   = document.getElementById('card-value-num');
    const selInput   = document.getElementById('card-value-select');
    const realInput  = document.getElementById('real-card-value');
    const nilaiWrap  = document.getElementById('nilai-wrap');
    const passWrap   = document.getElementById('pass-start-wrap');
    const hint       = document.getElementById('nilai-hint');
    const labelText  = document.getElementById('label-nilai-text');

    const noValueTypes = ['none', 'jail', 'free'];
    const moveTypes    = ['move_pos', 'move_steps'];

    nilaiWrap.style.display = noValueTypes.includes(type) ? 'none' : '';
    passWrap.classList.toggle('hidden', !moveTypes.includes(type));

    if (type === 'move_pos') {
        numInput.classList.add('hidden');
        selInput.classList.remove('hidden');
        if (!selInput.value) selInput.selectedIndex = 0;
        realInput.value = selInput.value;
        if (labelText) labelText.textContent = 'Tujuan Kota';
        if (hint) hint.textContent = 'Pilih petak tujuan perpindahan.';
    } else {
        numInput.classList.remove('hidden');
        selInput.classList.add('hidden');
        realInput.value = numInput.value;
        if (type === 'move_steps') {
            if (labelText) labelText.textContent = 'Jumlah Langkah';
            if (hint) hint.textContent = 'Positif = maju, negatif = mundur langkah.';
        } else {
            if (labelText) labelText.textContent = 'Nilai (Rp)';
            if (hint) hint.textContent = 'Positif = dapat uang, negatif = bayar uang.';
        }
    }
}

function updateModalTheme() {
    const type = document.getElementById('card-type').value;
    const wrap = document.getElementById('modal-icon-wrap');
    const icon = document.getElementById('modal-icon');
    if (!wrap || !icon) return;
    if (type === 'kesempatan') {
        wrap.className = 'w-11 h-11 rounded-2xl bg-amber-500/20 border border-amber-500/30 flex items-center justify-center';
        icon.className = 'fa-solid fa-question text-amber-400 text-lg';
    } else {
        wrap.className = 'w-11 h-11 rounded-2xl bg-emerald-500/20 border border-emerald-500/30 flex items-center justify-center';
        icon.className = 'fa-solid fa-gem text-emerald-400 text-lg';
    }
}

function editCard(card) {
    document.getElementById('modal-title').textContent = 'Edit Kartu';
    document.getElementById('card-id').value   = card.id;
    document.getElementById('card-type').value = card.type;
    document.getElementById('card-text').value = card.text;
    updateModalTheme();
    selectEffect(card.effect_type || 'none');

    document.getElementById('real-card-value').value = card.effect_value;
    if (card.effect_type === 'move_pos') {
        document.getElementById('card-value-select').value = card.effect_value;
    } else {
        document.getElementById('card-value-num').value = card.effect_value;
    }
    document.getElementById('card-pass-start').value = card.pass_start_money || 0;
    document.getElementById('card-active').checked   = card.is_active == 1;
    document.getElementById('add-modal').classList.remove('hidden');
    document.getElementById('add-modal').classList.add('flex');
}

function openAddModal() {
    document.getElementById('card-form').reset();
    document.getElementById('modal-title').textContent = 'Tambah Kartu';
    document.getElementById('card-id').value = 0;
    const wrap = document.getElementById('modal-icon-wrap');
    const icon = document.getElementById('modal-icon');
    if (wrap) wrap.className = 'w-11 h-11 rounded-2xl bg-blue-500/20 border border-blue-500/30 flex items-center justify-center';
    if (icon) icon.className = 'fa-solid fa-plus-circle text-blue-400 text-lg';
    selectEffect('none');
    document.getElementById('add-modal').classList.remove('hidden');
    document.getElementById('add-modal').classList.add('flex');
}

function closeModal() {
    document.getElementById('add-modal').classList.add('hidden');
    document.getElementById('add-modal').classList.remove('flex');
    document.getElementById('card-id').value = 0;
}

document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('card-value-num')?.addEventListener('input', e => {
        document.getElementById('real-card-value').value = e.target.value;
    });
    document.getElementById('card-value-select')?.addEventListener('change', e => {
        document.getElementById('real-card-value').value = e.target.value;
    });
    // Close modal on backdrop click
    document.getElementById('add-modal')?.addEventListener('click', e => {
        if (e.target === e.currentTarget) closeModal();
    });
});
</script>
</body>
</html>

<?php include 'app/views/templates/admin_footer.php'; ?>
