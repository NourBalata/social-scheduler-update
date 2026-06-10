<div
    id="react-autopilot-root"
    data-pages='@json($user->facebookPages->pluck("page_name"))'
    data-csrf="{{ csrf_token() }}"
    data-locale="{{ app()->getLocale() }}"
    data-route-generate="{{ route('autopilot.generate') }}"
    data-route-confirm="{{ route('autopilot.confirm') }}"
    data-route-generate-single="{{ route('autopilot.generate.single') }}"
    data-route-confirm-single="{{ route('autopilot.confirm.single') }}"
></div>