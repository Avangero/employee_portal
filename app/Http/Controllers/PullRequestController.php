<?php

namespace App\Http\Controllers;

use App\Models\PullRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class PullRequestController extends Controller {
    public function index(Request $request) {
        $stats = [
            'total' => PullRequest::where('author_id', auth()->id())->count(),
            'pending' => PullRequest::where('author_id', auth()->id())
                ->where('status', 'in_review')
                ->count(),
            'approved' => PullRequest::where('author_id', auth()->id())
                ->where('status', 'approved')
                ->count(),
            'rejected' => PullRequest::where('author_id', auth()->id())
                ->where('status', 'changes_requested')
                ->count(),
        ];

        $groupBy = $request->get('group_by', 'day'); // day, week, month

        $pullRequests = PullRequest::where('author_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy(function ($pr) use ($groupBy) {
                $date = Carbon::parse($pr->created_at);

                return match ($groupBy) {
                    'week' => 'Неделя ' .
                        $date->weekOfYear .
                        ' (' .
                        $date->startOfWeek()->format('d.m.Y') .
                        ' - ' .
                        $date->endOfWeek()->format('d.m.Y') .
                        ')',
                    'month' => $date->translatedFormat('F Y'),
                    default => $date->format('d.m.Y'),
                };
            });

        // Рассчитываем среднее количество возвратов для каждой группы
        $averageReturns = $pullRequests->map(function ($prs) {
            $totalReturns = $prs->sum('returns_count');
            $count = $prs->count();

            return $count > 0 ? round($totalReturns / $count, 1) : 0;
        });

        return view('pull-requests.index', compact('stats', 'pullRequests', 'groupBy', 'averageReturns'));
    }
}
