<div class="bulk-card">
    <h3 style="font-size:15px;font-weight:800;color:#065f46;margin-bottom:6px;display:flex;align-items:center;gap:8px;">
        <svg width="18" height="18" fill="none" stroke="#10b981" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
        {{ __('Bulk Schedule via CSV')}}
    </h3>
    <p style="font-size:12px;color:#6b7280;margin-bottom:14px;">
        {{ __('Columns')}}: <code style="background:#dcfce7;padding:1px 6px;border-radius:4px;font-size:11px;">{{ __('page_name, content, scheduled_at')}}</code>
    </p>
    <form action="{{ route('posts.bulk') }}" method="POST" enctype="multipart/form-data" style="display:flex;gap:10px;flex-wrap:wrap;">
        @csrf
        <label style="flex:1;min-width:200px;display:flex;align-items:center;gap:10px;border:2px dashed #6ee7b7;border-radius:12px;padding:12px 16px;cursor:pointer;background:#f0fdf4;transition:all .2s;" onmouseover="this.style.borderColor='#10b981'" onmouseout="this.style.borderColor='#6ee7b7'">
            <svg width="18" height="18" fill="none" stroke="#10b981" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
            <span style="font-size:13px;color:#047857;font-weight:600;" id="csv-label">{{ __('Choose CSV file')}}...</span>
            <input type="file" name="csv_file" accept=".csv" class="hidden" onchange="document.getElementById('csv-label').textContent=this.files[0].name">
        </label>
        <button type="submit" style="background:#10b981;color:#fff;font-weight:700;font-size:13px;padding:12px 20px;border-radius:12px;border:none;cursor:pointer;" onmouseover="this.style.background='#059669'" onmouseout="this.style.background='#10b981'">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
            {{ __('Upload & Schedule')}}
        </button>
    </form>
</div>