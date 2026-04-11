<x-app-layout>
     <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    {{ __("You're logged in!") }}
                </div>
            </div>
        </div>
    </div>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // إذا كان هناك رسالة نجاح قادمة من لارافيل (Session)
        @if(session('success'))
            showNotification("{{ session('success') }}", 'success');
        @endif

        // إذا كان هناك خطأ قادم من لارافيل
        @if(session('error'))
            showNotification("{{ session('error') }}", 'error');
        @endif
    });
</script>
       @vite(['resources/js/admin/users.js'])
</x-app-layout>
