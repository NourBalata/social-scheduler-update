  <div class="section-title">{{ __('Subscribers')}}</div>
    <div class="table-card">

        <div class="table-header">
            <div style="display:flex;align-items:center;gap:10px;">
                <span class="table-title">{{ __('All users')}}</span>
                <span class="badge badge-blue">{{ __($stats['total_users']) }}</span>
            </div>
            <div style="display:flex;align-items:center;gap:10px;">
                <div class="search-wrap">
                    <svg width="13" height="13" fill="none" stroke="#9ca3af" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path stroke-linecap="round" d="m21 21-4.35-4.35"/></svg>
                    <input type="text" id="searchInput" placeholder="{{ __('Search')}}...">
                </div>
                {{-- Filter by plan --}}
                <select id="planFilter" style="font-size:12px;padding:7px 12px;border:1px solid var(--steel);border-radius:9px;background:var(--mist);color:var(--ink);outline:none;">
                    <option value="">{{ __('All plans')}}</option>
                    @foreach($plans as $plan)
                        <option value="{{ $plan->name }}">{{ __($plan->name) }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div style="overflow-x:auto;">
            <table>
                <thead>
                    <tr>
                        <th>{{ __('User')}}</th>
                        <th>{{ __('Email')}}</th>
                        <th>{{ __('Plan')}}</th>
                        <th>{{ __('Stripe')}}</th>
                        <th>{{ __('Pages')}}</th>
                        <th>{{ __('Posts')}}</th>
                        <th>{{ __('Joined')}}</th>
                        <th>{{ __('Actions')}}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="userTableBody">
                @forelse($users as $user)
                    <tr class="user-row" data-name="{{ strtolower($user->name) }}" data-email="{{ strtolower($user->email) }}" data-plan="{{ $user->currentPlan?->name }}">
                        <td>
                            <div class="user-cell">
    @if(app()->getLocale() === 'ar')
        <span style="font-weight:600;color:#111827;">{{ $user->name }}</span>
        <div class="user-avatar">{{ substr($user->name, 0, 1) }}</div>
    @else
        <div class="user-avatar">{{ substr($user->name, 0, 1) }}</div>
        <span style="font-weight:600;color:#111827;">{{ $user->name }}</span>
    @endif
</div>
                        </td>
                        <td style="color:#6b7280;max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $user->email }}</td>
                        <td>
                            @if($user->currentPlan)
                                <span class="badge badge-purple">{{ __($user->currentPlan->name) }}</span>
                            @else
                                <span class="badge badge-gray">{{ __('Free')}}</span>
                            @endif
                        </td>
                        <td>
                            @php $s = $user->stripe_status; @endphp
                            @if($s)
                                <span class="stripe-chip chip-{{$s}}">{{__($s)}}</span>
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
                    <tr><td colspan="8" style="text-align:center;padding:40px;color:#9ca3af;">{{ __('No users yet')}}</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>