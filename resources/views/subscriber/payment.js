
let currentPlanId = null;

// ─── Open ─────────────────────────────────────────────────────────────────────
window.openPayModal = function (planName, planPrice, planId) {
    currentPlanId = planId;

    const price = parseFloat(planPrice).toFixed(2);

    document.getElementById('pay-plan-name').textContent    = planName + ' Plan';
    document.getElementById('pay-plan-price-big').textContent = '$' + price;
    document.getElementById('pay-summary-label').textContent  = planName + ' Plan — monthly';
    document.getElementById('pay-summary-price').textContent  = '$' + price;
    document.getElementById('pay-subtotal').textContent       = '$' + price;
    document.getElementById('pay-total').textContent          = '$' + price;
    document.getElementById('pay-btn-price').textContent      = '$' + price;

    closeUpgradeModal();

    document.getElementById('pay-error').style.display      = 'none';
    document.getElementById('pay-card-number').value        = '';
    document.getElementById('pay-expiry').value             = '';
    document.getElementById('pay-cvc').value                = '';
    document.getElementById('pay-name').value               = '';

    document.getElementById('payModal').style.display       = 'flex';
    document.body.style.overflow                            = 'hidden';
};

// ─── Close ────────────────────────────────────────────────────────────────────
window.closePayModal = function () {
    document.getElementById('payModal').style.display = 'none';
    document.body.style.overflow = '';
};

document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('payModal')?.addEventListener('click', function (e) {
        if (e.target === this) closePayModal();
    });
});

// ─── Card formatting ──────────────────────────────────────────────────────────
window.formatCardNumber = function (input) {
    const val    = input.value.replace(/\D/g, '').substring(0, 16);
    input.value  = val.replace(/(.{4})/g, '$1 ').trim();
};

window.formatExpiry = function (input) {
    let val     = input.value.replace(/\D/g, '').substring(0, 4);
    if (val.length >= 2) val = val.substring(0, 2) + ' / ' + val.substring(2);
    input.value = val;
};

// ─── Submit ───────────────────────────────────────────────────────────────────
window.submitPayment = async function () {
    const cardNumber = document.getElementById('pay-card-number').value.replace(/\s/g, '');
    const expiry     = document.getElementById('pay-expiry').value;
    const cvc        = document.getElementById('pay-cvc').value;
    const name       = document.getElementById('pay-name').value.trim();
    const email      = document.getElementById('pay-email').value.trim();
    const errEl      = document.getElementById('pay-error');

    if (!email || !cardNumber || !expiry || !cvc || !name) {
        errEl.textContent    = 'Please fill in all fields.';
        errEl.style.display  = 'block';
        return;
    }
    if (cardNumber.length < 16) {
        errEl.textContent    = 'Please enter a valid card number.';
        errEl.style.display  = 'block';
        return;
    }
    if (cvc.length < 3) {
        errEl.textContent    = 'Please enter a valid CVC.';
        errEl.style.display  = 'block';
        return;
    }

    errEl.style.display = 'none';

    const btn     = document.getElementById('pay-submit-btn');
    const btnText = document.getElementById('pay-btn-text');
    const spinner = document.getElementById('pay-spinner');
    btn.disabled          = true;
    btnText.style.display = 'none';
    spinner.style.display = 'inline-block';

    try {
        const url = window.AppConfig.routes.fakeCheckout.replace('__ID__', currentPlanId);
        const res  = await fetch(url, {
            method:      'POST',
            headers:     {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                'Accept':       'application/json',
            },
            credentials: 'same-origin',
        });

        const data = await res.json();

        if (data.success) {
            closePayModal();
            showToast('Plan activated! Refreshing...');
            setTimeout(() => window.location.reload(), 1500);
        } else {
            throw new Error(data.message ?? 'Failed');
        }

    } catch (e) {
        btn.disabled          = false;
        btnText.style.display = 'inline';
        spinner.style.display = 'none';
        errEl.textContent     = e.message || 'Something went wrong.';
        errEl.style.display   = 'block';
    }
};