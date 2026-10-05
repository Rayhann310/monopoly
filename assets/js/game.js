// ============================================================
// game.js — Monopoly Indonesia Board Controller
// ============================================================

// Players & session injected from PHP (see home/index.php)
let players = [];
let sessionId = null;

if (typeof dbPlayers !== 'undefined' && dbPlayers.length > 0) {
    players = dbPlayers.map(p => ({
        id: parseInt(p.id),
        name: p.name,
        color: p.color,
        position: parseInt(p.position),
        money: parseInt(p.money),
    }));
}

if (typeof dbSessionId !== 'undefined') {
    sessionId = dbSessionId;
}

// ---- Token Placement ----
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
}

// ---- Center Info Update ----
function updateCenterInfo() {
    const currentTurnPlayer = players.find(p => p.is_turn);
    const el = document.getElementById('center-turn-info');
    if (el && currentTurnPlayer) {
        el.innerHTML = `<span style="color: var(--color-${currentTurnPlayer.color})">👑 Giliran ${currentTurnPlayer.name}</span>`;
    }
}

// ---- Bank Modal: Tombol +/- ----
function bankAdjust(playerId, playerName, direction) {
    Swal.fire({
        title: `${direction > 0 ? '💰 Tambah' : '💸 Kurangi'} Uang`,
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
            let posChanged = false;
            serverPlayers.forEach(sp => {
                const lp = players.find(p => p.id == sp.id);
                if (!lp) return;
                if (lp.position != parseInt(sp.position)) {
                    lp.position = parseInt(sp.position);
                    posChanged = true;
                }
                lp.money = parseInt(sp.money);
                lp.is_turn = parseInt(sp.is_turn);
            });
            if (posChanged) placeTokens();
            updateBankModal();
            updateCenterInfo();
        })
        .catch(() => {}); // silent fail
}, POLLING_MS);

// ---- Init ----
document.addEventListener('DOMContentLoaded', function () {
    placeTokens();
    updateBankModal();
    updateCenterInfo();
});
