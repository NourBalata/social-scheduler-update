<div id="publishedPostsModal" class="modal-backdrop" style="z-index:55;">
    <div class="modal-inner" style="max-width:780px;width:95%;max-height:88vh;overflow-y:auto;">

        <div class="modal-head">
            <h3>📋 {{ __('Published Posts') }}</h3>
            <button class="modal-close" onclick="closeModal('publishedPostsModal')">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <div style="padding:16px 20px;border-bottom:1px solid var(--steel);display:flex;gap:10px;align-items:center;">
            <input
                type="text"
                id="publishedPostsSearch"
                placeholder="{{ __('Search posts...') }}"
                oninput="filterPublishedPosts()"
                style="flex:1;padding:8px 12px;border:1px solid var(--steel);border-radius:9px;font-size:13px;background:var(--mist);outline:none;">
            <span class="badge badge-green">{{ $publishedPosts->count() }} {{ __('posts') }}</span>
        </div>

        <div style="padding:16px 20px;display:flex;flex-direction:column;gap:10px;" id="publishedPostsList">

            @forelse($publishedPosts as $post)
            <div class="published-post-item"
                 data-content="{{ strtolower($post->content) }}"
                 style="border:1px solid var(--steel);border-radius:12px;padding:14px 16px;display:flex;align-items:flex-start;gap:12px;">

              
                <div style="width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,#34d399,#059669);color:#fff;font-weight:700;font-size:13px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    {{ substr($post->facebookPage->page_name ?? 'P', 0, 1) }}
                </div>

                <div style="flex:1;min-width:0;">
                    <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px;flex-wrap:wrap;">
                        <span style="font-size:12px;font-weight:600;color:#374151;">
                            {{ $post->facebookPage->page_name ?? '—' }}
                        </span>
                        <span style="font-size:11px;color:#9ca3af;">
                            {{ $post->user->name }}
                        </span>
                        <span style="font-size:11px;color:#9ca3af;">·</span>
                        <span style="font-size:11px;color:#9ca3af;">
                            {{ $post->published_at?->format('M d, Y') ?? $post->created_at->format('M d, Y') }}
                        </span>
                    </div>
                    <p style="font-size:13px;color:#374151;margin:0;line-height:1.5;
                        overflow:hidden;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;">
                        {{ $post->content }}
                    </p>
                </div>

            
                <button
                    onclick="openRepostModal({{ $post->id }})"
                    style="flex-shrink:0;padding:6px 12px;border-radius:8px;border:1.5px solid #bbf7d0;background:#f0fdf4;color:#065f46;font-size:12px;font-weight:600;cursor:pointer;white-space:nowrap;">
                    🔁 {{ __('Auto-Repost') }}
                </button>
            </div>
            @empty
            <div style="text-align:center;padding:40px;color:#9ca3af;font-size:13px;">
                {{ __('No published posts yet.') }}
            </div>
            @endforelse

        </div>
    </div>
</div>

<script>
function filterPublishedPosts() {
    const q = document.getElementById('publishedPostsSearch').value.toLowerCase();
    document.querySelectorAll('.published-post-item').forEach(item => {
        item.style.display = item.dataset.content.includes(q) ? '' : 'none';
    });
}

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
            alert('✅ Auto-Repost activated!');
            location.reload();
        } else {
            alert(d.message ?? 'Something went wrong.');
        }
    })
    .catch(err => console.error(err));
}
</script>