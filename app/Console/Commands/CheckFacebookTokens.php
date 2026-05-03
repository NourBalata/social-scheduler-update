<?php

namespace App\Console\Commands;

use App\Contracts\SocialMediaProvider;
use App\Models\FacebookPage;
use Illuminate\Console\Command;

class CheckFacebookTokens extends Command
{
    protected $signature   = 'facebook:check-tokens';
    protected $description = 'Check the validity of all stored Facebook page access tokens.';

    public function __construct(private readonly SocialMediaProvider $facebook)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $pages = FacebookPage::with('user')->get();

        if ($pages->isEmpty()) {
            $this->warn('No Facebook pages found.');
            return self::SUCCESS;
        }

        $this->info("Checking {$pages->count()} page(s)...");

        foreach ($pages as $page) {
            $this->line('');
            $this->info("Page : {$page->page_name} (DB ID: {$page->id})");
            $this->line("Owner: {$page->user->name} <{$page->user->email}>");

            if (empty($page->access_token)) {
                $this->error('  No token stored.');
                continue;
            }

            $this->checkToken($page);
        }

        return self::SUCCESS;
    }

    private function checkToken(FacebookPage $page): void
    {
        try {
            $valid = $this->facebook->validateToken($page->access_token);

            if ($valid) {
                $this->info('سToken is valid.');
            } else {
                $this->error('Token is invalid or expired.');
            }
        } catch (\Exception $e) {
            $this->error("  ✗ Error: {$e->getMessage()}");
        }
    }
}