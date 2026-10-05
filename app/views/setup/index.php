<?php
// Onboarding / Lobby Page
?>

<!-- Hero Section -->
<div class="relative overflow-hidden">
    <!-- Background decorations -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute top-20 left-10 w-72 h-72 bg-blue-500/10 rounded-full blur-3xl"></div>
        <div class="absolute top-40 right-10 w-96 h-96 bg-red-500/10 rounded-full blur-3xl"></div>
        <div class="absolute bottom-0 left-1/2 w-80 h-80 bg-purple-500/10 rounded-full blur-3xl"></div>
    </div>

    <div class="relative max-w-6xl mx-auto px-4 py-16 text-center">
        <!-- Logo -->
        <div class="float inline-block mb-8">
            <div class="w-24 h-24 bg-gradient-to-br from-red-500 to-rose-600 rounded-3xl flex items-center justify-center shadow-[0_20px_50px_rgba(239,68,68,0.4)] mx-auto">
                <i class="fa-solid fa-city text-white text-4xl"></i>
            </div>
        </div>

        <h1 class="text-5xl md:text-7xl font-black text-white mb-4 tracking-tight">
            MONOPOLY
        </h1>
        <h2 class="text-xl md:text-3xl font-bold text-red-400 tracking-[0.3em] mb-6">EDISI INDONESIA</h2>
        <p class="text-slate-400 text-lg max-w-xl mx-auto mb-12">
            Permainan properti terpopuler dengan kota-kota Indonesia. Sampai 4 pemain, mainkan dari perangkat masing-masing!
        </p>

        <!-- Tombol Buat Sesi Baru -->
        <button onclick="document.getElementById('create-modal').classList.remove('hidden'); document.getElementById('create-modal').classList.add('flex');"
            class="px-10 py-4 bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 text-white font-black text-xl rounded-2xl shadow-[0_0_30px_rgba(59,130,246,0.5)] transition-all hover:scale-105 inline-flex items-center gap-3">
            <i class="fa-solid fa-plus-circle"></i> Buat Permainan Baru
        </button>
    </div>
</div>

<!-- Daftar Sesi Aktif -->
<div class="max-w-6xl mx-auto px-4 pb-16">
    <div class="flex items-center justify-between mb-8">
        <h3 class="text-2xl font-black text-white">
            <i class="fa-solid fa-gamepad text-blue-400 mr-3"></i>Sesi Aktif
        </h3>
        <span class="text-slate-500 text-sm"><?= count($data['sessions']) ?> sesi</span>
    </div>

    <?php if (empty($data['sessions'])): ?>
    <div class="text-center py-20 text-slate-600">
        <i class="fa-solid fa-dice-d20 text-6xl mb-4 block opacity-20"></i>
        <p class="text-xl font-bold">Belum ada sesi permainan.</p>
        <p class="text-sm mt-2">Klik "Buat Permainan Baru" untuk memulai!</p>
    </div>
    <?php else: ?>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($data['sessions'] as $s): ?>
        <div class="session-card bg-slate-900/80 border border-white/10 rounded-2xl p-6 card-glow">
            <!-- Status Badge -->
            <div class="flex items-start justify-between mb-4">
                <div>
                    <h4 class="text-white font-black text-xl"><?= htmlspecialchars($s['name']) ?></h4>
                    <p class="text-slate-500 text-sm mt-1">
                        <i class="fa-solid fa-users mr-1"></i><?= $s['player_count'] ?> pemain
                        &nbsp;·&nbsp;
                        <i class="fa-solid fa-clock mr-1"></i><?= date('H:i', strtotime($s['created_at'])) ?>
                    </p>
                </div>
                <?php
                $statusColor = ['waiting' => 'yellow', 'playing' => 'green', 'finished' => 'slate'];
                $statusText = ['waiting' => 'Menunggu', 'playing' => 'Berlangsung', 'finished' => 'Selesai'];
                $sc = $statusColor[$s['status']] ?? 'slate';
                $st = $statusText[$s['status']] ?? 'Unknown';
                ?>
                <span class="text-xs font-bold px-3 py-1 rounded-full bg-<?= $sc ?>-500/20 text-<?= $sc ?>-400 border border-<?= $sc ?>-500/30">
                    <?= $st ?>
                </span>
            </div>

            <!-- Actions -->
            <div class="flex gap-3 mt-6">
                <a href="<?= BASEURL ?>/home/game/<?= $s['id'] ?>" 
                   class="flex-1 py-3 bg-blue-500 hover:bg-blue-600 text-white font-bold rounded-xl text-center transition flex items-center justify-center gap-2">
                    <i class="fa-solid fa-play"></i> Buka Board
                </a>
                <button onclick="confirmDelete(<?= $s['id'] ?>, '<?= htmlspecialchars($s['name']) ?>')"
                    class="px-4 py-3 bg-red-500/20 hover:bg-red-500/40 border border-red-500/30 text-red-400 font-bold rounded-xl transition">
                    <i class="fa-solid fa-trash"></i>
                </button>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<!-- Modal Buat Sesi Baru -->
<div id="create-modal" class="hidden fixed inset-0 bg-black/90 z-50 items-center justify-center p-4 backdrop-blur-lg">
    <div class="bg-slate-900 border border-white/10 rounded-3xl p-8 w-full max-w-lg shadow-2xl relative">
        <button onclick="document.getElementById('create-modal').classList.add('hidden'); document.getElementById('create-modal').classList.remove('flex');" 
            class="absolute top-4 right-4 w-10 h-10 bg-white/10 hover:bg-white/20 rounded-full flex items-center justify-center text-slate-400 hover:text-white transition">
            <i class="fa-solid fa-xmark"></i>
        </button>

        <h2 class="text-3xl font-black text-white mb-2"><i class="fa-solid fa-plus-circle text-blue-400 mr-3"></i>Permainan Baru</h2>
        <p class="text-slate-500 mb-8">Isi nama kelompok dan nama tiap pemain.</p>

        <form action="<?= BASEURL ?>/setup/create" method="POST">
            <!-- Nama Sesi -->
            <div class="mb-6">
                <label class="text-slate-400 font-bold text-sm uppercase tracking-widest mb-2 block">Nama Kelompok / Sesi</label>
                <input type="text" name="session_name" required placeholder="contoh: Kelompok A" value="Kelompok <?= chr(65 + count($data['sessions'])) ?>"
                    class="w-full bg-slate-800 border border-white/10 rounded-xl px-4 py-3 text-white font-bold placeholder:text-slate-600 focus:outline-none focus:border-blue-500 transition">
            </div>

            <!-- Jumlah Pemain -->
            <div class="mb-6">
                <label class="text-slate-400 font-bold text-sm uppercase tracking-widest mb-2 block">Jumlah Pemain</label>
                <div class="grid grid-cols-3 gap-3" id="player-count-btns">
                    <?php foreach ([2,3,4] as $n): ?>
                    <label class="cursor-pointer">
                        <input type="radio" name="num_players" value="<?= $n ?>" class="hidden peer" <?= $n === 4 ? 'checked' : '' ?>>
                        <div class="peer-checked:bg-blue-500 peer-checked:border-blue-400 border-2 border-white/10 rounded-xl py-3 text-center font-black text-lg hover:border-blue-500/50 transition">
                            <?= $n ?> Pemain
                        </div>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Nama Pemain -->
            <div class="mb-8 space-y-3">
                <label class="text-slate-400 font-bold text-sm uppercase tracking-widest mb-2 block">Nama Pemain</label>
                <?php
                $colors = [
                    'red'    => '<span style="display:inline-block;width:16px;height:16px;border-radius:50%;background:#ef4444;vertical-align:middle"></span>',
                    'blue'   => '<span style="display:inline-block;width:16px;height:16px;border-radius:50%;background:#3b82f6;vertical-align:middle"></span>',
                    'green'  => '<span style="display:inline-block;width:16px;height:16px;border-radius:50%;background:#22c55e;vertical-align:middle"></span>',
                    'yellow' => '<span style="display:inline-block;width:16px;height:16px;border-radius:50%;background:#eab308;vertical-align:middle"></span>',
                ];
                $i = 1;
                foreach ($colors as $color => $emoji): ?>
                <div class="flex items-center gap-3 player-input" id="pi-<?= $i ?>">
                    <span class="text-2xl w-10 text-center"><?= $emoji ?></span>
                    <input type="text" name="player_<?= $i ?>" placeholder="Pemain <?= $i ?>" value="Pemain <?= $i ?>"
                        class="flex-1 bg-slate-800 border border-white/10 rounded-xl px-4 py-3 text-white font-bold placeholder:text-slate-600 focus:outline-none focus:border-<?= $color ?>-500 transition">
                </div>
                <?php $i++; endforeach; ?>
            </div>

            <button type="submit" class="w-full py-4 bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 text-white font-black text-xl rounded-2xl transition-all hover:scale-[1.02] shadow-[0_0_20px_rgba(59,130,246,0.3)]">
                <i class="fa-solid fa-flag-checkered mr-2"></i> Mulai Permainan!
            </button>
        </form>
    </div>
</div>

<script>
// Tampilkan/sembunyikan input pemain berdasarkan jumlah yang dipilih
document.querySelectorAll('input[name="num_players"]').forEach(radio => {
    radio.addEventListener('change', function() {
        const n = parseInt(this.value);
        for (let i = 1; i <= 4; i++) {
            const el = document.getElementById('pi-' + i);
            if (el) el.style.display = i <= n ? 'flex' : 'none';
        }
    });
});
// Trigger on load
document.querySelector('input[name="num_players"]:checked').dispatchEvent(new Event('change'));

function confirmDelete(id, name) {
    Swal.fire({
        title: 'Hapus sesi ini?',
        html: `Sesi <b>"${name}"</b> dan semua data pemainnya akan dihapus permanen.`,
        icon: 'warning',
        background: '#0f172a',
        color: '#f1f5f9',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#475569',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then(result => {
        if (result.isConfirmed) window.location.href = '<?= BASEURL ?>/setup/delete/' + id;
    });
}
</script>
