<script>
const Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true,
    background: '#1e293b',
    color: '#f8fafc',
    iconColor: '#3b82f6',
});

document.addEventListener('DOMContentLoaded', () => {
    // Intercept form submissions
    document.body.addEventListener('submit', async (e) => {
        const form = e.target;
        if (form.tagName.toLowerCase() === 'form' && !form.classList.contains('no-ajax')) {
            e.preventDefault();
            const btn = form.querySelector('button[type="submit"]');
            if(btn) { btn.disabled = true; btn.dataset.original = btn.innerHTML; btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Menyimpan...'; }

            try {
                const formData = new FormData(form);
                formData.append('ajax', '1');
                
                const response = await fetch(form.action || window.location.href, {
                    method: form.method || 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                });

                const result = await response.json();
                
                if (result.status === 'success') {
                    Toast.fire({ icon: 'success', title: result.message });
                    
                    // Close modal if exists
                    const modals = document.querySelectorAll('.fixed.inset-0:not(.hidden)');
                    modals.forEach(m => { m.classList.add('hidden'); m.classList.remove('flex'); });
                    
                    // Refresh main content elegantly without reloading page
                    const html = await fetch(window.location.href).then(r => r.text());
                    const doc = new DOMParser().parseFromString(html, 'text/html');
                    const containers = ['.max-w-6xl', '.max-w-5xl', '.max-w-4xl', '.max-w-3xl', '.max-w-2xl', '.max-w-xl', '.max-w-md', '.grid'];
                    
                    for (let selector of containers) {
                        const current = document.querySelector(selector);
                        const fetched = doc.querySelector(selector);
                        if (current && fetched) {
                            current.innerHTML = fetched.innerHTML;
                            break;
                        }
                    }
                    
                    if (form.id === 'add-form' || form.id === 'card-form') form.reset();
                } else {
                    Toast.fire({ icon: 'error', title: result.message || 'Terjadi kesalahan' });
                }
            } catch (err) {
                console.error(err);
                Toast.fire({ icon: 'error', title: 'Gagal menghubungi server' });
            } finally {
                if(btn) { btn.disabled = false; btn.innerHTML = btn.dataset.original; }
            }
        }
    });

    // Intercept delete/action links
    document.body.addEventListener('click', async (e) => {
        const link = e.target.closest('a[onclick*="confirm"]');
        if (link) {
            e.preventDefault();
            const msg = link.getAttribute('onclick').match(/confirm\('([^']+)'\)/);
            const text = msg ? msg[1] : 'Lanjutkan aksi ini?';
            
            const conf = await Swal.fire({
                title: 'Konfirmasi',
                text: text,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, Lanjutkan',
                cancelButtonText: 'Batal',
                background: '#1e293b',
                color: '#f8fafc',
                customClass: { confirmButton: 'bg-blue-600 px-4 py-2 rounded font-bold mr-2', cancelButton: 'bg-slate-600 px-4 py-2 rounded font-bold' },
                buttonsStyling: false
            });

            if (conf.isConfirmed) {
                try {
                    const url = link.href + (link.href.includes('?') ? '&' : '?') + 'ajax=1';
                    const response = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } });
                    const result = await response.json();
                    if (result.status === 'success') {
                        Toast.fire({ icon: 'success', title: result.message });
                        
                        // Refresh main content gracefully
                        const html = await fetch(window.location.href).then(r => r.text());
                        const doc = new DOMParser().parseFromString(html, 'text/html');
                        const containers = ['.max-w-6xl', '.max-w-5xl', '.max-w-4xl', '.max-w-3xl', '.max-w-2xl', '.max-w-xl', '.max-w-md', '.grid'];
                        
                        for (let selector of containers) {
                            const current = document.querySelector(selector);
                            const fetched = doc.querySelector(selector);
                            if (current && fetched) {
                                current.innerHTML = fetched.innerHTML;
                                break;
                            }
                        }
                    } else {
                        Toast.fire({ icon: 'error', title: result.message || 'Gagal mengeksekusi aksi' });
                    }
                } catch(err) {
                    Toast.fire({ icon: 'error', title: 'Gagal menghubungi server' });
                }
            }
        }
    });
});
</script>
    </main>
</div>
</body>
</html>
