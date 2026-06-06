@php $sallaAccount = auth()->user()->sallaAccount; @endphp


<div id="sallaModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50 p-4">
    <div style="background:#fff;border-radius:20px;box-shadow:0 24px 64px rgba(0,0,0,.2);max-width:720px;width:100%;max-height:90vh;overflow-y:auto;">

        {{-- Header --}}
        <div style="display:flex;align-items:center;justify-content:space-between;padding:18px 24px;border-bottom:1px solid #f3f4f6;position:sticky;top:0;background:#fff;z-index:10;">
            <div style="display:flex;align-items:center;gap:10px;">
                <span style="font-size:22px;">🛍️</span>
                <h3 style="font-family:'Syne',sans-serif;font-weight:800;color:#0f1117;margin:0;font-size:16px;">متجر سلة</h3>
                @if($sallaAccount)
                    <span style="background:#d1fae5;color:#065f46;font-size:11px;font-weight:700;padding:3px 10px;border-radius:99px;">متصل ✓</span>
                @endif
            </div>
            <button onclick="closeSallaModal()" style="color:#9ca3af;border:none;background:none;cursor:pointer;font-size:20px;line-height:1;">✕</button>
        </div>

        <div style="padding:24px;">

        @if(!$sallaAccount)
        {{-- ── غير متصل ──────────────────────────────────────────────── --}}
        <div style="text-align:center;padding:40px 20px;">
            <div style="font-size:64px;margin-bottom:16px;">🔗</div>
            <h4 style="font-size:18px;font-weight:800;color:#0f1117;margin:0 0 8px;">اربط متجر سلة</h4>
            <p style="color:#6b7280;font-size:14px;margin:0 0 24px;line-height:1.7;">
                اربط متجرك على سلة لتتمكن من نشر منتجاتك تلقائياً على Facebook.<br>
                سيتم توجيهك لتسجيل الدخول على سلة مباشرة.
            </p>
            <a href="{{ route('salla.redirect') }}"
               style="display:inline-block;padding:13px 36px;background:linear-gradient(135deg,#6d28d9,#4f46e5);color:#fff;border-radius:12px;font-weight:700;font-size:15px;text-decoration:none;">
                🚀 ربط متجر سلة عبر OAuth
            </a>
        </div>

        @else
        {{-- ── متصل ──────────────────────────────────────────────────── --}}

        {{-- Store Info Bar --}}
        <div style="display:flex;align-items:center;justify-content:space-between;background:#f9fafb;border-radius:12px;padding:12px 16px;margin-bottom:18px;flex-wrap:wrap;gap:8px;">
            <div style="display:flex;align-items:center;gap:12px;">
                <div style="width:40px;height:40px;border-radius:50%;background:linear-gradient(135deg,#6d28d9,#4f46e5);display:flex;align-items:center;justify-content:center;color:#fff;font-size:18px;">🏪</div>
                <div>
                    <p style="font-weight:700;color:#111827;margin:0;font-size:14px;">{{ $sallaAccount->store_name }}</p>
                    <p style="color:#6b7280;font-size:11px;margin:0;">{{ $sallaAccount->store_email }}</p>
                </div>
            </div>
            <div style="display:flex;gap:6px;align-items:center;flex-wrap:wrap;">
                <button onclick="toggleSallaAutoPost()" id="autoPostBtn"
                        style="padding:6px 12px;border-radius:8px;border:1.5px solid {{ $sallaAccount->auto_post_enabled ? '#10b981' : '#e5e7eb' }};background:{{ $sallaAccount->auto_post_enabled ? '#d1fae5' : '#fff' }};color:{{ $sallaAccount->auto_post_enabled ? '#065f46' : '#6b7280' }};font-size:11px;font-weight:700;cursor:pointer;">
                    {{ $sallaAccount->auto_post_enabled ? '✅ نشر تلقائي' : '🔕 نشر تلقائي' }}
                </button>
                <button onclick="syncSallaProducts()"
                        style="padding:6px 12px;border-radius:8px;border:1.5px solid #e5e7eb;background:#fff;color:#374151;font-size:11px;font-weight:700;cursor:pointer;">
                    🔄 مزامنة
                </button>
                <form method="POST" action="{{ route('salla.disconnect') }}" onsubmit="return confirm('فصل المتجر؟')" style="margin:0;">
                    @csrf @method('DELETE')
                    <button type="submit" style="padding:6px 12px;border-radius:8px;border:1.5px solid #fca5a5;background:#fff;color:#dc2626;font-size:11px;font-weight:700;cursor:pointer;">فصل</button>
                </form>
            </div>
        </div>

        {{-- Search --}}
        <div style="margin-bottom:14px;">
            <input type="text" id="sallaSearch" placeholder="🔍 ابحث عن منتج..."
                   oninput="searchSallaProducts(this.value)"
                   style="width:100%;padding:9px 14px;border:1.5px solid #e5e7eb;border-radius:10px;font-size:13px;outline:none;box-sizing:border-box;">
        </div>

        {{-- Products Grid --}}
        <div id="sallaProductsGrid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:12px;">
            <div style="text-align:center;padding:40px;color:#9ca3af;grid-column:1/-1;">
                <div style="font-size:28px;margin-bottom:8px;">⏳</div>
                <p style="margin:0;font-size:12px;">جاري التحميل...</p>
            </div>
        </div>

        {{-- ═══ CAPTION PANEL (hidden until product selected) ═══ --}}
        <div id="sallaCaptionPanel" style="display:none;margin-top:22px;border-top:1.5px solid #f3f4f6;padding-top:22px;">

            {{-- Selected Product Preview --}}
            <div id="sallaSelectedProduct" style="display:flex;gap:12px;align-items:center;background:#f9fafb;border-radius:12px;padding:12px 16px;margin-bottom:16px;">
                <img id="selProductImg" src="" style="width:56px;height:56px;border-radius:10px;object-fit:cover;">
                <div>
                    <p id="selProductName" style="font-weight:700;color:#111827;margin:0 0 2px;font-size:13px;"></p>
                    <p id="selProductPrice" style="color:#6d28d9;font-weight:700;font-size:13px;margin:0;"></p>
                </div>
            </div>

            {{-- Tone & Language controls --}}
            <div style="display:flex;gap:10px;margin-bottom:14px;flex-wrap:wrap;">
                <select id="sallaTone" style="flex:1;min-width:120px;padding:8px 12px;border:1.5px solid #e5e7eb;border-radius:8px;font-size:13px;background:#fff;">
                    <option value="promotional">🔥 ترويجي</option>
                    <option value="friendly">😊 ودّي</option>
                    <option value="urgent">⚡ عاجل</option>
                    <option value="elegant">✨ راقي</option>
                </select>
                <select id="sallaLang" style="flex:1;min-width:120px;padding:8px 12px;border:1.5px solid #e5e7eb;border-radius:8px;font-size:13px;background:#fff;">
                    <option value="ar">🇸🇦 عربي</option>
                    <option value="en">🇬🇧 English</option>
                </select>
                <button onclick="generateSallaCaptions()"
                        style="flex:1;min-width:140px;padding:8px 16px;background:linear-gradient(135deg,#6d28d9,#4f46e5);color:#fff;border:none;border-radius:8px;font-size:13px;font-weight:700;cursor:pointer;">
                    🤖 توليد 3 كابشنات
                </button>
            </div>

            {{-- AI Captions --}}
            <div id="sallaCaptionsArea" style="display:none;">
                <p style="font-size:12px;font-weight:700;color:#374151;margin:0 0 8px;">اختر كابشن:</p>
                <div id="sallaCaptionsList" style="display:flex;flex-direction:column;gap:8px;"></div>

                {{-- Hashtags --}}
                <div id="sallaHashtags" style="margin-top:10px;display:flex;flex-wrap:wrap;gap:6px;"></div>

                {{-- Page + Schedule --}}
                <div style="margin-top:16px;display:flex;flex-direction:column;gap:10px;">
                    <select id="sallaPageSelect" style="padding:9px 12px;border:1.5px solid #e5e7eb;border-radius:8px;font-size:13px;background:#fff;">
                        <option value="">— اختر صفحة Facebook —</option>
                        @foreach(auth()->user()->facebookPages as $page)
                            <option value="{{ $page->id }}">{{ $page->name }}</option>
                        @endforeach
                    </select>

                    <div style="display:flex;gap:8px;flex-wrap:wrap;">
                        <input type="datetime-local" id="sallaScheduleAt"
                               style="flex:1;padding:9px 12px;border:1.5px solid #e5e7eb;border-radius:8px;font-size:13px;"
                               min="{{ now()->addMinutes(5)->format('Y-m-d\TH:i') }}">
                        <button onclick="scheduleSallaPost()"
                                style="padding:9px 18px;background:#10b981;color:#fff;border:none;border-radius:8px;font-weight:700;font-size:13px;cursor:pointer;">
                            📅 جدولة
                        </button>
                        <button onclick="publishSallaPostNow()"
                                style="padding:9px 18px;background:#4f46e5;color:#fff;border:none;border-radius:8px;font-weight:700;font-size:13px;cursor:pointer;">
                            🚀 نشر الآن
                        </button>
                    </div>
                </div>
            </div>

            {{-- Loading Spinner --}}
            <div id="sallaCaptionLoading" style="display:none;text-align:center;padding:24px;color:#6b7280;">
                <div style="font-size:28px;animation:spin 1s linear infinite;display:inline-block;">⏳</div>
                <p style="margin:8px 0 0;font-size:13px;">جاري توليد الكابشنات بالـ AI...</p>
            </div>
        </div>

        @endif
        </div>
    </div>
</div>

<style>
@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
.salla-caption-card { border: 1.5px solid #e5e7eb; border-radius: 10px; padding: 10px 14px; cursor: pointer; font-size: 13px; line-height: 1.6; color: #374151; transition: all .15s; }
.salla-caption-card:hover, .salla-caption-card.selected { border-color: #6d28d9; background: #f5f3ff; }
</style>

<script>
(function () {
    // ── State ──────────────────────────────────────────────────────────────────
    window._sallaSelectedProductId = null;
    window._sallaSelectedCaption   = null;

    // ── Modal open/close ───────────────────────────────────────────────────────
    window.openSallaModal = function () {
        var m = document.getElementById('sallaModal');
        m.classList.remove('hidden');
        m.style.display = 'flex';
        @if($sallaAccount) loadSallaProducts(); @endif
    };

    window.closeSallaModal = function () {
        var m = document.getElementById('sallaModal');
        m.classList.add('hidden');
        m.style.display = 'none';
    };

    // ── Load / Search Products ─────────────────────────────────────────────────
    function loadSallaProducts(search) {
        search = search || '';
        fetch('/salla/products?search=' + encodeURIComponent(search))
            .then(function (r) { return r.json(); })
            .then(function (data) { renderSallaProducts(data.products); })
            .catch(function () { showToast('❌ تعذّر تحميل المنتجات'); });
    }

    var sallaSearchTimer;
    window.searchSallaProducts = function (val) {
        clearTimeout(sallaSearchTimer);
        sallaSearchTimer = setTimeout(function () { loadSallaProducts(val); }, 400);
    };

    // ── FIX: استخدام data-attributes بدل inline string parameters ─────────────
    function renderSallaProducts(products) {
        var grid = document.getElementById('sallaProductsGrid');
        if (!products || !products.length) {
            grid.innerHTML = '<div style="text-align:center;padding:40px;color:#9ca3af;grid-column:1/-1;"><div style="font-size:28px;">📦</div><p style="margin:4px 0 0;">لا توجد منتجات</p></div>';
            return;
        }

        grid.innerHTML = products.map(function (p) {
            var saleTag = '';
            if (p.sale_price) {
                var pct = Math.round((p.price - p.sale_price) / p.price * 100);
                saleTag = '<span style="position:absolute;top:6px;right:6px;background:#ef4444;color:#fff;font-size:9px;font-weight:700;padding:2px 7px;border-radius:99px;">' + pct + '% OFF</span>';
            }
            var oldPrice = p.sale_price
                ? '<span style="font-size:10px;color:#9ca3af;text-decoration:line-through;margin-right:4px;">' + p.price + '</span>'
                : '';
            var imgSrc  = escHtmlAttr(p.image_url || ('https://picsum.photos/seed/' + p.id + '/300/300'));
            var name    = escHtmlAttr(p.name);
            var imgDisp = escHtml(p.image_url || ('https://picsum.photos/seed/' + p.id + '/300/300'));
            var price   = escHtmlAttr((p.sale_price || p.price) + ' ' + p.currency);

            return '<div class="salla-product-card"'
                + ' data-id="' + p.id + '"'
                + ' data-name="' + name + '"'
                + ' data-img="' + imgSrc + '"'
                + ' data-price="' + price + '"'
                + ' style="border:1.5px solid #f3f4f6;border-radius:12px;overflow:hidden;cursor:pointer;transition:all .2s;"'
                + ' onmouseover="this.style.borderColor=\'#6d28d9\'" onmouseout="this.style.borderColor=\'#f3f4f6\'">'
                + '<div style="position:relative;">'
                + '<img src="' + imgDisp + '" style="width:100%;height:120px;object-fit:cover;" loading="lazy">'
                + saleTag
                + '</div>'
                + '<div style="padding:8px;">'
                + '<p style="font-size:11px;font-weight:700;color:#111827;margin:0 0 4px;line-height:1.4;">' + escHtml(p.name) + '</p>'
                + '<span style="font-size:12px;font-weight:800;color:#6d28d9;">' + (p.sale_price || p.price) + ' ' + p.currency + '</span>'
                + oldPrice
                + '</div>'
                + '</div>';
        }).join('');

        // تسجيل الـ click handler بشكل آمن بدون inline JS
        grid.querySelectorAll('.salla-product-card').forEach(function (card) {
            card.addEventListener('click', function () {
                selectSallaProduct(
                    this.dataset.id,
                    this.dataset.name,
                    this.dataset.img,
                    this.dataset.price
                );
            });
        });
    }

    // ── Select Product → Show Caption Panel ───────────────────────────────────
    window.selectSallaProduct = function (id, name, imgUrl, priceText) {
        window._sallaSelectedProductId = id;
        window._sallaSelectedCaption   = null;

        document.getElementById('selProductImg').src              = imgUrl || 'https://picsum.photos/seed/' + id + '/300/300';
        document.getElementById('selProductName').textContent     = name;
        document.getElementById('selProductPrice').textContent    = priceText;

        document.getElementById('sallaCaptionPanel').style.display  = 'block';
        document.getElementById('sallaCaptionsArea').style.display  = 'none';
        document.getElementById('sallaCaptionLoading').style.display = 'none';

        document.getElementById('sallaCaptionPanel').scrollIntoView({ behavior: 'smooth', block: 'start' });
    };

    // ── Generate Captions ──────────────────────────────────────────────────────
    window.generateSallaCaptions = function () {
        if (!window._sallaSelectedProductId) { showToast('اختر منتجاً أولاً'); return; }

        document.getElementById('sallaCaptionLoading').style.display = 'block';
        document.getElementById('sallaCaptionsArea').style.display   = 'none';

        fetch('/generate-caption', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
            },
            body: JSON.stringify({
                product_id: window._sallaSelectedProductId,
                tone:       document.getElementById('sallaTone').value,
                language:   document.getElementById('sallaLang').value,
            }),
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            document.getElementById('sallaCaptionLoading').style.display = 'none';
            if (data.error) { showToast('❌ ' + data.error); return; }
            renderSallaCaptions(data.captions, data.hashtags || []);
        })
        .catch(function () {
            document.getElementById('sallaCaptionLoading').style.display = 'none';
            showToast('❌ حدث خطأ، حاول مرة أخرى');
        });
    };

    function renderSallaCaptions(captions, hashtags) {
        var list = document.getElementById('sallaCaptionsList');

        list.innerHTML = captions.map(function (c, i) {
            return '<div class="salla-caption-card" data-index="' + i + '">' + escHtml(c) + '</div>';
        }).join('');

        // تسجيل الـ click بشكل آمن — نحفظ النص الأصلي في مصفوفة
        var captionTexts = captions;
        list.querySelectorAll('.salla-caption-card').forEach(function (card, i) {
            card.addEventListener('click', function () {
                list.querySelectorAll('.salla-caption-card').forEach(function (c) { c.classList.remove('selected'); });
                card.classList.add('selected');
                var tags = Array.from(document.querySelectorAll('#sallaHashtags span')).map(function (s) { return s.textContent; }).join(' ');
                window._sallaSelectedCaption = captionTexts[i] + (tags ? '\n\n' + tags : '');
            });
        });

        var tagsEl = document.getElementById('sallaHashtags');
        tagsEl.innerHTML = hashtags.map(function (t) {
            return '<span style="background:#f3f0ff;color:#6d28d9;font-size:11px;font-weight:600;padding:3px 10px;border-radius:99px;">' + escHtml(t) + '</span>';
        }).join('');

        document.getElementById('sallaCaptionsArea').style.display = 'block';
    }

    // ── Schedule Post ──────────────────────────────────────────────────────────
    window.scheduleSallaPost = function () {
        var pageId     = document.getElementById('sallaPageSelect').value;
        var scheduleAt = document.getElementById('sallaScheduleAt').value;

        if (!window._sallaSelectedCaption) { showToast('اختر كابشن أولاً'); return; }
        if (!pageId)     { showToast('اختر صفحة Facebook'); return; }
        if (!scheduleAt) { showToast('حدد وقت الجدولة'); return; }

        fetch('/salla/schedule-post', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
            },
            body: JSON.stringify({
                product_id:       window._sallaSelectedProductId,
                facebook_page_id: pageId,
                content:          window._sallaSelectedCaption,
                scheduled_at:     scheduleAt,
            }),
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (data.success) {
                showToast(data.message || '✅ تم جدولة المنشور!');
                closeSallaModal();
            } else {
                showToast('❌ ' + (data.error || 'حدث خطأ'));
            }
        });
    };

    // ── Publish Now ────────────────────────────────────────────────────────────
    window.publishSallaPostNow = function () {
        var pageId = document.getElementById('sallaPageSelect').value;
        if (!window._sallaSelectedCaption) { showToast('اختر كابشن أولاً'); return; }
        if (!pageId) { showToast('اختر صفحة Facebook'); return; }

        var nowPlus1 = new Date(Date.now() + 60000).toISOString().slice(0, 16);

        fetch('/salla/schedule-post', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
            },
            body: JSON.stringify({
                product_id:       window._sallaSelectedProductId,
                facebook_page_id: pageId,
                content:          window._sallaSelectedCaption,
                scheduled_at:     nowPlus1,
            }),
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (data.success) {
                showToast('🚀 تم إرسال المنشور للنشر الفوري!');
                closeSallaModal();
            } else {
                showToast('❌ ' + (data.error || 'حدث خطأ'));
            }
        });
    };

    // ── Auto-Post Toggle ───────────────────────────────────────────────────────
    window.toggleSallaAutoPost = function () {
        fetch('/salla/toggle-auto-post', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content }
        })
        .then(function (r) { return r.json(); })
        .then(function (d) { showToast(d.message); setTimeout(function () { location.reload(); }, 1200); });
    };

    // ── Sync Products ──────────────────────────────────────────────────────────
    window.syncSallaProducts = function () {
        showToast('🔄 جاري المزامنة...');
        fetch('/salla/sync', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content }
        })
        .then(function (r) { return r.json(); })
        .then(function (d) {
            showToast('✅ تمت المزامنة: ' + d.synced + ' منتج');
            loadSallaProducts();
        });
    };

    // ── Helpers ────────────────────────────────────────────────────────────────
    function escHtml(s) {
        return String(s)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    // للـ HTML attributes — يحتاج escape إضافي لـ single quotes
    function escHtmlAttr(s) {
        return String(s)
            .replace(/&/g, '&amp;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');
    }
})();
</script>