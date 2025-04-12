<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Мои Pull Request\'ы') }}
            </h2>
            <div class="flex gap-2">
                <a href="{{ request()->fullUrlWithQuery(['group_by' => 'day']) }}"
                   class="px-3 py-2 text-sm font-medium rounded-md {{ $groupBy === 'day' ? 'bg-gray-900 text-white' : 'text-gray-700 hover:bg-gray-200' }}">
                    По дням
                </a>
                <a href="{{ request()->fullUrlWithQuery(['group_by' => 'week']) }}"
                   class="px-3 py-2 text-sm font-medium rounded-md {{ $groupBy === 'week' ? 'bg-gray-900 text-white' : 'text-gray-700 hover:bg-gray-200' }}">
                    По неделям
                </a>
                <a href="{{ request()->fullUrlWithQuery(['group_by' => 'month']) }}"
                   class="px-3 py-2 text-sm font-medium rounded-md {{ $groupBy === 'month' ? 'bg-gray-900 text-white' : 'text-gray-700 hover:bg-gray-200' }}">
                    По месяцам
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Статистика -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-3xl font-bold text-gray-900">{{ $stats['total'] }}</div>
                    <div class="text-sm text-gray-600">Всего PR</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-3xl font-bold text-yellow-500">{{ $stats['pending'] }}</div>
                    <div class="text-sm text-gray-600">На проверке</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-3xl font-bold text-green-500">{{ $stats['approved'] }}</div>
                    <div class="text-sm text-gray-600">Одобрено</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-3xl font-bold text-red-500">{{ $stats['rejected'] }}</div>
                    <div class="text-sm text-gray-600">Отклонено</div>
                </div>
            </div>

            <!-- Сгруппированные Pull Request'ы -->
            @foreach ($pullRequests as $date => $prs)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-4 bg-gray-50 border-b border-gray-200 flex justify-between items-center">
                        <h3 class="text-lg font-medium text-gray-900">{{ $date }}</h3>
                        <div class="flex items-center gap-2">
                            <span class="text-sm text-gray-600">Среднее кол-во возвратов:</span>
                            <span class="px-2 py-1 bg-orange-100 text-orange-800 text-sm font-semibold rounded-full">
                                {{ $averageReturns[$date] }}
                            </span>
                        </div>
                    </div>
                    <div class="p-6 text-gray-900">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pull Request</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Статус</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Возвраты</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Апрувы</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Создан</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Обновлен</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($prs as $pr)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <a href="{{ $pr->url }}" target="_blank" class="text-indigo-600 hover:text-indigo-900">
                                            PR #{{ $pr->id }}
                                        </a>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                            @if ($pr->status === 'created') bg-gray-100 text-gray-800
                                            @elseif ($pr->status === 'in_review') bg-yellow-100 text-yellow-800
                                            @elseif ($pr->status === 'changes_requested') bg-red-100 text-red-800
                                            @elseif ($pr->status === 'approved') bg-green-100 text-green-800
                                            @endif">
                                            {{ $pr->status === 'created' ? 'Создан' :
                                               ($pr->status === 'in_review' ? 'На ревью' :
                                               ($pr->status === 'changes_requested' ? 'Требует изменений' :
                                               ($pr->status === 'approved' ? 'Одобрен' : $pr->status))) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">{{ $pr->returns_count }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">{{ $pr->approvals_count }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $pr->created_at->format('d.m.Y H:i') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $pr->updated_at->format('d.m.Y H:i') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</x-app-layout> 