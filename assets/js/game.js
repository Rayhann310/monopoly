// ============================================================
// game.js — Monopoly Indonesia Board Controller
// ============================================================

// Players & session injected from PHP (see home/index.php)
let players = [];
let sessionId = null;

// Track ongoing animations to prevent overlapping
const animating = {};

if (typeof dbPlayers !== 'undefined' && dbPlayers.length > 0) {
    players = dbPlayers.map(p => ({
        id: parseInt(p.id),
        name: p.name,
        color: p.color,
        position: parseInt(p.position),
        money: parseInt(p.money),
        is_turn: parseInt(p.is_turn) === 1,
    }));
}

if (typeof dbSessionId !== 'undefined') {
    sessionId = dbSessionId;
}

// ---- Token: Get or Create ----
function getOrCreateToken(p) {
    let token = document.querySelector(`.player-token.p${p.id}`);
    if (!token) {
        // Create fresh token in starting cell
        const cell = document.getElementById(`cell-${p.position}`);
        if (!cell) return null;
        const container = cell.querySelector('.tokens');
        if (!container) return null;
        token = document.createElement('div');
        token.className = `player-token p${p.id}`;
        token.title = p.name;
        container.appendChild(token);
    }
    return token;
}

// ---- Token Initial Placement (no animation) ----
function placeTokens() {
    document.querySelectorAll('.player-token').forEach(t => t.remove());
    players.forEach(p => {
        const cell = document.getElementById(`cell-${p.position}`);
        if (!cell) return;
        const container = cell.querySelector('.tokens');
        if (!container) return;
        const token = document.createElement('div');
        token.className = `player-token p${p.id}`;
        token.title = p.name;
        container.appendChild(token);
    });
    updateCenterInfo();
}

// ---- Step-by-step Token Animation (Floating CSS Transition) ----
function animateTokenStepByStep(player, fromPos, toPos, stepDelay = 300) {
    if (animating[player.id]) return;
    animating[player.id] = true;

    const BOARD_SIZE = 40;
    const stepsNeeded = (toPos - fromPos + BOARD_SIZE) % BOARD_SIZE;
    if (stepsNeeded === 0) { animating[player.id] = false; return; }

    let currentPos = fromPos;
    let stepCount  = 0;

    // Sembunyikan token statis di posisi asal
    const staticToken = document.querySelector(`.player-token.p${player.id}`);
    if (staticToken) staticToken.style.opacity = '0';

    // Buat floating token (position: fixed, bergerak via CSS transition)
    const floatId = `float-p${player.id}`;
    let ft = document.getElementById(floatId);
    if (ft) ft.remove();
    ft = document.createElement('div');
    ft.id = floatId;
    ft.className = `player-token p${player.id}`;
    ft.style.cssText = `
        position: fixed; z-index: 9000; pointer-events: none;
        width: 22px; height: 22px;
        transition: left ${stepDelay * 0.75}ms cubic-bezier(0.34,1.56,0.64,1),
                    top  ${stepDelay * 0.75}ms cubic-bezier(0.34,1.56,0.64,1),
                    transform ${stepDelay * 0.3}ms ease;
        box-shadow: 0 6px 16px rgba(0,0,0,0.6);
    `;
    document.body.appendChild(ft);

    // Posisikan di sel awal
    function getCellCenter(pos) {
        const cell = document.getElementById(`cell-${pos}`);
        if (!cell) return null;
        const r = cell.getBoundingClientRect();
        return { x: r.left + r.width / 2 - 11, y: r.top + r.height / 2 - 11 };
    }

    const startPos = getCellCenter(fromPos);
    if (startPos) { ft.style.left = startPos.x + 'px'; ft.style.top = startPos.y + 'px'; }

    function moveOneStep() {
        currentPos = (currentPos + 1) % BOARD_SIZE;
        stepCount++;

        const center = getCellCenter(currentPos);
        if (center) {
            // Lompat sedikit ke atas saat bergerak (efek melompat)
            ft.style.transform = 'scale(1.3) translateY(-4px)';
            ft.style.left = center.x + 'px';
            ft.style.top  = center.y + 'px';
            // Kembali normal setelah transisi
            setTimeout(() => { ft.style.transform = ''; }, stepDelay * 0.4);
        }

        // Notifikasi jika melewati Start
        if (currentPos === 0 && stepCount < stepsNeeded) {
            showToast(`<i class="fa-solid fa-star" style="color:#eab308"></i> ${player.name} melewati Start! +Rp 2.000`);
        }

        if (stepCount < stepsNeeded) {
            setTimeout(moveOneStep, stepDelay);
        } else {
            // Animasi selesai — tempatkan token statis di tujuan
            setTimeout(() => {
                ft.remove();

                // Hapus token lama (yang disembunyikan)
                document.querySelectorAll(`.player-token.p${player.id}`).forEach(t => t.remove());

                // Buat token baru di sel tujuan
                const destCell = document.getElementById(`cell-${toPos}`);
                if (destCell) {
                    const container = destCell.querySelector('.tokens');
                    if (container) {
                        const newToken = document.createElement('div');
                        newToken.className = `player-token p${player.id}`;
                        newToken.title = player.name;
                        newToken.style.transform = 'scale(1.8)';
                        container.appendChild(newToken);
                        setTimeout(() => { newToken.style.transform = ''; }, 300);
                    }
                }

                player.position = toPos;
                animating[player.id] = false;
                updateCenterInfo();
            }, stepDelay * 0.8);
        }
    }

    // Mulai animasi setelah satu frame render
    requestAnimationFrame(() => setTimeout(moveOneStep, 80));
}

// ---- Toast Notification ----
function showToast(msg) {
    let toast = document.getElementById('game-toast');
    if (!toast) {
        toast = document.createElement('div');
        toast.id = 'game-toast';
        toast.style.cssText = `
            position: fixed; bottom: 30px; left: 50%; transform: translateX(-50%);
            background: rgba(15,23,42,0.95); color: #f1f5f9;
            padding: 12px 24px; border-radius: 12px;
            border: 1px solid rgba(255,255,255,0.15);
            font-family: 'Outfit', sans-serif; font-weight: 700;
            font-size: 1rem; z-index: 9999;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
            transition: opacity 0.4s;
        `;
        document.body.appendChild(toast);
    }
    toast.textContent = msg;
    toast.style.opacity = '1';
    clearTimeout(toast._timer);
    toast._timer = setTimeout(() => { toast.style.opacity = '0'; }, 2500);
}

// ---- Center Info Update ----
function updateCenterInfo() {
    const currentTurnPlayer = players.find(p => p.is_turn);
    const el = document.getElementById('center-turn-info');
    if (el) {
        el.innerHTML = currentTurnPlayer
            ? `<span style="color: var(--color-${currentTurnPlayer.color})"><i class="fa-solid fa-crown" style="color:#f59e0b"></i> Giliran ${currentTurnPlayer.name}</span>`
            : '';
    }

    // Update token highlight for turn indicator
    players.forEach(p => {
        const token = document.querySelector(`.player-token.p${p.id}`);
        if (token) {
            token.style.outline = p.is_turn ? '3px solid white' : '';
            token.style.transform = p.is_turn && !animating[p.id] ? 'scale(1.4)' : '';
            token.style.zIndex = p.is_turn ? '10' : '';
        }
    });
}

// ---- Bank Modal: Tombol +/- ----
function bankAdjust(playerId, playerName, direction) {
    Swal.fire({
        title: `${direction > 0 ? '<i class="fa-solid fa-coins"></i> Tambah' : '<i class="fa-solid fa-money-bill-wave"></i> Kurangi'} Uang`,
        html: `<b>${playerName}</b><br><small style="color:#94a3b8">Masukkan jumlah dalam Rupiah</small>`,
        input: 'number',
        inputAttributes: { min: 0, step: 1000, placeholder: '10000' },
        background: '#0f172a',
        color: '#f1f5f9',
        confirmButtonColor: direction > 0 ? '#10b981' : '#ef4444',
        confirmButtonText: direction > 0 ? '+ Tambahkan' : '- Kurangi',
        showCancelButton: true,
        cancelButtonText: 'Batal',
        cancelButtonColor: '#475569',
    }).then(result => {
        if (result.isConfirmed && result.value) {
            const amount = parseInt(result.value) * direction;
            fetch(BASEURL + '/home/apiAdjustMoney', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `player_id=${playerId}&amount=${amount}`
            })
            .then(r => r.json())
            .then(data => {
                if (data.status === 'success') {
                    const p = players.find(p => p.id === playerId);
                    if (p) { p.money = data.new_money; }
                    updateBankModal();
                    Swal.fire({ title: 'Berhasil!', icon: 'success', background: '#0f172a', color: '#fff', timer: 1000, showConfirmButton: false });
                }
            });
        }
    });
}

function updateBankModal() {
    players.forEach(p => {
        const el = document.getElementById(`bank-money-${p.id}`);
        if (el) el.textContent = 'Rp ' + p.money.toLocaleString('id-ID');
    });
}

// ---- Realtime Polling ----
const POLLING_MS = typeof POLLING_INTERVAL !== 'undefined' ? POLLING_INTERVAL : 1500;

setInterval(() => {
    if (!sessionId) return;
    fetch(BASEURL + '/home/apiStatus/' + sessionId)
        .then(res => res.json())
        .then(serverPlayers => {
            if (!Array.isArray(serverPlayers)) return;
            serverPlayers.forEach(sp => {
                const lp = players.find(p => p.id == sp.id);
                if (!lp) return;

                const serverPos  = parseInt(sp.position);
                const serverTurn = parseInt(sp.is_turn) === 1;
                const oldPos     = lp.position;

                // Hanya animasikan jika posisi berubah DAN tidak sedang animasi
                if (serverPos !== oldPos && !animating[lp.id]) {
                    animateTokenStepByStep(lp, oldPos, serverPos, 280);
                }

                // Update data lokal TANPA mereset posisi (posisi di-handle oleh animasi)
                lp.money   = parseInt(sp.money);
                lp.is_turn = serverTurn;
            });

            // Jangan panggil placeTokens() di sini! Biarkan animasi yang handle.
            updateBankModal();
            updateCenterInfo();
        })
        .catch(() => {});
}, POLLING_MS);

// ---- Init ----
document.addEventListener('DOMContentLoaded', function () {
    placeTokens();
    updateBankModal();
    updateCenterInfo();
});
