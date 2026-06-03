@php $sallaAccount = auth()->user()->sallaAccount; @endphp

<div id="sallaModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50 p-4">
    <div style="background:#fff;border-radius:20px;box-shadow:0 24px 64px rgba(0,0,0,.2);max-width:700px;width:100%;max-height:90vh;overflow-y:auto;">

        {{-- Header --}}
        <div style="display:flex;align-items:center;justify-content:space-between;padding:20px 24px;border-bottom:1px solid #f3f4f6;position:sticky;top:0;background:#fff;z-index:1;">
            <div style="display:flex;align-items:center;gap:10px;">
                <span style="font-size:24px;">🛍️</span>
                <h3 style="font-family:'Syne',sans-serif;font-weight:800;color:#0f1117;margin:0;">متجر سلة</h3>
                @if($sallaAccount)
                    <span style="background:#d1fae5;color:#065f46;font-size:11px;font-weight:700;padding:3px 10px;border-radius:99px;">متصل</span>
                @endif
            </div>
            <button onclick="closeSallaModal()" style="color:#9ca3af;border:none;background:none;cursor:pointer;font-size:20px;">✕</button>
        </div>

        <div style="padding:24px;">

            @if(!$sallaAccount)
            {{-- غير متصل --}}
            <div style="text-align:center;padding:40px 20px;">
                <div style="font-size:64px;margin-bottom:16px;">🔗</div>
                <h4 style="font-size:18px;font-weight:800;color:#0f1117;margin:0 0 8px;">اربط متجر سلة</h4>
                <p style="color:#6b7280;font-size:14px;margin:0 0 24px;line-height:1.6;">
                    اربط متجرك على سلة لتتمكن من نشر منتجاتك تلقائياً على Facebook
                </p>
                <a href="{{ route('salla.redirect') }}"
                   style="display:inline-block;padding:12px 32px;background:linear-gradient(135deg,#6d28d9,#4f46e5);color:#fff;border-radius:12px;font-weight:700;font-size:15px;text-decoration:none;">
                    🚀 ربط متجر سلة
                </a>
            </div>

            @else
            {{-- متصل --}}
            <div style="display:flex;align-items:center;justify-content:space-between;background:#f9fafb;border-radius:12px;padding:14px 18px;margin-bottom:20px;flex-wrap:wrap;gap:10px;">
                <div style="display:flex;align-items:center;gap:12px;">
                    <div style="width:44px;height:44px;border-radius:50%;background:linear-gradient(135deg,#6d28d9,#4f46e5);display:flex;align-items:center;justify-content:center;color:#fff;font-size:20px;">🏪</div>
                    <div>
                        <p style="font-weight:700;color:#111827;margin:0;font-size:15px;">{{ $sallaAccount->store_name }}</p>
                        <p style="color:#6b7280;font-size:12px;margin:0;">{{ $sallaAccount->store_email }}</p>
                    </div>
                </div>
                <div style="display:flex;gap:8px;align-items:center;">
                    <button onclick="toggleSallaAutoPost()"
                            id="autoPostBtn"
                            style="padding:7px 14px;border-radius:8px;border:1.5px solid {{ $sallaAccount->auto_post_enabled ? '#10b981' : '#e5e7eb' }};background:{{ $sallaAccount->auto_post_enabled ? '#d1fae5' : '#fff' }};color:{{ $sallaAccount->auto_post_enabled ? '#065f46' : '#6b7280' }};font-size:12px;font-weight:700;cursor:pointer;">
                        {{ $sallaAccount->auto_post_enabled ? '✅ نشر تلقائي' : '🔕 نشر تلقائي' }}
                    </button>
                    <button onclick="syncSallaProducts()"
                            style="padding:7px 14px;border-radius:8px;border:1.5px solid #e5e7eb;background:#fff;color:#374151;font-size:12px;font-weight:700;cursor:pointer;">
                        🔄 مزامنة
                    </button>
                    <form method="POST" action="{{ route('salla.disconnect') }}" onsubmit="return confirm('فصل المتجر؟')">
                        @csrf @method('DELETE')
                        <button type="submit" style="padding:7px 14px;border-radius:8px;border:1.5px solid #fca5a5;background:#fff;color:#dc2626;font-size:12px;font-weight:700;cursor:pointer;">
                            فصل
                        </button>
                    </form>
                </div>
            </div>

            {{-- بحث --}}
            <div style="margin-bottom:16px;">
                <input type="text" id="sallaSearch" placeholder="🔍 ابحث عن منتج..."
                       oninput="searchSallaProducts(this.value)"
                       style="width:100%;padding:10px 14px;border:1.5px solid #e5e7eb;border-radius:10px;font-size:14px;outline:none;box-sizing:border-box;">
            </div>

            {{-- منتجات --}}
            <div id="sallaProductsGrid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:14px;">
                <div style="text-align:center;padding:40px;color:#9ca3af;grid-column:1/-1;">
                    <div style="font-size:32px;margin-bottom:8px;">⏳</div>
                    <p style="margin:0;font-size:13px;">جاري التحميل...</p>
                </div>
            </div>

            @endif
        </div>
    </div>
</div>

<script>
function openSallaModal() {
    const m = document.getElementById('sallaModal');
    m.classList.remove('hidden');
    m.style.display = 'flex';
    @if($sallaAccount) loadSallaProducts(); @endif
}
function closeSallaModal() {
    const m = document.getElementById('sallaModal');
    m.classList.add('hidden');
    m.style.display = 'none';
}

function loadSallaProducts(search = '') {
    fetch(`/salla/products?search=${encodeURIComponent(search)}`)
        .then(r => r.json())
        .then(data => renderSallaProducts(data.products));
}

let sallaSearchTimer;
function searchSallaProducts(val) {
    clearTimeout(sallaSearchTimer);
    sallaSearchTimer = setTimeout(() => loadSallaProducts(val), 400);
}

function renderSallaProducts(products) {
    const grid = document.getElementById('sallaProductsGrid');
    if (!products.length) {
        grid.innerHTML = '<div style="text-align:center;padding:40px;color:#9ca3af;grid-column:1/-1;"><div style="font-size:32px;">📦</div><p>لا توجد منتجات</p></div>';
        return;
    }
    grid.innerHTML = products.map(p => `
        <div style="border:1.5px solid #f3f4f6;border-radius:14px;overflow:hidden;cursor:pointer;transition:all .2s;" onmouseover="this.style.borderColor='#6d28d9'" onmouseout="this.style.borderColor='#f3f4f6'">
            <div style="position:relative;">
                <img src="${p.image_url || 'https://picsum.photos/seed/'+p.id+'/400/400'}" style="width:100%;height:140px;object-fit:cover;">
                ${p.sale_price ? `<span style="position:absolute;top:8px;right:8px;background:#ef4444;color:#fff;font-size:10px;font-weight:700;padding:3px 8px;border-radius:99px;">${Math.round((p.price-p.sale_price)/p.price*100)}% OFF</span>` : ''}
            </div>
            <div style="padding:10px;">
                <p style="font-size:12px;font-weight:700;color:#111827;margin:0 0 6px;line-height:1.4;">${p.name}</p>
                <div style="display:flex;align-items:center;gap:6px;">
                    <span style="font-size:13px;font-weight:800;color:#6d28d9;">${p.sale_price || p.price} ${p.currency}</span>
                    ${p.sale_price ? `<span style="font-size:11px;color:#9ca3af;text-decoration:line-through;">${p.price}</span>` : ''}
                </div>
            </div>
        </div>
    `).join('');
}

function syncSallaProducts() {
    fetch('/salla/sync', {method:'POST', headers:{'X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content}})
        .then(r => r.json())
        .then(d => { showToast('✅ تمت المزامنة: ' + d.synced + ' منتج'); loadSallaProducts(); });
}

function toggleSallaAutoPost() {
    fetch('/salla/toggle-auto-post', {method:'POST', headers:{'X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content}})
        .then(r => r.json())
        .then(d => { showToast(d.message); setTimeout(() => location.reload(), 1000); });
}
</script>