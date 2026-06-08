@php $sallaStore = auth()->user()->sallaStore; @endphp

<div id="sallaModal"
     style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,.55);backdrop-filter:blur(4px);align-items:center;justify-content:center;padding:16px;">
 {{ __('Connected')}}
    <div style="background:#fff;border-radius:20px;width:100%;max-width:780px;max-height:90vh;overflow:hidden;display:flex;flex-direction:column;box-shadow:0 24px 64px rgba(0,0,0,.18);">

        {{-- Header --}}
        <div style="padding:20px 24px;border-bottom:1.5px solid #f3f4f6;display:flex;align-items:center;justify-content:space-between;flex-shrink:0;">
            <div style="display:flex;align-items:center;gap:12px;">
                <div style="width:40px;height:40px;background:linear-gradient(135deg,#7c3aed,#2563eb);border-radius:12px;display:flex;align-items:center;justify-content:center;">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                </div>
                <div>
                    <h3 style="font-size:16px;font-weight:800;color:#111827;margin:0;">{{ __('Salla') }}</h3>
                    <p style="font-size:12px;color:#6b7280;margin:0;">
                        @if($sallaStore)
                          {{ __('Connected')}}: <strong style="color:#059669;">{{ $sallaStore->store_name }}</strong>
                        @else
                            {{ __('Connect your store to auto-generate posts') }}
                        @endif
                    </p>
                </div>
            </div>
            <button onclick="closeSallaModal()" style="width:32px;height:32px;border-radius:8px;border:1.5px solid #e5e7eb;background:#fff;cursor:pointer;display:flex;align-items:center;justify-content:center;color:#6b7280;">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        {{-- Body --}}
        <div style="flex:1;overflow-y:auto;padding:24px;">

            @if(! $sallaStore)


            <div style="text-align:center;padding:40px 20px;">
                <div style="width:80px;height:80px;background:linear-gradient(135deg,#f5f3ff,#ede9fe);border-radius:24px;display:flex;align-items:center;justify-content:center;margin:0 auto 20px;">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#7c3aed" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                </div>
                <h4 style="font-size:20px;font-weight:800;color:#111827;margin:0 0 8px;">{{ __('Connect Your Salla Store') }}</h4>
                <p style="font-size:14px;color:#6b7280;margin:0 0 8px;max-width:420px;margin-inline:auto;">
                    {{ __('Link your Salla store, pick any product, and let AI write a ready-to-post Facebook caption — with the product image and price — in seconds.') }}
                </p>

                <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;margin:24px 0;text-align:left;">
                    @foreach([
   ['🛍️', __('Product Picker'), __('Browse your Salla catalog and choose any product')],
    ['🤖', __('AI Copywriting'), __('Get 3 caption variations in Arabic or English')],
    ['📅', __('Auto-Schedule'),  __('Schedule directly to your Facebook pages')],
                    ] as [$icon, $title, $desc])
                    <div style="background:#f9fafb;border:1.5px solid #f0f0f0;border-radius:14px;padding:16px;">
                        <div style="font-size:24px;margin-bottom:8px;">{{ $icon }}</div>
                        <div style="font-size:13px;font-weight:700;color:#111827;margin-bottom:4px;">{{ $title }}</div>
                        <div style="font-size:11px;color:#6b7280;line-height:1.5;">{{ $desc }}</div>
                    </div>
                    @endforeach
                </div>

                <a href="{{ route('salla.redirect') }}"
                   style="display:inline-flex;align-items:center;gap:10px;padding:14px 32px;background:linear-gradient(135deg,#7c3aed,#2563eb);color:#fff;border-radius:12px;font-weight:800;font-size:15px;text-decoration:none;box-shadow:0 4px 16px rgba(124,58,237,.35);">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                    {{ __('Connect with Salla') }}
                </a>
                <p style="font-size:11px;color:#9ca3af;margin-top:12px;">{{ __("You'll be redirected to Salla to authorize the connection") }}</p>
            </div>

            @else

            {{-- ===== STEP 1 : PRODUCT GRID ===== --}}
            <div id="sallaStep1" style="">

                <div style="display:flex;gap:10px;margin-bottom:16px;">
                    <input type="text" id="sallaSearch" placeholder="🔍  {{ __('Search products') }}..."
                           style="flex:1;padding:10px 14px;border:1.5px solid #e5e7eb;border-radius:10px;font-size:13px;outline:none;"
                           oninput="debounceSallaSearch(this.value)"
                           onfocus="this.style.borderColor='#7c3aed'" onblur="this.style.borderColor='#e5e7eb'">
                    <form method="POST" action="{{ route('salla.disconnect') }}" onsubmit="return confirm('Disconnect Salla store?')">
                        @csrf
                        <button type="submit"
                                style="padding:10px 16px;border:1.5px solid #fecaca;background:#fff5f5;color:#dc2626;border-radius:10px;font-size:12px;font-weight:700;cursor:pointer;">
                            {{ __('Disconnect') }}
                        </button>
                    </form>
                </div>

                <div id="sallaProductsGrid"
                     style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:12px;min-height:200px;">
                    <div style="grid-column:1/-1;text-align:center;padding:40px;color:#9ca3af;font-size:13px;" id="sallaLoadingMsg">
                        <div style="width:32px;height:32px;border:3px solid #e5e7eb;border-top-color:#7c3aed;border-radius:50%;animation:spin 1s linear infinite;margin:0 auto 12px;"></div>
                        {{ __('Loading products') }}...
                    </div>
                </div>
            </div>

            {{-- ===== STEP 2 : CAPTION BUILDER ===== --}}
            <div id="sallaStep2" style="display:none;">

                <button onclick="backToProducts()" style="display:flex;align-items:center;gap:6px;font-size:12px;font-weight:700;color:#6b7280;background:none;border:none;cursor:pointer;margin-bottom:16px;">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                    {{ __('Back to products') }}
                </button>

                {{-- Product preview --}}
                <div id="sallaProductPreview" style="background:#f9fafb;border:1.5px solid #f0f0f0;border-radius:14px;padding:16px;display:flex;gap:14px;margin-bottom:20px;">
                    <img id="sallaProdImg" src="" alt="" style="width:72px;height:72px;border-radius:10px;object-fit:cover;border:1px solid #e5e7eb;flex-shrink:0;" onerror="this.style.display='none'">
                    <div style="flex:1;min-width:0;">
                        <div id="sallaProdName"  style="font-size:14px;font-weight:800;color:#111827;margin-bottom:4px;"></div>
                        <div id="sallaProdPrice" style="font-size:13px;font-weight:700;color:#059669;"></div>
                        <div id="sallaProdDesc"  style="font-size:11px;color:#6b7280;margin-top:4px;line-height:1.4;max-height:36px;overflow:hidden;"></div>
                    </div>
                </div>

                {{-- Settings row --}}
                <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;margin-bottom:16px;">
                    <div>
                        <label style="font-size:11px;font-weight:700;color:#374151;display:block;margin-bottom:6px;">{{ __('Language') }}</label>
                        <select id="sallaLang" style="width:100%;padding:8px 10px;border:1.5px solid #e5e7eb;border-radius:8px;font-size:12px;font-weight:600;">
                            <option value="ar">{{ __('🇸🇦 Arabic') }}</option>
                            <option value="en">{{ __('🇬🇧 English') }}</option>
                        </select>
                    </div>
                    <div>
                        <label style="font-size:11px;font-weight:700;color:#374151;display:block;margin-bottom:6px;">{{ __('Tone') }}</label>
                        <select id="sallaTone" style="width:100%;padding:8px 10px;border:1.5px solid #e5e7eb;border-radius:8px;font-size:12px;font-weight:600;">
                            <option value="exciting">🔥 {{ __('Exciting') }}</option>
                            <option value="friendly">😊 {{ __('Friendly') }}</option>
                            <option value="formal">💼 {{ __('Formal') }}</option>
                            <option value="humorous">😄 {{ __('Humorous') }}</option>
                        </select>
                    </div>
                    <div>
                        <label style="font-size:11px;font-weight:700;color:#374151;display:block;margin-bottom:6px;">{{ __('Goal') }}</label>
                        <select id="sallaGoal" style="width:100%;padding:8px 10px;border:1.5px solid #e5e7eb;border-radius:8px;font-size:12px;font-weight:600;">
                            <option value="sales">💰 {{ __('Drive Sales') }}</option>
                            <option value="awareness">📢 {{ __('Awareness') }}</option>
                            <option value="engagement">💬 {{ __('Engagement') }}</option>
                        </select>
                    </div>
                </div>

                <button onclick="generateSallaCaption()"
                        id="sallaGenBtn"
                        style="width:100%;padding:12px;background:linear-gradient(135deg,#7c3aed,#2563eb);color:#fff;border:none;border-radius:12px;font-weight:800;font-size:14px;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px;margin-bottom:16px;">
                    <svg width="16" height="16" fill="none" stroke="white" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    {{ __('Generate AI Caption') }}
                </button>

                {{-- Caption + schedule area --}}
                <div id="sallaCaptionArea" style="display:none;">
                    <div style="margin-bottom:12px;">
                        <label style="font-size:11px;font-weight:700;color:#374151;display:block;margin-bottom:6px;">
                            {{ __('Generated Caption') }} <span style="color:#9ca3af;font-weight:500;">({{ __('you can edit') }})</span>
                        </label>
                        <textarea id="sallaCaptionText"
                                  rows="7"
                                  style="width:100%;padding:12px 14px;border:1.5px solid #e5e7eb;border-radius:10px;font-size:13px;line-height:1.6;resize:vertical;outline:none;font-family:inherit;box-sizing:border-box;"
                                  onfocus="this.style.borderColor='#7c3aed'" onblur="this.style.borderColor='#e5e7eb'"></textarea>
                    </div>

                    <div style="background:#f5f3ff;border:1.5px solid #ede9fe;border-radius:12px;padding:16px;">
                        <div style="font-size:12px;font-weight:800;color:#5b21b6;margin-bottom:12px;">📅 {{ __('Schedule This Post') }}</div>
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                            <div>
                                <label style="font-size:11px;font-weight:700;color:#374151;display:block;margin-bottom:6px;">{{ __('Facebook Page') }}</label>
                                <select id="sallaPageSelect" style="width:100%;padding:8px 10px;border:1.5px solid #ddd6fe;border-radius:8px;font-size:12px;">
                                    @foreach(auth()->user()->facebookPages as $page)
                                        <option value="{{ $page->id }}">{{ $page->page_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label style="font-size:11px;font-weight:700;color:#374151;display:block;margin-bottom:6px;">{{ __('Schedule Time') }}</label>
                                <input type="datetime-local" id="sallaScheduledAt"
                                       style="width:100%;padding:8px 10px;border:1.5px solid #ddd6fe;border-radius:8px;font-size:12px;box-sizing:border-box;">
                            </div>
                        </div>
                        <button onclick="scheduleSallaPost()"
                                id="sallaScheduleBtn"
                                style="width:100%;margin-top:12px;padding:11px;background:#5b21b6;color:#fff;border:none;border-radius:10px;font-weight:800;font-size:13px;cursor:pointer;">
                            ✅ {{ __('Schedule Post') }}
                        </button>
                    </div>
                </div>

            </div>
            @endif

        </div>
    </div>
</div>

{{-- ===== STYLES ===== --}}
<style>
@keyframes spin { to { transform: rotate(360deg); } }

.salla-product-card {
    border: 1.5px solid #f0f0f0;
    border-radius: 14px;
    overflow: hidden;
    cursor: pointer;
    transition: all .18s;
    background: #fff;
}
.salla-product-card:hover {
    border-color: #7c3aed;
    box-shadow: 0 4px 16px rgba(124,58,237,.15);
    transform: translateY(-2px);
}
.salla-product-card.selected {
    border-color: #7c3aed;
    background: #f5f3ff;
}
</style>

{{-- ===== SCRIPT ===== --}}
<script>

let sallaSelectedProduct = null;
let sallaSearchTimer     = null;

const SALLA_CSRF         = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
const SALLA_PRODUCTS_URL = "{{ route('salla.products') }}";
const SALLA_GENERATE_URL = "{{ route('salla.generate-caption') }}";
const SALLA_SCHEDULE_URL = "{{ route('salla.schedule-post') }}";

/* ─── Auto-detect page language ─────────────────────────── */
function detectPageLang() {
    const htmlLang = document.documentElement.lang || '';
    const htmlDir  = document.documentElement.dir  || '';
    const isArabic = htmlLang.startsWith('ar') || htmlDir === 'rtl';

    const langSelect = document.getElementById('sallaLang');
    if (langSelect) {
        langSelect.value = isArabic ? 'ar' : 'en';
    }
}

/* ─── Modal open / close ─────────────────────────────────── */
function openSallaModal() {
    const modal = document.getElementById('sallaModal');
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';

    detectPageLang(); // ← set language based on page locale

    @if($sallaStore)
    loadSallaProducts();
    @endif
}

function closeSallaModal() {
    document.getElementById('sallaModal').style.display = 'none';
    document.body.style.overflow = '';
}

document.getElementById('sallaModal').addEventListener('click', function(e) {
    if (e.target === this) closeSallaModal();
});

/* ─── Load products ──────────────────────────────────────── */
async function loadSallaProducts(search = '') {
    const grid = document.getElementById('sallaProductsGrid');
    grid.innerHTML = `
        <div style="grid-column:1/-1;text-align:center;padding:40px;color:#9ca3af;font-size:13px;">
            <div style="width:32px;height:32px;border:3px solid #e5e7eb;border-top-color:#7c3aed;border-radius:50%;animation:spin 1s linear infinite;margin:0 auto 12px;"></div>
            {{ __('Loading products') }}...
        </div>`;

    try {
        const url = SALLA_PRODUCTS_URL + (search ? `?search=${encodeURIComponent(search)}` : '');
        const res  = await fetch(url, {
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': SALLA_CSRF },
            credentials: 'same-origin'
        });
        const data = await res.json();

        if (data.error) {
            grid.innerHTML = `<div style="grid-column:1/-1;text-align:center;padding:40px;color:#dc2626;font-size:13px;">⚠️ ${data.error}</div>`;
            return;
        }

        if (!data.products || data.products.length === 0) {
            grid.innerHTML = `<div style="grid-column:1/-1;text-align:center;padding:40px;color:#9ca3af;font-size:13px;">{{ __('No products found.') }}</div>`;
            return;
        }

        grid.innerHTML = data.products.map(p => `
            <div class="salla-product-card" onclick="selectSallaProduct(${JSON.stringify(p).replace(/"/g,'&quot;')})" data-id="${p.id}">
                <div style="height:130px;background:#f9fafb;overflow:hidden;">
                    ${p.image
                        ? `<img src="${p.image}" alt="${p.name}" style="width:100%;height:100%;object-fit:cover;"
                               onerror="this.parentElement.innerHTML='<div style=\\'height:100%;display:flex;align-items:center;justify-content:center;font-size:32px;\\'>🛍️</div>'">`
                        : `<div style="height:100%;display:flex;align-items:center;justify-content:center;font-size:32px;">🛍️</div>`
                    }
                </div>
                <div style="padding:10px 12px;">
                    <div style="font-size:12px;font-weight:700;color:#111827;line-height:1.4;margin-bottom:6px;min-height:32px;">${p.name}</div>
                    <div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap;">
                        ${p.sale_price && p.sale_price !== p.price
                            ? `<span style="font-size:13px;font-weight:800;color:#059669;">${p.sale_price} ${p.currency}</span>
                               <span style="font-size:11px;color:#9ca3af;text-decoration:line-through;">${p.price} ${p.currency}</span>`
                            : p.price
                                ? `<span style="font-size:13px;font-weight:800;color:#059669;">${p.price} ${p.currency}</span>`
                                : ''
                        }
                    </div>
                </div>
            </div>
        `).join('');

    } catch(e) {
        grid.innerHTML = `<div style="grid-column:1/-1;text-align:center;padding:40px;color:#dc2626;font-size:13px;">⚠️ {{ __('Failed to load products.') }}</div>`;
    }
}

function debounceSallaSearch(val) {
    clearTimeout(sallaSearchTimer);
    sallaSearchTimer = setTimeout(() => loadSallaProducts(val), 450);
}

/* ─── Select product → go to step 2 ─────────────────────── */
function selectSallaProduct(product) {
    sallaSelectedProduct = product;

    document.getElementById('sallaStep1').style.display = 'none';
    document.getElementById('sallaStep2').style.display = 'block';

    document.getElementById('sallaProdName').textContent  = product.name;
    document.getElementById('sallaProdDesc').textContent  = product.description || '';
    document.getElementById('sallaProdImg').src           = product.image || '';

    let priceHtml = '';
    if (product.sale_price && product.sale_price !== product.price) {
        priceHtml = `<span style="text-decoration:line-through;color:#9ca3af;font-weight:500;margin-left:6px;">${product.price} ${product.currency}</span>
                     <span style="color:#dc2626;font-weight:800;">${product.sale_price} ${product.currency}</span>`;
    } else if (product.price) {
        priceHtml = `${product.price} ${product.currency}`;
    }
    document.getElementById('sallaProdPrice').innerHTML = priceHtml;

    document.getElementById('sallaCaptionArea').style.display = 'none';
    document.getElementById('sallaCaptionText').value = '';

    // Set default schedule to tomorrow at 9 am
    const tomorrow = new Date();
    tomorrow.setDate(tomorrow.getDate() + 1);
    tomorrow.setHours(9, 0, 0, 0);
    document.getElementById('sallaScheduledAt').value = tomorrow.toISOString().slice(0, 16);
}

function backToProducts() {
    document.getElementById('sallaStep2').style.display = 'none';
    document.getElementById('sallaStep1').style.display = '';
    sallaSelectedProduct = null;
}

/* ─── Generate caption ───────────────────────────────────── */
async function generateSallaCaption() {
    if (!sallaSelectedProduct) return;

    const btn = document.getElementById('sallaGenBtn');
    btn.disabled = true;
    btn.innerHTML = '<div style="width:16px;height:16px;border:2px solid rgba(255,255,255,.4);border-top-color:#fff;border-radius:50%;animation:spin 1s linear infinite;display:inline-block;vertical-align:middle;margin-right:6px;"></div> {{ __("Generating...") }}';

    const p = sallaSelectedProduct;

    try {
        const res = await fetch(SALLA_GENERATE_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': SALLA_CSRF,
                'Accept': 'application/json'
            },
            credentials: 'same-origin',
            body: JSON.stringify({
                product_name: p.name,
                description:  p.description,
                price:        p.price,
                sale_price:   p.sale_price,
                currency:     p.currency,
                image_url:    p.image,
                product_url:  p.url,
                tone:         document.getElementById('sallaTone').value,
                language:     document.getElementById('sallaLang').value,
                goal:         document.getElementById('sallaGoal').value,
            }),
        });

        const data = await res.json();

        if (!res.ok || data.error) {
            alert('⚠️ ' + (data.error ?? '{{ __("Something went wrong.") }}'));
            return;
        }

        const captions = data.captions ?? (data.caption ? [data.caption] : []);
        if (!captions.length) {
            alert('⚠️ {{ __("No captions returned. Please try again.") }}');
            return;
        }

        showSallaCaptionPicker(captions);

    } catch(e) {
        alert('{{ __("Failed to generate caption. Please try again.") }}');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<svg width="16" height="16" fill="none" stroke="white" stroke-width="2" viewBox="0 0 24 24" style="vertical-align:middle;margin-right:6px;"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg> {{ __("Generate AI Caption") }}';
    }
}

/* ─── Caption picker ─────────────────────────────────────── */
function showSallaCaptionPicker(captions) {
    document.getElementById('sallaCaptionPicker')?.remove();

    const textarea = document.getElementById('sallaCaptionText');

    const picker = document.createElement('div');
    picker.id = 'sallaCaptionPicker';
    picker.style.cssText = 'background:#f8faff;border:1.5px solid #bfdbfe;border-radius:14px;padding:14px;margin-bottom:12px;display:flex;flex-direction:column;gap:8px;';

    const title = document.createElement('p');
    title.style.cssText = 'font-size:11px;font-weight:700;color:#4f46e5;margin:0 0 4px;text-transform:uppercase;letter-spacing:.06em;';
    title.textContent = '✨ {{ __("Choose a caption:") }}';
    picker.appendChild(title);

    captions.forEach((caption, i) => {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.style.cssText = 'text-align:left;padding:10px 14px;border-radius:10px;border:1.5px solid #e0e7ff;background:#fff;font-size:13px;color:#374151;cursor:pointer;line-height:1.5;width:100%;';
        btn.innerHTML = `<span style="font-size:10px;font-weight:800;color:#7c3aed;margin-right:6px;">{{ __("Option") }} ${i+1}</span>${caption}`;
        btn.onmouseover = () => btn.style.background = '#f5f3ff';
        btn.onmouseout  = () => btn.style.background = '#fff';
        btn.onclick = () => {
            textarea.value = '';
            let j = 0;
            const timer = setInterval(() => {
                if (j < caption.length) {
                    textarea.value += caption[j++];
                } else {
                    clearInterval(timer);
                }
            }, 12);
            picker.remove();
            document.getElementById('sallaCaptionArea').style.display = 'block';
            document.getElementById('sallaCaptionArea').scrollIntoView({ behavior: 'smooth', block: 'start' });
        };
        picker.appendChild(btn);
    });

    const captionArea = document.getElementById('sallaCaptionArea');
    captionArea.parentElement.insertBefore(picker, captionArea);
    picker.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

/* ─── Schedule post ──────────────────────────────────────── */
async function scheduleSallaPost() {
    const caption     = document.getElementById('sallaCaptionText').value.trim();
    const pageId      = document.getElementById('sallaPageSelect').value;
    const scheduledAt = document.getElementById('sallaScheduledAt').value;
    const btn         = document.getElementById('sallaScheduleBtn');

    if (!caption || !pageId || !scheduledAt) {
        alert('{{ __("Please fill all fields before scheduling.") }}');
        return;
    }

    btn.disabled = true;
    btn.textContent = '{{ __("Scheduling...") }}';

    try {
        const res = await fetch(SALLA_SCHEDULE_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': SALLA_CSRF,
                'Accept': 'application/json'
            },
            credentials: 'same-origin',
            body: JSON.stringify({
                facebook_page_id: pageId,
                content:          caption,
                scheduled_at:     scheduledAt,
                image_url:        sallaSelectedProduct?.image,
                product_url:      sallaSelectedProduct?.url,
            }),
        });

        const data = await res.json();

        if (data.success) {
            if (typeof calendar !== 'undefined' && data.event) {
                calendar.addEvent(data.event);
            }
            closeSallaModal();

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'success',
                    title: '{{ __("Scheduled!") }}',
                    text: '{{ __("Your Salla product post is scheduled.") }}',
                    timer: 3000,
                    showConfirmButton: false
                });
            } else {
                alert('✅ {{ __("Post scheduled successfully!") }}');
            }
        } else {
            alert('⚠️ ' + (data.error || '{{ __("Something went wrong.") }}'));
        }

    } catch(e) {
        alert('{{ __("Failed to schedule post. Please try again.") }}');
    } finally {
        btn.disabled = false;
        btn.textContent = '✅ {{ __("Schedule Post") }}';
    }
}
</script>