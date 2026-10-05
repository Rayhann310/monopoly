<?php
    $colorMap = [
        'red' => 'from-red-500 to-rose-600',
        'blue' => 'from-blue-500 to-indigo-600',
        'green' => 'from-emerald-400 to-green-600',
        'yellow' => 'from-amber-400 to-orange-500',
    ];
    $bgGrad = isset($colorMap[$data['player']['color']]) ? $colorMap[$data['player']['color']] : 'from-slate-500 to-slate-600';
?>

<style>
@keyframes shake {
    0% { transform: translate(1px, 1px) rotate(0deg); }
    10% { transform: translate(-1px, -2px) rotate(-1deg); }
    20% { transform: translate(-3px, 0px) rotate(1deg); }
    30% { transform: translate(3px, 2px) rotate(0deg); }
    40% { transform: translate(1px, -1px) rotate(1deg); }
    50% { transform: translate(-1px, 2px) rotate(-1deg); }
    60% { transform: translate(-3px, 1px) rotate(0deg); }
    70% { transform: translate(3px, 1px) rotate(-1deg); }
    80% { transform: translate(-1px, -1px) rotate(1deg); }
    90% { transform: translate(1px, 2px) rotate(0deg); }
    100% { transform: translate(1px, -2px) rotate(-1deg); }
}
.shake { animation: shake 0.3s; animation-iteration-count: infinite; }
</style>

<!-- Header / Status Bar -->
<div class="bg-gradient-to-r <?= $bgGrad ?> p-6 rounded-b-[2rem] shadow-2xl relative overflow-hidden">
    <div class="absolute -right-10 -top-10 text-white/10 text-9xl">
        <i class="fa-solid fa-user"></i>
    </div>
    <div class="relative z-10 flex flex-col gap-2">
        <div class="text-white/80 font-bold uppercase tracking-widest text-sm">Identitas Pemain</div>
        <h1 class="text-3xl font-black text-white"><?= $data['player']['name'] ?></h1>
        <div class="flex items-center gap-3 mt-4">
            <div class="bg-black/30 backdrop-blur px-4 py-2 rounded-xl flex items-center gap-2 border border-white/20">
                <i class="fa-solid fa-wallet text-emerald-400"></i>
                <span class="text-xl font-bold font-mono">Rp <?= number_format($data['player']['money'], 0, ',', '.') ?></span>
            </div>
        </div>
    </div>
</div>

<!-- Status Giliran -->
<div id="turn-badge" class="mx-6 mt-4 rounded-2xl px-4 py-3 flex items-center gap-3 <?= $data['player']['is_turn'] ? 'bg-emerald-500/20 border border-emerald-500/40' : 'bg-slate-800/60 border border-white/10' ?>">
<?php if ($data['player']['is_turn']): ?>
    <span class="text-2xl animate-pulse">👑</span>
    <div>
        <div class="text-emerald-400 font-black text-base">GILIRAN KAMU!</div>
        <div class="text-emerald-400/60 text-xs">Lempar dadu sekarang</div>
    </div>
<?php else: ?>
    <span class="text-2xl opacity-50">⏳</span>
    <div>
        <div class="text-slate-400 font-black text-base">MENUNGGU...</div>
        <div class="text-slate-600 text-xs">Bukan giliranmu saat ini</div>
    </div>
<?php endif; ?>
</div>

<!-- Dice Action Area -->
<div class="flex-1 flex flex-col items-center justify-center p-6 gap-5 mt-2">
    <!-- Dice Display -->
    <div class="flex gap-6 justify-center items-center h-32 w-full bg-slate-900/50 rounded-2xl border border-white/5 shadow-inner">
        <i id="mobile-die1" class="fa-solid fa-dice-one text-6xl text-<?= $data['player']['color'] ?>-500 drop-shadow-[0_0_15px_rgba(255,255,255,0.2)]"></i>
        <i id="mobile-die2" class="fa-solid fa-dice-one text-6xl text-<?= $data['player']['color'] ?>-500 drop-shadow-[0_0_15px_rgba(255,255,255,0.2)]"></i>
    </div>

    <!-- Roll Button -->
    <button id="mobile-roll-btn"
        <?= !$data['player']['is_turn'] ? 'disabled' : '' ?>
        class="w-full max-w-sm py-4 bg-gradient-to-r <?= $bgGrad ?> text-white rounded-2xl font-black text-xl shadow-[0_10px_30px_rgba(0,0,0,0.5)] active:scale-95 transition-transform border border-white/30 disabled:opacity-30 disabled:cursor-not-allowed disabled:active:scale-100">
        <i class="fa-solid fa-hand-sparkles mr-2"></i> LEMPAR DADU
    </button>

    <!-- End Turn Button (muncul setelah roll) -->
    <button id="end-turn-btn" class="hidden w-full max-w-sm py-4 bg-emerald-500 hover:bg-emerald-600 text-white rounded-2xl font-black text-xl shadow-[0_10px_30px_rgba(0,0,0,0.5)] active:scale-95 transition-transform border border-emerald-400/30">
        <i class="fa-solid fa-check mr-2"></i> SELESAI GILIRAN
    </button>
</div>

<!-- Properties Slider -->
<div class="mt-auto">
    <div class="px-6 flex justify-between items-end mb-2">
        <h3 class="font-bold text-white text-lg"><i class="fa-solid fa-city text-blue-400 mr-2"></i>Aset Tanah</h3>
        <span class="text-xs text-slate-500 font-bold bg-slate-800 px-2 py-1 rounded">Geser <i class="fa-solid fa-arrow-right"></i></span>
    </div>
    
    <div class="card-slider">
        <!-- Placeholder properties -->
        <div class="property-card border-t-4 border-t-slate-500 flex flex-col justify-center items-center opacity-50">
            <i class="fa-solid fa-plus text-2xl mb-2"></i>
            <span class="text-xs font-bold">Beli Properti</span>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const player = <?= json_encode($data['player']); ?>;
        const board = <?= json_encode($data['board']); ?>;
        const BASEURL = '<?= BASEURL ?>';
        let hasRolled = <?= $data['player']['has_rolled'] ? 'true' : 'false' ?>;
        let isTurn = <?= $data['player']['is_turn'] ? 'true' : 'false' ?>;

        const rollBtn = document.getElementById('mobile-roll-btn');
        const endTurnBtn = document.getElementById('end-turn-btn');

        // Jika sudah roll tapi belum selesai giliran, tampilkan End Turn
        if (hasRolled && isTurn) {
            rollBtn.disabled = true;
            rollBtn.classList.add('opacity-30');
            endTurnBtn.classList.remove('hidden');
        }

        // Helper SweetAlert
        function showModal(title, text, icon, color) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title, html: text, icon,
                    background: '#0f172a', color: '#f1f5f9',
                    confirmButtonColor: color || '#3b82f6',
                    confirmButtonText: '<i class="fa fa-check"></i> Oke',
                    customClass: { popup: 'rounded-2xl border border-white/10 shadow-2xl' }
                });
            } else {
                const modal = document.getElementById('fallback-modal');
                document.getElementById('fm-title').innerText = title;
                document.getElementById('fm-text').innerHTML = text;
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            }
        }

        // Roll Dadu
        rollBtn.addEventListener('click', function() {
            if (this.disabled) return;
            this.disabled = true;
            const btn = this;
            const d1el = document.getElementById('mobile-die1');
            const d2el = document.getElementById('mobile-die2');
            d1el.classList.add('shake');
            d2el.classList.add('shake');

            setTimeout(() => {
                d1el.classList.remove('shake');
                d2el.classList.remove('shake');

                const v1 = Math.floor(Math.random() * 6) + 1;
                const v2 = Math.floor(Math.random() * 6) + 1;
                const total = v1 + v2;
                const icons = ['','fa-dice-one','fa-dice-two','fa-dice-three','fa-dice-four','fa-dice-five','fa-dice-six'];
                d1el.className = `fa-solid ${icons[v1]} text-6xl text-${player.color}-500 drop-shadow-[0_0_15px_rgba(255,255,255,0.2)]`;
                d2el.className = `fa-solid ${icons[v2]} text-6xl text-${player.color}-500 drop-shadow-[0_0_15px_rgba(255,255,255,0.2)]`;

                player.position = (parseInt(player.position) + total) % 40;
                const landedCell = board[player.position];

                let title = `🎲 Dadu: ${total}`;
                let text = `<b>Mendarat di:</b><br><span style="font-size:1.4rem;font-weight:900;color:#38bdf8">${landedCell.name}</span>`;
                let icon = 'success', color = '#38bdf8';

                if (landedCell.name === 'Kesempatan') { icon='question'; title='🃏 Kartu Kesempatan!'; text='Ambil kartu <b>Kesempatan</b> dan ikuti instruksinya.'; color='#f59e0b'; }
                else if (landedCell.name === 'Dana Umum') { icon='question'; title='💰 Dana Umum!'; text='Ambil kartu <b>Dana Umum</b> dan ikuti instruksinya.'; color='#10b981'; }
                else if (landedCell.name === 'Penjara') { icon='info'; title='👀 Hanya Berkunjung'; text='Kamu di area Penjara sebagai <b>pengunjung bebas</b>.'; color='#6366f1'; }
                else if (landedCell.name === 'Masuk Penjara') { icon='error'; title='🚔 DITANGKAP!'; text='Kamu masuk penjara!'; color='#ef4444'; }
                else if (landedCell.name === 'Pajak Mewah' || landedCell.name === 'Pajak') { icon='warning'; title='🏛️ Bayar Pajak!'; text=`Kamu kena pajak di <b>${landedCell.name}</b>.`; color='#f97316'; }
                else if (landedCell.name === 'Parkir Bebas') { icon='success'; title='🅿️ Parkir Bebas!'; text='Tidak ada denda!'; color='#22c55e'; }
                else if (landedCell.name === 'Start') { title='⭐ Melewati Start!'; text='Terima <b>Rp 2.000</b> dari Bank!'; color='#eab308'; }

                showModal(title, text, icon, color);

                // Kirim ke server
                fetch(BASEURL + '/player/apiRoll', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `id=${player.id}&new_position=${player.position}`
                }).then(r => r.json()).then(res => {
                    if (res.status === 'success') {
                        hasRolled = true;
                        // Tampilkan tombol Selesai Giliran
                        endTurnBtn.classList.remove('hidden');
                    } else if (res.msg) {
                        showModal('⚠️ ' + res.msg, '', 'warning', '#f59e0b');
                        btn.disabled = false;
                    }
                }).catch(() => { btn.disabled = false; });

            }, 800);
        });

        // Selesai Giliran
        endTurnBtn.addEventListener('click', function() {
            this.disabled = true;
            fetch(BASEURL + '/player/apiEndTurn', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `id=${player.id}`
            }).then(r => r.json()).then(res => {
                if (res.status === 'success') {
                    showModal('✅ Giliran Selesai!', 'Menunggu giliran berikutnya...', 'info', '#3b82f6');
                    endTurnBtn.classList.add('hidden');
                    isTurn = false;
                    // Polling akan update status
                } else {
                    this.disabled = false;
                }
            });
        });

        // Polling status giliran (setiap 2 detik)
        setInterval(() => {
            fetch(BASEURL + '/player/apiStatus/' + player.id)
                .then(r => r.json())
                .then(status => {
                    const wasMyTurn = isTurn;
                    isTurn = status.is_turn;
                    const serverHasRolled = status.has_rolled;

                    // Update uang
                    const moneyEl = document.querySelector('.font-mono');
                    if (moneyEl) moneyEl.textContent = 'Rp ' + parseInt(status.money).toLocaleString('id-ID');

                    if (!wasMyTurn && isTurn) {
                        // Giliran baru dimulai!
                        hasRolled = false;
                        rollBtn.disabled = false;
                        rollBtn.classList.remove('opacity-30');
                        endTurnBtn.classList.add('hidden');
                        showModal('🎲 Giliran Kamu!', 'Sekarang giliranmu! Lempar dadu.', 'success', '#22c55e');
                    }

                    // Update badge status
                    const badge = document.getElementById('turn-badge');
                    if (badge) {
                        if (isTurn) {
                            badge.className = 'mx-6 mt-4 rounded-2xl px-4 py-3 flex items-center gap-3 bg-emerald-500/20 border border-emerald-500/40';
                            badge.innerHTML = '<span class="text-2xl animate-pulse">👑</span><div><div class="text-emerald-400 font-black text-base">GILIRAN KAMU!</div><div class="text-emerald-400/60 text-xs">Lempar dadu sekarang</div></div>';
                        } else {
                            badge.className = 'mx-6 mt-4 rounded-2xl px-4 py-3 flex items-center gap-3 bg-slate-800/60 border border-white/10';
                            badge.innerHTML = '<span class="text-2xl opacity-50">⏳</span><div><div class="text-slate-400 font-black text-base">MENUNGGU...</div><div class="text-slate-600 text-xs">Bukan giliranmu saat ini</div></div>';
                            // Nonaktifkan tombol jika bukan giliran
                            rollBtn.disabled = true;
                            rollBtn.classList.add('opacity-30');
                        }
                    }
                }).catch(() => {});
        }, 2000);
    });
</script>

<!-- Fallback Modal -->
<div id="fallback-modal" class="hidden fixed inset-0 bg-black/90 z-[999] items-center justify-center p-6">
    <div class="bg-slate-900 border border-white/10 rounded-2xl p-8 w-full max-w-sm text-center shadow-2xl">
        <h2 id="fm-title" class="text-2xl font-black text-white mb-4"></h2>
        <p id="fm-text" class="text-slate-300 mb-6"></p>
        <button onclick="document.getElementById('fallback-modal').classList.add('hidden');document.getElementById('fallback-modal').classList.remove('flex');"
            class="px-8 py-3 bg-blue-500 text-white font-bold rounded-xl w-full">Oke</button>
    </div>
</div>

