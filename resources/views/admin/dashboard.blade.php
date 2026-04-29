<x-app-layout>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    {{-- <link href="https://fonts.googleapis.com/css2?family=Syne:wght@600;700;800&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet"> --}}

    <style>
        :root {
            --ink:    #0f1117;
            --mist:   #f4f5f7;
            --steel:  #e8eaed;
            --blue:   #2563eb;
            --blue-l: #eff6ff;
            --green:  #10b981;
            --amber:  #f59e0b;
            --red:    #ef4444;
            --card:   #ffffff;
            --radius: 16px;
        }

        * { box-sizing: border-box; }
        body { font-family: 'DM Sans', sans-serif; background: #f0f2f5; }
        h1, h2, h3, h4 { font-family: 'Syne', sans-serif; }

        /* ── Blobs ── */
        .dash-bg { position: fixed; inset: 0; z-index: 0; overflow: hidden; pointer-events: none; }
        .blob { position: absolute; border-radius: 50%; filter: blur(80px); opacity: 0.07; animation: blobFloat 12s ease-in-out infinite; }
        .blob-1 { width: 500px; height: 500px; background: #2563eb; top: -120px; left: -100px; animation-delay: 0s; }
        .blob-2 { width: 400px; height: 400px; background: #10b981; bottom: -80px; right: -80px; animation-delay: -4s; }
        .blob-3 { width: 300px; height: 300px; background: #f59e0b; top: 40%; left: 60%; animation-delay: -8s; }
        @keyframes blobFloat {
            0%, 100% { transform: translate(0, 0) scale(1); }
            33%       { transform: translate(30px, -20px) scale(1.05); }
            66%       { transform: translate(-20px, 15px) scale(0.97); }
        }

        /* ── Layout ── */
        .dash-wrap { position: relative; z-index: 1; max-width: 1280px; margin: 0 auto; padding: 32px 24px; }

        /* ── Stats ── */
        .stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-bottom: 28px; }
        @media (max-width: 768px) { .stats-grid { grid-template-columns: 1fr; } }

        .stat-card {
            background: var(--card);
            border-radius: var(--radius);
            padding: 22px;
            border: 1px solid var(--steel);
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            display: flex;
            align-items: center;
            gap: 16px;
            transition: transform .2s, box-shadow .2s;
            animation: fadeUp .5s both;
        }
        .stat-card:hover { transform: translateY(-3px); box-shadow: 0 8px 24px rgba(0,0,0,0.09); }
        .stat-card:nth-child(1) { animation-delay: .05s }
        .stat-card:nth-child(2) { animation-delay: .10s }
        .stat-card:nth-child(3) { animation-delay: .15s }

        .stat-icon { width: 48px; height: 48px; border-radius: 14px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .stat-num  { font-family: 'Syne', sans-serif; font-size: 30px; font-weight: 800; color: var(--ink); line-height: 1; }
        .stat-label { font-size: 12px; color: #6b7280; margin-top: 3px; font-weight: 500; }

        /* ── Table Card ── */
        .table-card {
            background: var(--card);
            border-radius: var(--radius);
            border: 1px solid var(--steel);
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            overflow: hidden;
            animation: fadeUp .5s .2s both;
        }
        .table-header {
            padding: 20px 24px;
            border-bottom: 1px solid var(--steel);
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
        }
        .table-title { font-family: 'Syne', sans-serif; font-size: 17px; font-weight: 800; color: var(--ink); }

        /* ── Search ── */
        .search-wrap {
            display: flex;
            align-items: center;
            gap: 8px;
            background: var(--mist);
            border: 1px solid var(--steel);
            border-radius: 10px;
            padding: 8px 14px;
            transition: border-color .15s, box-shadow .15s;
        }
        .search-wrap:focus-within {
            border-color: var(--blue);
            box-shadow: 0 0 0 3px rgba(37,99,235,.1);
            background: #fff;
        }
        .search-wrap input {
            background: none; border: none; outline: none;
            font-size: 13px; color: var(--ink);
            font-family: 'DM Sans', sans-serif; width: 180px;
        }
        .search-wrap input::placeholder { color: #9ca3af; }

        /* ── Add Button ── */
        .add-btn {
            display: inline-flex; align-items: center; gap: 7px;
            background: linear-gradient(135deg, var(--blue), #1d4ed8);
            color: #fff; font-weight: 700; font-size: 13px;
            padding: 9px 18px; border-radius: 10px; border: none;
            cursor: pointer; font-family: 'DM Sans', sans-serif;
            box-shadow: 0 4px 12px rgba(37,99,235,.3);
            transition: all .2s;
        }
        .add-btn:hover { transform: translateY(-1px); box-shadow: 0 6px 18px rgba(37,99,235,.4); }

        /* ── Table ── */
        table { width: 100%; border-collapse: collapse; }
        thead { background: #f9fafb; }
        th {
            padding: 11px 20px;
            font-size: 11px; font-weight: 700;
            color: #9ca3af; text-transform: uppercase;
            letter-spacing: .07em; text-align: left;
            border-bottom: 1px solid var(--steel);
        }
        th.center { text-align: center; }
        td { padding: 14px 20px; font-size: 13px; color: #374151; border-bottom: 1px solid #f3f4f6; }
        tr:last-child td { border-bottom: none; }
        tr.user-row { transition: background .12s; }
        tr.user-row:hover td { background: #fafbff; }

        /* ── User Avatar ── */
        .user-avatar-circle {
            width: 38px; height: 38px; border-radius: 50%;
            background: linear-gradient(135deg, #60a5fa, #2563eb);
            color: #fff; font-weight: 700; font-size: 14px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .user-cell { display: flex; align-items: center; gap: 11px; }
        .user-name-text { font-weight: 600; color: #111827; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 180px; }

        /* ── Badges ── */
        .badge {
            display: inline-flex; align-items: center;
            padding: 4px 11px; border-radius: 99px;
            font-size: 11px; font-weight: 700; white-space: nowrap;
        }
        .badge-purple { background: #f3f0ff; color: #6d28d9; }
        .badge-gray   { background: #f3f4f6; color: #6b7280; }
        .badge-green  { background: #d1fae5; color: #065f46; }
        .badge-blue   { background: #dbeafe; color: #1e40af; }

        /* ── Eye Button ── */
        .eye-btn {
            background: none; border: none; cursor: pointer;
            color: #60a5fa; padding: 6px; border-radius: 8px;
            display: inline-flex; align-items: center;
            transition: background .15s, color .15s;
        }
        .eye-btn:hover { background: var(--blue-l); color: var(--blue); }

        /* ── Empty State ── */
        .empty-state { padding: 60px 24px; text-align: center; }
        .empty-state svg { margin: 0 auto 12px; opacity: .25; display: block; }
        .empty-state p { font-size: 15px; font-weight: 600; color: #9ca3af; }

        /* ── User Chip (header) ── */
        .user-chip {
            display: flex; align-items: center; gap: 10px;
            background: var(--card); border: 1px solid var(--steel);
            padding: 7px 14px 7px 7px; border-radius: 40px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.06);
        }
        .chip-avatar {
            width: 30px; height: 30px; border-radius: 50%;
            background: linear-gradient(135deg, var(--blue), #7c3aed);
            color: #fff; font-weight: 700; font-size: 12px;
            display: flex; align-items: center; justify-content: center;
        }
        .chip-name { font-size: 13px; font-weight: 500; color: #374151; }

        .logout-btn {
            display: flex; align-items: center; gap: 5px;
            font-size: 12px; font-weight: 600; color: #ef4444;
            background: #fef2f2; border: 1px solid #fecaca;
            padding: 7px 14px; border-radius: 9px; cursor: pointer;
            transition: all .15s; font-family: 'DM Sans', sans-serif;
        }
        .logout-btn:hover { background: #ef4444; color: #fff; border-color: #ef4444; }

        .brand {
            font-family: 'Syne', sans-serif; font-size: 20px; font-weight: 800;
            background: linear-gradient(135deg, #2563eb, #7c3aed);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
        }

        /* ── Modal ── */
        .modal-backdrop {
            position: fixed; inset: 0;
            background: rgba(0,0,0,.5);
            backdrop-filter: blur(4px);
            display: none; align-items: center; justify-content: center;
            z-index: 50; padding: 16px;
        }
        .modal-backdrop.open { display: flex; }
        .modal-inner {
            background: #fff; border-radius: 20px;
            box-shadow: 0 24px 64px rgba(0,0,0,.2);
            max-width: 640px; width: 100%;
            max-height: 90vh; overflow-y: auto;
            animation: fadeUp .25s both;
        }
        .modal-head {
            position: sticky; top: 0; background: #fff;
            border-bottom: 1px solid #f3f4f6;
            padding: 18px 24px;
            display: flex; align-items: center; justify-content: space-between;
        }
        .modal-head h3 { font-family: 'Syne', sans-serif; font-size: 18px; font-weight: 800; color: var(--ink); }
        .modal-close { background: none; border: none; cursor: pointer; color: #9ca3af; padding: 4px; }
        .modal-close:hover { color: #374151; }
        .modal-body { padding: 24px; }

        /* ── Form ── */
        .dash-label { display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 7px; }
        .dash-input {
            width: 100%; border: 1.5px solid var(--steel);
            border-radius: 11px; padding: 10px 14px;
            font-size: 14px; font-family: 'DM Sans', sans-serif;
            color: var(--ink); outline: none;
            transition: border-color .15s, box-shadow .15s;
            background: var(--card);
        }
        .dash-input:focus { border-color: var(--blue); box-shadow: 0 0 0 3px rgba(37,99,235,.1); }
        .dash-select {
            width: 100%; border: 1.5px solid var(--steel);
            border-radius: 11px; padding: 10px 14px;
            font-size: 14px; font-family: 'DM Sans', sans-serif;
            color: var(--ink); outline: none;
            background: var(--card) url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='none' stroke='%239ca3af' stroke-width='2' viewBox='0 0 24 24'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E") no-repeat right 14px center;
            appearance: none; cursor: pointer;
            transition: border-color .15s, box-shadow .15s;
        }
        .dash-select:focus { border-color: var(--blue); box-shadow: 0 0 0 3px rgba(37,99,235,.1); }

        .fb-section {
            background: linear-gradient(135deg, #eff6ff, #f0f4ff);
            border: 1px solid #bfdbfe;
            border-radius: 14px; padding: 18px;
        }
        .fb-section-title {
            font-size: 12px; font-weight: 700; color: #1e40af;
            display: flex; align-items: center; gap: 7px;
            text-transform: uppercase; letter-spacing: .06em;
            margin-bottom: 14px;
        }

        .btn-primary {
            display: inline-flex; align-items: center; gap: 8px;
            background: linear-gradient(135deg, var(--blue), #1d4ed8);
            color: #fff; font-weight: 700; font-size: 14px;
            padding: 11px 24px; border-radius: 11px; border: none;
            cursor: pointer; font-family: 'DM Sans', sans-serif;
            box-shadow: 0 4px 14px rgba(37,99,235,.3);
            transition: all .2s;
        }
        .btn-primary:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(37,99,235,.4); }
        .btn-cancel {
            padding: 11px 20px; border: 1.5px solid #e5e7eb;
            border-radius: 11px; font-weight: 600; font-size: 14px;
            color: #6b7280; cursor: pointer; background: #fff;
            font-family: 'DM Sans', sans-serif; transition: all .15s;
        }
        .btn-cancel:hover { background: var(--mist); }

        .error-msg { display: none; color: #ef4444; font-size: 12px; margin-top: 4px; }
        .field-group { display: flex; flex-direction: column; }

        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        @media (max-width: 600px) { .grid-2 { grid-template-columns: 1fr; } }

        /* ── Spinner ── */
        @keyframes spin { to { transform: rotate(360deg); } }
        .spinner { animation: spin 1s linear infinite; }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(16px); }
            to   { opacity: 1; transform: translateY(0); }
        }
    </style>

    {{-- ── Slot: Header ── --}}
    <x-slot name="header">
        <div style="display:flex;align-items:center;justify-content:space-between;" dir="ltr">

            <span class="brand">⚡ PostFlow</span>

            <div style="display:flex;align-items:center;gap:12px;">

                @if(auth()->user()->is_admin)
                    <button id="openFormBtn" class="add-btn">
                        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                        </svg>
                        Add subscriber
                    </button>
                @endif

                <div class="user-chip">
                    <div class="chip-avatar">{{ substr(auth()->user()->name, 0, 1) }}</div>
                    <span class="chip-name">{{ auth()->user()->name }}</span>
                </div>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="logout-btn">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        Logout
                    </button>
                </form>

            </div>
        </div>
    </x-slot>

    {{-- ── Background Blobs ── --}}
    <div class="dash-bg">
        <div class="blob blob-1"></div>
        <div class="blob blob-2"></div>
        <div class="blob blob-3"></div>
    </div>

    <div class="dash-wrap" dir="ltr">

        @if(auth()->user()->is_admin)

            {{-- ── Stats ── --}}
            <div class="stats-grid">

                <div class="stat-card">
                    <div class="stat-icon" style="background:#eff6ff;">
                        <svg width="22" height="22" fill="none" stroke="#2563eb" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="stat-num">{{ $users->count() }}</div>
                        <div class="stat-label">Total subscribers</div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon" style="background:#f5f3ff;">
                        <svg width="22" height="22" fill="none" stroke="#7c3aed" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="stat-num">{{ $users->whereNotNull('plan_id')->count() }}</div>
                        <div class="stat-label">Subscribers Pro</div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon" style="background:#eff6ff;">
                        <svg width="22" height="22" fill="#2563eb" viewBox="0 0 24 24">
                            <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="stat-num">{{ \App\Models\FacebookPage::count() }}</div>
                        <div class="stat-label">Facebook accounts</div>
                    </div>
                </div>

            </div>

            {{-- ── Subscribers Table ── --}}
            <div class="table-card">

                <div class="table-header">
                    <div style="display:flex;align-items:center;gap:12px;">
                        <h3 class="table-title">Subscribers</h3>
                        <span class="badge badge-blue">{{ $users->count() }} total</span>
                    </div>
                    <div class="search-wrap">
                        <svg width="14" height="14" fill="none" stroke="#9ca3af" stroke-width="2" viewBox="0 0 24 24">
                            <circle cx="11" cy="11" r="8"/>
                            <path stroke-linecap="round" d="m21 21-4.35-4.35"/>
                        </svg>
                        <input type="text" id="searchInput" placeholder="Search subscribers...">
                    </div>
                </div>

                <div style="overflow-x:auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>Subscriber</th>
                                <th>Email</th>
                                <th class="center">Plan</th>
                                <th class="center">Facebook</th>
                                <th class="center">Status</th>
                                <th class="center"></th>
                            </tr>
                        </thead>
                        <tbody id="userTableBody">
                            @forelse($users as $user)
                            <tr class="user-row" data-user-id="{{ $user->id }}">

                                <td>
                                    <div class="user-cell">
                                        <div class="user-avatar-circle">
                                            {{ substr($user->name, 0, 1) }}
                                        </div>
                                        <span class="user-name-text">{{ $user->name }}</span>
                                    </div>
                                </td>

                                <td style="color:#6b7280;max-width:220px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                    {{ $user->email }}
                                </td>

                                <td style="text-align:center;">
                                    @if($user->currentPlan)
                                        <span class="badge badge-purple">{{ $user->currentPlan->name }}</span>
                                    @else
                                        <span class="badge badge-gray">Free</span>
                                    @endif
                                </td>

                                <td style="text-align:center;">
                                    @if($user->pages && $user->pages->count())
                                        <span class="badge badge-green">Connected</span>
                                    @else
                                        <span class="badge badge-gray">Not connected</span>
                                    @endif
                                </td>

                                <td style="text-align:center;">
                                    <span class="badge badge-green">Active</span>
                                </td>

                                {{-- <td style="text-align:center;">
                                    <button class="eye-btn" title="View user">
                                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </button>
                                </td> --}}

                            </tr>
                            @empty
                            <tr>
                                <td colspan="6">
                                    <div class="empty-state">
                                        <svg width="48" height="48" fill="none" stroke="#9ca3af" stroke-width="1.2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                        </svg>
                                        <p>No subscribers yet</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            </div>

            {{-- ── Add Subscriber Modal ── --}}
            <div id="userModal" class="modal-backdrop">
                <div class="modal-inner">

                    <div class="modal-head">
                        <h3 id="modalTitle">Add Subscriber</h3>
                        <button id="closeModalBtn" class="modal-close">
                            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <div class="modal-body">
                        <form id="userForm" action="{{ route('admin.users.store') }}" method="POST">
                            @csrf
                            <input type="hidden" name="user_id" id="userIdField">

                            <div style="display:flex;flex-direction:column;gap:18px;">

                                <div class="grid-2">
                                    <div class="field-group">
                                        <label class="dash-label">Full Name</label>
                                        <input type="text" name="name" id="userNameInput" required class="dash-input" placeholder="e.g. Ahmad Karimi">
                                        <span data-field="name" class="error-msg"></span>
                                    </div>
                                    <div class="field-group">
                                        <label class="dash-label">Email</label>
                                        <input type="email" name="email" id="userEmailInput" required class="dash-input" placeholder="e.g. ahmad@example.com">
                                        <span data-field="email" class="error-msg"></span>
                                    </div>
                                </div>

                                <div class="grid-2">
                                    <div class="field-group">
                                        <label class="dash-label">Password</label>
                                        <input type="password" name="password" class="dash-input" placeholder="Min. 8 characters">
                                        <span data-field="password" class="error-msg"></span>
                                    </div>
                                    <div class="field-group">
                                        <label class="dash-label">Plan</label>
                                        <select name="plan_id" id="userPlanSelect" class="dash-select">
                                            <option value="">No Plan (Free)</option>
                                            @foreach($plans as $plan)
                                                <option value="{{ $plan->id }}">{{ $plan->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <hr style="border:none;border-top:1px solid #f3f4f6;">

                                <div class="fb-section">
                                    <div class="fb-section-title">
                                        <svg width="16" height="16" fill="#2563eb" viewBox="0 0 24 24">
                                            <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                                        </svg>
                                        Add Facebook Page
                                    </div>

                                    <div class="grid-2" style="margin-bottom:14px;">
                                        <div class="field-group">
                                            <label class="dash-label">Page ID</label>
                                            <input type="text" name="page_id" class="dash-input" placeholder="e.g. 123456789">
                                        </div>
                                        <div class="field-group">
                                            <label class="dash-label">Page Name</label>
                                            <input type="text" name="page_name" class="dash-input" placeholder="My Business Page">
                                        </div>
                                    </div>

                                    <div class="field-group">
                                        <label class="dash-label">Page Access Token</label>
                                        <input type="text" name="page_access_token" class="dash-input" placeholder="EAAxxxxxxx...">
                                    </div>
                                </div>

                                <div style="display:flex;gap:10px;padding-top:6px;">
                                    <button type="submit" id="submitBtn" class="btn-primary" style="flex:1;justify-content:center;">
                                        <svg id="submitLoader" class="spinner" style="display:none;width:16px;height:16px;" fill="none" viewBox="0 0 24 24">
                                            <circle cx="12" cy="12" r="10" stroke="white" stroke-width="3" stroke-dasharray="60" stroke-dashoffset="20" stroke-linecap="round"/>
                                        </svg>
                                        <span id="submitBtnText">Save subscriber</span>
                                    </button>
                                    <button type="button" id="cancelBtn" class="btn-cancel">Cancel</button>
                                </div>

                            </div>
                        </form>
                    </div>

                </div>
            </div>

            {{-- ── JS ── --}}
            <script>
                // Modal open/close
                const modal      = document.getElementById('userModal');
                const openBtn    = document.getElementById('openFormBtn');
                const closeBtn   = document.getElementById('closeModalBtn');
                const cancelBtn  = document.getElementById('cancelBtn');

                function openModal()  { modal.classList.add('open');    document.body.style.overflow = 'hidden'; }
                function closeModal() { modal.classList.remove('open'); document.body.style.overflow = ''; }

                openBtn?.addEventListener('click', openModal);
                closeBtn?.addEventListener('click', closeModal);
                cancelBtn?.addEventListener('click', closeModal);
                modal?.addEventListener('click', e => { if (e.target === modal) closeModal(); });

                // Search / filter
                document.getElementById('searchInput')?.addEventListener('input', function () {
                    const q = this.value.toLowerCase();
                    document.querySelectorAll('#userTableBody tr.user-row').forEach(row => {
                        row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
                    });
                });

                // Submit loader
                document.getElementById('userForm')?.addEventListener('submit', function () {
                    const btn    = document.getElementById('submitBtn');
                    const loader = document.getElementById('submitLoader');
                    const text   = document.getElementById('submitBtnText');
                    btn.disabled       = true;
                    loader.style.display = 'block';
                    text.textContent   = 'Saving...';
                });
            </script>

            {{-- @vite(['resources/js/admin/app.js']) --}}

        @else
            @include('subscriber.dashboard')
        @endif

    </div>
</x-app-layout>