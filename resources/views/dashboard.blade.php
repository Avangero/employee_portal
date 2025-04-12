<x-app-layout>
    <x-slot name="header">
        dashboard
    </x-slot>

    <div class="space-y-8">
        <!-- Верхние виджеты -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Приветствие -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h1 class="text-2xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">
                    Привет, {{ Auth::user()->first_name }}! 👋
                </h1>
                <p class="mt-2 text-gray-600">Добро пожаловать в панель статистики Employee Portal.</p>
            </div>

            <!-- Информация о команде -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="flex items-center space-x-4">
                    <div class="p-2 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500">Твоя команда</div>
                        <div class="text-lg font-semibold text-gray-900">{{ Auth::user()->team?->name ?? 'Не назначена' }}</div>
                    </div>
                </div>
                <div class="flex items-center space-x-4 mt-4">
                    <div class="p-2 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500">Твой руководитель</div>
                        <div class="text-lg font-semibold text-gray-900">{{ Auth::user()->manager?->full_name ?? 'Не назначен' }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Виджет статистики пулл-реквестов -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="border-b px-8 py-4 bg-gradient-to-r from-gray-50 to-white">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-900">Статистика пулл-реквестов</h2>
                    <span class="text-sm text-gray-500">За последнюю неделю</span>
                </div>
            </div>

            <div class="p-8">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Общее количество PR -->
                    <div class="bg-gradient-to-br from-indigo-50 to-purple-50 rounded-lg p-6">
                        <div class="flex items-center">
                            <div class="p-2 bg-indigo-100 rounded-lg">
                                <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Всего PR</p>
                                <p class="text-2xl font-bold text-gray-900">{{ $weeklyStats['total'] ?? 0 }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Одобренные PR -->
                    <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-lg p-6">
                        <div class="flex items-center">
                            <div class="p-2 bg-green-100 rounded-lg">
                                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Одобрено</p>
                                <p class="text-2xl font-bold text-gray-900">{{ $weeklyStats['approved'] ?? 0 }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Среднее количество возвратов -->
                    <div class="bg-gradient-to-br from-orange-50 to-amber-50 rounded-lg p-6">
                        <div class="flex items-center">
                            <div class="p-2 bg-orange-100 rounded-lg">
                                <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Среднее кол-во возвратов</p>
                                <p class="text-2xl font-bold text-gray-900">{{ number_format($weeklyStats['avg_returns'] ?? 0, 1) }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
