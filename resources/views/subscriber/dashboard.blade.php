<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between" dir="ltr">
            <div class="flex items-center gap-6">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center font-bold">
                        {{ substr(auth()->user()->name, 0, 1) }}
                    </div>
                    <span class="text-sm text-gray-600">Welcome, {{ auth()->user()->name }}</span>
                </div>

                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit" class="text-red-600 hover:text-red-800 font-medium text-sm flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        Logout
                    </button>
                </form>
            </div>
        </div>
    </x-slot>

    <div class="py-8" dir="ltr">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-left">

            @if(session('success'))
                <div class="bg-green-100 text-green-700 p-4 rounded-lg mb-6 border border-green-200">
                    {{ session('success') }}
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100 transition hover:shadow-md">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm">Remaining Posts</p>
                            <p class="text-3xl font-bold text-gray-800 mt-1">{{ auth()->user()->remainingPostsCount() }}</p>
                        </div>
                        <div class="bg-blue-100 p-3 rounded-lg text-blue-600">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100 transition hover:shadow-md">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm">Linked Pages</p>
                            <p class="text-3xl font-bold text-gray-800 mt-1">{{ auth()->user()->pages->count() }}</p>
                        </div>
                        <div class="bg-blue-100 p-3 rounded-lg text-blue-600">
                            <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                    <h3 class="font-bold text-gray-800 mb-4 text-lg flex items-center gap-2 border-b pb-3">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                        </svg>
                        Active Pages
                    </h3>
                    <ul class="space-y-3">
                        @forelse(auth()->user()->pages as $page)
                            <li class="flex items-center justify-between p-3 bg-gray-50 rounded-lg group hover:bg-blue-50 transition border border-transparent hover:border-blue-100">
                                <span class="text-sm font-medium text-gray-700">{{ $page->page_name }}</span>
                                <span class="flex items-center gap-1">
                                    <span class="text-[10px] text-green-600 font-bold uppercase">Active</span>
                                    <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span>
                                </span>
                            </li>
                        @empty
                            <li class="text-gray-400 text-sm text-center py-4 italic">No connected pages.</li>
                        @endforelse
                    </ul>
                </div>

                <div class="lg:col-span-2">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8">
                        <h3 class="text-xl font-bold text-gray-800 mb-6 flex items-center gap-2">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            CREATE NEW POST
                        </h3>

                        {{-- تعديل: إضافة enctype لإرسال الملفات --}}
                        <form action="{{ route('posts.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                            @csrf
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Select Page</label>
                                    <div class="relative">
                                        <input type="text" name="page_name" list="existing_pages" placeholder="Choose or create page" value="{{ old('page_name') }}"
                                               class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 outline-none transition">
                                        <datalist id="existing_pages">
                                            @foreach(auth()->user()->pages as $page)
                                                <option value="{{ $page->page_name }}">
                                            @endforeach
                                        </datalist>
                                    </div>
                                    <div class="mt-2 text-left">
                                        <button type="button" id="openPageModalBtnQuick" class="text-blue-600 hover:text-blue-800 text-xs font-bold flex items-center gap-1">
                                            <span>+ Add a new page</span>
                                        </button>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Schedule Time</label>
                                    <input type="datetime-local" name="scheduled_at" value="{{ old('scheduled_at') }}"
                                           class="w-full border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 py-3 px-4 transition">
                                </div>
                            </div>

                            {{-- إضافة: حقل رفع الميديا --}}
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Post Media (Image/Video)</label>
                                <div class="flex items-center justify-center w-full">
                                    <label class="flex flex-col items-center justify-center w-full h-32 border-2 border-gray-300 border-dashed rounded-xl cursor-pointer bg-gray-50 hover:bg-gray-100 transition">
                                        <div class="flex flex-col items-center justify-center pt-5 pb-6 text-center" id="upload-placeholder">
                                            <svg class="w-8 h-8 mb-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                            </svg>
                                            <p class="text-xs text-gray-500">Click to upload media</p>
                                        </div>
                                        <input type="file" name="media" id="media" class="hidden" accept="image/*,video/*" />
                                        <div id="preview-container" class="hidden p-2 text-center">
                                            <p id="file-name-display" class="text-sm font-bold text-blue-600"></p>
                                            <p class="text-[10px] text-gray-400">Click to change</p>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Post Content</label>
                                <textarea name="content" rows="4" required class="w-full border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 transition p-4"
                                          placeholder="What's on your mind?">{{ old('content') }}</textarea>
                            </div>

                            <div class="flex justify-start">
                                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold px-8 py-3 rounded-xl transition-all shadow-lg flex items-center gap-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                                    </svg>
                                    Confirm Post
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Add Page --}}
    <div id="pageModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 p-4" dir="ltr">
        <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full text-left">
            <div class="sticky top-0 bg-white border-b border-gray-100 px-6 py-4 flex items-center justify-between">
                <h3 class="text-xl font-bold text-gray-800">Add New Page</h3>
                <button id="closePageModalBtn" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <form action="{{ route('pages.storeAnotherPage') }}" method="POST" class="p-6 space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Page ID</label>
                    <input type="text" name="page_id" placeholder="Enter ID..." class="w-full border border-gray-300 rounded-lg px-4 py-2.5 outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Page Name</label>
                    <input type="text" name="page_name" placeholder="Enter name..." class="w-full border border-gray-300 rounded-lg px-4 py-2.5 outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Page Access Token</label>
                    <input type="text" name="page_access_token" placeholder="Enter token..." class="w-full border border-gray-300 rounded-lg px-4 py-2.5 outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                </div>

                <div class="flex items-center gap-3 pt-4 border-t border-gray-100">
                    <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-lg transition">
                        Save Page
                    </button>
                    <button type="button" id="cancelPageModalBtn" class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Modal Logic
        const pageModal = document.getElementById('pageModal');
        const openPageModalBtn = document.getElementById('openPageModalBtnQuick');
        const closePageModalBtn = document.getElementById('closePageModalBtn');
        const cancelPageModalBtn = document.getElementById('cancelPageModalBtn');

        openPageModalBtn?.addEventListener('click', () => {
            pageModal.classList.remove('hidden');
            pageModal.classList.add('flex');
        });

        [closePageModalBtn, cancelPageModalBtn].forEach(btn => {
            btn?.addEventListener('click', () => {
                pageModal.classList.add('hidden');
                pageModal.classList.remove('flex');
            });
        });

        // Media Upload Logic
        const mediaInput = document.getElementById('media');
        const uploadPlaceholder = document.getElementById('upload-placeholder');
        const previewContainer = document.getElementById('preview-container');
        const fileNameDisplay = document.getElementById('file-name-display');

        mediaInput?.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                uploadPlaceholder.classList.add('hidden');
                previewContainer.classList.remove('hidden');
                fileNameDisplay.textContent = this.files[0].name;
            }
        });
    </script>
</x-app-layout>