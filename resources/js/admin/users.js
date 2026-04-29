// State Management
const state = {
    isLoading: false,
};

// DOM Elements
const elements = {
    // مودال المشترك (User)
    userModal: document.getElementById('userModal'),
    userForm: document.getElementById('userForm'),
    
    // مودال الصفحة (Page)
    pageModal: document.getElementById('pageModal'),
    pageForm: document.getElementById('pageForm'),

    // أزرار الفتح
    openUserBtn: document.getElementById('openFormBtn'),
    openPageBtns: [
        document.getElementById('openPageModalBtn'),
        document.getElementById('openPageModalBtnQuick')
    ],

    // عناصر الجدول والبحث
    tableBody: document.getElementById('userTableBody'),
    searchInput: document.getElementById('searchInput'),
};

function init() {
    bindEvents();
}

function bindEvents() {
    // --- أحداث مودال المشترك ---
    elements.openUserBtn?.addEventListener('click', () => openModal('user'));
    elements.userForm?.addEventListener('submit', (e) => handleAjaxSubmit(e, 'user'));
    document.getElementById('closeModalBtn')?.addEventListener('click', () => closeModal('user'));
    document.getElementById('cancelBtn')?.addEventListener('click', () => closeModal('user'));

    // --- أحداث مودال الصفحة ---
    elements.openPageBtns.forEach(btn => {
        btn?.addEventListener('click', () => openModal('page'));
    });
    elements.pageForm?.addEventListener('submit', (e) => handleAjaxSubmit(e, 'page'));
    document.getElementById('closePageModalBtn')?.addEventListener('click', () => closeModal('page'));
    document.getElementById('cancelPageBtn')?.addEventListener('click', () => closeModal('page'));

    // --- أحداث عامة ---
    elements.searchInput?.addEventListener('input', handleSearch);

    window.addEventListener('click', (e) => {
        if (e.target === elements.userModal) closeModal('user');
        if (e.target === elements.pageModal) closeModal('page');
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeModal('user');
            closeModal('page');
        }
    });
}

// دالة فتح المودال الموحدة
function openModal(type) {
    const modal = type === 'user' ? elements.userModal : elements.pageModal;
    if (modal) {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.style.overflow = 'hidden';
    }
}

// دالة إغلاق المودال الموحدة
function closeModal(type) {
    const modal = type === 'user' ? elements.userModal : elements.pageModal;
    const form = type === 'user' ? elements.userForm : elements.pageForm;
    if (modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.style.overflow = '';
        form?.reset();
        clearErrors(form);
    }
}

// دالة الإرسال الموحدة (Ajax)
async function handleAjaxSubmit(e, type) {
    e.preventDefault();
    if (state.isLoading) return;

    const form = type === 'user' ? elements.userForm : elements.pageForm;
    const submitBtn = form.querySelector('button[type="submit"]');
    const isPage = type === 'page';

    clearErrors(form);
    setLoading(true, submitBtn, isPage);

    const formData = new FormData(form);
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || formData.get('_token');

    try {
        const response = await fetch(form.action, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': token
            },
            body: formData
        });

        const data = await response.json();

        if (response.status === 422) {
            handleValidationErrors(data.errors, form);
            return;
        }

        if (!response.ok) throw new Error(data.message || 'حدث خطأ في النظام');

        showNotification(isPage ? 'تم إضافة الصفحة بنجاح' : 'تم إضافة المشترك بنجاح');
        setTimeout(() => location.reload(), 1000);
        closeModal(type);

    } catch (error) {
        showNotification(error.message, 'error');
    } finally {
        setLoading(false, submitBtn, isPage);
    }
}

function handleValidationErrors(errors, form) {
    Object.keys(errors).forEach(field => {
        const errorElement = form.querySelector(`[data-field="${field}"]`);
        const input = form.querySelector(`[name="${field}"]`);
        if (errorElement) {
            errorElement.textContent = errors[field][0];
            errorElement.classList.remove('hidden');
        }
        if (input) input.classList.add('border-red-500');
    });
}

function clearErrors(form) {
    if (!form) return;
    form.querySelectorAll('.error-msg').forEach(el => el.classList.add('hidden'));
    form.querySelectorAll('input, select').forEach(input => input.classList.remove('border-red-500'));
}

function setLoading(loading, btn, isPage) {
    state.isLoading = loading;
    if (!btn) return;
    btn.disabled = loading;
    const textSpan = btn.querySelector('span:not(.animate-spin)');
    const loader = btn.querySelector('.animate-spin');
    
    if (textSpan) textSpan.textContent = loading ? 'جاري الحفظ...' : (isPage ? 'حفظ الصفحة' : 'حفظ المشترك');
    loading ? loader?.classList.remove('hidden') : loader?.classList.add('hidden');
}

function handleSearch(e) {
    const query = e.target.value.toLowerCase();
    const rows = elements.tableBody?.querySelectorAll('tr[data-user-id]');
    rows?.forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(query) ? '' : 'none';
    });
}

function showNotification(message, type = 'success') {
    if (typeof Swal !== 'undefined') {
        Swal.fire({ toast: true, position: 'top-start', icon: type, title: message, showConfirmButton: false, timer: 3000 });
    } else {
        alert(message);
    }
}

init();



