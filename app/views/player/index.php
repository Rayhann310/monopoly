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
    <i class="fa-solid fa-crown text-2xl text-amber-400 animate-pulse"></i>
    <div>
        <div class="text-emerald-400 font-black text-base">GILIRAN KAMU!</div>
        <div class="text-emerald-400/60 text-xs">Lempar dadu sekarang</div>
    </div>
<?php else: ?>
    <i class="fa-regular fa-clock text-2xl text-slate-500"></i>
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
        <?php if (empty($data['properties'])): ?>
            <div class="property-card border-t-4 border-t-slate-500 flex flex-col justify-center items-center opacity-50">
                <i class="fa-solid fa-city text-2xl mb-2 text-slate-500"></i>
                <span class="text-xs font-bold text-center text-slate-500">Belum ada properti</span>
            </div>
        <?php else: ?>
            <?php foreach ($data['properties'] as $prop): 
                // Cari data board berdasarkan cell_index
                $boardCell = null;
                foreach ($data['board'] as $cell) {
                    if ($cell['index'] == $prop['cell_index']) {
                        $boardCell = $cell;
                        break;
                    }
                }
                if (!$boardCell) continue;
                $color = $boardCell['color_group'] ?? 'slate';
            ?>
            <div class="property-card border-t-4 border-t-<?= $color ?>-500 flex flex-col justify-between">
                <div>
                    <div class="text-[0.65rem] font-bold text-slate-400 uppercase tracking-wider mb-1">Kota</div>
                    <div class="font-black text-white text-sm leading-tight mb-2"><?= $boardCell['name'] ?></div>
                    <div class="flex gap-1">
                        <?php 
                        $houses = (int)$prop['houses'];
                        if ($houses > 0): 
                            for ($i = 0; $i < $houses; $i++): 
                                $isHotel = ($i === 4);
                        ?>
                            <i class="fa-solid <?= $isHotel ? 'fa-hotel text-red-500' : 'fa-house text-emerald-500' ?> text-xs"></i>
                            <?php if ($isHotel) break; ?>
                        <?php 
                            endfor; 
                        else:
                        ?>
                            <span class="text-xs text-slate-500 italic">Belum ada rumah</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const player = <?= json_encode($data['player']); ?>;
        const board = <?= json_encode($data['board']); ?>;
        const BASEURL = '<?= BASEURL ?>';
        let currentPropsHash = JSON.stringify(<?= json_encode($data['properties'] ?? []) ?>);
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
            const icons = ['','fa-dice-one','fa-dice-two','fa-dice-three','fa-dice-four','fa-dice-five','fa-dice-six'];
            
            d1el.classList.add('shake');
            d2el.classList.add('shake');

            // Animasi putaran dadu acak cepat
            let rollInterval = setInterval(() => {
                const r1 = Math.floor(Math.random() * 6) + 1;
                const r2 = Math.floor(Math.random() * 6) + 1;
                d1el.className = `fa-solid ${icons[r1]} text-6xl text-slate-400 opacity-80`;
                d2el.className = `fa-solid ${icons[r2]} text-6xl text-slate-400 opacity-80`;
            }, 60);

            setTimeout(() => {
                clearInterval(rollInterval);
                d1el.classList.remove('shake');
                d2el.classList.remove('shake');

                const v1 = Math.floor(Math.random() * 6) + 1;
                const v2 = Math.floor(Math.random() * 6) + 1;
                const total = v1 + v2;
                d1el.className = `fa-solid ${icons[v1]} text-6xl text-${player.color}-500 drop-shadow-[0_0_15px_rgba(255,255,255,0.4)] transform scale-110 transition-transform`;
                d2el.className = `fa-solid ${icons[v2]} text-6xl text-${player.color}-500 drop-shadow-[0_0_15px_rgba(255,255,255,0.4)] transform scale-110 transition-transform`;
                
                setTimeout(() => {
                    d1el.classList.remove('scale-110');
                    d2el.classList.remove('scale-110');
                }, 200);

                player.position = (parseInt(player.position) + total) % 40;
                const landedCell = board[player.position];

                let title = `<i class='fa-solid fa-dice text-blue-400 mr-1'></i> Dadu: ${total}`;
                let text = `<b>Mendarat di:</b><br><span style="font-size:1.4rem;font-weight:900;color:#38bdf8">${landedCell.name}</span>`;
                let icon = 'success', color = '#38bdf8';

                function showCardAnimation(cardType, cardText, cardImg) {
                    cardText = cardText || 'Ambil kartu fisik dan ikuti instruksinya.';
                    const isKesempatan = cardType === 'Kesempatan';
                    const cColor = isKesempatan ? '#f59e0b' : '#10b981';
                    const cIcon = isKesempatan ? 'fa-question' : 'fa-gem';
                    
                    const html = `
                    <style>
                    .mc-card-container { perspective: 1000px; width: 100%; height: 280px; margin-top: 10px; cursor: pointer; }
                    .mc-card { position: relative; width: 100%; height: 100%; text-align: center; transition: transform 0.8s cubic-bezier(0.34, 1.56, 0.64, 1); transform-style: preserve-3d; }
                    .mc-card.is-flipped { transform: rotateY(180deg); }
                    .mc-face { position: absolute; width: 100%; height: 100%; backface-visibility: hidden; border-radius: 16px; display: flex; flex-direction: column; justify-content: center; align-items: center; box-shadow: 0 15px 35px rgba(0,0,0,0.5); padding: 20px; border: 4px solid ${cColor}; }
                    .mc-front { background: radial-gradient(circle, #1e293b, #0f172a); }
                    .mc-back { background: white; color: #0f172a; transform: rotateY(180deg); }
                    .mc-front-icon { font-size: 80px; color: ${cColor}; text-shadow: 0 0 20px ${cColor}80; }
                    </style>
                    <div class="mc-card-container" onclick="this.querySelector('.mc-card').classList.add('is-flipped')">
                        <div class="mc-card">
                            <div class="mc-face mc-front">
                                <i class="fa-solid ${cIcon} mc-front-icon animate-pulse"></i>
                                <div style="color:white; margin-top:20px; font-weight:bold; font-size:1.2rem; letter-spacing:2px; text-transform:uppercase">${cardType}</div>
                                <div style="color:#94a3b8; font-size:0.8rem; margin-top:10px">Ketuk untuk membalik</div>
                            </div>
                            <div class="mc-face mc-back">
                                <div style="background:${cColor}; color:white; width:calc(100% + 40px); margin-top:-20px; padding:10px 15px; font-weight:900; text-transform:uppercase; font-size:1rem; border-top-left-radius: 10px; border-top-right-radius: 10px;">
                                    ${cardType}
                                </div>
                                <div style="flex-grow:1; display:flex; flex-direction:column; justify-content:center; align-items:center; padding:15px;">
                                    ${cardImg ? `<img src="${cardImg}" style="width:80px;height:80px;object-fit:cover;border-radius:10px;margin-bottom:12px;box-shadow:0 4px 12px rgba(0,0,0,0.2)">` : `<div style="width:70px;height:70px;border-radius:10px;background:${cColor}20;display:flex;align-items:center;justify-content:center;margin-bottom:12px;"><i class="fa-solid ${cIcon}" style="font-size:2rem;color:${cColor}"></i></div>`}
                                    <h3 style="font-weight:bold; font-size:1.05rem; color:#1e293b; line-height:1.4; text-align:center">${cardText}</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                    `;
                    
                    Swal.fire({
                        html: html,
                        background: 'transparent',
                        showConfirmButton: true,
                        confirmButtonText: 'Oke, Sudah Dipahami',
                        confirmButtonColor: cColor,
                        backdrop: 'rgba(0,0,0,0.9)'
                    });
                    
                    setTimeout(() => {
                        const card = document.querySelector('.mc-card');
                        if(card && !card.classList.contains('is-flipped')) {
                            card.classList.add('is-flipped');
                        }
                    }, 1200);
                }

                // Kirim ke server — server yang handle semua logika
                fetch(BASEURL + '/player/apiRoll', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `id=${player.id}&new_position=${player.position}&dice=${total}`
                }).then(r => r.json()).then(res => {
                    if (res.status !== 'success') {
                        showModal('<i class="fa-solid fa-triangle-exclamation mr-1"></i> ' + (res.msg || 'Error'), '', 'warning', '#f59e0b');
                        btn.disabled = false;
                        return;
                    }

                    hasRolled = true;
                    const act = res.action || {};

                    // Update local position from server (e.g. jail redirect)
                    player.position = res.position;

                    // Update uang di UI
                    const moneyEl = document.querySelector('.font-mono');
                    if (moneyEl) moneyEl.textContent = 'Rp ' + parseInt(res.money).toLocaleString('id-ID');

                    // Show action modal
                    if (act.type === 'card') {
                        const isKesempatan = act.card_type === 'kesempatan';
                        showCardAnimation(
                            isKesempatan ? 'Kesempatan' : 'Dana Umum',
                            act.card_text || 'Baca kartu fisikmu.',
                            act.card_image || null
                        );
                    } else if (act.type === 'buy') {
                        Swal.fire({
                            title: '<i class="fa-solid fa-building mr-1"></i> Beli Properti?',
                            html: `Beli <b>${act.name}</b> seharga <b class="text-emerald-400">Rp ${parseInt(act.price).toLocaleString('id-ID')}</b>?`,
                            icon: 'question',
                            background: '#0f172a', color: '#f1f5f9',
                            showCancelButton: true,
                            confirmButtonText: '<i class="fa-solid fa-handshake mr-1"></i> Beli!',
                            cancelButtonText: 'Lewati',
                            confirmButtonColor: '#10b981',
                            cancelButtonColor: '#475569',
                        }).then(result => {
                            if (result.isConfirmed) {
                                fetch(BASEURL + '/player/apiBuy', {
                                    method: 'POST',
                                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                                    body: `player_id=${player.id}&cell_index=${res.position}`
                                }).then(r => r.json()).then(buyRes => {
                                    if (buyRes.status === 'success') {
                                        if (moneyEl) moneyEl.textContent = 'Rp ' + parseInt(buyRes.money).toLocaleString('id-ID');
                                        showModal('<i class="fa-solid fa-check mr-1"></i> Berhasil!', buyRes.msg, 'success', '#10b981');
                                    } else {
                                        showModal('Gagal', buyRes.msg, 'error', '#ef4444');
                                    }
                                });
                            }
                        });
                    } else if (act.type === 'upgrade') {
                        Swal.fire({
                            title: '<i class="fa-solid fa-arrow-up mr-1"></i> Tingkatkan Properti?',
                            html: `Tingkatkan <b>${act.name}</b> ke level ${act.current_level + 1} seharga <b class="text-emerald-400">Rp ${parseInt(act.price).toLocaleString('id-ID')}</b>?`,
                            icon: 'question',
                            background: '#0f172a', color: '#f1f5f9',
                            showCancelButton: true,
                            confirmButtonText: '<i class="fa-solid fa-arrow-up mr-1"></i> Tingkatkan!',
                            cancelButtonText: 'Lewati',
                            confirmButtonColor: '#3b82f6',
                            cancelButtonColor: '#475569',
                        }).then(result => {
                            if (result.isConfirmed) {
                                fetch(BASEURL + '/player/apiBuy', {
                                    method: 'POST',
                                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                                    body: `player_id=${player.id}&cell_index=${res.position}`
                                }).then(r => r.json()).then(buyRes => {
                                    if (buyRes.status === 'success') {
                                        if (moneyEl) moneyEl.textContent = 'Rp ' + parseInt(buyRes.money).toLocaleString('id-ID');
                                        showModal('<i class="fa-solid fa-check mr-1"></i> Berhasil!', buyRes.msg, 'success', '#3b82f6');
                                    } else {
                                        showModal('Gagal', buyRes.msg, 'error', '#ef4444');
                                    }
                                });
                            }
                        });
                    } else if (act.type === 'rent') {
                        showModal('<i class="fa-solid fa-money-bill-wave mr-1"></i> Bayar Sewa!', act.msg, 'warning', '#f97316');
                    } else if (act.type === 'tax') {
                        showModal('<i class="fa-solid fa-landmark mr-1"></i> Bayar Pajak!', act.msg, 'warning', '#f97316');
                    } else if (act.type === 'jail') {
                        showModal('<i class="fa-solid fa-handcuffs mr-1"></i> DITANGKAP!', act.msg || 'Masuk penjara!', 'error', '#ef4444');
                    } else if (act.pass_go) {
                        showModal('<i class="fa-solid fa-star mr-1"></i> Melewati Start!', `Terima bonus <b class="text-yellow-400">Rp ${parseInt(act.pass_go_bonus).toLocaleString('id-ID')}</b>!`, 'success', '#eab308');
                    } else {
                        showModal(title, text, 'info', color);
                    }

                    // Tampilkan tombol Selesai Giliran
                    endTurnBtn.classList.remove('hidden');
                    endTurnBtn.disabled = false;
                    endTurnBtn.innerHTML = '<i class="fa-solid fa-flag-checkered mr-2"></i>Selesai Giliran';
                    setTimeout(() => { endTurnBtn.scrollIntoView({ behavior: 'smooth', block: 'center' }); }, 300);

                }).catch(() => { btn.disabled = false; });

            }, 800);
        });

        // Selesai Giliran
        endTurnBtn.addEventListener('click', function() {
            const btn = endTurnBtn; // Simpan referensi — jangan pakai 'this' di dalam .then()
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-2"></i>Memproses...';

            fetch(BASEURL + '/player/apiEndTurn', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `id=${player.id}`
            })
            .then(r => r.json())
            .then(res => {
                if (res.status === 'success') {
                    btn.innerHTML = '<i class="fa-solid fa-check mr-2"></i>Selesai!';
                    hasRolled = false;
                    isTurn = false;
                    showModal('<i class="fa-solid fa-check mr-1"></i> Giliran Selesai!', 'Menunggu giliran berikutnya...', 'info', '#3b82f6');
                    setTimeout(() => {
                        btn.classList.add('hidden');
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fa-solid fa-flag-checkered mr-2"></i>Selesai Giliran';
                    }, 1200);
                } else {
                    // Gagal — kembalikan tombol
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fa-solid fa-flag-checkered mr-2"></i>Selesai Giliran';
                    showModal('<i class="fa-solid fa-triangle-exclamation mr-1"></i> Gagal', res.msg || 'Coba lagi.', 'warning', '#f59e0b');
                }
            })
            .catch(() => {
                // Network error — selalu kembalikan tombol
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-flag-checkered mr-2"></i>Selesai Giliran';
                showModal('<i class="fa-solid fa-wifi mr-1"></i> Koneksi Error', 'Periksa jaringan lalu coba lagi.', 'error', '#ef4444');
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

                    // Check if properties changed (bought new property or upgraded house)
                    if (status.properties) {
                        const newHash = JSON.stringify(status.properties);
                        if (newHash !== currentPropsHash) {
                            // Properties changed! Reload page to render new properties
                            location.reload();
                            return;
                        }
                    }

                    if (!wasMyTurn && isTurn) {
                        // Giliran baru dimulai!
                        hasRolled = false;
                        rollBtn.disabled = false;
                        rollBtn.classList.remove('opacity-30');
                        endTurnBtn.classList.add('hidden');
                        showModal('<i class="fa-solid fa-dice mr-1"></i> Giliran Kamu!', 'Sekarang giliranmu! Lempar dadu.', 'success', '#22c55e');
                    }

                    // Update badge status
                    const badge = document.getElementById('turn-badge');
                    if (badge) {
                        if (isTurn) {
                            badge.className = 'mx-6 mt-4 rounded-2xl px-4 py-3 flex items-center gap-3 bg-emerald-500/20 border border-emerald-500/40';
                            badge.innerHTML = '<i class="fa-solid fa-crown text-2xl text-amber-400 animate-pulse"></i><div><div class="text-emerald-400 font-black text-base">GILIRAN KAMU!</div><div class="text-emerald-400/60 text-xs">Lempar dadu sekarang</div></div>';
                        } else {
                            badge.className = 'mx-6 mt-4 rounded-2xl px-4 py-3 flex items-center gap-3 bg-slate-800/60 border border-white/10';
                            badge.innerHTML = '<i class="fa-regular fa-clock text-2xl text-slate-500"></i><div><div class="text-slate-400 font-black text-base">MENUNGGU...</div><div class="text-slate-600 text-xs">Bukan giliranmu saat ini</div></div>';
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

