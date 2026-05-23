<x-app-layout>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

@include('admin.partials.styles')

<x-slot name="header">
    <div style="display:flex;align-items:center;gap:10px;">
    <button onclick="document.getElementById('revenueModal').classList.add('open')"
        style="background:#f5f3ff;border:1px solid #e0e7ff;color:#7c3aed;font-size:13px;font-weight:600;padding:8px 16px;border-radius:9px;cursor:pointer;display:inline-flex;align-items:center;gap:6px;">
        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
        {{ __('Revenue Analyzer')}}
    </button>
    <button id="openAddUserBtn" class="add-btn">
        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
        {{__('Add user')}}
    </button>
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" style="background:none;border:1px solid #fecaca;color:#ef4444;padding:7px 14px;border-radius:9px;font-size:12px;font-weight:600;cursor:pointer;">Logout</button>
    </form>
             <div style="display:flex;gap:8px;">
    <form method="POST" action="{{ route('lang.switch', 'ar') }}">
        @csrf
        <button type="submit" style="padding:6px 14px;border-radius:8px;border:1.5px solid #e5e7eb;background:{{ app()->getLocale() === 'ar' ? '#2563eb' : '#fff' }};color:{{ app()->getLocale() === 'ar' ? '#fff' : '#374151' }};font-weight:700;cursor:pointer;">
            عربي
        </button>
    </form>
    <form method="POST" action="{{ route('lang.switch', 'en') }}">
        @csrf
        <button type="submit" style="padding:6px 14px;border-radius:8px;border:1.5px solid #e5e7eb;background:{{ app()->getLocale() === 'en' ? '#2563eb' : '#fff' }};color:{{ app()->getLocale() === 'en' ? '#fff' : '#374151' }};font-weight:700;cursor:pointer;">
            English
        </button>
    </form>
</div>
</div>
</x-slot>

<div class="dash-wrap" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
    @include('admin.partials.stats')
    @include('admin.partials.charts')
    @include('admin.partials.plans')
    @include('admin.partials.users')
</div>

  
@include('admin.partials.modals')
@include('admin.partials.scripts')



  

</div>


<div id="revenueModal" class="modal-backdrop" style="z-index:60;">
    <div class="modal-inner" style="max-width:1100px;width:95%;max-height:92vh;overflow-y:auto;">
        <div class="modal-head">
            <h3>📊 {{ __('Revenue Anomaly Analyzer')}}</h3>
            <button class="modal-close" onclick="document.getElementById('revenueModal').classList.remove('open')">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div style="padding:0;">
            @livewire('revenue-analyzer')
        </div>
    </div>
</div>


</x-app-layout>