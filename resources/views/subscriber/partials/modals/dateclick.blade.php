<date-click-modal
    ref="dateClickRef"
    :pages="{{ json_encode($pages ?? []) }}"
    csrf="{{ csrf_token() }}"
    generate-single-route="{{ route('autopilot.generate.single') }}"
    confirm-single-route="{{ route('autopilot.confirm.single') }}"
    @post-saved="onPostSaved"
></date-click-modal>