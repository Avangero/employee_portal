<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100">
            <div class="flex">
                <!-- Sidebar -->
                <div class="fixed inset-y-0 left-0 w-64 bg-white shadow-lg z-10">
                    <div class="flex items-center justify-center h-16">
                        <a href="/" class="flex items-center space-x-2 group">
                            <div class="h-8 w-8 rounded-lg bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center shadow-md group-hover:shadow-lg transition-all duration-300">
                                <svg class="h-5 w-5 text-white" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <span class="text-xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">Employee Portal</span>
                        </a>
                    </div>

                    <nav class="mt-6">
                        <div class="px-4 space-y-3">
                            <a href="{{ route('dashboard') }}" class="flex items-center px-4 py-3 {{ request()->routeIs('dashboard') ? 'bg-gradient-to-r from-indigo-50 to-purple-50 text-indigo-600 border-r-4 border-indigo-600' : 'text-gray-600 hover:bg-gray-50' }} transition-all duration-200">
                                <svg class="w-5 h-5 mr-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                                </svg>
                                Дашборд
                            </a>

                            <a href="{{ route('pull-requests.index') }}" class="flex items-center px-4 py-3 {{ request()->routeIs('pull-requests.*') ? 'bg-gradient-to-r from-indigo-50 to-purple-50 text-indigo-600 border-r-4 border-indigo-600' : 'text-gray-600 hover:bg-gray-50' }} transition-all duration-200">
                                <svg class="w-5 h-5 mr-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                                </svg>
                                Пулл-реквесты
                            </a>
                        </div>
                    </nav>

                    <!-- User Profile -->
                    <div class="absolute bottom-0 w-64">
                        <div class="flex items-center px-6 py-4">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 rounded-full bg-gradient-to-r from-indigo-500 to-purple-600 flex items-center justify-center text-white font-semibold">
                                    {{ substr(Auth::user()->first_name, 0, 1) }}
                                </div>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-900">{{ Auth::user()->full_name }}</p>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="text-xs text-gray-500 hover:text-gray-700">Выйти</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Main Content -->
                <div class="flex-1 ml-64">
                    <!-- Page Header -->
                    @if (isset($header))
                        <header class="bg-white/40 backdrop-blur-sm border-b border-gray-100">
                            <div class="max-w-7xl mx-auto px-8 h-20 flex items-center">
                                <div class="flex items-center justify-between w-full">
                                    <div class="flex items-center space-x-2 text-lg">
                                        <a href="{{ route('dashboard') }}" class="text-gray-400 hover:text-indigo-600 transition-colors duration-200">app</a>
                                        <span class="text-gray-300">/</span>
                                        <span class="text-gray-600 font-medium">{{ $header }}</span>
                                    </div>
                                    {{ $headerActions ?? '' }}
                                </div>
                            </div>
                        </header>
                    @endif

                    <!-- Page Content -->
                    <main class="p-8">
                        {{ $slot }}
                    </main>
                </div>
            </div>
        </div>
    </body>
</html>
