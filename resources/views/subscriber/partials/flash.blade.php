@if(session('success'))
    <div style="background:#d1fae5;color:#065f46;padding:14px 18px;border-radius:12px;margin-bottom:24px;border:1px solid #a7f3d0;font-weight:500;font-size:14px;display:flex;align-items:center;gap:8px;">
        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        {{ session('success') }}
    </div>
@endif

@if($errors->any())
    <div style="background:#fee2e2;color:#991b1b;padding:14px 18px;border-radius:12px;margin-bottom:24px;border:1px solid #fecaca;font-weight:500;font-size:14px;">
        <ul style="margin:0;padding-left:18px;">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif