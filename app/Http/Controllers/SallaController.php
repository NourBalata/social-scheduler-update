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

        $account = SallaAccount::updateOrCreate(
            ['user_id' => $user->id],
            [
                'salla_merchant_id' => 'demo_' . $user->id,
                'store_name'        => 'متجر تجريبي 🛍️',
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
            ['name' => 'عطر ورد الطائف',       'price' => 299, 'sale_price' => 199, 'description' => 'عطر فاخر من ورد الطائف الأصيل',        'image_url' => 'https://picsum.photos/seed/p1/400/400', 'status' => 'active', 'currency' => 'SAR'],
            ['name' => 'كريم مرطب فاخر',       'price' => 150, 'sale_price' => null,'description' => 'كريم مرطب للبشرة الجافة',             'image_url' => 'https://picsum.photos/seed/p2/400/400', 'status' => 'active', 'currency' => 'SAR'],
            ['name' => 'مجموعة عناية بالشعر',  'price' => 450, 'sale_price' => 380, 'description' => 'مجموعة متكاملة للعناية بالشعر',        'image_url' => 'https://picsum.photos/seed/p3/400/400', 'status' => 'active', 'currency' => 'SAR'],
            ['name' => 'سيروم فيتامين سي',      'price' => 220, 'sale_price' => null,'description' => 'سيروم مضيء ومضاد للأكسدة',            'image_url' => 'https://picsum.photos/seed/p4/400/400', 'status' => 'active', 'currency' => 'SAR'],
            ['name' => 'ماسك الطين الأخضر',    'price' => 89,  'sale_price' => 69,  'description' => 'ماسك طبيعي لتنظيف المسام',            'image_url' => 'https://picsum.photos/seed/p5/400/400', 'status' => 'active', 'currency' => 'SAR'],
            ['name' => 'زيت أرغان المغربي',    'price' => 180, 'sale_price' => null,'description' => 'زيت طبيعي 100% للبشرة والشعر',        'image_url' => 'https://picsum.photos/seed/p6/400/400', 'status' => 'active', 'currency' => 'SAR'],
        ];

        foreach ($demoProducts as $i => $p) {
            SallaProduct::updateOrCreate(
                ['salla_account_id' => $account->id, 'salla_product_id' => 'demo_' . ($i + 1)],
                $p
            );
        }

        return redirect()->route('dashboard')->with('success', '✅ تم ربط المتجر التجريبي!');
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
        $account = auth()->user()->sallaAccount;
        if (! $account) {
            return response()->json(['error' => 'No store connected.'], 404);
        }

        $account->update(['auto_post_enabled' => ! $account->auto_post_enabled]);

        return response()->json([
            'enabled' => $account->auto_post_enabled,
            'message' => $account->auto_post_enabled ? '✅ Auto-post enabled' : '🔕 Auto-post disabled',
        ]);
    }

    public function disconnect()
    {
        $account = auth()->user()->sallaAccount;
        if ($account) {
            $account->products()->delete();
            $account->delete();
        }

        return redirect()->route('dashboard')->with('success', 'تم فصل المتجر.');
    }
}