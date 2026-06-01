{{--
    resources/views/admin/partials/reposts.blade.php
    يُضاف في dashboard.blade.php بعد @include('admin.partials.plans')
--}}

<div class="section-title" style="margin-top:32px;">🔁 {{ __('Auto-Repost') }}</div>

<div class="table-card">
    <div class="table-header">
        <span class="table-title">{{ __('Active Repost Rules') }}</span>
        <span class="badge badge-blue">{{ $repostRules->count() }}</span>
    </div>

    @if($repostRules->isEmpty())
        <div style="padding:32px;text-align:center;color:#9ca3af;font-size:13px;">
            {{ __('No repost rules yet.') }}
        </div>
    @else
        <div style="overflow-x:auto;">
            <table>
                <thead>
                    <tr>
                        <th>{{ __('User') }}</th>
                        <th>{{ __('Page') }}</th>
                        <th>{{ __('Original Post') }}</th>
                        <th>{{ __('Interval') }}</th>
                        <th>{{ __('Next Repost') }}</th>
                        <th>{{ __('Count') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($repostRules as $rule)
                    <tr>
                        <td>
                            <div class="user-cell">
                                <div class="user-avatar">{{ substr($rule->user->name, 0, 1) }}</div>
                                <span style="font-weight:600;">{{ $rule->user->name }}</span>
                            </div>
                        </td>
                        <td style="color:#6b7280;font-size:12px;">
                            {{ $rule->facebookPage->page_name ?? '—' }}
                        </td>
                        <td style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:12px;color:#374151;">
                            {{ Str::limit($rule->original_content, 60) }}
                        </td>
                        <td>
                            <span class="badge {{ $rule->interval === 'weekly' ? 'badge-blue' : 'badge-purple' }}">
                                {{ $rule->interval === 'weekly' ? __('Weekly') : __('Monthly') }}
                            </span>
                        </td>
                        <td style="font-size:12px;color:#6b7280;">
                            {{ $rule->next_repost_at->format('M d, Y') }}
                        </td>
                        <td style="text-align:center;font-weight:600;">
                            {{ $rule->repost_count }}
                        </td>
                        <td>
                            @if($rule->is_active)
                                <span class="badge badge-green">{{ __('Active') }}</span>
                            @else
                                <span class="badge badge-gray">{{ __('Paused') }}</span>
                            @endif
                        </td>
                        <td>
                            <div style="display:flex;gap:6px;">
                                <button
                                    onclick="toggleRepostRule({{ $rule->id }}, {{ $rule->is_active ? 'false' : 'true' }})"
                                    class="action-btn"
                                    title="{{ $rule->is_active ? __('Pause') : __('Resume') }}"
                                    style="color:{{ $rule->is_active ? '#f59e0b' : '#10b981' }};">
                                    @if($rule->is_active)
                                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 9v6m4-6v6"/>
                                            <rect x="3" y="3" width="18" height="18" rx="3"/>
                                        </svg>
                                    @else
                                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <polygon points="5 3 19 12 5 21 5 3"/>
                                        </svg>
                                    @endif
                                </button>

                                <button
                                    onclick="deleteRepostRule({{ $rule->id }})"
                                    class="action-btn"
                                    title="{{ __('Delete') }}"
                                    style="color:#ef4444;">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

{{-- Modal: إنشاء Repost Rule --}}
<div id="repostModal" class="modal-backdrop">
    <div class="modal-inner" style="max-width:480px;">
        <div class="modal-head">
            <h3>🔁 {{ __('Setup Auto-Repost') }}</h3>
            <button class="modal-close" onclick="closeModal('repostModal')">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="repostPostId">

            <div>
                <label class="field-label">{{ __('Repost Interval') }}</label>
                <select id="repostInterval" style="width:100%;padding:9px 12px;border:1px solid var(--steel);border-radius:9px;font-size:13px;background:var(--mist);">
                    <option value="weekly">{{ __('Every Week') }}</option>
                    <option value="monthly">{{ __('Every Month') }}</option>
                </select>
            </div>

            <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;padding:12px 14px;font-size:12px;color:#065f46;">
                💡 {{ __("AI will rewrite the post content each time it's reposted, keeping the same message but with fresh wording.") }}
            </div>

            <button onclick="submitRepostRule()" class="add-btn" style="width:100%;justify-content:center;">
                🔁 {{ __('Activate Auto-Repost') }}
            </button>
        </div>
    </div>
</div>

<script>
function openRepostModal(postId) {
    document.getElementById('repostPostId').value = postId;
    openModal('repostModal');
}

function submitRepostRule() {
    const postId   = document.getElementById('repostPostId').value;
    const interval = document.getElementById('repostInterval').value;

    fetch('/repost-rules', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ post_id: postId, interval }),
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) {
            closeModal('repostModal');
            closeModal('publishedPostsModal');
            Swal.fire({
                icon: 'success',
                title: '{{ __("Auto-Repost Activated") }}',
                text: '{{ __("AI will rewrite and repost this content automatically.") }}',
                timer: 2000,
                showConfirmButton: false,
            });
            setTimeout(() => location.reload(), 2100);
        } else {
            alert(d.message ?? 'Something went wrong.');
        }
    })
    .catch(err => console.error(err));
}

function toggleRepostRule(ruleId, newStatus) {
    fetch(`/repost-rules/${ruleId}/toggle`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ is_active: newStatus }),
    })
    .then(r => r.json())
    .then(d => { if (d.success) location.reload(); });
}

function deleteRepostRule(ruleId) {
    Swal.fire({
        title: '{{ __("Delete this rule?") }}',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        confirmButtonText: '{{ __("Yes, delete") }}',
        cancelButtonText: '{{ __("Cancel") }}',
    }).then(r => {
        if (!r.isConfirmed) return;
        fetch(`/repost-rules/${ruleId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
        })
        .then(r => r.json())
        .then(d => { if (d.success) location.reload(); });
    });
}
</script>