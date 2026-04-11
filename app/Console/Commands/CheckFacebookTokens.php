<?php

namespace App\Console\Commands;

use App\Models\FacebookPage;
use Illuminate\Console\Command;

class CheckFacebookTokens extends Command
{
    protected $signature = 'fb:check-tokens';
    protected $description = 'Check Facebook page tokens status';

    public function handle()
    {
        $this->info('--- فحص حالة Facebook Tokens ---');
        
        $pages = FacebookPage::with('user')->get();
        
        if ($pages->isEmpty()) {
            $this->warn('  لا توجد صفحات Facebook مربوطة');
            return;
        }

        foreach ($pages as $page) {
            $this->line('');
            $this->info("الصفحة: {$page->page_name} (ID: {$page->id})");
            $this->line("المستخدم: {$page->user->name} ({$page->user->email})");
            
            if (empty($page->access_token)) {
                $this->error("❌ Token مفقود!");
            } else {
                $tokenPreview = substr($page->access_token, 0, 20) . '...';
                $this->info("✅ Token موجود: {$tokenPreview}");
                
                // Check if token is valid
                $this->checkTokenValidity($page);
            }
        }
    }

    private function checkTokenValidity($page)
    {
        try {
            $response = \Http::get("https://graph.facebook.com/v18.0/me", [
                'access_token' => $page->access_token
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $this->info("✅ Token صالح - Page ID: {$data['id']}");
            } else {
                $error = $response->json();
                $this->error("❌ Token غير صالح: " . ($error['error']['message'] ?? 'Unknown error'));
            }
        } catch (\Exception $e) {
            $this->error("❌ خطأ في الفحص: {$e->getMessage()}");
        }
    }
}