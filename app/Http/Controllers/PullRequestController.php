<?php

namespace App\Http\Controllers;

use App\Models\PullRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class PullRequestController extends Controller
{
    public function index(Request $request)
    {
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
                    'week' => $this->formatWeekRange($date),
                    'month' => $this->formatMonth($date),
                    default => $date->format('d.m.Y'),
                };
            });

        // Рассчитываем среднее количество возвратов для каждой группы
        $averageReturns = $pullRequests->map(function ($prs) {
            $totalReturns = $prs->sum('returns_count');
            $count = $prs->count();

            return $count > 0 ? round($totalReturns / $count, 1) : 0;
        });

        return view('pull-requests.index', [
            'stats' => $stats,
            'pullRequests' => $pullRequests,
            'groupBy' => $groupBy,
            'averageReturns' => $averageReturns,
        ]);
    }

    private function formatWeekRange(Carbon $date): string
    {
        $startOfWeek = $date->copy()->startOfWeek();
        $endOfWeek = $date->copy()->endOfWeek();

        // Получаем первый день месяца
        $firstDayOfMonth = $startOfWeek->copy()->startOfMonth();

        // Вычисляем номер недели в месяце
        $weekNumber = 1;
        while ($firstDayOfMonth->lt($startOfWeek)) {
            $weekNumber++;
            $firstDayOfMonth->addWeek();
        }

        // Если начало и конец недели в разных месяцах
        if ($startOfWeek->format('m') !== $endOfWeek->format('m')) {
            return sprintf(
                '%d неделя %s-%s %s',
                $weekNumber,
                $startOfWeek->translatedFormat('F'),
                $endOfWeek->translatedFormat('F'),
                $startOfWeek->format('Y')
            );
        }

        // Если в одном месяце
        return sprintf(
            '%d неделя %s %s',
            $weekNumber,
            $startOfWeek->translatedFormat('F'),
            $startOfWeek->format('Y')
        );
    }

    private function formatMonth(Carbon $date): string
    {
        return $date->translatedFormat('F Y');
    }
}
