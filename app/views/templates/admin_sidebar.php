    <!-- Sidebar -->
    <aside class="w-64 bg-slate-950 border-r border-white/5 flex flex-col fixed h-full z-30">
        <div class="p-6 border-b border-white/5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-gradient-to-br from-red-500 to-rose-700 rounded-xl flex items-center justify-center">
                    <i class="fa-solid fa-shield-halved text-white"></i>
                </div>
                <div>
                    <div class="text-white font-black text-sm">Admin Panel</div>
                    <div class="text-slate-500 text-xs">Monopoly Indonesia</div>
                </div>
            </div>
        </div>

        <nav class="flex-1 p-4 space-y-1">
            <?php 
                $curr = $data['judul'] ?? ''; 
                $menu = [
                    'Admin Dashboard' => ['icon'=>'fa-chart-pie', 'url'=>'/admin/dashboard'],
                    'Pengaturan Game' => ['icon'=>'fa-gear', 'url'=>'/admin/settings'],
                    'Sesi Aktif'      => ['icon'=>'fa-gamepad', 'url'=>'/admin/sessions'],
                    'Database'        => ['icon'=>'fa-database', 'url'=>'/admin/database'],
                    'Kelola Kartu'    => ['icon'=>'fa-layer-group', 'url'=>'/admin/cards'],
                    'Ganti Password'  => ['icon'=>'fa-key', 'url'=>'/admin/password']
                ];
                foreach ($menu as $title => $m):
                    $act = ($curr === $title) ? 'active-link' : 'border-transparent text-slate-400 hover:text-white';
            ?>
            <a href="<?= BASEURL ?><?= $m['url'] ?>" class="w-full text-left px-4 py-3 rounded-xl border transition flex items-center gap-3 font-bold <?= $act ?>">
                <i class="fa-solid <?= $m['icon'] ?> w-5"></i> <?= $title ?>
            </a>
            <?php endforeach; ?>
        </nav>

        <div class="p-4 border-t border-white/5 space-y-2">
            <a href="<?= BASEURL ?>/setup" class="block px-4 py-3 rounded-xl text-slate-500 hover:text-white transition flex items-center gap-3 text-sm">
                <i class="fa-solid fa-house w-5"></i> Kembali ke Lobby
            </a>
            <a href="<?= BASEURL ?>/admin/logout" class="block px-4 py-3 rounded-xl text-red-500/70 hover:text-red-400 transition flex items-center gap-3 text-sm font-bold">
                <i class="fa-solid fa-right-from-bracket w-5"></i> Logout
            </a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="ml-64 flex-1 p-8">
        <!-- Header -->
        <div class="flex items-center justify-between mb-10">
            <div>
                <h1 class="text-3xl font-black text-white"><?= htmlspecialchars($data['judul'] ?? 'Dashboard') ?></h1>
                <p class="text-slate-500">Halo, <span class="text-blue-400 font-bold"><?= htmlspecialchars($data['admin'] ?? '') ?></span></p>
            </div>
            <div class="text-slate-600 text-sm"><?= date('l, d F Y — H:i') ?></div>
        </div>
