<?php

namespace App\Console\Commands;

use App\Jobs\GenerateRepostJob;
use App\Models\RepostRule;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProcessRepostsCommand extends Command
{
    protected $signature   = 'reposts:process';
    protected $description = 'Check due repost rules and dispatch GenerateRepostJob for each one';

    public function handle(): void
    {
        $rules = RepostRule::due()
            ->with(['originalPost', 'user', 'facebookPage'])
            ->get();

        if ($rules->isEmpty()) {
            $this->info('No repost rules due.');
            return;
        }

        $this->info("Found {$rules->count()} rule(s) to process...");

        foreach ($rules as $rule) {
            // تحقق إن الصفحة والبوست الأصلي لسا موجودين
            if (! $rule->originalPost || ! $rule->facebookPage) {
                $this->warn("Rule #{$rule->id} skipped — missing post or page.");
                $rule->update(['is_active' => false]);
                continue;
            }

            GenerateRepostJob::dispatch($rule);

            $this->line("  ✓ Dispatched job for rule #{$rule->id} (user: {$rule->user_id})");

            Log::info('ProcessRepostsCommand: job dispatched', [
                'rule_id' => $rule->id,
                'user_id' => $rule->user_id,
            ]);
        }

        $this->info('Done.');
    }
}