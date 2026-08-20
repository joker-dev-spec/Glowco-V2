// --- assets/js/main.js ---

document.addEventListener('DOMContentLoaded', () => {

    // ── Mobile menu toggle ─────────────────────────────────────────
    const menuBtn = document.getElementById('mobileMenuBtn');
    const navEl   = document.querySelector('header nav');
    if (menuBtn && navEl) {
        menuBtn.addEventListener('click', () => {
            const open = navEl.classList.toggle('mobile-open');
            menuBtn.classList.toggle('open', open);
            menuBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
        });
        navEl.querySelectorAll('a').forEach(a => {
            a.addEventListener('click', () => {
                navEl.classList.remove('mobile-open');
                menuBtn.classList.remove('open');
                menuBtn.setAttribute('aria-expanded', 'false');
            });
        });
    }

    // ── Flash auto-dismiss ──────────────────────────────────────────
    const flashes = document.querySelectorAll('.flash');
    flashes.forEach(el => {
        setTimeout(() => {
            el.style.transition = 'opacity .4s ease, transform .4s ease';
            el.style.opacity = '0';
            el.style.transform = 'translateY(-8px)';
            setTimeout(() => el.remove(), 400);
        }, 4000);
    });

    // ── Cart quantity guard ─────────────────────────────────────────
    document.querySelectorAll('input[type="number"][name="quantity"]').forEach(input => {
        const max = parseInt(input.getAttribute('max'), 10);

        input.addEventListener('change', () => {
            let val = parseInt(input.value, 10);
            if (isNaN(val) || val < 1) val = 1;
            if (max && val > max) val = max;
            input.value = val;
        });
    });

    // ── Confirm delete ─────────────────────────────────────────────
    document.querySelectorAll('[data-confirm]').forEach(el => {
        el.addEventListener('click', e => {
            if (!confirm(el.dataset.confirm || 'Are you sure?')) {
                e.preventDefault();
            }
        });
    });

    // ── Active nav link ────────────────────────────────────────────
    const currentPath = window.location.pathname;
    document.querySelectorAll('header nav a.nav-link, .admin-nav a').forEach(link => {
        const href = link.getAttribute('href');
        if (!href) return;
        const page = href.split('/').pop();
        if (page && currentPath.endsWith(page)) {
            link.classList.add('active');
        }
    });

    // ── Image preview on product forms ────────────────────────────
    const fileInputs = document.querySelectorAll('input[type="file"][name="image"]');
    fileInputs.forEach(input => {
        input.addEventListener('change', () => {
            const file = input.files[0];
            if (!file || !file.type.startsWith('image/')) return;

            const existing = input.parentElement.querySelector('.img-preview');
            if (existing) existing.remove();

            const img = document.createElement('img');
            img.className = 'img-preview';
            img.style.cssText = 'width:100px;height:100px;object-fit:cover;border-radius:8px;border:1px solid var(--color-border);margin-top:.5rem;';
            img.src = URL.createObjectURL(file);
            input.parentElement.insertBefore(img, input.nextSibling);
        });
    });

    // ── Sort form auto-submit ──────────────────────────────────────
    const sortSelect = document.querySelector('.sort-form select');
    if (sortSelect) {
        sortSelect.addEventListener('change', () => sortSelect.closest('form').submit());
    }

    // ── Admin table row click (navigates to edit) ──────────────────
    document.querySelectorAll('.admin-table tr[data-href]').forEach(row => {
        row.style.cursor = 'pointer';
        row.addEventListener('click', e => {
            if (!e.target.closest('a') && !e.target.closest('button')) {
                window.location.href = row.dataset.href;
            }
        });
    });

    // ── Wishlist add feedback ──────────────────────────────────────
    document.querySelectorAll('form[action*="wishlist/add"]').forEach(form => {
        form.addEventListener('submit', () => {
            const btn = form.querySelector('button');
            if (btn) {
                btn.textContent = 'Added';
                btn.disabled = true;
                btn.style.opacity = '.6';
            }
        });
    });

    // ── Cart add (AJAX, no page reload) ────────────────────────────
    document.querySelectorAll('form[action*="cart/add"]').forEach(form => {
        form.addEventListener('submit', async e => {
            e.preventDefault();

            const btn = form.querySelector('button[type="submit"]');
            const originalHTML = btn ? btn.innerHTML : '';
            if (btn) { btn.disabled = true; btn.innerHTML = 'Adding...'; }

            try {
                const res  = await fetch(form.action, {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    body: new FormData(form)
                });
                const data = await res.json();

                const toast = document.getElementById('toast');
                if (toast) {
                    toast.textContent = data.success ? ('✓ ' + data.message) : data.message;
                    toast.classList.add('show');
                    setTimeout(() => toast.classList.remove('show'), 3500);
                }

                if (btn) {
                    if (data.success) {
                        btn.innerHTML = '✓ Added';
                        btn.style.background = 'var(--plum)';
                        btn.style.color = '#fff';
                        setTimeout(() => {
                            btn.innerHTML = originalHTML;
                            btn.style.background = '';
                            btn.style.color = '';
                            btn.disabled = false;
                        }, 1800);
                    } else {
                        btn.innerHTML = originalHTML;
                        btn.disabled = false;
                    }
                }
            } catch {
                // Network error — fall back to a normal submit so the user
                // still gets the item in their cart.
                form.submit();
            }
        });
    });

});