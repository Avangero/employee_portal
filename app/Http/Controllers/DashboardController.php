<?php

/**
 * Dashboard Controller
 *
 * Handles the main dashboard functionality of the application.
 *
 * PHP version 8.2
 *
 * @category Controllers
 *
 * @author   Employee Portal Team <team@employee-portal.com>
 * @license  MIT License
 *
 * @link     https://github.com/your-repo/employee-portal
 */

namespace App\Http\Controllers;

use App\Models\PullRequest;
use Carbon\Carbon;

/**
 * Class DashboardController
 *
 * Controls the dashboard view and related functionality.
 *
 * @category Controllers
 *
 * @author   Employee Portal Team <team@employee-portal.com>
 * @license  MIT License
 *
 * @link     https://github.com/your-repo/employee-portal
 */
class DashboardController extends Controller
{
    /**
     * Display the dashboard page.
     *
     * Shows weekly statistics for pull requests.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $weekStart = Carbon::now()->startOfWeek();
        $weekEnd = Carbon::now()->endOfWeek();

        $weeklyQuery = PullRequest::where('author_id', auth()->id())
            ->whereBetween('created_at', [$weekStart, $weekEnd]);

        $weeklyPRs = $weeklyQuery->get();

        $weeklyStats = [
            'total' => $weeklyPRs->count(),
            'approved' => $weeklyPRs->where('status', 'approved')->count(),
            'avg_returns' => $weeklyPRs->count() > 0
                ? round($weeklyPRs->sum('returns_count') / $weeklyPRs->count(), 1)
                : 0,
        ];

        return view('dashboard', compact('weeklyStats'));
    }
}
