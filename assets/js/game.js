// ============================================================
// game.js — Monopoly Indonesia Board Controller
// ============================================================

// 🔊 Audio context (shared, reused)
let _audioCtx = null;
function getAudioCtx() {
    if (!_audioCtx) {
        try { _audioCtx = new (window.AudioContext || window.webkitAudioContext)(); } catch(e) {}
    }
    return _audioCtx;
}

/** Suara "tok" langkah kaki token di papan */
function playTokenStep() {
    const ctx = getAudioCtx();
    if (!ctx) return;
    try {
        const osc  = ctx.createOscillator();
        const gain = ctx.createGain();
        osc.connect(gain);
        gain.connect(ctx.destination);
        osc.type = 'triangle';
        osc.frequency.setValueAtTime(300, ctx.currentTime);
        osc.frequency.exponentialRampToValueAtTime(140, ctx.currentTime + 0.07);
        gain.gain.setValueAtTime(0.22, ctx.currentTime);
        gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.12);
        osc.start(ctx.currentTime);
        osc.stop(ctx.currentTime + 0.12);
    } catch(e) {}
}

/** Suara "bum" mendarat di kotak */
function playTokenLand() {
    const ctx = getAudioCtx();
    if (!ctx) return;
    try {
        const osc  = ctx.createOscillator();
        const gain = ctx.createGain();
        osc.connect(gain);
        gain.connect(ctx.destination);
        osc.type = 'sine';
        osc.frequency.setValueAtTime(180, ctx.currentTime);
        osc.frequency.exponentialRampToValueAtTime(60, ctx.currentTime + 0.25);
        gain.gain.setValueAtTime(0.35, ctx.currentTime);
        gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.3);
        osc.start(ctx.currentTime);
        osc.stop(ctx.currentTime + 0.3);
    } catch(e) {}
}

// Init audio context on first user interaction
document.addEventListener('click', () => { getAudioCtx(); }, { once: true });

let players  = [];
let sessionId = null;
const animating = {};


if (typeof dbPlayers !== 'undefined' && dbPlayers.length > 0) {
    players = dbPlayers.map(p => ({
        id:       parseInt(p.id),
        name:     p.name,
        color:    p.color,
        position: parseInt(p.position),
        money:    parseInt(p.money),
        is_turn:  parseInt(p.is_turn) === 1,
    }));
}
if (typeof dbSessionId !== 'undefined') sessionId = dbSessionId;

// ============================================================
// TOKEN SYSTEM — Tokens live as absolute children of .monopoly-board
// ============================================================

/**
 * Hitung posisi tengah sel relatif terhadap board
 * Offset disesuaikan jumlah pemain agar tidak tumpuk
 */
function getCellPosOnBoard(cellIndex, playerIndex) {
    const board = document.querySelector('.monopoly-board');
    const cell  = document.getElementById(`cell-${cellIndex}`);
    if (!board || !cell) return null;

    const total = players.length;
    let offsets;
    if (total === 2) {
        offsets = [
            { dx: -7, dy: 0 },
            { dx:  7, dy: 0 },
        ];
    } else if (total === 3) {
        offsets = [
            { dx: -7, dy: -5 },
            { dx:  7, dy: -5 },
            { dx:  0, dy:  7 },
        ];
    } else {
        offsets = [
            { dx: -7, dy: -6 },
            { dx:  7, dy: -6 },
            { dx: -7, dy:  6 },
            { dx:  7, dy:  6 },
        ];
    }
    const off = offsets[playerIndex % offsets.length] || { dx: 0, dy: 0 };

    return {
        left: cell.offsetLeft + cell.offsetWidth / 2 - 10 + off.dx,
        top:  cell.offsetTop  + cell.offsetHeight / 2 - 10 + off.dy,
    };
}

/**
 * Tempatkan token di sel tertentu (tanpa animasi)
 */
function snapTokenToCell(token, cellIndex, playerIndex) {
    const pos = getCellPosOnBoard(cellIndex, playerIndex);
    if (!pos) return;
    // Nonaktifkan transition sementara untuk snap langsung
    token.style.transition = 'none';
    token.style.left = pos.left + 'px';
    token.style.top  = pos.top  + 'px';
    // Kembalikan transition setelah satu frame
    requestAnimationFrame(() => {
        token.style.transition = '';
    });
}

/**
 * Definisi visual unik per slot pemain
 */
const TOKEN_STYLES = [
    { bg: 'linear-gradient(135deg,#ef4444,#b91c1c)', border: '#fca5a5', icon: '♟', shadow: 'rgba(239,68,68,0.7)' },
    { bg: 'linear-gradient(135deg,#3b82f6,#1d4ed8)', border: '#93c5fd', icon: '⚑', shadow: 'rgba(59,130,246,0.7)' },
    { bg: 'linear-gradient(135deg,#22c55e,#15803d)', border: '#86efac', icon: '★', shadow: 'rgba(34,197,94,0.7)'  },
    { bg: 'linear-gradient(135deg,#eab308,#a16207)', border: '#fde047', icon: '♛', shadow: 'rgba(234,179,8,0.7)'  },
];

/**
 * Buat / reset semua token di board (dipanggil saat init & refresh)
 */
function placeTokens() {
    // Hapus token lama
    document.querySelectorAll('.board-token').forEach(t => t.remove());

    const board = document.querySelector('.monopoly-board');
    if (!board) return;

    players.forEach((p, idx) => {
        const style = TOKEN_STYLES[idx % TOKEN_STYLES.length];
        const token = document.createElement('div');
        token.className = `board-token player-token p${p.id}`;
        token.id        = `token-p${p.id}`;
        token.title     = p.name;

        // Inisial nama pemain
        const initials = p.name.substring(0, 2).toUpperCase();

        token.style.cssText = `
            position: absolute;
            z-index: 200;
            pointer-events: none;
            width: 22px; height: 22px;
            border-radius: 50%;
            background: ${style.bg};
            border: 2px solid ${style.border};
            box-shadow: 0 2px 8px ${style.shadow}, 0 0 0 1px rgba(255,255,255,0.3);
            display: flex; align-items: center; justify-content: center;
            font-size: 10px; font-weight: 900; color: white;
            font-family: 'Outfit', sans-serif;
            text-shadow: 0 1px 2px rgba(0,0,0,0.5);
            transition:
                left   350ms cubic-bezier(0.34, 1.56, 0.64, 1),
                top    350ms cubic-bezier(0.34, 1.56, 0.64, 1),
                transform 200ms ease,
                box-shadow 200ms ease;
        `;
        token.innerHTML = `<span style="line-height:1;font-size:9px;">${initials}</span>`;

        board.appendChild(token);
        snapTokenToCell(token, p.position, idx);
    });

    updateCenterInfo();
}

// ============================================================
// STEP-BY-STEP ANIMATION
// Setiap langkah: update CSS left/top → CSS transition handle animasi
// ============================================================
function animateTokenStepByStep(player, fromPos, toPos, stepDelay = 380) {
    if (animating[player.id]) return;
    animating[player.id] = true;

    const BOARD_SIZE  = 40;
    const stepsNeeded = (toPos - fromPos + BOARD_SIZE) % BOARD_SIZE;

    if (stepsNeeded === 0) {
        animating[player.id] = false;
        return;
    }

    const playerIndex = players.findIndex(p => p.id === player.id);
    const token = document.getElementById(`token-p${player.id}`);
    if (!token) { animating[player.id] = false; return; }

    let currentPos = fromPos;
    let stepCount  = 0;

    function moveOneStep() {
        currentPos = (currentPos + 1) % BOARD_SIZE;
        stepCount++;

        // Hitung posisi CSS tujuan
        const pos = getCellPosOnBoard(currentPos, playerIndex);
        if (pos) {
            // CSS transition otomatis menganimasikan pergerakan
            token.style.left = pos.left + 'px';
            token.style.top  = pos.top  + 'px';

            // 🔊 Suara langkah setiap petak
            playTokenStep();

            // Efek lompat kecil: naik saat bergerak, kembali saat mendarat
            token.style.transform = 'scale(1.25) translateY(-5px)';
            setTimeout(() => {
                token.style.transform = '';
            }, stepDelay * 0.45);
        }

        // Notifikasi jika melewati petak Start
        if (currentPos === 0 && stepCount < stepsNeeded) {
            showToast('<i class="fa-solid fa-star" style="color:#eab308;margin-right:6px"></i>' + player.name + ' melewati Start! +Rp 2.000');
        }

        if (stepCount < stepsNeeded) {
            setTimeout(moveOneStep, stepDelay);
        } else {
            // Selesai — update state
            player.position = toPos;
            animating[player.id] = false;

            // 🔊 Suara mendarat
            playTokenLand();

            // Efek mendarat: flash besar lalu normal
            token.style.transform = 'scale(1.8)';
            token.style.boxShadow = '0 0 20px 6px rgba(255,255,255,0.5)';
            setTimeout(() => {
                token.style.transform = '';
                token.style.boxShadow = '';
                updateCenterInfo();
            }, 350);
        }
    }

    // Mulai setelah 1 frame agar browser siap render
    requestAnimationFrame(() => setTimeout(moveOneStep, 30));
}

// ============================================================
// TOAST
// ============================================================
function showToast(msg) {
    let toast = document.getElementById('game-toast');
    if (!toast) {
        toast = document.createElement('div');
        toast.id = 'game-toast';
        toast.style.cssText = `
            position:fixed; bottom:28px; left:50%; transform:translateX(-50%);
            background:rgba(15,23,42,0.96); color:#f1f5f9;
            padding:12px 24px; border-radius:14px;
            border:1px solid rgba(255,255,255,0.12);
            font-family:'Outfit',sans-serif; font-weight:700; font-size:1rem;
            z-index:9999; box-shadow:0 10px 30px rgba(0,0,0,0.5);
            transition:opacity 0.4s; opacity:0;
            display:flex; align-items:center; gap:8px;
            white-space:nowrap;
        `;
        document.body.appendChild(toast);
    }
    toast.innerHTML = msg;
    toast.style.opacity = '1';
    clearTimeout(toast._t);
    toast._t = setTimeout(() => { toast.style.opacity = '0'; }, 2800);
}

// ============================================================
// CENTER INFO & TURN HIGHLIGHT
// ============================================================
function updateCenterInfo() {
    const curr = players.find(p => p.is_turn);
    const el   = document.getElementById('center-turn-info');
    if (el) {
        el.innerHTML = curr
            ? `<span style="color:var(--color-${curr.color})"><i class="fa-solid fa-crown" style="color:#f59e0b;margin-right:6px"></i>Giliran ${curr.name}</span>`
            : '';
    }

    // Highlight token pemain yang sedang giliran
    players.forEach(p => {
        const t = document.getElementById(`token-p${p.id}`);
        if (!t) return;
        if (p.is_turn && !animating[p.id]) {
            t.style.outline   = '3px solid white';
            t.style.outlineOffset = '2px';
            t.style.transform = 'scale(1.4)';
        } else {
            t.style.outline   = '';
            t.style.outlineOffset = '';
            if (!animating[p.id]) t.style.transform = '';
        }
    });
}

// ============================================================
// BANK MODAL
// ============================================================
function bankAdjust(playerId, playerName, direction) {
    Swal.fire({
        title: direction > 0
            ? '<i class="fa-solid fa-coins"></i> Tambah Uang'
            : '<i class="fa-solid fa-money-bill-wave"></i> Kurangi Uang',
        html:  `<b>${playerName}</b><br><small style="color:#94a3b8">Masukkan jumlah dalam Rupiah</small>`,
        input: 'number',
        inputAttributes: { min: 0, step: 1000, placeholder: '10000' },
        background: '#0f172a', color: '#f1f5f9',
        confirmButtonColor: direction > 0 ? '#10b981' : '#ef4444',
        confirmButtonText: direction > 0 ? '+ Tambahkan' : '- Kurangi',
        showCancelButton: true, cancelButtonText: 'Batal', cancelButtonColor: '#475569',
    }).then(result => {
        if (!result.isConfirmed || !result.value) return;
        const amount = parseInt(result.value) * direction;
        fetch(BASEURL + '/home/apiAdjustMoney', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `player_id=${playerId}&amount=${amount}`,
        })
        .then(r => r.json())
        .then(data => {
            if (data.status === 'success') {
                const p = players.find(p => p.id === playerId);
                if (p) p.money = data.new_money;
                updateBankModal();
                Swal.fire({ title: 'Berhasil!', icon: 'success', background: '#0f172a', color: '#fff', timer: 1000, showConfirmButton: false });
            }
        });
    });
}

function updateBankModal() {
    players.forEach(p => {
        const el = document.getElementById(`bank-money-${p.id}`);
        if (el) el.textContent = 'Rp ' + p.money.toLocaleString('id-ID');
    });
}

// ============================================================
// FLOATING MONEY ANIMATION
// ============================================================
function showFloatingMoney(playerId, amount) {
    const token = document.getElementById(`token-p${playerId}`);
    if (!token) return;

    const floater = document.createElement('div');
    const isPositive = amount > 0;
    
    floater.innerHTML = (isPositive ? '+' : '-') + ' Rp ' + Math.abs(amount).toLocaleString('id-ID');
    floater.style.cssText = `
        position: absolute;
        top: -20px;
        left: 50%;
        transform: translateX(-50%);
        font-family: 'Outfit', sans-serif;
        font-weight: 900;
        font-size: 1.2rem;
        white-space: nowrap;
        color: ${isPositive ? '#34d399' : '#f87171'};
        text-shadow: 0px 2px 4px rgba(0,0,0,0.8), 0 0 10px ${isPositive ? '#059669' : '#dc2626'};
        pointer-events: none;
        z-index: 300;
        opacity: 1;
        transition: all 1.5s cubic-bezier(0.25, 1, 0.5, 1);
    `;
    
    token.appendChild(floater);

    // Trigger animation next frame
    requestAnimationFrame(() => {
        floater.style.top = '-60px';
        floater.style.opacity = '0';
        floater.style.transform = `translateX(-50%) scale(1.2)`;
    });

    setTimeout(() => {
        if (floater.parentNode) floater.parentNode.removeChild(floater);
    }, 1500);
}

// ============================================================
// POLLING REALTIME
// ============================================================
const POLLING_MS = typeof POLLING_INTERVAL !== 'undefined' ? POLLING_INTERVAL : 1500;

let lastActiveCardText = null;
let cardModalOpen = false;

function showBoardCardModal(cardData) {
    if (cardModalOpen) return;
    cardModalOpen = true;
    const isKesempatan = cardData.type === 'kesempatan';
    const cColor = isKesempatan ? '#f59e0b' : '#10b981';
    const cIcon  = isKesempatan ? 'fa-question' : 'fa-gem';
    const title  = isKesempatan ? 'KESEMPATAN' : 'DANA UMUM';

    const html = `
    <style>
    .bc-card{perspective:1000px;width:100%;height:260px;margin:10px 0;cursor:pointer}
    .bc-inner{position:relative;width:100%;height:100%;transition:transform .8s cubic-bezier(.34,1.56,.64,1);transform-style:preserve-3d}
    .bc-inner.flipped{transform:rotateY(180deg)}
    .bc-face{position:absolute;width:100%;height:100%;backface-visibility:hidden;border-radius:16px;display:flex;flex-direction:column;justify-content:center;align-items:center;padding:20px;border:4px solid ${cColor}}
    .bc-front{background:radial-gradient(circle,#1e293b,#0f172a)}
    .bc-back{background:white;color:#0f172a;transform:rotateY(180deg)}
    </style>
    <div style="color:#94a3b8;font-size:.8rem;margin-bottom:4px"><b style="color:#f1f5f9">${cardData.player_name}</b> mendapat kartu:</div>
    <div class="bc-card" onclick="this.querySelector('.bc-inner').classList.add('flipped')">
      <div class="bc-inner">
        <div class="bc-face bc-front">
          <i class="fa-solid ${cIcon}" style="font-size:70px;color:${cColor};text-shadow:0 0 20px ${cColor}80"></i>
          <div style="color:white;margin-top:16px;font-weight:900;font-size:1.1rem;letter-spacing:2px">${title}</div>
          <div style="color:#94a3b8;font-size:.75rem;margin-top:8px">Ketuk untuk membalik</div>
        </div>
        <div class="bc-face bc-back">
          <div style="background:${cColor};color:white;width:calc(100%+40px);margin-top:-20px;padding:8px 15px;font-weight:900;font-size:.9rem;border-radius:10px 10px 0 0;width:100%">${title}</div>
          <div style="flex-grow:1;display:flex;align-items:center;justify-content:center;padding:15px;text-align:center">
            <h3 style="font-weight:bold;font-size:1rem;color:#1e293b;line-height:1.5">${cardData.text}</h3>
          </div>
        </div>
      </div>
    </div>`;

    if (typeof Swal !== 'undefined') {
        Swal.fire({
            html,
            background: 'transparent',
            showConfirmButton: true,
            confirmButtonText: 'Tutup',
            confirmButtonColor: cColor,
            backdrop: 'rgba(0,0,0,0.85)'
        }).then(() => { cardModalOpen = false; lastActiveCardText = null; });
        setTimeout(() => {
            const inner = document.querySelector('.bc-inner');
            if (inner && !inner.classList.contains('flipped')) inner.classList.add('flipped');
        }, 1400);
    }
}

setInterval(() => {
    if (!sessionId) return;
    fetch(BASEURL + '/home/apiStatus/' + sessionId)
        .then(r => r.json())
        .then(serverData => {
            const serverPlayers = serverData.players;
            if (!Array.isArray(serverPlayers)) return;
            serverPlayers.forEach(sp => {
                const lp = players.find(p => p.id == sp.id);
                if (!lp) return;

                const serverPos = parseInt(sp.position);
                const serverMoney = parseInt(sp.money);

                if (serverPos !== lp.position && !animating[lp.id]) {
                    animateTokenStepByStep(lp, lp.position, serverPos);
                }

                if (serverMoney !== lp.money) {
                    const diff = serverMoney - lp.money;
                    showFloatingMoney(lp.id, diff);
                    lp.money = serverMoney;
                }

                lp.is_turn = parseInt(sp.is_turn) === 1;
            });

            if (serverData.properties) {
                renderProperties(serverData.properties);
            }

            // Card popup only shown on player screen (phone), not on board

            updateBankModal();
            updateCenterInfo();
        })
        .catch(() => {});
}, POLLING_MS);

function renderProperties(properties) {
    // Bersihkan ownership lama
    document.querySelectorAll('.owner-bar, .house-container').forEach(el => el.remove());
    
    properties.forEach(prop => {
        const cell = document.getElementById(`cell-${prop.cell_index}`);
        if (!cell) return;
        
        // Bar kepemilikan
        let barClass = `owner-bar bg-${prop.owner_color}-500 absolute z-30 opacity-80 shadow-[inset_0_0_8px_rgba(0,0,0,0.5)] `;
        if (cell.classList.contains('cell-bottom')) barClass += 'left-0 right-0 top-0 h-3';
        else if (cell.classList.contains('cell-top')) barClass += 'left-0 right-0 bottom-0 h-3';
        else if (cell.classList.contains('cell-left')) barClass += 'top-0 bottom-0 right-0 w-3';
        else if (cell.classList.contains('cell-right')) barClass += 'top-0 bottom-0 left-0 w-3';
        else barClass += 'left-0 right-0 bottom-0 h-3';

        const ownerBar = document.createElement('div');
        ownerBar.className = barClass;
        cell.appendChild(ownerBar);
        
        // Tampilkan Tanda Tanah/Rumah di sebelah owner bar (bukan di atasnya)
        let houseClass = 'house-container flex absolute gap-0 z-40 items-center justify-center flex-wrap bg-black/60 shadow-lg ';
        if (cell.classList.contains('cell-bottom')) houseClass += 'top-3 left-0 right-0 h-4 flex-row border-b border-white/20';
        else if (cell.classList.contains('cell-top')) houseClass += 'bottom-3 left-0 right-0 h-4 flex-row border-t border-white/20';
        else if (cell.classList.contains('cell-left')) houseClass += 'right-3 top-0 bottom-0 w-4 flex-col border-l border-white/20';
        else if (cell.classList.contains('cell-right')) houseClass += 'left-3 top-0 bottom-0 w-4 flex-col border-r border-white/20';
        else houseClass += 'top-3 left-0 right-0 h-4 flex-row';

        const houseContainer = document.createElement('div');
        houseContainer.className = houseClass;
        
        let level = parseInt(prop.houses);
        if (level === 0) {
            // Icon Tanah (belum ada bangunan, tapi sudah dimiliki)
            houseContainer.innerHTML = `<i class="fa-solid fa-map-pin text-white text-[9px] drop-shadow-md"></i>`;
        } else if (level === 5) {
            // Level 5 (Hotel/Apartemen) -> Tampil satu icon saja
            houseContainer.innerHTML = `<i class="fa-solid fa-hotel text-white text-[12px] drop-shadow-md mx-[1px] my-[1px]"></i>`;
        } else {
            // Level 1-4 -> Tampil rumah sejumlah level
            for (let i = 0; i < level; i++) {
                houseContainer.innerHTML += `<i class="fa-solid fa-house text-white text-[9px] drop-shadow-md mx-[1px] my-[1px]"></i>`;
            }
        }
        cell.appendChild(houseContainer);
    });
}

// ============================================================
// INIT
// ============================================================
document.addEventListener('DOMContentLoaded', () => {
    placeTokens();
    updateBankModal();
});
