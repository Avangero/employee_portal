<?php

namespace App\Http\Controllers;

use App\Models\PullRequest;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Display the dashboard page.
     *
     * Shows weekly statistics for pull requests.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function index()
    {
        $user = Auth::user();
        $query = PullRequest::query();
        $statsScope = 'Ваши';

        // Определяем scope запроса в зависимости от роли
        if ($user->isAdministrator()) {
            $statsScope = 'Компания';
            // Для админа показываем все PR
        } elseif ($user->role?->slug === 'manager' && $user->team) {
            $statsScope = 'Команда';
            // Для менеджера показываем PR его команды
            $query->whereHas('author', function ($q) use ($user) {
                $q->where('team_id', $user->team->id);
            });
        } else {
            // Для обычного пользователя показываем только его PR
            $query->where('author_id', $user->id);
        }

        // Получаем статистику за неделю
        $startDate = Carbon::now()->subWeek();
        $weeklyStats = [
            'total' => $query->where('created_at', '>=', $startDate)->count(),
            'approved' => $query->clone()->where('created_at', '>=', $startDate)
                ->where('status', 'approved')->count(),
            'avg_returns' => round($query->clone()->where('created_at', '>=', $startDate)
                ->avg('returns_count') ?? 0, 1),
        ];

        // Получаем данные для графика за последние 7 дней
        $chartData = [
            'labels' => [],
            'prCounts' => [],
        ];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $chartData['labels'][] = $date->format('d.m');

            $chartData['prCounts'][] = $query->clone()
                ->whereDate('created_at', $date)
                ->count();
        }

        return view('dashboard', compact('weeklyStats', 'chartData', 'statsScope'));
    }
}
