<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Employee Portal') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="antialiased bg-gray-50">
        <!-- Header -->
        <x-header />

        <!-- Hero section -->
        <div class="relative isolate overflow-hidden">
            <div class="absolute inset-x-0 -top-40 -z-10 transform-gpu overflow-hidden blur-3xl sm:-top-80" aria-hidden="true">
                <div class="relative left-[calc(50%-11rem)] aspect-[1155/678] w-[36.125rem] -translate-x-1/2 rotate-[30deg] bg-gradient-to-tr from-[#ff80b5] to-[#9089fc] opacity-30 sm:left-[calc(50%-30rem)] sm:w-[72.1875rem]" style="clip-path: polygon(74.1% 44.1%, 100% 61.6%, 97.5% 26.9%, 85.5% 0.1%, 80.7% 2%, 72.5% 32.5%, 60.2% 62.4%, 52.4% 68.1%, 47.5% 58.3%, 45.2% 34.5%, 27.5% 76.7%, 0.1% 64.9%, 17.9% 100%, 27.6% 76.8%, 76.1% 97.7%, 74.1% 44.1%)"></div>
            </div>

            <div class="mx-auto max-w-7xl px-6 py-24 sm:py-32 lg:px-8">
                <div class="mx-auto max-w-2xl text-center">
                    <h1 class="text-4xl font-bold tracking-tight text-gray-900 sm:text-6xl">Портал сотрудников</h1>
                    <p class="mt-6 text-lg leading-8 text-gray-600">
                        Добро пожаловать в корпоративный портал. Здесь вы найдете всю необходимую информацию и инструменты для эффективной работы.
                    </p>
                    <div class="mt-10 flex items-center justify-center gap-x-6">
                        @auth
                            <a href="{{ url('/dashboard') }}" class="rounded-md bg-indigo-600 px-3.5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">Перейти в панель управления</a>
                        @else
                            <a href="{{ route('login') }}" class="rounded-md bg-indigo-600 px-3.5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">Войти в систему</a>
                        @endauth
                    </div>
                </div>
            </div>

            <div class="absolute inset-x-0 top-[calc(100%-13rem)] -z-10 transform-gpu overflow-hidden blur-3xl sm:top-[calc(100%-30rem)]" aria-hidden="true">
                <div class="relative left-[calc(50%+3rem)] aspect-[1155/678] w-[36.125rem] -translate-x-1/2 bg-gradient-to-tr from-[#ff80b5] to-[#9089fc] opacity-30 sm:left-[calc(50%+36rem)] sm:w-[72.1875rem]" style="clip-path: polygon(74.1% 44.1%, 100% 61.6%, 97.5% 26.9%, 85.5% 0.1%, 80.7% 2%, 72.5% 32.5%, 60.2% 62.4%, 52.4% 68.1%, 47.5% 58.3%, 45.2% 34.5%, 27.5% 76.7%, 0.1% 64.9%, 17.9% 100%, 27.6% 76.8%, 76.1% 97.7%, 74.1% 44.1%)"></div>
            </div>
        </div>

        <!-- Feature section -->
        <div class="bg-white py-24 sm:py-32">
            <div class="mx-auto max-w-7xl px-6 lg:px-8">
                <div class="mx-auto max-w-2xl lg:text-center">
                    <h2 class="text-base font-semibold leading-7 text-indigo-600">Все необходимое в одном месте</h2>
                    <p class="mt-2 text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">Возможности портала</p>
                    <p class="mt-6 text-lg leading-8 text-gray-600">
                        Наш портал предоставляет все необходимые инструменты для эффективной работы и коммуникации.
                    </p>
                </div>

                <!-- Carousel section -->
                <div class="relative mt-16">
                    <!-- Carousel container -->
                    <div class="overflow-hidden">
                        <div id="carousel" class="flex transition-transform duration-500 ease-in-out">
                            <!-- Card 1 -->
                            <div class="w-full md:w-1/3 flex-shrink-0 p-4">
                                <div class="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-lg transition-shadow duration-300 h-full border border-gray-100">
                                    <div class="p-6">
                                        <div class="h-12 w-12 bg-indigo-100 rounded-lg flex items-center justify-center mb-4">
                                            <svg class="h-6 w-6 text-indigo-600" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                <path fill-rule="evenodd" d="M5.5 17a4.5 4.5 0 01-1.44-8.765 4.5 4.5 0 018.302-3.046 3.5 3.5 0 014.504 4.272A4 4 0 0115 17H5.5zm3.75-2.75a.75.75 0 001.5 0V9.66l1.95 2.1a.75.75 0 101.1-1.02l-3.25-3.5a.75.75 0 00-1.1 0l-3.25 3.5a.75.75 0 101.1 1.02l1.95-2.1v4.59z" clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                        <h3 class="text-xl font-semibold text-gray-900 mb-2">Документы и файлы</h3>
                                        <p class="text-gray-600">Доступ к корпоративным документам, формам и шаблонам в любое время.</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Card 2 -->
                            <div class="w-full md:w-1/3 flex-shrink-0 p-4">
                                <div class="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-lg transition-shadow duration-300 h-full border border-gray-100">
                                    <div class="p-6">
                                        <div class="h-12 w-12 bg-indigo-100 rounded-lg flex items-center justify-center mb-4">
                                            <svg class="h-6 w-6 text-indigo-600" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                <path fill-rule="evenodd" d="M10 1a4.5 4.5 0 00-4.5 4.5V9H5a2 2 0 00-2 2v6a2 2 0 002 2h10a2 2 0 002-2v-6a2 2 0 00-2-2h-.5V5.5A4.5 4.5 0 0010 1zm3 8V5.5a3 3 0 10-6 0V9h6z" clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                        <h3 class="text-xl font-semibold text-gray-900 mb-2">Безопасность</h3>
                                        <p class="text-gray-600">Защищенный доступ к корпоративным ресурсам и данным.</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Card 3 -->
                            <div class="w-full md:w-1/3 flex-shrink-0 p-4">
                                <div class="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-lg transition-shadow duration-300 h-full border border-gray-100">
                                    <div class="p-6">
                                        <div class="h-12 w-12 bg-indigo-100 rounded-lg flex items-center justify-center mb-4">
                                            <svg class="h-6 w-6 text-indigo-600" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                <path fill-rule="evenodd" d="M15.312 11.424a5.5 5.5 0 01-9.201 2.466l-.312-.311h2.433a.75.75 0 000-1.5H3.989a.75.75 0 00-.75.75v4.242a.75.75 0 001.5 0v-2.43l.31.31a7 7 0 0011.712-3.138.75.75 0 00-1.449-.39zm1.23-3.723a.75.75 0 00.219-.53V2.929a.75.75 0 00-1.5 0V5.36l-.31-.31A7 7 0 003.239 8.188a.75.75 0 101.448.389A5.5 5.5 0 0113.89 6.11l.311.31h-2.432a.75.75 0 000 1.5h4.243a.75.75 0 00.53-.219z" clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                        <h3 class="text-xl font-semibold text-gray-900 mb-2">Коммуникации</h3>
                                        <p class="text-gray-600">Инструменты для эффективной коммуникации между сотрудниками.</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Card 4 -->
                            <div class="w-full md:w-1/3 flex-shrink-0 p-4">
                                <div class="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-lg transition-shadow duration-300 h-full border border-gray-100">
                                    <div class="p-6">
                                        <div class="h-12 w-12 bg-indigo-100 rounded-lg flex items-center justify-center mb-4">
                                            <svg class="h-6 w-6 text-indigo-600" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                <path fill-rule="evenodd" d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z" clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                        <h3 class="text-xl font-semibold text-gray-900 mb-2">Уведомления</h3>
                                        <p class="text-gray-600">Мгновенные уведомления о важных событиях и обновлениях.</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Card 5 -->
                            <div class="w-full md:w-1/3 flex-shrink-0 p-4">
                                <div class="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-lg transition-shadow duration-300 h-full border border-gray-100">
                                    <div class="p-6">
                                        <div class="h-12 w-12 bg-indigo-100 rounded-lg flex items-center justify-center mb-4">
                                            <svg class="h-6 w-6 text-indigo-600" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                        <h3 class="text-xl font-semibold text-gray-900 mb-2">Календарь</h3>
                                        <p class="text-gray-600">Планирование встреч, мероприятий и управление расписанием.</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Card 6 -->
                            <div class="w-full md:w-1/3 flex-shrink-0 p-4">
                                <div class="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-lg transition-shadow duration-300 h-full border border-gray-100">
                                    <div class="p-6">
                                        <div class="h-12 w-12 bg-indigo-100 rounded-lg flex items-center justify-center mb-4">
                                            <svg class="h-6 w-6 text-indigo-600" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                        <h3 class="text-xl font-semibold text-gray-900 mb-2">Профиль</h3>
                                        <p class="text-gray-600">Управление личной информацией и настройками аккаунта.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Navigation buttons -->
                    <button id="prevBtn" class="absolute left-0 top-1/2 -translate-y-1/2 bg-white rounded-full p-2 shadow-md hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 z-10">
                        <svg class="h-6 w-6 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>
                    <button id="nextBtn" class="absolute right-0 top-1/2 -translate-y-1/2 bg-white rounded-full p-2 shadow-md hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 z-10">
                        <svg class="h-6 w-6 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>

                    <!-- Indicators -->
                    <div class="flex justify-center mt-6 space-x-2">
                        @php
                            $totalCards = 4; // Общее количество карточек в карусели
                            $totalIndicators = $totalCards; // Один индикатор для каждой карточки
                        @endphp
                        @for ($i = 0; $i < $totalIndicators; $i++)
                            <button class="w-2.5 h-2.5 rounded-full bg-gray-300 indicator" data-index="{{ $i }}"></button>
                        @endfor
                    </div>
                </div>
            </div>
        </div>

        <!-- Carousel Script -->
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Получаем элементы карусели
                const carousel = document.getElementById('carousel');
                const prevBtn = document.getElementById('prevBtn');
                const nextBtn = document.getElementById('nextBtn');
                const indicators = document.querySelectorAll('.indicator');
                const cards = document.querySelectorAll('#carousel > div');

                // Настройки карусели
                const totalCards = cards.length - 2;
                let currentCard = 0;
                let cardsPerView = window.innerWidth >= 768 ? 3 : 1;

                // Функция для перемещения карусели
                function moveCarousel(cardIndex) {
                    // Ограничиваем индекс карточки допустимыми значениями
                    if (cardIndex < 0) cardIndex = totalCards - 1;
                    if (cardIndex >= totalCards) cardIndex = 0;

                    // Обновляем текущую карточку
                    currentCard = cardIndex;

                    // Рассчитываем смещение (каждая карточка = 100% / cardsPerView)
                    const offset = currentCard * (100 / cardsPerView);
                    carousel.style.transform = `translateX(-${offset}%)`;

                    // Обновляем индикаторы
                    indicators.forEach((indicator, index) => {
                        if (index === currentCard) {
                            indicator.classList.remove('bg-gray-300');
                            indicator.classList.add('bg-indigo-600');
                        } else {
                            indicator.classList.remove('bg-indigo-600');
                            indicator.classList.add('bg-gray-300');
                        }
                    });
                }

                // Инициализируем карусель
                moveCarousel(0);

                // Обработчики событий
                prevBtn.addEventListener('click', function() {
                    moveCarousel(currentCard - 1);
                });

                nextBtn.addEventListener('click', function() {
                    moveCarousel(currentCard + 1);
                });

                // Обработчики для индикаторов
                indicators.forEach((indicator, index) => {
                    indicator.addEventListener('click', function() {
                        moveCarousel(index);
                    });
                });

                // Обработка изменения размера экрана
                window.addEventListener('resize', function() {
                    let newCardsPerView = window.innerWidth >= 768 ? 3 : 1;
                    if (cardsPerView !== newCardsPerView) {
                        cardsPerView = newCardsPerView;
                        moveCarousel(currentCard);
                    }
                });
            });
        </script>
    </body>
</html>
