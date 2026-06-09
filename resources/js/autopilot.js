import React, { useState } from 'react'
import { createRoot } from 'react-dom/client'
import AutopilotModal from './components/AutopilotModal.jsx'
import './components/AutopilotModal.css'

document.addEventListener('DOMContentLoaded', () => {
    const mountEl = document.getElementById('autopilot-app')
    if (!mountEl) return

    const pages  = JSON.parse(mountEl.dataset.pages  || '[]')
    const csrf   = mountEl.dataset.csrf  || ''
    const routes = {
        generate: mountEl.dataset.routeGenerate || '/autopilot/generate',
        confirm:  mountEl.dataset.routeConfirm  || '/autopilot/confirm',
    }

    let showModal = false
    let rootInstance = null

    function render() {
        rootInstance?.render(
            <AutopilotModal
                show={showModal}
                pages={pages}
                csrf={csrf}
                routes={routes}
                onClose={() => { showModal = false; render() }}
                onScheduled={(posts) => {
                    document.dispatchEvent(
                        new CustomEvent('autopilot:scheduled', { detail: posts })
                    )
                }}
            />
        )
    }

    rootInstance = createRoot(mountEl)
    render()

    // نفس الـ window functions القديمة — شغّالة من Blade
    window.openAutopilotModal  = () => { showModal = true;  render() }
    window.closeAutopilotModal = () => { showModal = false; render() }
})