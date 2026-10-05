<?php include '../app/views/templates/admin_header.php'; ?>
<?php include '../app/views/templates/admin_sidebar.php'; ?>

<!-- === TAB: PASSWORD === -->
        <h2 class="text-2xl font-black text-white mb-6"><i class="fa-solid fa-key text-yellow-400 mr-2"></i>Ganti Password Admin</h2>
            <div class="max-w-md bg-slate-900/80 border border-white/10 rounded-2xl p-8">
                <form method="POST" action="<?= BASEURL ?>/admin/changePassword">
                    <div class="mb-5">
                        <label class="text-slate-400 text-sm font-bold uppercase tracking-widest block mb-2">Password Baru</label>
                        <input type="password" name="new_password" required minlength="6" placeholder="Min. 6 karakter"
                            class="w-full bg-slate-800 border border-white/10 rounded-xl px-4 py-3 text-white font-bold focus:outline-none focus:border-blue-500 transition">
                    </div>
                    <button type="submit" class="w-full py-3 bg-gradient-to-r from-yellow-500 to-orange-500 text-white font-black rounded-xl">
                        <i class="fa-solid fa-save mr-2"></i> Simpan Password
                    </button>
                </form>
            </div>

<?php include '../app/views/templates/admin_footer.php'; ?>
