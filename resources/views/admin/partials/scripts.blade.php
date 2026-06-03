@php
    $plansBreakdownTranslated = $plansBreakdown->map(fn($p) => [
        'name'  => __('plans.' . $p['name']),
        'count' => $p['count'],
    ]);
@endphp

<script>
const trans = {
    edit:        "{{ __('Edit') }}",
    newPlan:     "{{ __('New plan') }}",
    changePlan:  "{{ __('Change plan') }}",
    planUpdated: "{{ __('Plan updated') }}",
    setTo:       "{{ __('Set to') }}",
    delete:      "{{ __('Delete') }}",
    deleteText:  "{{ __('This will remove all their data permanently.') }}",
    yesDelete:   "{{ __('Yes, delete') }}",
    cancel:      "{{ __('Cancel') }}",
    saved:       "{{ __('Saved') }}",
};

function closeModal(id) {
    document.getElementById(id).classList.remove('open');
    document.body.style.overflow = '';
}
function openModal(id) {
    document.getElementById(id).classList.add('open');
    document.body.style.overflow = 'hidden';
}

document.querySelectorAll('.modal-backdrop').forEach(m => {
    m.addEventListener('click', e => { if (e.target === m) closeModal(m.id); });
});

document.getElementById('openAddUserBtn')?.addEventListener('click', () => openModal('addUserModal'));

function filterTable() {
    const q    = document.getElementById('searchInput').value.toLowerCase();
    const plan = document.getElementById('planFilter').value.toLowerCase();
    document.querySelectorAll('#userTableBody tr.user-row').forEach(row => {
        const matchQ    = !q    || row.dataset.name.includes(q) || row.dataset.email.includes(q);
        const matchPlan = !plan || (row.dataset.plan ?? '').toLowerCase() === plan;
        row.style.display = (matchQ && matchPlan) ? '' : 'none';
    });
}
document.getElementById('searchInput')?.addEventListener('input', filterTable);
document.getElementById('planFilter')?.addEventListener('change', filterTable);

function openChangePlan(userId, userName, currentPlanId) {
    document.getElementById('changePlanTitle').textContent = `${trans.changePlan} — ${userName}`;
    document.getElementById('changePlanUserId').value = userId;
    if (currentPlanId) document.getElementById('changePlanSelect').value = currentPlanId;
    openModal('changePlanModal');
}

function submitChangePlan() {
    const userId = document.getElementById('changePlanUserId').value;
    const planId = document.getElementById('changePlanSelect').value;
    const expiry = document.getElementById('changePlanExpiry').value;

    fetch(`/admin/users/${userId}/plan`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({ plan_id: planId, plan_expires_at: expiry || null }),
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) {
            closeModal('changePlanModal');
            Swal.fire({ icon: 'success', title: trans.planUpdated, text: `${trans.setTo} ${d.plan_name}`, timer: 1800, showConfirmButton: false });
            setTimeout(() => location.reload(), 1900);
        }
    });
}

function deleteUser(userId, userName) {
    Swal.fire({
        title: `${trans.delete} ${userName}?`,
        text: trans.deleteText,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        confirmButtonText: trans.yesDelete,
        cancelButtonText: trans.cancel,
    }).then(r => {
        if (!r.isConfirmed) return;
        fetch(`/admin/users/${userId}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        })
        .then(r => r.json())
        .then(d => {
            if (d.success) {
                document.querySelector(`tr[data-name]`)?.remove();
                location.reload();
            }
        });
    });
}

function openPlanModal() {
    document.getElementById('planModalTitle').textContent = trans.newPlan;
    document.getElementById('planModalId').value = '';
    document.getElementById('planModalName').readOnly = false;
    ['planModalName','planModalSlug','planModalPrice','planModalStripeId','planModalPosts','planModalPages']
        .forEach(id => document.getElementById(id).value = '');
    document.getElementById('planModalActive').checked = true;
    openModal('planModal');
}

function editPlan(id, name, slug, price, posts, pages, stripeId, active) {
    document.getElementById('planModalTitle').textContent = `${trans.edit} — ${name}`;
    document.getElementById('planModalId').value          = id;
    document.getElementById('planModalName').value        = name;
    document.getElementById('planModalName').readOnly     = true;
    document.getElementById('planModalSlug').value        = slug;
    document.getElementById('planModalSlug').readOnly     = true;
    document.getElementById('planModalPrice').value       = price;
    document.getElementById('planModalStripeId').value    = stripeId || '';
    document.getElementById('planModalPosts').value       = posts;
    document.getElementById('planModalPages').value       = pages;
    document.getElementById('planModalActive').checked    = active;
    openModal('planModal');
}

function submitPlan() {
    const id = document.getElementById('planModalId').value;
    const isEdit = !!id;
    const body = {
        ...(!isEdit && {
            name: document.getElementById('planModalName').value,
            slug: document.getElementById('planModalSlug').value,
        }),
        price:           document.getElementById('planModalPrice').value,
        stripe_price_id: document.getElementById('planModalStripeId').value,
        posts_limit:     document.getElementById('planModalPosts').value,
        pages_limit:     document.getElementById('planModalPages').value,
        active:          document.getElementById('planModalActive').checked,
    };

    const url = id ? `/admin/plans/${id}` : '/admin/plans';

    fetch(url, {
        method: id ? 'PATCH' : 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify(body),
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) {
            closeModal('planModal');
            Swal.fire({ icon: 'success', title: trans.saved, timer: 1500, showConfirmButton: false });
            setTimeout(() => location.reload(), 1600);
        }
    });
}

const revenueData = @json($revenueChart);
const planData    = @json($plansBreakdownTranslated);

new Chart(document.getElementById('revenueChart'), {
    type: 'bar',
    data: {
        labels: revenueData.map(d => d.label),
        datasets: [{
            label: 'Revenue ($)',
            data: revenueData.map(d => d.amount),
            backgroundColor: '#3b82f680',
            borderColor: '#2563eb',
            borderWidth: 1.5,
            borderRadius: 6,
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            y: { beginAtZero: true, grid: { color: '#f3f4f6' }, ticks: { font: { size: 11 } } },
            x: { grid: { display: false }, ticks: { font: { size: 11 } } },
        }
    }
});

new Chart(document.getElementById('planChart'), {
    type: 'doughnut',
    data: {
        labels: planData.map(d => d.name),
        datasets: [{
            data: planData.map(d => d.count),
            backgroundColor: ['#e0e7ff','#dbeafe','#d1fae5','#fef3c7'],
            borderColor:     ['#6366f1','#3b82f6','#10b981','#f59e0b'],
            borderWidth: 2,
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { position: 'bottom', labels: { font: { size: 11 }, padding: 12 } }
        }
    }
});
</script>