<?php

namespace App\Http\Controllers;

use App\Models\PullRequest;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class PullRequestController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $groupBy = $request->get('group_by', 'week');
        $filterType = $request->get('filter_type', 'personal'); // personal, user, team, company
        $filterId = $request->get('filter_id');

        // Базовый запрос
        $query = PullRequest::query()
            ->with(['author', 'author.team']); // Подгружаем связанные данные

        // Применяем фильтры в зависимости от роли и выбранного типа фильтра
        switch ($filterType) {
            case 'personal':
                $query->where('author_id', $user->id);
                break;

            case 'user':
                if ($user->isAdministrator()) {
                    $query->where('author_id', $filterId);
                } elseif ($user->isManager()) {
                    // Проверяем, что пользователь действительно является руководителем для выбранного сотрудника
                    $subordinate = User::find($filterId);
                    if ($subordinate && $subordinate->manager_id === $user->id) {
                        $query->where('author_id', $filterId);
                    } else {
                        $query->where('author_id', $user->id);
                    }
                }
                break;

            case 'team':
                if ($user->isAdministrator()) {
                    $query->whereHas('author', function ($q) use ($filterId) {
                        $q->where('team_id', $filterId);
                    });
                } elseif ($user->isManager() && $user->team_id === (int) $filterId) {
                    $query->whereHas('author', function ($q) use ($filterId) {
                        $q->where('team_id', $filterId);
                    });
                }
                break;

            case 'company':
                if ($user->isAdministrator()) {
                    // Не добавляем дополнительных условий - показываем все PR
                } else {
                    $query->where('author_id', $user->id);
                }
                break;
        }

        // Определяем период для статистики
        $now = Carbon::now();
        switch ($groupBy) {
            case 'week':
                $startDate = $now->copy()->startOfWeek();
                $endDate = $now->copy()->endOfWeek();
                break;
            case 'month':
                $startDate = $now->copy()->startOfMonth();
                $endDate = $now->copy()->endOfMonth();
                break;
            case 'year':
                $startDate = $now->copy()->startOfYear();
                $endDate = $now->copy()->endOfYear();
                break;
        }

        // Получаем PR за выбранный период
        $periodPRs = $query->clone()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        // Рассчитываем статистику за период
        $stats = [
            'total' => $periodPRs->count(),
            'pending' => $periodPRs->where('status', 'in_review')->count(),
            'approved' => $periodPRs->where('status', 'approved')->count(),
            'avg_returns' => $periodPRs->count() > 0 ? round($periodPRs->sum('returns_count') / $periodPRs->count(), 1) : 0,
        ];

        // Группируем PR по датам
        $pullRequests = $query->clone()
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy(function ($pr) use ($groupBy) {
                $date = Carbon::parse($pr->created_at);

                return match ($groupBy) {
                    'week' => $this->formatWeekRange($date),
                    'month' => $this->formatMonth($date),
                    'year' => $date->format('Y'),
                    default => $date->format('d.m.Y'),
                };
            });

        // Рассчитываем среднее количество возвратов для каждой группы
        $averageReturns = $pullRequests->map(function ($prs) {
            $totalReturns = $prs->sum('returns_count');
            $count = $prs->count();

            return $count > 0 ? round($totalReturns / $count, 1) : 0;
        });

        // Получаем данные для графика
        $chartData = $this->getChartData($groupBy, $query);

        // Получаем данные для фильтров в зависимости от роли
        $filters = $this->getFiltersData($user);

        return view('pull-requests.index', [
            'stats' => $stats,
            'pullRequests' => $pullRequests,
            'groupBy' => $groupBy,
            'averageReturns' => $averageReturns,
            'chartData' => $chartData,
            'filters' => $filters,
            'filterType' => $filterType,
            'filterId' => $filterId,
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

    private function getFiltersData(User $user): array
    {
        $filters = [
            'types' => [
                'personal' => 'Мои PR',
            ],
            'users' => collect(),
            'teams' => collect(),
        ];

        if ($user->isAdministrator()) {
            $filters['types'] = [
                'personal' => 'Мои PR',
                'user' => 'По сотруднику',
                'team' => 'По команде',
                'company' => 'По компании',
            ];
            $filters['users'] = User::orderBy('first_name')->get();
            $filters['teams'] = Team::orderBy('name')->get();
        } elseif ($user->isManager()) {
            $subordinates = User::where('manager_id', $user->id)->exists();
            if ($subordinates) {
                $filters['types']['user'] = 'По сотруднику';
            }

            if ($user->team_id) {
                $filters['types']['team'] = 'По команде';
            }

            $filters['users'] = User::where('manager_id', $user->id)
                ->orderBy('first_name')
                ->get();

            if ($user->team_id) {
                $filters['teams'] = Team::where('id', $user->team_id)->get();
            }
        }

        return $filters;
    }

    private function getChartData(string $groupBy, Builder $query): array
    {
        $now = Carbon::now();

        // Определяем период для графика
        switch ($groupBy) {
            case 'week':
                $startDate = $now->copy()->startOfWeek();
                $endDate = $now->copy()->endOfWeek();
                $interval = '1 day';
                $format = 'D';
                break;
            case 'month':
                $startDate = $now->copy()->startOfMonth();
                $endDate = $now->copy()->endOfMonth();
                $interval = '1 day';
                $format = 'd.m';
                break;
            case 'year':
                $startDate = $now->copy()->startOfYear();
                $endDate = $now->copy()->endOfYear();
                $interval = '1 month';
                $format = 'F';
                break;
        }

        // Получаем данные из базы за выбранный период
        $data = $query->clone()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get()
            ->groupBy(function ($pr) use ($format) {
                return $pr->created_at->format($format);
            });

        // Генерируем все метки времени для выбранного периода
        $labels = [];
        $prCounts = [];
        $avgReturns = [];

        $current = $startDate->copy();
        while ($current <= $endDate) {
            $label = $current->format($format);
            $labels[] = $label;

            $periodData = $data->get($label, collect([]));
            $prCounts[] = $periodData->count();

            $returns = $periodData->sum('returns_count');
            $count = $periodData->count();
            $avgReturns[] = $count > 0 ? round($returns / $count, 1) : 0;

            // Увеличиваем текущую дату на интервал
            switch ($interval) {
                case '1 day':
                    $current->addDay();
                    break;
                case '1 month':
                    $current->addMonth();
                    break;
            }
        }

        return [
            'labels' => $labels,
            'prCounts' => $prCounts,
            'avgReturns' => $avgReturns,
        ];
    }
}
