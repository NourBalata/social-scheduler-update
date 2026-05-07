<x-app-layout>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<style>
:root {
    --ink: #0f1117; --mist: #f4f5f7; --steel: #e8eaed;
    --blue: #2563eb; --blue-l: #eff6ff;
    --green: #10b981; --amber: #f59e0b; --red: #ef4444;
    --purple: #7c3aed; --card: #ffffff; --radius: 16px;
}
* { box-sizing: border-box; }
body { font-family: 'DM Sans', system-ui, sans-serif; background: #f0f2f5; }

.dash-wrap { max-width: 1280px; margin: 0 auto; padding: 28px 20px; }


.section-title { font-size: 13px; font-weight: 700; color: #6b7280; text-transform: uppercase; letter-spacing: .06em; margin: 28px 0 14px; }

.stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 12px; margin-bottom: 8px; }
.stat-card { background: var(--card); border-radius: 14px; padding: 18px; border: 1px solid var(--steel); }
.stat-icon { width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; margin-bottom: 12px; }
.stat-num  { font-size: 26px; font-weight: 700; color: var(--ink); line-height: 1; }
.stat-label { font-size: 12px; color: #6b7280; margin-top: 4px; }


.charts-row { display: grid; grid-template-columns: 2fr 1fr; gap: 16px; margin-bottom: 8px; }
@media(max-width:768px){ .charts-row { grid-template-columns: 1fr; } }
.chart-card { background: var(--card); border-radius: 14px; padding: 20px; border: 1px solid var(--steel); }
.chart-title { font-size: 14px; font-weight: 600; color: var(--ink); margin-bottom: 16px; }


.table-card { background: var(--card); border-radius: 14px; border: 1px solid var(--steel); overflow: hidden; }
.table-header { padding: 16px 20px; border-bottom: 1px solid var(--steel); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px; }
.table-title { font-size: 15px; font-weight: 700; color: var(--ink); }
.search-wrap { display: flex; align-items: center; gap: 8px; background: var(--mist); border: 1px solid var(--steel); border-radius: 10px; padding: 7px 12px; }
.search-wrap input { background: none; border: none; outline: none; font-size: 13px; color: var(--ink); width: 170px; }
.add-btn { display: inline-flex; align-items: center; gap: 6px; background: var(--blue); color: #fff; font-weight: 600; font-size: 13px; padding: 8px 16px; border-radius: 9px; border: none; cursor: pointer; }

table { width: 100%; border-collapse: collapse; }
thead { background: #f9fafb; }
th { padding: 10px 16px; font-size: 11px; font-weight: 700; color: #9ca3af; text-transform: uppercase; letter-spacing: .06em; text-align: left; border-bottom: 1px solid var(--steel); }
td { padding: 13px 16px; font-size: 13px; color: #374151; border-bottom: 1px solid #f3f4f6; }
tr:last-child td { border-bottom: none; }
tr.user-row:hover td { background: #fafbff; }


.badge { display: inline-flex; align-items: center; padding: 3px 10px; border-radius: 99px; font-size: 11px; font-weight: 700; white-space: nowrap; }
.badge-purple { background: #f3f0ff; color: #6d28d9; }
.badge-gray   { background: #f3f4f6; color: #6b7280; }
.badge-green  { background: #d1fae5; color: #065f46; }
.badge-blue   { background: #dbeafe; color: #1e40af; }
.badge-amber  { background: #fef3c7; color: #92400e; }
.badge-red    { background: #fee2e2; color: #991b1b; }


.user-avatar { width: 34px; height: 34px; border-radius: 50%; background: linear-gradient(135deg,#60a5fa,#2563eb); color:#fff; font-weight:700; font-size:13px; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.user-cell { display: flex; align-items: center; gap: 10px; }


.action-btn { background: none; border: none; cursor: pointer; padding: 5px; border-radius: 7px; display: inline-flex; align-items: center; transition: background .12s; color: #9ca3af; }
.action-btn:hover { background: var(--mist); color: var(--ink); }


.plans-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px; }
.plan-card { background: var(--card); border-radius: 12px; border: 1px solid var(--steel); padding: 16px; }
.plan-name { font-weight: 700; font-size: 15px; color: var(--ink); }
.plan-price { font-size: 22px; font-weight: 700; color: var(--blue); margin: 6px 0; }
.plan-meta { font-size: 12px; color: #6b7280; }
.plan-actions { display: flex; gap: 8px; margin-top: 12px; }
.plan-edit-btn { font-size: 12px; padding: 5px 12px; border-radius: 7px; border: 1px solid var(--steel); cursor: pointer; background: var(--mist); color: #374151; font-weight: 500; }

/* ── Modal ── */
.modal-backdrop { position: fixed; inset: 0; background: rgba(0,0,0,.45); display: none; align-items: center; justify-content: center; z-index: 50; padding: 16px; }
.modal-backdrop.open { display: flex; }
.modal-inner { background:#fff; border-radius:18px; max-width:600px; width:100%; max-height:90vh; overflow-y:auto; }
.modal-head { padding:18px 22px; border-bottom:1px solid var(--steel); display:flex; align-items:center; justify-content:space-between; }
.modal-head h3 { font-size:16px; font-weight:700; color:var(--ink); }
.modal-close { background:none; border:none; cursor:pointer; color:#9ca3af; padding:3px; }
.modal-body { padding:22px; display:flex; flex-direction:column; gap:16px; }
.field-label { font-size:12px; font-weight:600; color:#374151; margin-bottom:5px; display:block; }
.field-input { width:100%; border:1.5px solid var(--steel); border-radius:10px; padding:9px 13px; font-size:14px; color:var(--ink); outline:none; transition:border-color .15s; }
.field-input:focus { border-color:var(--blue); }
.grid-2 { display:grid; grid-template-columns:1fr 1fr; gap:14px; }
.btn-save { background:var(--blue); color:#fff; border:none; border-radius:10px; padding:10px 22px; font-size:14px; font-weight:600; cursor:pointer; }
.btn-cancel-sm { background:var(--mist); color:#374151; border:1px solid var(--steel); border-radius:10px; padding:10px 18px; font-size:14px; cursor:pointer; }
.modal-footer { display:flex; gap:10px; padding-top:6px; }


.stripe-chip { display:inline-flex; align-items:center; gap:4px; padding:2px 8px; border-radius:99px; font-size:10px; font-weight:700; }
.chip-active   { background:#d1fae5; color:#065f46; }
.chip-past_due { background:#fef3c7; color:#92400e; }
.chip-canceled { background:#fee2e2; color:#991b1b; }
.chip-none     { background:#f3f4f6; color:#6b7280; }
</style>

<x-slot name="header">
    <div style="display:flex;align-items:center;justify-content:space-between;" dir="ltr">
        <span style="font-size:18px;font-weight:700;color:var(--ink)">Admin Dashboard</span>
        <div style="display:flex;align-items:center;gap:10px;">
            <button id="openAddUserBtn" class="add-btn">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                Add user
            </button>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" style="background:none;border:1px solid #fecaca;color:#ef4444;padding:7px 14px;border-radius:9px;font-size:12px;font-weight:600;cursor:pointer;">Logout</button>
            </form>
        </div>
    </div>
</x-slot>

<div class="dash-wrap" dir="ltr">


    <div class="section-title">Overview</div>
    <div class="stats-grid">

        <div class="stat-card">
            <div class="stat-icon" style="background:#eff6ff;">
                <svg width="20" height="20" fill="none" stroke="#2563eb" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            </div>
            <div class="stat-num">{{ $stats['total_users'] }}</div>
            <div class="stat-label">Total users</div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background:#f5f3ff;">
                <svg width="20" height="20" fill="none" stroke="#7c3aed" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
            </div>
            <div class="stat-num">{{ $stats['active_paid'] }}</div>
            <div class="stat-label">Paid subscribers</div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background:#d1fae5;">
                <svg width="20" height="20" fill="none" stroke="#10b981" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div class="stat-num">${{ number_format($stats['mrr'], 0) }}</div>
            <div class="stat-label">MRR this month</div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background:#fef3c7;">
                <svg width="20" height="20" fill="none" stroke="#f59e0b" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            </div>
            <div class="stat-num">{{ number_format($stats['posts_this_month']) }}</div>
            <div class="stat-label">Posts this month</div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background:#d1fae5;">
                <svg width="20" height="20" fill="none" stroke="#10b981" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            </div>
            <div class="stat-num">{{ number_format($stats['posts_published']) }}</div>
            <div class="stat-label">Published</div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background:#fee2e2;">
                <svg width="20" height="20" fill="none" stroke="#ef4444" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div class="stat-num">{{ number_format($stats['posts_failed']) }}</div>
            <div class="stat-label">Failed posts</div>
        </div>

    </div>

  
    <div class="section-title">Revenue & Distribution</div>
    <div class="charts-row">

        <div class="chart-card">
            <div class="chart-title">Monthly Revenue (last 6 months)</div>
            <canvas id="revenueChart" height="120"></canvas>
        </div>

        <div class="chart-card">
            <div class="chart-title">Users by plan</div>
            <canvas id="planChart" height="160"></canvas>
        </div>

    </div>

  
    <div class="section-title" style="display:flex;align-items:center;justify-content:space-between;">
        <span>Plans</span>
        <button class="add-btn" onclick="openPlanModal()" style="font-size:12px;padding:6px 13px;">+ New plan</button>
    </div>

    <div class="plans-grid" style="margin-bottom:8px;">
        @foreach($plans as $plan)
        <div class="plan-card">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:4px;">
                <div class="plan-name">{{ $plan->name }}</div>
                @if($plan->active)
                    <span class="badge badge-green" style="font-size:10px;">Active</span>
                @else
                    <span class="badge badge-gray" style="font-size:10px;">Inactive</span>
                @endif
            </div>
            <div class="plan-price">${{ number_format($plan->price, 2) }}<span style="font-size:13px;font-weight:400;color:#6b7280;">/mo</span></div>
            <div class="plan-meta">{{ $plan->posts_limit }} posts · {{ $plan->pages_limit }} pages</div>
            @if($plan->stripe_price_id)
                <div class="plan-meta" style="margin-top:3px;font-size:11px;color:#9ca3af;">{{ $plan->stripe_price_id }}</div>
            @endif
            <div class="plan-actions">
                <button class="plan-edit-btn" onclick="editPlan({{ $plan->id }}, '{{ $plan->name }}', {{ $plan->price }}, {{ $plan->posts_limit }}, {{ $plan->pages_limit }}, '{{ $plan->stripe_price_id }}', {{ $plan->active ? 'true' : 'false' }})">Edit</button>
                <span style="font-size:12px;color:#6b7280;padding:5px 0;">{{ $plansBreakdown->firstWhere('name', $plan->name)['count'] ?? 0 }} users</span>
            </div>
        </div>
        @endforeach
    </div>


    <div class="section-title">Subscribers</div>
    <div class="table-card">

        <div class="table-header">
            <div style="display:flex;align-items:center;gap:10px;">
                <span class="table-title">All users</span>
                <span class="badge badge-blue">{{ $stats['total_users'] }}</span>
            </div>
            <div style="display:flex;align-items:center;gap:10px;">
                <div class="search-wrap">
                    <svg width="13" height="13" fill="none" stroke="#9ca3af" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path stroke-linecap="round" d="m21 21-4.35-4.35"/></svg>
                    <input type="text" id="searchInput" placeholder="Search...">
                </div>
                {{-- Filter by plan --}}
                <select id="planFilter" style="font-size:12px;padding:7px 12px;border:1px solid var(--steel);border-radius:9px;background:var(--mist);color:var(--ink);outline:none;">
                    <option value="">All plans</option>
                    @foreach($plans as $plan)
                        <option value="{{ $plan->name }}">{{ $plan->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div style="overflow-x:auto;">
            <table>
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Email</th>
                        <th>Plan</th>
                        <th>Stripe</th>
                        <th>Pages</th>
                        <th>Posts</th>
                        <th>Joined</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="userTableBody">
                @forelse($users as $user)
                    <tr class="user-row" data-name="{{ strtolower($user->name) }}" data-email="{{ strtolower($user->email) }}" data-plan="{{ $user->currentPlan?->name }}">
                        <td>
                            <div class="user-cell">
                                <div class="user-avatar">{{ substr($user->name, 0, 1) }}</div>
                                <span style="font-weight:600;color:#111827;">{{ $user->name }}</span>
                            </div>
                        </td>
                        <td style="color:#6b7280;max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $user->email }}</td>
                        <td>
                            @if($user->currentPlan)
                                <span class="badge badge-purple">{{ $user->currentPlan->name }}</span>
                            @else
                                <span class="badge badge-gray">Free</span>
                            @endif
                        </td>
                        <td>
                            @php $s = $user->stripe_status; @endphp
                            @if($s)
                                <span class="stripe-chip chip-{{ $s }}">{{ $s }}</span>
                            @else
                                <span class="stripe-chip chip-none">—</span>
                            @endif
                        </td>
                        <td style="text-align:center;">{{ $user->facebookPages->count() }}</td>
                        <td style="text-align:center;">{{ $user->scheduledPosts->count() }}</td>
                        <td style="color:#9ca3af;font-size:12px;">{{ $user->created_at->format('M d, Y') }}</td>
                        <td>
                            <div style="display:flex;gap:4px;">
                            
                                <button class="action-btn" title="Change plan"
                                    onclick="openChangePlan({{ $user->id }}, '{{ addslashes($user->name) }}', {{ $user->plan_id ?? 'null' }})">
                                    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 7h10M7 12h10M7 17h6"/></svg>
                                </button>
                     
                                <button class="action-btn" title="Delete user" style="color:#ef4444;"
                                    onclick="deleteUser({{ $user->id }}, '{{ addslashes($user->name) }}')">
                                    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" style="text-align:center;padding:40px;color:#9ca3af;">No users yet</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>


<div id="addUserModal" class="modal-backdrop">
    <div class="modal-inner">
        <div class="modal-head">
            <h3>Add subscriber</h3>
            <button class="modal-close" onclick="closeModal('addUserModal')">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form id="addUserForm" action="{{ route('admin.users.store') }}" method="POST">
            @csrf
            <div class="modal-body">
                <div class="grid-2">
                    <div><label class="field-label">Name</label><input type="text" name="name" required class="field-input" placeholder="Full name"></div>
                    <div><label class="field-label">Email</label><input type="email" name="email" required class="field-input" placeholder="email@example.com"></div>
                </div>
                <div class="grid-2">
                    <div><label class="field-label">Password</label><input type="password" name="password" required class="field-input" placeholder="Min 8 chars"></div>
                    <div>
                        <label class="field-label">Plan</label>
                        <select name="plan_id" class="field-input" style="cursor:pointer;">
                            @foreach($plans as $plan)
                                <option value="{{ $plan->id }}">{{ $plan->name }} — ${{ $plan->price }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn-save">Save user</button>
                    <button type="button" class="btn-cancel-sm" onclick="closeModal('addUserModal')">Cancel</button>
                </div>
            </div>
        </form>
    </div>
</div>


<div id="changePlanModal" class="modal-backdrop">
    <div class="modal-inner" style="max-width:400px;">
        <div class="modal-head">
            <h3 id="changePlanTitle">Change plan</h3>
            <button class="modal-close" onclick="closeModal('changePlanModal')">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="changePlanUserId">
            <div>
                <label class="field-label">New plan</label>
                <select id="changePlanSelect" class="field-input" style="cursor:pointer;">
                    @foreach($plans as $plan)
                        <option value="{{ $plan->id }}">{{ $plan->name }} — ${{ $plan->price }}/mo</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="field-label">Expires at (optional)</label>
                <input type="date" id="changePlanExpiry" class="field-input" min="{{ now()->toDateString() }}">
            </div>
            <div class="modal-footer">
                <button class="btn-save" onclick="submitChangePlan()">Update plan</button>
                <button class="btn-cancel-sm" onclick="closeModal('changePlanModal')">Cancel</button>
            </div>
        </div>
    </div>
</div>


<div id="planModal" class="modal-backdrop">
    <div class="modal-inner" style="max-width:480px;">
        <div class="modal-head">
            <h3 id="planModalTitle">New plan</h3>
            <button class="modal-close" onclick="closeModal('planModal')">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="planModalId">
            <div class="grid-2">
                <div><label class="field-label">Name</label><input id="planModalName" class="field-input" placeholder="Pro" readonly></div>
                <div><label class="field-label">Slug</label><input id="planModalSlug" class="field-input" placeholder="pro"></div>
            </div>
            <div class="grid-2">
                <div><label class="field-label">Price ($/mo)</label><input type="number" id="planModalPrice" class="field-input" placeholder="9.99" step="0.01" min="0"></div>
                <div><label class="field-label">Stripe Price ID</label><input id="planModalStripeId" class="field-input" placeholder="price_xxx..."></div>
            </div>
            <div class="grid-2">
                <div><label class="field-label">Posts/month limit</label><input type="number" id="planModalPosts" class="field-input" min="1"></div>
                <div><label class="field-label">Pages limit</label><input type="number" id="planModalPages" class="field-input" min="1"></div>
            </div>
            <div style="display:flex;align-items:center;gap:8px;">
                <input type="checkbox" id="planModalActive" checked style="width:16px;height:16px;">
                <label for="planModalActive" class="field-label" style="margin:0;">Active</label>
            </div>
            <div class="modal-footer">
                <button class="btn-save" onclick="submitPlan()">Save plan</button>
                <button class="btn-cancel-sm" onclick="closeModal('planModal')">Cancel</button>
            </div>
        </div>
    </div>
</div>

<script>

function closeModal(id) {
    document.getElementById(id).classList.remove('open');
    document.body.style.overflow = '';
}
function openModal(id) {
    document.getElementById(id).classList.add('open');
    document.body.style.overflow = 'hidden';
}

// close on backdrop click
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
    document.getElementById('changePlanTitle').textContent = `Change plan — ${userName}`;
    document.getElementById('changePlanUserId').value = userId;
    if (currentPlanId) document.getElementById('changePlanSelect').value = currentPlanId;
    openModal('changePlanModal');
}

function submitChangePlan() {
    const userId  = document.getElementById('changePlanUserId').value;
    const planId  = document.getElementById('changePlanSelect').value;
    const expiry  = document.getElementById('changePlanExpiry').value;

    fetch(`/admin/users/${userId}/plan`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({ plan_id: planId, plan_expires_at: expiry || null }),
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) {
            closeModal('changePlanModal');
            Swal.fire({ icon: 'success', title: 'Plan updated', text: `Set to ${d.plan_name}`, timer: 1800, showConfirmButton: false });
            setTimeout(() => location.reload(), 1900);
        }
    });
}


function deleteUser(userId, userName) {
    Swal.fire({
        title: `Delete ${userName}?`,
        text: 'This will remove all their data permanently.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        confirmButtonText: 'Yes, delete',
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
    document.getElementById('planModalTitle').textContent = 'New plan';
    document.getElementById('planModalId').value = '';
    document.getElementById('planModalName').readOnly = false;
    ['planModalName','planModalSlug','planModalPrice','planModalStripeId','planModalPosts','planModalPages']
        .forEach(id => document.getElementById(id).value = '');
    document.getElementById('planModalActive').checked = true;
    openModal('planModal');
}

function editPlan(id, name, price, posts, pages, stripeId, active) {
    document.getElementById('planModalTitle').textContent = `Edit — ${name}`;
    document.getElementById('planModalId').value   = id;
    document.getElementById('planModalName').value = name;
    document.getElementById('planModalName').readOnly = true;
    document.getElementById('planModalPrice').value    = price;
    document.getElementById('planModalStripeId').value = stripeId || '';
    document.getElementById('planModalPosts').value    = posts;
    document.getElementById('planModalPages').value    = pages;
    document.getElementById('planModalActive').checked = active;
    openModal('planModal');
}

function submitPlan() {
    const id = document.getElementById('planModalId').value;
    const body = {
        name:             document.getElementById('planModalName').value,
        slug:             document.getElementById('planModalSlug').value,
        price:            document.getElementById('planModalPrice').value,
        stripe_price_id:  document.getElementById('planModalStripeId').value,
        posts_limit:      document.getElementById('planModalPosts').value,
        pages_limit:      document.getElementById('planModalPages').value,
        active:           document.getElementById('planModalActive').checked,
    };

    const url    = id ? `/admin/plans/${id}` : '/admin/plans';
    const method = 'POST';

    fetch(url, {
        method,
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify(body),
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) {
            closeModal('planModal');
            Swal.fire({ icon: 'success', title: 'Saved', timer: 1500, showConfirmButton: false });
            setTimeout(() => location.reload(), 1600);
        }
    });
}


const revenueData = @json($revenueChart);
const planData    = @json($plansBreakdown);

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
</x-app-layout>