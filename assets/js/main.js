// --- assets/js/main.js ---

document.addEventListener('DOMContentLoaded', () => {

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
    document.querySelectorAll('.navbar__links a, .admin-nav a').forEach(link => {
        if (link.getAttribute('href') && currentPath.includes(link.getAttribute('href').split('/').pop())) {
            link.style.color = 'var(--color-primary)';
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

    // ── Cart add button feedback ───────────────────────────────────
    document.querySelectorAll('form[action*="cart/add"]').forEach(form => {
        form.addEventListener('submit', () => {
            const btn = form.querySelector('button[type="submit"]');
            if (btn) {
                const original = btn.textContent;
                btn.textContent = '✓ Added';
                btn.style.background = 'var(--color-success)';
                setTimeout(() => {
                    btn.textContent = original;
                    btn.style.background = '';
                }, 1500);
            }
        });
    });

});