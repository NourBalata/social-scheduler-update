<div
    id="autopilot-app"
    data-pages='@json($user->facebookPages->pluck("page_name"))'
    data-csrf="{{ csrf_token() }}"
    data-route-generate="{{ route('autopilot.generate') }}"
    data-route-confirm="{{ route('autopilot.confirm') }}"
></div>