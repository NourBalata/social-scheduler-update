<div class="max-w-2xl mx-auto p-6 bg-white shadow-md rounded-lg">
    <h2 class="text-2xl font-bold mb-6">إعدادات تطبيق فيسبوك</h2>
    
    <form action="{{ route('settings.save') }}" method="POST">
        @csrf
        <div class="mb-4">
            <label class="block text-gray-700">Facebook App ID</label>
            <input type="text" name="fb_client_id" value="{{ session('fb_client_id') }}" class="w-full p-2 border rounded" placeholder="أدخل الـ App ID هنا">
        </div>

        <div class="mb-4">
            <label class="block text-gray-700">Facebook App Secret</label>
            <input type="password" name="fb_client_secret" value="{{ session('fb_client_secret') }}" class="w-full p-2 border rounded" placeholder="أدخل الـ App Secret هنا">
        </div>

        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">حفظ الإعدادات</button>
    </form>
</div>