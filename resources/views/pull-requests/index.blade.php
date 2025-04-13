<x-app-layout>
    <x-slot name="header">
        pull-requests
    </x-slot>

    <x-slot name="headerActions">
        <div class="flex items-center gap-6">
            <!-- Фильтр по пользователю/команде -->
            @if(auth()->user()->isManager() || auth()->user()->isAdministrator())
            <div class="flex items-center gap-3">
                <select name="filter_type" 
                        class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                        onchange="updateFilter(this.value)">
                    @foreach($filters['types'] as $value => $label)
                        <option value="{{ $value }}" {{ $filterType === $value ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>

                <!-- Выбор пользователя -->
                <select name="filter_id" 
                        id="user-filter"
                        class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm {{ $filterType === 'user' ? '' : 'hidden' }}"
                        onchange="applyFilter()">
                    @foreach($filters['users'] as $filterUser)
                        <option value="{{ $filterUser->id }}" {{ $filterId == $filterUser->id ? 'selected' : '' }}>
                            {{ $filterUser->full_name }}
                        </option>
                    @endforeach
                </select>

                <!-- Выбор команды -->
                <select name="filter_id" 
                        id="team-filter"
                        class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm {{ $filterType === 'team' ? '' : 'hidden' }}"
                        onchange="applyFilter()">
                    @foreach($filters['teams'] as $team)
                        <option value="{{ $team->id }}" {{ $filterId == $team->id ? 'selected' : '' }}>
                            {{ $team->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            @endif

            <!-- Группировка по периоду -->
            <div class="flex gap-3">
                <a href="{{ request()->fullUrlWithQuery(['group_by' => 'week']) }}"
                   class="px-3 py-2 text-sm font-medium rounded-md {{ $groupBy === 'week' ? 'bg-indigo-600 text-white' : 'text-gray-700 hover:bg-gray-100' }} transition-all duration-200">
                    По неделям
                </a>
                <a href="{{ request()->fullUrlWithQuery(['group_by' => 'month']) }}"
                   class="px-3 py-2 text-sm font-medium rounded-md {{ $groupBy === 'month' ? 'bg-indigo-600 text-white' : 'text-gray-700 hover:bg-gray-100' }} transition-all duration-200">
                    По месяцам
                </a>
                <a href="{{ request()->fullUrlWithQuery(['group_by' => 'year']) }}"
                   class="px-3 py-2 text-sm font-medium rounded-md {{ $groupBy === 'year' ? 'bg-indigo-600 text-white' : 'text-gray-700 hover:bg-gray-100' }} transition-all duration-200">
                    По годам
                </a>
            </div>
        </div>
    </x-slot>

    <script>
        function updateFilter(type) {
            // Скрываем все фильтры
            document.getElementById('user-filter').classList.add('hidden');
            document.getElementById('team-filter').classList.add('hidden');

            // Показываем нужный фильтр
            if (type === 'user') {
                document.getElementById('user-filter').classList.remove('hidden');
            } else if (type === 'team') {
                document.getElementById('team-filter').classList.remove('hidden');
            }

            // Применяем фильтр
            applyFilter();
        }

        function applyFilter() {
            const type = document.querySelector('select[name="filter_type"]').value;
            let id = '';

            if (type === 'user') {
                id = document.getElementById('user-filter').value;
            } else if (type === 'team') {
                id = document.getElementById('team-filter').value;
            }

            // Обновляем URL с новыми параметрами
            const url = new URL(window.location.href);
            url.searchParams.set('filter_type', type);
            if (id) {
                url.searchParams.set('filter_id', id);
            } else {
                url.searchParams.delete('filter_id');
            }

            // Сохраняем параметр group_by
            const groupBy = url.searchParams.get('group_by');
            if (groupBy) {
                url.searchParams.set('group_by', groupBy);
            }

            window.location.href = url.toString();
        }
    </script>

    <div class="space-y-8">
        <!-- Статистика -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="bg-white rounded-xl p-6 shadow-sm">
                <div class="flex items-center">
                    <div class="p-2 bg-blue-50 rounded-lg">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Всего PR</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $stats['total'] }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl p-6 shadow-sm">
                <div class="flex items-center">
                    <div class="p-2 bg-yellow-50 rounded-lg">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">На проверке</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $stats['pending'] }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl p-6 shadow-sm">
                <div class="flex items-center">
                    <div class="p-2 bg-green-50 rounded-lg">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Одобрено</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $stats['approved'] }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl p-6 shadow-sm">
                <div class="flex items-center">
                    <div class="p-2 bg-orange-50 rounded-lg">
                        <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Возвраты</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $stats['avg_returns'] }}</p>
                    </div>
                </div>
            </div>
        </div>

        @if($chartData)
        <!-- График -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <canvas id="pullRequestsChart" class="w-full h-[400px]"></canvas>
        </div>
        @endif

        <!-- Сгруппированные Pull Request'ы -->
        @foreach ($pullRequests as $date => $prs)
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="border-b px-6 py-4">
                    <div class="flex justify-between items-center">
                        <h3 class="text-lg font-semibold text-gray-900">{{ $date }}</h3>
                        <div class="flex items-center gap-2">
                            <span class="text-sm text-gray-600">Среднее кол-во возвратов:</span>
                            <span class="px-3 py-1 bg-orange-50 text-orange-700 text-sm font-medium rounded-full">
                                {{ $averageReturns[$date] }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr class="bg-gray-50">
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pull Request</th>
                                @if($filterType === 'team' || $filterType === 'company')
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Автор</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Команда</th>
                                @endif
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Статус</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Возвраты</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Апрувы</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Создан</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Обновлен</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($prs as $pr)
                            <tr class="hover:bg-gray-50 transition-colors duration-200">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <a href="{{ $pr->url }}" target="_blank" class="text-indigo-600 hover:text-indigo-900 font-medium">
                                        PR #{{ $pr->id }}
                                    </a>
                                </td>
                                @if($filterType === 'team' || $filterType === 'company')
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $pr->author->full_name }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $pr->author->team?->name ?? 'Нет команды' }}</div>
                                </td>
                                @endif
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-medium rounded-full
                                        @if ($pr->status === 'created') bg-gray-100 text-gray-800
                                        @elseif ($pr->status === 'in_review') bg-yellow-50 text-yellow-700
                                        @elseif ($pr->status === 'changes_requested') bg-red-50 text-red-700
                                        @elseif ($pr->status === 'approved') bg-green-50 text-green-700
                                        @endif">
                                        {{ $pr->status === 'created' ? 'Создан' :
                                           ($pr->status === 'in_review' ? 'На ревью' :
                                           ($pr->status === 'changes_requested' ? 'Требует изменений' :
                                           ($pr->status === 'approved' ? 'Одобрен' : $pr->status))) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <span class="px-2 py-1 bg-red-50 text-red-700 rounded">{{ $pr->returns_count }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <span class="px-2 py-1 bg-green-50 text-green-700 rounded">{{ $pr->approvals_count }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-gray-500">{{ $pr->created_at->format('d.m.Y H:i') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-gray-500">{{ $pr->updated_at->format('d.m.Y H:i') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach
    </div>

    @if($chartData)
    <!-- Подключаем Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        // Данные для графика
        const chartData = @json($chartData);
        const groupBy = @json($groupBy);

        // Создаем график
        const ctx = document.getElementById('pullRequestsChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: chartData.labels,
                datasets: [
                    {
                        label: 'Количество PR',
                        data: chartData.prCounts,
                        borderColor: 'rgb(37, 99, 235)',
                        backgroundColor: 'rgba(37, 99, 235, 0.1)',
                        tension: 0.4,
                        fill: true,
                    },
                    {
                        label: 'Среднее кол-во возвратов',
                        data: chartData.avgReturns,
                        borderColor: 'rgb(234, 88, 12)',
                        backgroundColor: 'rgba(234, 88, 12, 0.1)',
                        tension: 0.4,
                        fill: true,
                    }
                ]
            },
            options: {
                responsive: true,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    title: {
                        display: true,
                        text: 'Статистика Pull Request\'ов',
                        font: {
                            size: 16,
                            weight: 'bold'
                        },
                        padding: {
                            bottom: 20
                        }
                    },
                    legend: {
                        position: 'top',
                        labels: {
                            usePointStyle: true,
                            padding: 20
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed.y !== null) {
                                    label += context.parsed.y.toFixed(1);
                                }
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        title: {
                            display: true,
                            text: 'Количество'
                        },
                        grid: {
                            drawOnChartArea: false
                        },
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
    @endif
</x-app-layout> 