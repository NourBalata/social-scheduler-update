import { createApp, ref } from 'vue'
import AutopilotModal from './components/AutopilotModal.vue'

/**
 * Mount the AutopilotModal Vue app on a dedicated element.
 * The Blade template passes config via data-* attributes.
 *
 * Usage in Blade:
 *   <div
 *     id="autopilot-app"
 *     data-pages='@json($user->facebookPages->pluck("page_name"))'
 *     data-csrf="{{ csrf_token() }}"
 *     data-route-generate="{{ route('autopilot.generate') }}"
 *     data-route-confirm="{{ route('autopilot.confirm') }}"
 *   ></div>
 */

document.addEventListener('DOMContentLoaded', () => {
    const mountEl = document.getElementById('autopilot-app')
    if (!mountEl) return

    // Read config from data attributes
    const pages   = JSON.parse(mountEl.dataset.pages   || '[]')
    const csrf    = mountEl.dataset.csrf   || ''
    const routes  = {
        generate: mountEl.dataset.routeGenerate || '/autopilot/generate',
        confirm:  mountEl.dataset.routeConfirm  || '/autopilot/confirm',
    }

    // Reactive visibility flag – exposed on window so Blade JS can call it
    const show = ref(false)

    const app = createApp({
        setup() {
            return { show, pages, csrf, routes }
        },
        template: `
            <AutopilotModal
                v-model:show="show"
                :pages="pages"
                :csrf="csrf"
                :routes="routes"
                @scheduled="onScheduled"
            />
        `,
        methods: {
            onScheduled(posts) {
                // Fire a custom DOM event so the FullCalendar code in Blade can react
                document.dispatchEvent(new CustomEvent('autopilot:scheduled', { detail: posts }))
            },
        },
    })

    app.component('AutopilotModal', AutopilotModal)
    app.mount(mountEl)

    // Expose open/close globally so the old Blade onclick handlers still work
    window.openAutopilotModal  = () => { show.value = true  }
    window.closeAutopilotModal = () => { show.value = false }
})