@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-gray-800">جدولة منشور جديد</h2>
                <span class="bg-blue-100 text-blue-800 text-xs font-semibold px-2.5 py-0.5 rounded">
                    خطة: {{ auth()->user()->plan->name }}
                </span>
            </div>

            <form action="{{ route('posts.store') }}" method="POST" id="postForm" class="space-y-6">
                @csrf
                
                <div>
                    <label for="facebook_page_id" class="block text-sm font-medium text-gray-700">انشر على صفحة:</label>
                    <select name="facebook_page_id" id="facebook_page_id" 
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        @foreach($pages as $page)
                            <option value="{{ $page->id }}">{{ $page->page_name }}</option>
                        @endforeach
                    </select>
                    @error('facebook_page_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="postContent" class="block text-sm font-medium text-gray-700">محتوى المنشور</label>
                    <textarea id="postContent" name="content" rows="6" 
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" 
                        placeholder="اكتب ما تريد نشره هنا..." required>{{ old('content') }}</textarea>
                    <div class="flex justify-between mt-1">
                        <p class="text-xs text-gray-500">يدعم النصوص فقط حالياً.</p>
                        <p class="text-sm {{ strlen(old('content')) > 5000 ? 'text-red-600' : 'text-gray-500' }}">
                            <span id="charCount">0</span> / 5000
                        </p>
                    </div>
                    @error('content') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="scheduledAt" class="block text-sm font-medium text-gray-700">تاريخ ووقت النشر</label>
                        <input type="datetime-local" name="scheduled_at" id="scheduledAt" value="{{ old('scheduled_at') }}"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" required>
                        @error('scheduled_at') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    
                    <div class="flex items-end">
                        <p class="text-xs text-gray-400 pb-2 italic">
                            * سيتم تنفيذ النشر تلقائياً عبر نظام الجدولة.
                        </p>
                    </div>
                </div>

                <div class="pt-4 border-t flex items-center justify-end space-x-3 rtl:space-x-reverse">
                    <a href="{{ route('posts.index') }}" class="text-gray-600 hover:underline">إلغاء</a>
                    <button type="submit" id="submitBtn" 
                        class="inline-flex justify-center py-2 px-6 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200">
                        جدولة الآن
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const postContent = document.getElementById('postContent');
    const charCount = document.getElementById('charCount');
    const scheduledAt = document.getElementById('scheduledAt');
    const postForm = document.getElementById('postForm');
    const submitBtn = document.getElementById('submitBtn');

    // 1. تحديث العداد عند التحميل (في حال وجود old value)
    charCount.textContent = postContent.value.length;

    // 2. عداد الحروف Real-time
    postContent.addEventListener('input', function() {
        const length = this.value.length;
        charCount.textContent = length;
        
        if (length > 5000) {
            charCount.classList.replace('text-gray-500', 'text-red-600');
            submitBtn.disabled = true;
            submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
        } else {
            charCount.classList.replace('text-red-600', 'text-gray-500');
            submitBtn.disabled = false;
            submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
        }
    });

    // 3. ضبط الحد الأدنى للوقت (منع الماضي)
    const now = new Date();
    // تعديل الوقت ليناسب Local ISO string
    now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
    scheduledAt.min = now.toISOString().slice(0, 16);

    // 4. حماية من الـ Double Click وإظهار Loading بسيط
    postForm.addEventListener('submit', function(e) {
        if(postContent.value.length > 5000) {
            e.preventDefault();
            alert('المحتوى طويل جداً!');
            return;
        }
        submitBtn.disabled = true;
        submitBtn.innerHTML = `
            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            جاري الحفظ...
        `;
    });
});
</script>
@endsection