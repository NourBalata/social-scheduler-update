<div id="addUserModal" class="modal-backdrop">
    <div class="modal-inner">
        <div class="modal-head">
            <h3>{{ __('Add subscriber')}}</h3>
            <button class="modal-close" onclick="closeModal('addUserModal')">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form id="addUserForm" action="{{ route('admin.users.store') }}" method="POST">
            @csrf
            <div class="modal-body">
                <div class="grid-2">
                    <div><label class="field-label">{{__('Name')}}</label><input type="text" name="name" required class="field-input" placeholder="Full name"></div>
                    <div><label class="field-label">{{ __('Email')}}</label><input type="email" name="email" required class="field-input" placeholder="email@example.com"></div>
                </div>
                <div class="grid-2">
                    <div><label class="field-label">{{ __('Password')}}</label><input type="password" name="password" required class="field-input" placeholder="Min 8 chars"></div>
                    <div>
                        <label class="field-label">{{ __('Plan')}}</label>
                        <select name="plan_id" class="field-input" style="cursor:pointer;">
                            @foreach($plans as $plan)
                                <option value="{{ $plan->id }}">{{ $plan->name }} — ${{ $plan->price }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn-save">{{ __('Save user')}}</button>
                    <button type="button" class="btn-cancel-sm" onclick="closeModal('addUserModal')">{{ __('Cancel')}}</button>
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
                <label class="field-label">{{__('New plan')}}</label>
                <select id="changePlanSelect" class="field-input" style="cursor:pointer;">
                    @foreach($plans as $plan)
                        <option value="{{ $plan->id }}">{{  __($plan->name )}} — ${{ $plan->price }}/mo</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="field-label">{{ __('Expires at (optional)')}}</label>
                <input type="date" id="changePlanExpiry" class="field-input" min="{{ now()->toDateString() }}">
            </div>
            <div class="modal-footer">
                <button class="btn-save" onclick="submitChangePlan()">{{ __('Update plan')}}</button>
                <button class="btn-cancel-sm" onclick="closeModal('changePlanModal')">{{ __('No update')}}</button>
            </div>
        </div>
    </div>
</div>


<div id="planModal" class="modal-backdrop">
    <div class="modal-inner" style="max-width:480px;">
        <div class="modal-head">
            <h3 id="planModalTitle">{{ __('New plan')}}</h3>
            <button class="modal-close" onclick="closeModal('planModal')">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="planModalId">
            <div class="grid-2">
                <div><label class="field-label">{{ __('Name')}}</label><input id="planModalName" class="field-input" placeholder="Pro" readonly></div>
                <div><label class="field-label">{{ __('Slug')}}</label><input id="planModalSlug" class="field-input" placeholder="pro"></div>
            </div>
            <div class="grid-2">
                <div><label class="field-label">{{ __('Price ($/mo)')}}</label><input type="number" id="planModalPrice" class="field-input" placeholder="9.99" step="0.01" min="0"></div>
                <div><label class="field-label">{{__('Stripe Price ID')}}</label><input id="planModalStripeId" class="field-input" placeholder="price_xxx..."></div>
            </div>
            <div class="grid-2">
                <div><label class="field-label">{{ __('Posts/month limit')}}</label><input type="number" id="planModalPosts" class="field-input" min="1"></div>
                <div><label class="field-label">{{ __('Pages limit')}}</label><input type="number" id="planModalPages" class="field-input" min="1"></div>
            </div>
            <div style="display:flex;align-items:center;gap:8px;">
                <input type="checkbox" id="planModalActive" checked style="width:16px;height:16px;">
                <label for="planModalActive" class="field-label" style="margin:0;">{{__('Active')}}</label>
            </div>
            <div class="modal-footer">
                <button class="btn-save" onclick="submitPlan()">{{__('Save plan')}}</button>
                <button class="btn-cancel-sm" onclick="closeModal('planModal')">{{__('Cancel')}}</button>
            </div>
        </div>
    </div>
</div>