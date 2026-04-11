<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            {{-- <h2 class="font-bold text-2xl text-gray-800">لوحة التحكم</h2> --}}

            <div class="flex items-center gap-6">
                <div class="flex items-center gap-2">
                    <span class="text-sm text-gray-600">أهلاً، {{ auth()->user()->name }}</span>
                    <div class="w-8 h-8 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center font-bold">
                        {{ substr(auth()->user()->name, 0, 1) }}
                    </div>
                </div>

                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit" class="text-red-600 hover:text-red-800 font-medium text-sm flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        تسجيل الخروج
                    </button>
                </form>
            </div>
        </div>
    </x-slot>

    <div class="py-8" dir="rtl">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8 text-right">
                <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100 transition hover:shadow-md">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm">المنشورات المتبقية</p>
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
                            <p class="text-gray-500 text-sm">الصفحات المربوطة</p>
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

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 text-right">
                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                    <h3 class="font-bold text-gray-800 mb-4 text-lg flex items-center gap-2 border-b pb-3">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                        </svg>
                        صفحاتك النشطة
                    </h3>
                    <ul class="space-y-3">
                        @forelse(auth()->user()->pages as $page)
                            <li class="flex items-center justify-between p-3 bg-gray-50 rounded-lg group hover:bg-blue-50 transition border border-transparent hover:border-blue-100">
                                <span class="text-sm font-medium text-gray-700">{{ $page->page_name }}</span>
                                <span class="flex items-center gap-1">
                                    <span class="text-[10px] text-green-600 font-bold">نشط</span>
                                    <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span>
                                </span>
                            </li>
                        @empty
                            <li class="text-gray-400 text-sm text-center py-4 italic">لا توجد صفحات مربوطة.</li>
                        @endforelse
                    </ul>
                </div>

                <div class="lg:col-span-2">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8">
                        <h3 class="text-xl font-bold text-gray-800 mb-6 flex items-center gap-2">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            جدولة منشور جديد
                        </h3>
                        
                        <form action="{{ route('posts.store') }}" method="POST" class="space-y-6">
                            @csrf
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                            
    <label class="block text-sm font-semibold text-gray-700 mb-2">اسم صفحة الفيسبوك</label>
    <input type="text" 
           name="page_name" 
           placeholder="أدخل اسم الصفحة (مثلاً: United Investment)" 
           class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 outline-none transition"
           required>
    <span data-field="page_name" class="error-msg text-red-500 text-xs mt-1 hidden"></span>
</div>
                             

                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">تاريخ ووقت النشر</label>
                                    <input type="datetime-local" name="scheduled_at" class="w-full border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent py-3 transition">
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">محتوى المنشور</label>
                                <textarea name="content" rows="4" class="w-full border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition p-4" placeholder="بماذا تفكر؟ اكتب محتوى منشورك هنا..."></textarea>
                            </div>

                            <div class="flex justify-end">
                                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold px-8 py-3 rounded-xl transition-all shadow-lg hover:shadow-blue-200 active:transform active:scale-95 flex items-center gap-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" /></svg>
                                    تأكيد الجدولة
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>