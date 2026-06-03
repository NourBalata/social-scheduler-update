<?php

namespace App\Console\Commands;

use App\Contracts\SocialMediaProvider;
use App\Models\FacebookPage;
use Illuminate\Console\Command;

class CheckFacebookTokens extends Command
{
    protected $signature   = 'facebook:check-tokens';
    protected $description = 'Check validity of all active Facebook page access tokens.';

    public function __construct(private readonly SocialMediaProvider $facebook)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $pages = FacebookPage::where('is_active', true)
            ->with('user')
            ->get();

        if ($pages->isEmpty()) {
            $this->warn('No active Facebook pages found.');
            return self::SUCCESS;
        }

        $this->info("Checking {$pages->count()} active page(s)...");

        $valid   = 0;
        $invalid = 0;

        foreach ($pages as $page) {
            $this->line('');
            $this->info("Page : {$page->page_name} (ID: {$page->id})");
            $this->line("Owner: {$page->user->name} <{$page->user->email}>");

            if (empty($page->access_token)) {
                $this->error('  ✗ No token stored.');
                $invalid++;
                continue;
            }

            try {
                if ($this->facebook->validateToken($page->access_token)) {
                    $this->info('  ✓ Token valid.');
                    $valid++;
                } else {
                    $this->error('  ✗ Token invalid or expired.');
                    $invalid++;
                }
            } catch (\Exception $e) {
                $this->error("  ✗ Error: {$e->getMessage()}");
                $invalid++;
            }
        }

        $this->line('');
        $this->info("Summary: {$valid} valid, {$invalid} invalid.");

        return $invalid > 0 ? self::FAILURE : self::SUCCESS;
    }
}