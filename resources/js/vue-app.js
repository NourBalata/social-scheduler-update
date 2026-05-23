import { createApp, ref } from 'vue';
import AutopilotModal from './components/AutopilotModal.vue';
import DateClickModal from './components/DateClickModal.vue';

const vueApp = createApp({
    setup() {
        const autopilotRef = ref(null);
        const dateClickRef = ref(null);

        window.openAutopilotModal = () => autopilotRef.value?.open();
        window.openDateClickModal = (date) => dateClickRef.value?.open(date);

        function onPostsScheduled({ posts, page }) {
            if (!window.calendarInstance) return;
            const colors = { educational:'#8b5cf6', promotional:'#f59e0b', entertainment:'#ec4899', engagement:'#06b6d4' };
            posts.forEach(p => {
                window.calendarInstance.addEvent({
                    title: p.content.slice(0, 25) + '...',
                    start: p.scheduled_at,
                    color: colors[p.post_type] || '#3b82f6',
                    extendedProps: { status:'pending', page, content:p.content, post_type:p.post_type },
                });
            });
        }

        function onPostSaved(event) {
            if (window.calendarInstance && event) {
                window.calendarInstance.addEvent(event);
            }
        }

        return { autopilotRef, dateClickRef, onPostsScheduled, onPostSaved };
    }
});

vueApp.component('AutopilotModal', AutopilotModal);
vueApp.component('DateClickModal', DateClickModal);
vueApp.mount('#vue-app');