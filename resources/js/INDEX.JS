
                // Modal open/close
                const modal      = document.getElementById('userModal');
                const openBtn    = document.getElementById('openFormBtn');
                const closeBtn   = document.getElementById('closeModalBtn');
                const cancelBtn  = document.getElementById('cancelBtn');

                function openModal()  { modal.classList.add('open');    document.body.style.overflow = 'hidden'; }
                function closeModal() { modal.classList.remove('open'); document.body.style.overflow = ''; }

                openBtn?.addEventListener('click', openModal);
                closeBtn?.addEventListener('click', closeModal);
                cancelBtn?.addEventListener('click', closeModal);
                modal?.addEventListener('click', e => { if (e.target === modal) closeModal(); });

                // Search / filter
                document.getElementById('searchInput')?.addEventListener('input', function () {
                    const q = this.value.toLowerCase();
                    document.querySelectorAll('#userTableBody tr.user-row').forEach(row => {
                        row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
                    });
                });

                // Submit loader
                document.getElementById('userForm')?.addEventListener('submit', function () {
                    const btn    = document.getElementById('submitBtn');
                    const loader = document.getElementById('submitLoader');
                    const text   = document.getElementById('submitBtnText');
                    btn.disabled       = true;
                    loader.style.display = 'block';
                    text.textContent   = 'Saving...';
                });
        