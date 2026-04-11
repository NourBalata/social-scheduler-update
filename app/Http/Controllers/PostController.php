<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePostRequest;
use App\Models\ScheduledPost; // تم التأكد من اسم الموديل الصحيح
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Http\Request;
class PostController extends Controller
{
    // public function store(StorePostRequest $request)
    // {
    //     $user = auth()->user();

    //     // 1. فحص الاشتراك (تأكدي أن الميثود موجودة في موديل User)
    //     if (!$user->canSchedulePost()) {
    //         return response()->json([
    //             'status' => 'error', 
    //             'message' => 'لقد وصلت للحد الأقصى لخطتك الحالية. يرجى الترقية!'
    //         ], 403);
    //     }

    //     try {
    //         // 2. التخزين باستخدام الموديل الموحد
    //         $post = ScheduledPost::create([
    //             'user_id'          => $user->id,
    //             'content'          => $request->content,
    //             'scheduled_at'     => Carbon::parse($request->scheduled_at),
    //             'status'           => 'pending',
    //             'facebook_page_id' => $request->facebook_page_id,
    //         ]);

    //         return response()->json([
    //             'status' => 'success',
    //             'message' => 'تمت جدولة المنشور بنجاح!',
    //             'data' => $post
    //         ]);

    //     } catch (\Exception $e) {
    //         // في حال فشل التخزين (مثلاً نقص حقل في الداتابيز)
    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'حدث خطأ تقني أثناء التخزين: ' . $e->getMessage()
    //         ], 500);
    //     }
    // }

//    public function store(Request $request)
// {

// // dd($request->all());
//   // 1. تنظيف الاسم الذي كتبه المستخدم
//     $pageName = trim($request->page_name);

//     // 2. البحث عن الصفحة (تأكدي أن الاسم في الداتابيز هو فعلاً "وسيم تست")
//     $page = \App\Models\FacebookPage::where('page_name', $pageName)->first();

//     if (!$page) {
//         // هذه الرسالة ستخبرنا فوراً إذا كان هناك اختلاف في حرف أو مسافة
//         return back()->with('error', "خطأ: لم نجد صفحة مربوطة باسم [{$pageName}].");
//     }

//     // 3. الحفظ الفعلي
//     $post = new \App\Models\ScheduledPost();
//     $post->user_id = auth()->id();
//     $post->facebook_page_id = $page->id;
//     $post->content = $request->content;
//     $post->scheduled_at = $request->scheduled_at ?? now();
//     $post->status = 'pending';
//     $post->save();

//     return redirect()->route('dashboard')->with('success', 'تم حفظ المنشور رقم 10 بنجاح!');
// }


public function store(Request $request)
{
    // 1. البحث عن الصفحة مع تجاهل المسافات الزائدة
    $page = \App\Models\FacebookPage::where('page_name', 'LIKE', '%' . trim($request->page_name) . '%')->first();

    // 2. إذا لم يجد الصفحة، سنوقف الكود لنعرف السبب (مؤقتاً للتيست)
    if (!$page) {
        return "لم نجد صفحة باسم: " . $request->page_name . ". الصفحات المتاحة في الداتابيز هي: " . \App\Models\FacebookPage::pluck('page_name')->implode(', ');
    }

    // 3. الحفظ المباشر (Direct Save) لتجاوز الـ fillable
    $post = new \App\Models\ScheduledPost();
    $post->user_id = auth()->id() ?? 1; // إذا كنتِ تجربين بدون لوجن ضعي آيدي المستخدم يدوياً
    $post->facebook_page_id = $page->id;
    $post->content = $request->content;
    $post->scheduled_at = $request->scheduled_at ?? now();
    $post->status = 'pending';
    
    if ($post->save()) {
       return back()->with('success','تم حفظ المنشور بنجاح! رقم المنشور هو: ');
    }

    return "فشل.";
}
}