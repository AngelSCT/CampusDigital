import './bootstrap';

// ─── Amount selector for recharge ───────────────────────────────
document.addEventListener('DOMContentLoaded', () => {

    // Amount quick-select buttons
    const amountBtns = document.querySelectorAll('[data-amount]');
    const amountInput = document.getElementById('amount-input');

    amountBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            amountBtns.forEach(b => b.classList.remove('selected'));
            btn.classList.add('selected');
            if (amountInput) {
                amountInput.value = btn.dataset.amount;
            }
        });
    });

    // Custom amount clears quick-select
    if (amountInput) {
        amountInput.addEventListener('input', () => {
            amountBtns.forEach(b => b.classList.remove('selected'));
        });
    }

    // ─── Flash messages auto-hide ───────────────────────────────
    const flashMessages = document.querySelectorAll('[data-flash]');
    flashMessages.forEach(msg => {
        setTimeout(() => {
            msg.style.transition = 'opacity 0.4s ease';
            msg.style.opacity = '0';
            setTimeout(() => msg.remove(), 400);
        }, 4000);
    });

    // ─── Sidebar active state ───────────────────────────────────
    const currentPath = window.location.pathname;
    const navLinks = document.querySelectorAll('[data-nav]');
    navLinks.forEach(link => {
        if (link.dataset.nav && currentPath.includes(link.dataset.nav)) {
            link.classList.add('active');
        }
    });

    // ─── Copy card/reference number ─────────────────────────────
    const copyBtns = document.querySelectorAll('[data-copy]');
    copyBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            navigator.clipboard.writeText(btn.dataset.copy).then(() => {
                const original = btn.innerHTML;
                btn.innerHTML = `<svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>`;
                setTimeout(() => { btn.innerHTML = original; }, 1500);
            });
        });
    });
});
