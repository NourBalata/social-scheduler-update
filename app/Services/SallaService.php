<?php
namespace App\Services;

use App\Models\SallaAccount;
use App\Models\SallaProduct;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SallaService
{
    private string $baseUrl  = 'https://api.salla.dev/admin/v2';
    private string $authUrl  = 'https://accounts.salla.sa/oauth2/auth';
    private string $tokenUrl = 'https://accounts.salla.sa/oauth2/token';

    public function getAuthUrl(): string
    {
        return $this->authUrl . '?' . http_build_query([
            'client_id'     => config('services.salla.client_id'),
            'redirect_uri'  => config('services.salla.redirect'),
            'response_type' => 'code',
            'scope'         => 'offline_access',
            'state'         => csrf_token(),
        ]);
    }

    public function exchangeCode(string $code): array
    {
        $response = Http::asForm()->post($this->tokenUrl, [
            'client_id'     => config('services.salla.client_id'),
            'client_secret' => config('services.salla.client_secret'),
            'grant_type'    => 'authorization_code',
            'redirect_uri'  => config('services.salla.redirect'),
            'code'          => $code,
        ]);
        if ($response->failed()) throw new \Exception('Failed to get Salla token.');
        return $response->json();
    }

    public function refreshToken(SallaAccount $account): void
    {
        $response = Http::asForm()->post($this->tokenUrl, [
            'client_id'     => config('services.salla.client_id'),
            'client_secret' => config('services.salla.client_secret'),
            'grant_type'    => 'refresh_token',
            'refresh_token' => $account->refresh_token,
        ]);
        if ($response->failed()) throw new \Exception('Failed to refresh token.');
        $data = $response->json();
        $account->update([
            'access_token'     => $data['access_token'],
            'refresh_token'    => $data['refresh_token'] ?? $account->refresh_token,
            'token_expires_at' => now()->addSeconds($data['expires_in'] ?? 7776000),
        ]);
    }

    public function getMerchantInfo(string $token): array
    {
        $response = Http::withToken($token)->get("{$this->baseUrl}/store");
        if ($response->failed())
            $response = Http::withToken($token)->get("{$this->baseUrl}/store/info");
        if ($response->failed()) throw new \Exception('Failed to fetch store info.');
        return $response->json('data') ?? $response->json() ?? [];
    }

    public function syncProducts(SallaAccount $account): int
    {
        if ($account->isTokenExpired()) { $this->refreshToken($account); $account->refresh(); }

        $page = 1; $total = 0;
        do {
            $data     = Http::withToken($account->access_token)
                            ->get("{$this->baseUrl}/products", ['page' => $page, 'per_page' => 20, 'status' => 'active'])
                            ->json();
            $products = $data['data'] ?? [];
            if (empty($products)) break;

            foreach ($products as $item) {
                SallaProduct::updateOrCreate(
                    ['salla_account_id' => $account->id, 'salla_product_id' => (string) $item['id']],
                    [
                        'name'        => $item['name'] ?? '',
                        'description' => strip_tags($item['description'] ?? ''),
                        'price'       => $item['price']['amount'] ?? null,
                        'currency'    => $item['price']['currency'] ?? 'SAR',
                        'sale_price'  => $item['sale_price']['amount'] ?? null,
                        'image_url'   => $item['images'][0]['url'] ?? null,
                        'product_url' => $item['url'] ?? null,
                        'status'      => $item['status'] ?? 'active',
                    ]
                );
                $total++;
            }
            $lastPage = $data['pagination']['totalPages'] ?? 1;
            $page++;
        } while ($page <= $lastPage && $page <= 5);

        return $total;
    }

    public function connectStore(User $user, string $code): SallaAccount
    {
        $tokenData    = $this->exchangeCode($code);
        $merchantInfo = $this->getMerchantInfo($tokenData['access_token']);

        $account = SallaAccount::updateOrCreate(
            ['salla_merchant_id' => (string) ($merchantInfo['id'] ?? uniqid())],
            [
                'user_id'          => $user->id,
                'store_name'       => $merchantInfo['name'] ?? 'My Salla Store',
                'store_email'      => $merchantInfo['email'] ?? null,
                'store_avatar'     => $merchantInfo['logo'] ?? null,
                'store_url'        => $merchantInfo['domain'] ?? null,
                'access_token'     => $tokenData['access_token'],
                'refresh_token'    => $tokenData['refresh_token'] ?? null,
                'token_expires_at' => now()->addSeconds($tokenData['expires_in'] ?? 7776000),
            ]
        );

        try { $this->syncProducts($account); } catch (\Exception $e) { Log::warning($e->getMessage()); }

        return $account;
    }
}