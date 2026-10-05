let players = [];
if (typeof dbPlayers !== 'undefined' && dbPlayers.length > 0) {
    players = dbPlayers.map(p => ({
        id: parseInt(p.id),
        name: p.name,
        color: p.color,
        position: parseInt(p.position),
        money: parseInt(p.money),
        properties: []
    }));
} else {
    players = [
        { id: 1, name: 'Pemain 1', color: 'red', position: 0, money: 1500, properties: [] },
        { id: 2, name: 'Pemain 2', color: 'blue', position: 0, money: 1500, properties: [] },
        { id: 3, name: 'Pemain 3', color: 'green', position: 0, money: 1500, properties: [] },
        { id: 4, name: 'Pemain 4', color: 'yellow', position: 0, money: 1500, properties: [] }
    ];
}

let currentPlayerIndex = 0;
let isRolling = false;

function initGame() {
    updatePlayersUI();
    placeTokens();
    logAction('Data pemain berhasil dimuat dari Database.');
    const msg = document.getElementById('game-message');
    if (msg) {
        msg.innerHTML = `Giliran <span class="text-${players[currentPlayerIndex].color}-400 font-bold">${players[currentPlayerIndex].name}</span>`;
    }
}

function updatePlayersUI() {
    const list = document.getElementById('players-list');
    if (!list) return;
    list.innerHTML = '';
    
    players.forEach((p, idx) => {
        const isActive = idx === currentPlayerIndex;
        const activeStyles = isActive 
            ? 'border-white/50 bg-white/10 shadow-[0_0_20px_rgba(255,255,255,0.1)] scale-105 z-10' 
            : 'border-white/5 bg-transparent opacity-60 hover:opacity-100';
            
        const colorGradients = {
            'red': 'from-red-500 to-rose-600',
            'blue': 'from-blue-500 to-indigo-600',
            'green': 'from-emerald-400 to-green-600',
            'yellow': 'from-amber-400 to-orange-500'
        };
        const grad = colorGradients[p.color];
        
        list.innerHTML += `
            <div class="p-3 lg:p-4 rounded-xl border transition-all duration-300 ${activeStyles} backdrop-blur-sm">
                <div class="flex justify-between items-center">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-gradient-to-br ${grad} flex items-center justify-center shadow-lg border border-white/20">
                            <i class="fa-solid fa-user text-white text-sm"></i> 
                        </div>
                        <span class="font-bold text-white tracking-wide">${p.name}</span>
                    </div>
                    <div class="text-lg bg-black/30 px-3 py-1.5 rounded-lg border border-white/10 shadow-inner font-mono text-emerald-400 font-bold">
                        $${p.money}
                    </div>
                </div>
            </div>
        `;
    });
}

function placeTokens() {
    document.querySelectorAll('.player-token').forEach(t => t.remove());
    
    players.forEach(p => {
        const cell = document.getElementById(`cell-${p.position}`);
        if (cell) {
            const tokenContainer = cell.querySelector('.tokens');
            const token = document.createElement('div');
            token.className = `player-token p${p.id}`;
            tokenContainer.appendChild(token);
        }
    });
}

function logAction(msg) {
    const log = document.getElementById('log-container');
    if (!log) return;
    const entry = document.createElement('div');
    entry.className = 'border-l-2 border-slate-600 pl-3 opacity-0 translate-x-4 transition-all duration-300 animate-[fadeIn_0.4s_forwards]';
    entry.innerHTML = `<span class="text-slate-500 mr-2">›</span> ${msg}`;
    log.appendChild(entry);
    log.scrollTop = log.scrollHeight;
}

function rollDice() {
    if (isRolling) return;
    isRolling = true;
    
    const btn = document.getElementById('roll-btn');
    btn.disabled = true;
    btn.classList.add('opacity-50', 'cursor-not-allowed');
    
    let rollCount = 0;
    const interval = setInterval(() => {
        const d1 = Math.floor(Math.random() * 6) + 1;
        const d2 = Math.floor(Math.random() * 6) + 1;
        
        const die1 = document.getElementById('die1');
        const die2 = document.getElementById('die2');
        
        die1.className = `fa-solid fa-dice-${['one','two','three','four','five','six'][d1-1]} dice-icon scale-110 -rotate-12`;
        die2.className = `fa-solid fa-dice-${['one','two','three','four','five','six'][d2-1]} dice-icon scale-110 rotate-12`;
        
        rollCount++;
        if (rollCount >= 12) {
            clearInterval(interval);
            die1.classList.remove('scale-110', '-rotate-12');
            die2.classList.remove('scale-110', 'rotate-12');
            handleRollComplete(d1, d2);
        }
    }, 80);
}

function handleRollComplete(d1, d2) {
    const total = d1 + d2;
    const player = players[currentPlayerIndex];
    
    logAction(`<span class="font-bold text-${player.color}-400">${player.name}</span> melempar <span class="font-bold text-white bg-white/10 px-2 py-0.5 rounded">${total}</span>`);
    
    const msg = document.getElementById('game-message');
    if (msg) {
        msg.innerHTML = `<span class="text-${player.color}-400">${player.name}</span> maju ${total} langkah!`;
    }
    
    player.position = (player.position + total) % 40;
    
    if (player.position < total) {
        player.money += 200;
        logAction(`<span class="font-bold text-${player.color}-400">${player.name}</span> melewati <span class="font-bold text-amber-400">START</span> dan mendapat <span class="font-bold text-emerald-400">+$200</span>!`);
    }
    
    placeTokens();
    updatePlayersUI();
    
    setTimeout(() => {
        currentPlayerIndex = (currentPlayerIndex + 1) % players.length;
        updatePlayersUI();
        
        const btn = document.getElementById('roll-btn');
        if (btn) {
            btn.disabled = false;
            btn.classList.remove('opacity-50', 'cursor-not-allowed');
        }
        isRolling = false;
        
        const msg = document.getElementById('game-message');
        if (msg) {
            msg.innerHTML = `Giliran <span class="text-${players[currentPlayerIndex].color}-400 font-bold">${players[currentPlayerIndex].name}</span>`;
        }
    }, 1500);
}

const rollBtn = document.getElementById('roll-btn');
if (rollBtn) {
    rollBtn.addEventListener('click', rollDice);
}

const style = document.createElement('style');
style.textContent = `
    @keyframes fadeIn {
        to { opacity: 1; transform: translateX(0); }
    }
`;
document.head.appendChild(style);

initGame();
// Realtime Polling
setInterval(() => {
    fetch('/monopoly/home/apiStatus')
        .then(res => res.json())
        .then(data => {
            let changed = false;
            data.forEach(serverPlayer => {
                const localPlayer = players.find(p => p.id == serverPlayer.id);
                if (localPlayer) {
                    if (localPlayer.position != serverPlayer.position) {
                        localPlayer.position = parseInt(serverPlayer.position);
                        changed = true;
                    }
                    if (localPlayer.money != serverPlayer.money) {
                        localPlayer.money = parseInt(serverPlayer.money);
                        // Future: update money on Bank Modal or Sidebar if added back
                    }
                }
            });
            
            if (changed) {
                placeTokens();
            }
        })
        .catch(err => console.error("Sync error:", err));
}, 1000);
