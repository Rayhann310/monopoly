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

// ---- Step-by-step Token Animation ----
// Moves a token from `fromPos` to `toPos` one step at a time.
// stepDelay: ms between each step
function animateTokenStepByStep(player, fromPos, toPos, stepDelay = 250) {
    if (animating[player.id]) return; // Already animating, skip
    animating[player.id] = true;

    const BOARD_SIZE = 40;
    let currentPos = fromPos;

    // Calculate steps, wrapping around the board
    const stepsNeeded = (toPos - fromPos + BOARD_SIZE) % BOARD_SIZE;
    if (stepsNeeded === 0) {
        animating[player.id] = false;
        return;
    }

    let stepCount = 0;
    const passedStart = toPos < fromPos; // Wrapped around

    function moveOneStep() {
        currentPos = (currentPos + 1) % BOARD_SIZE;
        stepCount++;

        // Move token DOM element to next cell
        const nextCell = document.getElementById(`cell-${currentPos}`);
        if (nextCell) {
            // Remove token from old cell
            const oldToken = document.querySelector(`.player-token.p${player.id}`);
            if (oldToken) oldToken.remove();

            // Create token in new cell with bounce animation
            const container = nextCell.querySelector('.tokens');
            if (container) {
                const token = document.createElement('div');
                token.className = `player-token p${player.id}`;
                token.title = player.name;
                token.style.transition = 'transform 0.15s ease, box-shadow 0.15s ease';
                token.style.transform = 'scale(1.5) translateY(-6px)';
                token.style.boxShadow = '0 8px 20px rgba(0,0,0,0.7)';
                container.appendChild(token);

                // Bounce back to normal
                setTimeout(() => {
                    token.style.transform = '';
                    token.style.boxShadow = '';
                }, 130);
            }
        }

        // Notify if passed Start (position 0)
        if (currentPos === 0 && stepCount < stepsNeeded) {
            showToast(`<i class="fa-solid fa-star" style="color:#eab308"></i> ${player.name} melewati Start! +Rp 2.000`);
        }

        if (stepCount < stepsNeeded) {
            setTimeout(moveOneStep, stepDelay);
        } else {
            // Done animating — update local position & highlight
            player.position = toPos;
            animating[player.id] = false;
            updateCenterInfo();

            // Final "landed" flash
            const finalToken = document.querySelector(`.player-token.p${player.id}`);
            if (finalToken) {
                finalToken.style.transition = 'transform 0.2s ease, outline 0.2s ease';
                finalToken.style.transform = 'scale(1.8)';
                setTimeout(() => { finalToken.style.transform = ''; }, 300);
            }
        }
    }

    moveOneStep();
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
