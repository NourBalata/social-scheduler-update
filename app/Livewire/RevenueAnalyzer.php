<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class RevenueAnalyzer extends Component
{
    use WithFileUploads;

    public array  $rows          = [];
    public string $aiExplanation = '';
    public bool   $loading       = false;
    public bool   $aiLoading     = false;
    public string $error         = '';
    public string $dateFrom      = '';
    public string $dateTo        = '';
    public $csvFile              = null;

    public array $chartLabels  = [];
    public array $chartValues  = [];
    public array $movingAvg    = [];
    public array $anomalies    = [];
    public float $totalRevenue = 0;
    public float $avgMonthly   = 0;
    public float $growthRate   = 0;

    // ─── Lifecycle ────────────────────────────────────────────────

    public function mount(): void
    {
        $this->rows = $this->loadFromDatabase();
        $this->analyze();
    }

    // ─── Watchers ────────────────────────────────────────────────

    public function updatedCsvFile(): void
    {
        $this->uploadCsv();
    }

    // ─── Date Range ───────────────────────────────────────────────

 public function applyDateRange(): void
{
    $this->error         = '';
    $this->aiExplanation = '';

    if (empty($this->dateFrom) || empty($this->dateTo)) {
        $this->rows = $this->loadFromDatabase();
        $this->analyze();
        return;
    }

    if ($this->dateFrom > $this->dateTo) {
        $this->error = 'Start date must be before end date.';
        return;
    }

    // دايماً من أول الشهر لآخره
    $from = Carbon::parse($this->dateFrom)->startOfMonth()->toDateTimeString();
    $to   = Carbon::parse($this->dateTo)->endOfMonth()->toDateTimeString();

    $rows = $this->loadFromDatabase($from, $to);

    if (empty($rows)) {
        $this->error = 'No data found in this date range. Available data: Jan 2024 → Dec 2024.';
        return;
    }

    $this->rows = $rows;
    $this->analyze();
}
    // ─── Quick Shortcuts ──────────────────────────────────────────

    public function setQuickRange(string $range): void
    {
        $this->error         = '';
        $this->aiExplanation = '';

        if ($range === 'all') {
            $this->dateFrom = '';
            $this->dateTo   = '';
            $this->rows     = $this->loadFromDatabase();
            $this->analyze();
            return;
        }

        // احسب من آخر تاريخ موجود في DB
        $lastDate = DB::table('subscription_invoices')
            ->where('status', 'paid')
            ->whereNotNull('paid_at')
            ->max('paid_at');

        if (!$lastDate) {
            $this->error = 'No data found.';
            return;
        }

        $to   = Carbon::parse($lastDate);
        $from = match ($range) {
            '3m'    => $to->copy()->subMonths(3),
            '6m'    => $to->copy()->subMonths(6),
            '1y'    => $to->copy()->subMonths(12),
            default => null,
        };

        if (!$from) {
            $this->rows = $this->loadFromDatabase();
            $this->analyze();
            return;
        }

        $this->dateFrom = $from->format('Y-m-d');
        $this->dateTo   = $to->format('Y-m-d');

        $rows = $this->loadFromDatabase(
            $from->startOfDay()->toDateTimeString(),
            $to->endOfDay()->toDateTimeString()
        );

        if (empty($rows)) {
            $this->error = 'No data found in the selected date range.';
            return;
        }

        $this->rows = $rows;
        $this->analyze();
    }

    // ─── Data Loading ─────────────────────────────────────────────

    private function loadFromDatabase(?string $from = null, ?string $to = null): array
    {
        $query = DB::table('subscription_invoices')
            ->where('status', 'paid')
            ->whereNotNull('paid_at');

        if ($from && $to) {
            $query->whereBetween('paid_at', [$from, $to]);
        }

        return $query
            ->selectRaw("DATE_FORMAT(paid_at, '%b %Y') as month")
            ->selectRaw("YEAR(paid_at) as year")
            ->selectRaw("MONTH(paid_at) as month_num")
            ->selectRaw("SUM(amount) as revenue")
            ->groupByRaw("year, month_num, DATE_FORMAT(paid_at, '%b %Y')")
            ->orderByRaw("year, month_num")
            ->get()
            ->map(fn($r) => [
                'month'   => $r->month,
                'revenue' => (float) $r->revenue,
            ])
            ->toArray();
    }

    // ─── Analysis ─────────────────────────────────────────────────

    public function analyze(): void
    {
        if (empty($this->rows)) return;

        $values = array_column($this->rows, 'revenue');
        $n      = count($values);

        $ma = [];
        for ($i = 0; $i < $n; $i++) {
            $start = max(0, $i - 1);
            $end   = min($n - 1, $i + 1);
            $slice = array_slice($values, $start, $end - $start + 1);
            $ma[]  = round(array_sum($slice) / count($slice));
        }

        $anomalies = [];
        for ($i = 0; $i < $n; $i++) {
            if ($ma[$i] > 0) {
                $deviation = abs($values[$i] - $ma[$i]) / $ma[$i];
                if ($deviation > 0.20) {
                    $anomalies[] = $i;
                }
            }
        }

        $this->chartLabels  = array_column($this->rows, 'month');
        $this->chartValues  = array_map('intval', $values);
        $this->movingAvg    = $ma;
        $this->anomalies    = $anomalies;
        $this->totalRevenue = array_sum($values);
        $this->avgMonthly   = $n > 0 ? round($this->totalRevenue / $n) : 0;

        if ($n >= 2 && $values[$n - 2] > 0) {
            $this->growthRate = round((($values[$n - 1] - $values[$n - 2]) / $values[$n - 2]) * 100, 1);
        } else {
            $this->growthRate = 0;
        }

        $this->aiExplanation = '';
    }

    // ─── CSV Upload ───────────────────────────────────────────────

    public function uploadCsv(): void
    {
        $this->error = '';

        $this->validate(['csvFile' => 'file|mimes:csv,txt']);

        $tmpFile = $this->csvFile->getRealPath();
        $handle  = fopen($tmpFile, 'r');
        fgetcsv($handle); // skip header row
        $rows = [];

        while (($cols = fgetcsv($handle)) !== false) {
            if (count($cols) < 2) continue;
            $rows[] = [
                'month'   => trim($cols[0]),
                'revenue' => (float) str_replace([',', '$'], '', trim($cols[1])),
            ];
        }
        fclose($handle);

        if (empty($rows)) {
            $this->error = 'CSV appears empty or malformed.';
            return;
        }

        $this->rows     = $rows;
        $this->dateFrom = '';
        $this->dateTo   = '';
        $this->analyze();
    }

    // ─── AI Analysis ──────────────────────────────────────────────

    public function explainWithAI(): void
    {
        $this->aiLoading     = true;
        $this->aiExplanation = '';

        $anomalyDescriptions = [];
        foreach ($this->anomalies as $idx) {
            $row  = $this->rows[$idx];
            $ma   = $this->movingAvg[$idx];
            $dev  = $ma > 0 ? round((($row['revenue'] - $ma) / $ma) * 100, 1) : 0;
            $dir  = $dev > 0 ? 'spike' : 'drop';
            $anomalyDescriptions[] = "{$row['month']}: \${$row['revenue']} ({$dev}% {$dir} vs moving avg \${$ma})";
        }

        $dataStr = implode(', ', array_map(
            fn ($r) => "{$r['month']}: \${$r['revenue']}",
            $this->rows
        ));

        $rangeNote = ($this->dateFrom && $this->dateTo)
            ? " (filtered range: {$this->dateFrom} → {$this->dateTo})"
            : '';

        $prompt = "You are a fiscal analyst. Analyze this monthly revenue data{$rangeNote}: {$dataStr}. "
            . "Anomalies detected (>20% deviation from 3-month moving average): "
            . (empty($anomalyDescriptions) ? 'none' : implode('; ', $anomalyDescriptions)) . ". "
            . "In 3-4 sentences explain: (1) overall trend, (2) possible causes for anomalies, "
            . "(3) one actionable recommendation. Be concise and professional.";

        $result = $this->tryGemini($prompt) ?? $this->tryGroq($prompt);

        $this->aiExplanation = $result ?? 'AI service unavailable. Please try again later.';
        $this->aiLoading     = false;
    }

    private function tryGemini(string $prompt): ?string
    {
        $keys = array_filter([
            config('services.gemini.key1'),
            config('services.gemini.key2'),
            config('services.gemini.key3'),
        ]);

        foreach ($keys as $key) {
            try {
                $response = Http::timeout(30)->post(
                    "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$key}",
                    ['contents' => [['parts' => [['text' => $prompt]]]]]
                );
                $text = $response->json('candidates.0.content.parts.0.text');
                if ($text) return trim($text);
            } catch (\Exception $e) {
                continue;
            }
        }

        return null;
    }

    private function tryGroq(string $prompt): ?string
    {
        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . config('services.groq.key'),
                    'Content-Type'  => 'application/json',
                ])
                ->post('https://api.groq.com/openai/v1/chat/completions', [
                    'model'      => 'llama-3.1-8b-instant',
                    'messages'   => [['role' => 'user', 'content' => $prompt]],
                    'max_tokens' => 300,
                ]);

            return $response->json('choices.0.message.content');
        } catch (\Exception $e) {
            return null;
        }
    }

    // ─── Reset ────────────────────────────────────────────────────

    public function resetToAll(): void
    {
        $this->dateFrom      = '';
        $this->dateTo        = '';
        $this->aiExplanation = '';
        $this->error         = '';
        $this->csvFile       = null;
        $this->rows          = $this->loadFromDatabase();
        $this->analyze();
    }

    // ─── Render ───────────────────────────────────────────────────

    public function render()
    {
        return view('livewire.revenue-analyzer');
    }
}