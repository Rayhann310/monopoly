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

<!-- Dice Action Area -->
<div class="flex-1 flex flex-col items-center justify-center p-6 gap-6 mt-4">
    <div class="text-center">
        <h2 class="text-slate-400 font-bold mb-2">Giliran Kamu?</h2>
        <p class="text-slate-500 text-sm max-w-xs">Tekan tombol di bawah untuk melempar dadu. Hasil akan otomatis terkirim ke papan utama.</p>
    </div>

    <!-- Dice Display -->
    <div class="flex gap-6 justify-center items-center h-32 w-full bg-slate-900/50 rounded-2xl border border-white/5 shadow-inner">
        <i id="mobile-die1" class="fa-solid fa-dice-one text-6xl text-<?= $data['player']['color'] ?>-500 drop-shadow-[0_0_15px_rgba(255,255,255,0.2)]"></i>
        <i id="mobile-die2" class="fa-solid fa-dice-one text-6xl text-<?= $data['player']['color'] ?>-500 drop-shadow-[0_0_15px_rgba(255,255,255,0.2)]"></i>
    </div>

    <!-- Roll Button -->
    <button id="mobile-roll-btn" class="w-full max-w-sm py-4 bg-gradient-to-r <?= $bgGrad ?> text-white rounded-2xl font-black text-xl shadow-[0_10px_30px_rgba(0,0,0,0.5)] active:scale-95 transition-transform border border-white/30">
        <i class="fa-solid fa-hand-sparkles mr-2"></i> LEMPAR DADU
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
        
        // Helper SweetAlert yang aman - tidak pernah pakai alert() biasa
        function showModal(title, text, icon, color) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: title,
                    html: text,
                    icon: icon,
                    background: '#0f172a',
                    color: '#f1f5f9',
                    confirmButtonColor: color || '#3b82f6',
                    confirmButtonText: '<i class="fa fa-check"></i> Oke',
                    showClass: { popup: 'animate__animated animate__fadeInDown animate__faster' },
                    hideClass: { popup: 'animate__animated animate__fadeOutUp animate__faster' },
                    customClass: { popup: 'rounded-2xl border border-white/10 shadow-2xl' }
                });
            } else {
                // Fallback: modal custom sendiri jika Swal belum load
                const modal = document.getElementById('fallback-modal');
                document.getElementById('fm-title').innerText = title;
                document.getElementById('fm-text').innerHTML = text;
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            }
        }
        
        document.getElementById('mobile-roll-btn').addEventListener('click', function() {
            if(this.disabled) return;
            this.disabled = true;
            const btn = this;
            
            // Animasi dadu kocok
            const d1 = document.getElementById('mobile-die1');
            const d2 = document.getElementById('mobile-die2');
            d1.classList.add('shake');
            d2.classList.add('shake');
            
            setTimeout(() => {
                d1.classList.remove('shake');
                d2.classList.remove('shake');
                
                const v1 = Math.floor(Math.random() * 6) + 1;
                const v2 = Math.floor(Math.random() * 6) + 1;
                const total = v1 + v2;
                
                const diceIcons = ['', 'fa-dice-one', 'fa-dice-two', 'fa-dice-three', 'fa-dice-four', 'fa-dice-five', 'fa-dice-six'];
                d1.className = `fa-solid ${diceIcons[v1]} text-6xl text-${player.color}-500 drop-shadow-[0_0_15px_rgba(255,255,255,0.2)]`;
                d2.className = `fa-solid ${diceIcons[v2]} text-6xl text-${player.color}-500 drop-shadow-[0_0_15px_rgba(255,255,255,0.2)]`;
                
                // Hitung posisi baru
                player.position = (parseInt(player.position) + total) % 40;
                const landedCell = board[player.position];
                
                let title = `🎲 Dadu: ${total}`;
                let text = `<b>Kamu mendarat di:</b><br><span style="font-size:1.4rem;font-weight:900;color:#38bdf8">${landedCell.name}</span>`;
                let icon = 'success';
                let color = '#38bdf8';
                
                if(landedCell.name === 'Kesempatan') {
                    icon = 'question';
                    title = '🃏 Kartu Kesempatan!';
                    text = 'Ambil kartu dari tumpukan <b>Kesempatan</b> dan ikuti instruksinya.';
                    color = '#f59e0b';
                } else if(landedCell.name === 'Dana Umum') {
                    icon = 'question';
                    title = '💰 Dana Umum!';
                    text = 'Ambil kartu dari tumpukan <b>Dana Umum</b> dan ikuti instruksinya.';
                    color = '#10b981';
                } else if(landedCell.name === 'Penjara') {
                    icon = 'info';
                    title = '👀 Hanya Berkunjung';
                    text = 'Kamu sedang berada di area Penjara sebagai <b>pengunjung bebas</b>.';
                    color = '#6366f1';
                } else if(landedCell.name === 'Masuk Penjara') {
                    icon = 'error';
                    title = '🚔 DITANGKAP!';
                    text = 'Kamu masuk penjara! Pindah pion ke kotak Penjara.';
                    color = '#ef4444';
                } else if(landedCell.name === 'Pajak Mewah' || landedCell.name === 'Pajak') {
                    icon = 'warning';
                    title = '🏛️ Bayar Pajak!';
                    text = `Kamu kena pajak di <b>${landedCell.name}</b>. Bayar ke Bank Negara!`;
                    color = '#f97316';
                } else if(landedCell.name === 'Parkir Bebas') {
                    icon = 'success';
                    title = '🅿️ Parkir Bebas!';
                    text = 'Kamu istirahat di Parkir Bebas. Tidak ada denda!';
                    color = '#22c55e';
                } else if(landedCell.name === 'Start') {
                    icon = 'success';
                    title = '⭐ Melewati Start!';
                    text = 'Kamu melewati kotak Start. Terima <b>Rp 2.000</b> dari Bank!';
                    color = '#eab308';
                }
                
                showModal(title, text, icon, color);
                
                // Kirim AJAX ke server
                fetch('<?= BASEURL; ?>/player/apiRoll', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `id=${player.id}&new_position=${player.position}`
                }).then(res => res.json())
                  .then(() => {
                      setTimeout(() => { btn.disabled = false; }, 2000);
                  })
                  .catch(() => {
                      setTimeout(() => { btn.disabled = false; }, 2000);
                  });
                  
            }, 800);
        });
    });
</script>

<!-- Fallback Modal jika Swal belum load -->
<div id="fallback-modal" class="hidden fixed inset-0 bg-black/90 z-[999] items-center justify-center p-6">
    <div class="bg-slate-900 border border-white/10 rounded-2xl p-8 w-full max-w-sm text-center shadow-2xl">
        <h2 id="fm-title" class="text-2xl font-black text-white mb-4"></h2>
        <p id="fm-text" class="text-slate-300 mb-6"></p>
        <button onclick="document.getElementById('fallback-modal').classList.add('hidden'); document.getElementById('fallback-modal').classList.remove('flex');" class="px-8 py-3 bg-blue-500 hover:bg-blue-600 text-white font-bold rounded-xl w-full transition">
            Oke
        </button>
    </div>
</div>
