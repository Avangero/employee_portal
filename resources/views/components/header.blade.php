<!-- Header -->
<header class="bg-white/80 backdrop-blur-sm shadow-sm sticky top-0 z-10">
    <nav class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8" aria-label="Top">
        <div class="flex h-16 items-center justify-between">
            <div class="flex items-center">
                <a href="/" class="flex items-center space-x-2 group">
                    <div class="h-8 w-8 rounded-lg bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center shadow-md group-hover:shadow-lg transition-all duration-300">
                        <svg class="h-5 w-5 text-white" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <span class="text-2xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">Pulse</span>
                </a>
            </div>

            @auth
                <div class="flex items-center space-x-4">
                    <a href="{{ route('home') }}" class="text-gray-600 hover:text-indigo-600 transition duration-300">Главная</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-gray-600 hover:text-indigo-600 transition duration-300">
                            Выйти
                        </button>
                    </form>
                </div>
            @endauth
        </div>
    </nav>
</header> 