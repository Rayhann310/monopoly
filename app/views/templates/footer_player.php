    <script>
        // Simple logic to animate mobile dice
        const rollBtn = document.getElementById('mobile-roll-btn');
        const die1 = document.getElementById('mobile-die1');
        const die2 = document.getElementById('mobile-die2');

        rollBtn.addEventListener('click', () => {
            // Animate
            die1.classList.add('shake');
            die2.classList.add('shake');
            rollBtn.disabled = true;
            rollBtn.classList.add('opacity-50');

            setTimeout(() => {
                const d1 = Math.floor(Math.random() * 6) + 1;
                const d2 = Math.floor(Math.random() * 6) + 1;
                
                die1.classList.remove('shake');
                die2.classList.remove('shake');
                
                const faces = ['one','two','three','four','five','six'];
                
                // Keep the color classes but change the icon
                die1.className = `fa-solid fa-dice-${faces[d1-1]} dice-3d text-<?= $data['player']['color'] ?>-500`;
                die2.className = `fa-solid fa-dice-${faces[d2-1]} dice-3d text-<?= $data['player']['color'] ?>-500`;
                
                rollBtn.disabled = false;
                rollBtn.classList.remove('opacity-50');
                
                // In a real app, send AJAX to backend here
                // fetch(BASEURL + '/api/roll/' + playerId + '/' + d1 + '/' + d2)
                
                alert(`Kamu melempar angka ${d1 + d2}! (Lihat di layar utama)`);
            }, 1000);
        });
    </script>
</body>
</html>
