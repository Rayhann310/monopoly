    <script>
        // Inject database players to JS
        const dbPlayers = <?= json_encode($data['players']); ?>;
    </script>
    <script src="<?= BASEURL; ?>/assets/js/game.js"></script>
</body>
</html>
