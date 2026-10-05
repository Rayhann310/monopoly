// ============================================================
// game.js — Monopoly Indonesia Board Controller
// ============================================================

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
 */
function getCellPosOnBoard(cellIndex, playerIndex) {
    const board = document.querySelector('.monopoly-board');
    const cell  = document.getElementById(`cell-${cellIndex}`);
    if (!board || !cell) return null;

    const br = board.getBoundingClientRect();
    const cr = cell.getBoundingClientRect();

    // Offset kecil agar token 4 pemain tidak tumpuk persis
    const offsets = [
        { dx: -5, dy: -5 },
        { dx:  5, dy: -5 },
        { dx: -5, dy:  5 },
        { dx:  5, dy:  5 },
    ];
    const off = offsets[playerIndex % 4] || { dx: 0, dy: 0 };

    return {
        left: cr.left - br.left + cr.width  / 2 - 11 + off.dx,
        top:  cr.top  - br.top  + cr.height / 2 - 11 + off.dy,
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
 * Buat / reset semua token di board (dipanggil saat init & refresh)
 */
function placeTokens() {
    // Hapus token lama
    document.querySelectorAll('.board-token').forEach(t => t.remove());

    const board = document.querySelector('.monopoly-board');
    if (!board) return;

    players.forEach((p, idx) => {
        const token = document.createElement('div');
        token.className   = `board-token player-token p${p.id}`;
        token.id          = `token-p${p.id}`;
        token.title       = p.name;
        token.style.cssText = `
            position: absolute;
            z-index: 200;
            pointer-events: none;
            width: 20px; height: 20px;
            border-radius: 50%;
            transition:
                left  350ms cubic-bezier(0.34, 1.56, 0.64, 1),
                top   350ms cubic-bezier(0.34, 1.56, 0.64, 1),
                transform 200ms ease,
                box-shadow 200ms ease;
        `;
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
// POLLING REALTIME
// ============================================================
const POLLING_MS = typeof POLLING_INTERVAL !== 'undefined' ? POLLING_INTERVAL : 1500;

setInterval(() => {
    if (!sessionId) return;
    fetch(BASEURL + '/home/apiStatus/' + sessionId)
        .then(r => r.json())
        .then(serverPlayers => {
            if (!Array.isArray(serverPlayers)) return;
            serverPlayers.forEach(sp => {
                const lp = players.find(p => p.id == sp.id);
                if (!lp) return;

                const serverPos = parseInt(sp.position);

                // Animasi step-by-step HANYA jika posisi berubah & belum animasi
                if (serverPos !== lp.position && !animating[lp.id]) {
                    animateTokenStepByStep(lp, lp.position, serverPos);
                }

                lp.money   = parseInt(sp.money);
                lp.is_turn = parseInt(sp.is_turn) === 1;
            });

            updateBankModal();
            updateCenterInfo();
        })
        .catch(() => {});
}, POLLING_MS);

// ============================================================
// INIT
// ============================================================
document.addEventListener('DOMContentLoaded', () => {
    placeTokens();
    updateBankModal();
});
