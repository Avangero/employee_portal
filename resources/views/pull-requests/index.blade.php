<x-app-layout>
    <x-slot name="header">
        pull-requests
    </x-slot>

    <x-slot name="headerActions">
        <div class="flex gap-2">
            <a href="{{ request()->fullUrlWithQuery(['group_by' => 'day']) }}"
               class="px-3 py-2 text-sm font-medium rounded-md {{ $groupBy === 'day' ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white' : 'text-gray-700 hover:bg-gray-100' }} transition-all duration-200">
                По дням
            </a>
            <a href="{{ request()->fullUrlWithQuery(['group_by' => 'week']) }}"
               class="px-3 py-2 text-sm font-medium rounded-md {{ $groupBy === 'week' ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white' : 'text-gray-700 hover:bg-gray-100' }} transition-all duration-200">
                По неделям
            </a>
            <a href="{{ request()->fullUrlWithQuery(['group_by' => 'month']) }}"
               class="px-3 py-2 text-sm font-medium rounded-md {{ $groupBy === 'month' ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white' : 'text-gray-700 hover:bg-gray-100' }} transition-all duration-200">
                По месяцам
            </a>
        </div>
    </x-slot>

    <div class="space-y-8">
        <!-- Статистика -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-blue-100 rounded-lg">
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

            <div class="bg-gradient-to-br from-yellow-50 to-amber-50 rounded-xl p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-yellow-100 rounded-lg">
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

            <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-xl p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-green-100 rounded-lg">
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

            <div class="bg-gradient-to-br from-red-50 to-rose-50 rounded-xl p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-red-100 rounded-lg">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Отклонено</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $stats['rejected'] }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Сгруппированные Pull Request'ы -->
        @foreach ($pullRequests as $date => $prs)
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="border-b px-8 py-4 bg-gradient-to-r from-gray-50 to-white">
                    <div class="flex justify-between items-center">
                        <h3 class="text-lg font-semibold text-gray-900">{{ $date }}</h3>
                        <div class="flex items-center gap-2">
                            <span class="text-sm text-gray-600">Среднее кол-во возвратов:</span>
                            <span class="px-3 py-1 bg-gradient-to-r from-orange-100 to-amber-100 text-orange-800 text-sm font-semibold rounded-full">
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
                                    <a href="{{ $pr->url }}" target="_blank" class="text-blue-600 hover:text-blue-900 font-medium">
                                        PR #{{ $pr->id }}
                                    </a>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full
                                        @if ($pr->status === 'created') bg-gradient-to-r from-gray-100 to-gray-200 text-gray-800
                                        @elseif ($pr->status === 'in_review') bg-gradient-to-r from-yellow-100 to-amber-100 text-yellow-800
                                        @elseif ($pr->status === 'changes_requested') bg-gradient-to-r from-red-100 to-rose-100 text-red-800
                                        @elseif ($pr->status === 'approved') bg-gradient-to-r from-green-100 to-emerald-100 text-green-800
                                        @endif">
                                        {{ $pr->status === 'created' ? 'Создан' :
                                           ($pr->status === 'in_review' ? 'На ревью' :
                                           ($pr->status === 'changes_requested' ? 'Требует изменений' :
                                           ($pr->status === 'approved' ? 'Одобрен' : $pr->status))) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <span class="px-2 py-1 bg-red-50 text-red-600 rounded">{{ $pr->returns_count }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <span class="px-2 py-1 bg-green-50 text-green-600 rounded">{{ $pr->approvals_count }}</span>
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
</x-app-layout> 