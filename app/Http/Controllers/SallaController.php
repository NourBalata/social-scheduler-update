<?php

namespace App\Http\Controllers;

use App\Models\SallaAccount;
use App\Models\SallaProduct;
use Illuminate\Http\Request;

class SallaController extends Controller
{
    public function redirect()
{
    $user = auth()->user();
    $locale = app()->getLocale(); // 'ar' or 'en'

    $account = SallaAccount::updateOrCreate(
        ['user_id' => $user->id],
        [
            'salla_merchant_id' => 'demo_' . $user->id,
            'store_name'        => $locale === 'ar' ? 'متجر سلة 🛍️' : 'Salla Store 🛍️',
            'store_email'       => $user->email,
            'store_avatar'      => null,
            'store_url'         => 'https://demo.salla.sa',
            'access_token'      => 'demo_token',
            'refresh_token'     => 'demo_refresh',
            'token_expires_at'  => now()->addYear(),
            'auto_post_enabled' => false,
        ]
    );

    $demoProducts = [
        [
            'name'        => $locale === 'ar' ? 'عطر ورد الطائف'      : 'Taif Rose Perfume',
            'description' => $locale === 'ar' ? 'عطر فاخر من ورد الطائف الأصيل' : 'Luxury authentic Taif rose perfume',
            'price' => 299, 'sale_price' => 199, 'image_url' => 'https://picsum.photos/seed/p1/400/400', 'status' => 'active', 'currency' => 'SAR',
        ],
        [
            'name'        => $locale === 'ar' ? 'كريم مرطب فاخر'      : 'Luxury Moisturizer',
            'description' => $locale === 'ar' ? 'كريم مرطب للبشرة الجافة' : 'Moisturizing cream for dry skin',
            'price' => 150, 'sale_price' => null, 'image_url' => 'https://picsum.photos/seed/p2/400/400', 'status' => 'active', 'currency' => 'SAR',
        ],
        [
            'name'        => $locale === 'ar' ? 'مجموعة عناية بالشعر' : 'Hair Care Set',
            'description' => $locale === 'ar' ? 'مجموعة متكاملة للعناية بالشعر' : 'Complete hair care collection',
            'price' => 450, 'sale_price' => 380, 'image_url' => 'https://picsum.photos/seed/p3/400/400', 'status' => 'active', 'currency' => 'SAR',
        ],
        [
            'name'        => $locale === 'ar' ? 'سيروم فيتامين سي'    : 'Vitamin C Serum',
            'description' => $locale === 'ar' ? 'سيروم مضيء ومضاد للأكسدة' : 'Brightening antioxidant serum',
            'price' => 220, 'sale_price' => null, 'image_url' => 'https://picsum.photos/seed/p4/400/400', 'status' => 'active', 'currency' => 'SAR',
        ],
        [
            'name'        => $locale === 'ar' ? 'ماسك الطين الأخضر'   : 'Green Clay Mask',
            'description' => $locale === 'ar' ? 'ماسك طبيعي لتنظيف المسام' : 'Natural pore-cleansing mask',
            'price' => 89, 'sale_price' => 69, 'image_url' => 'https://picsum.photos/seed/p5/400/400', 'status' => 'active', 'currency' => 'SAR',
        ],
        [
            'name'        => $locale === 'ar' ? 'زيت أرغان المغربي'   : 'Moroccan Argan Oil',
            'description' => $locale === 'ar' ? 'زيت طبيعي 100% للبشرة والشعر' : '100% natural oil for skin and hair',
            'price' => 180, 'sale_price' => null, 'image_url' => 'https://picsum.photos/seed/p6/400/400', 'status' => 'active', 'currency' => 'SAR',
        ],
    ];

    foreach ($demoProducts as $i => $p) {
        SallaProduct::updateOrCreate(
            ['salla_account_id' => $account->id, 'salla_product_id' => 'demo_' . ($i + 1)],
            $p
        );
    }

    return redirect()->route('dashboard')->with('success', $locale === 'ar' ? '✅ تم ربط المتجر التجريبي!' : '✅ Demo store connected!');
}

    public function products(Request $request)
    {
        $account = auth()->user()->sallaAccount;
        if (! $account) {
            return response()->json(['products' => [], 'has_more' => false]);
        }

        $products = $account->products()
            ->when($request->filled('search'), fn ($q) => $q->where('name', 'like', "%{$request->search}%"))
            ->where('status', 'active')
            ->latest()
            ->paginate(12);

        // نحول الحقول لتتطابق مع ما يتوقعه الـ JS في الـ view
        $items = collect($products->items())->map(fn ($p) => [
            'id'          => $p->id,
            'name'        => $p->name,
            'description' => $p->description,
            'price'       => $p->price,
            'sale_price'  => $p->sale_price,
            'currency'    => $p->currency ?? 'SAR',
            'image'       => $p->image_url,      // الـ view يستخدم "image" وليس "image_url"
            'image_url'   => $p->image_url,      // نبعثهما معاً لتجنب أي تعارض
            'url'         => $p->product_url,
            'product_url' => $p->product_url,
            'status'      => $p->status,
        ])->values()->all();

        return response()->json([
            'products' => $items,
            'has_more' => $products->hasMorePages(),
        ]);
    }

    public function syncProducts()
    {
        return response()->json(['success' => true, 'synced' => 6]);
    }

   public function toggleAutoPost()
{
    $locale = app()->getLocale();
    $account = auth()->user()->sallaAccount;

    if (! $account) {
        return response()->json(['error' => $locale === 'ar' ? 'لا يوجد متجر مرتبط.' : 'No store connected.'], 404);
    }

    $account->update(['auto_post_enabled' => ! $account->auto_post_enabled]);

    return response()->json([
        'enabled' => $account->auto_post_enabled,
        'message' => $account->auto_post_enabled
            ? ($locale === 'ar' ? '✅ تم تفعيل النشر التلقائي' : '✅ Auto-post enabled')
            : ($locale === 'ar' ? '🔕 تم إيقاف النشر التلقائي' : '🔕 Auto-post disabled'),
    ]);
}

public function disconnect()
{
    $locale = app()->getLocale();
    $account = auth()->user()->sallaAccount;

    if ($account) {
        $account->products()->delete();
        $account->delete();
    }

    $msg = $locale === 'ar' ? 'تم فصل المتجر.' : 'Store disconnected.';
    return redirect()->route('dashboard')->with('success', $msg);
}
}