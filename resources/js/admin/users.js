// State Management
const state = {
    isLoading: false,
    searchQuery: ''
};

// DOM Elements
const elements = {
    modal: null,
    form: null,
    submitBtn: null,
    submitBtnText: null,
    submitLoader: null,
    tableBody: null,
    searchInput: null,
    openFormBtn: null,
    closeModalBtn: null,
    cancelBtn: null
};

// Initialize
function init() {
    cacheElements();
    bindEvents();
}

function cacheElements() {
    elements.modal = document.getElementById('userModal');
    elements.form = document.getElementById('userForm');
    elements.submitBtn = document.getElementById('submitBtn');
    elements.submitBtnText = document.getElementById('submitBtnText');
    elements.submitLoader = document.getElementById('submitLoader');
    elements.tableBody = document.getElementById('userTableBody');
    elements.searchInput = document.getElementById('searchInput');
    elements.openFormBtn = document.getElementById('openFormBtn');
    elements.closeModalBtn = document.getElementById('closeModalBtn');
    elements.cancelBtn = document.getElementById('cancelBtn');
}

function bindEvents() {
    // فتح وإغلاق
    elements.openFormBtn?.addEventListener('click', openModal);
    elements.closeModalBtn?.addEventListener('click', closeModal);
    elements.cancelBtn?.addEventListener('click', closeModal);
    
    // إرسال الفورم
    elements.form?.addEventListener('submit', handleSubmit);
    
    // البحث
    elements.searchInput?.addEventListener('input', handleSearch);
    
    // إغلاق عند الضغط خارج المودال
    elements.modal?.addEventListener('click', (e) => {
        if (e.target === elements.modal) closeModal();
    });

    // إغلاق بـ Escape
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && elements.modal && !elements.modal.classList.contains('hidden')) {
            closeModal();
        }
    });
}

function openModal() {
    elements.modal.classList.remove('hidden');
    elements.modal.classList.add('flex');
    document.body.style.overflow = 'hidden';
}

function closeModal() {
    elements.modal.classList.add('hidden');
    elements.modal.classList.remove('flex');
    document.body.style.overflow = '';
    elements.form.reset();
    clearErrors();
}

async function handleSubmit(e) {
    e.preventDefault();
    
    if (state.isLoading) return;
    
    clearErrors();
    setLoading(true);

    const formData = new FormData(elements.form);
    
    // جلب التوكن من الميتا تاق أو من داخل الفورم
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') 
                  || formData.get('_token');

    try {
        const response = await fetch(elements.form.action || '/admin/users', {
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
            handleValidationErrors(data.errors);
            return;
        }

        if (!response.ok) {
            throw new Error(data.message || 'حدث خطأ في النظام');
        }

        // إذا نجح الرد
        showNotification('تم إضافة المشترك بنجاح', 'success');
        
        // تحديث الجدول أو عمل ريفريش
        // addUserToTable(data.user, data.plan_name); // استخدم هذه لإضافة سطر فوراً
        setTimeout(() => location.reload(), 1000); // أو ريفريش بعد ثانية
        
        closeModal();

    } catch (error) {
        console.error('Error:', error);
        showNotification(error.message, 'error');
    } finally {
        setLoading(false);
    }
}

function handleValidationErrors(errors) {
    Object.keys(errors).forEach(field => {
        const errorElement = document.querySelector(`[data-field="${field}"]`);
        const input = elements.form.querySelector(`[name="${field}"]`);
        
        if (errorElement) {
            errorElement.textContent = errors[field][0];
            errorElement.classList.remove('hidden');
        }
        
        if (input) {
            input.classList.add('border-red-500');
        }
    });
}

function clearErrors() {
    document.querySelectorAll('.error-msg').forEach(el => el.classList.add('hidden'));
    elements.form.querySelectorAll('input, select').forEach(input => {
        input.classList.remove('border-red-500');
    });
}

function setLoading(loading) {
    state.isLoading = loading;
    elements.submitBtn.disabled = loading;
    elements.submitBtnText.textContent = loading ? 'جاري الحفظ...' : 'حفظ المشترك';
    loading ? elements.submitLoader.classList.remove('hidden') : elements.submitLoader.classList.add('hidden');
}

function handleSearch(e) {
    const query = e.target.value.toLowerCase();
    const rows = elements.tableBody.querySelectorAll('tr[data-user-id]');
    rows.forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(query) ? '' : 'none';
    });
}

function showNotification(message, type = 'success') {
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-start', // يظهر من جهة اليمين (لأن الصفحة RTL)
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer)
            toast.addEventListener('mouseleave', Swal.resumeTimer)
        }
    });

    Toast.fire({
        icon: type,
        title: message
    });
}

// التشغيل
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
} else {
    init();
}