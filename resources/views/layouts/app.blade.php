<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }}</title>
    
    @vite(['resources/css/app.css','resources/css/admin.css','resources/css/subsciber.css','resources/js/app.js','resources/js/INDEX.JS'])
</head>
<body class="bg-gray-50 font-sans antialiased">
    <div class="min-h-screen">
        <nav class="bg-white border-b border-gray-100">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
<div dir="ltr" class="flex w-full items-center justify-start px-4 py-6">
    <h2 class="text-2xl font-semibold tracking-tight text-slate-900">Dashboard</h2>
</div>
                </div>
            </div>
        </nav>
        @if (isset($header))
            <header class="bg-white shadow">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </header>
        @endif
        <main>
            {{ $slot }}
        </main>
    </div>
</body>
</html>