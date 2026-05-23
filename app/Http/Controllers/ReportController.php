<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    public function download(Request $request)
    {
        $user  = auth()->user();
        $month = $request->integer('month', now()->month);
        $year  = $request->integer('year',  now()->year);

        $date      = Carbon::createFromDate($year, $month, 1);
        $monthName = $date->format('F Y');


        $posts = $user->scheduledPosts()
            ->whereYear('scheduled_at',  $year)
            ->whereMonth('scheduled_at', $month)
            ->with('facebookPage')
            ->orderBy('scheduled_at')
            ->get();

        $stats = [
            'total'     => $posts->count(),
            'published' => $posts->where('status', 'published')->count(),
            'failed'    => $posts->where('status', 'failed')->count(),
            'pending'   => $posts->whereIn('status', ['pending', 'processing'])->count(),
        ];

        $daysInMonth = $date->daysInMonth;
        $daily = collect(range(1, $daysInMonth))->map(function ($day) use ($posts, $year, $month) {
            $dayPosts = $posts->filter(fn($p) =>
                Carbon::parse($p->scheduled_at)->day === $day
            );
            return [
                'day'       => $day,
                'published' => $dayPosts->where('status', 'published')->count(),
                'failed'    => $dayPosts->where('status', 'failed')->count(),
                'pending'   => $dayPosts->whereIn('status', ['pending','processing'])->count(),
            ];
        })->filter(fn($d) => $d['published'] + $d['failed'] + $d['pending'] > 0);

      
        $bestDay = $posts->where('status', 'published')
            ->groupBy(fn($p) => Carbon::parse($p->scheduled_at)->format('l'))
            ->map->count()
            ->sortDesc()
            ->keys()
            ->first() ?? '—';

        $pdf = Pdf::loadView('reports.monthly', compact(
            'user', 'monthName', 'stats', 'daily', 'posts', 'bestDay', 'year', 'month'
        ))->setPaper('a4', 'portrait');

        return $pdf->download("PostFlow-Report-{$year}-{$month}.pdf");
    }
}