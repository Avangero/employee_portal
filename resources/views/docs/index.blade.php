<x-app-layout>
    <x-slot name="header">docs</x-slot>

    <div class="docs-container">
        <div class="max-w-7xl mx-auto">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="docs-content p-6 text-gray-900">
                    <div class="docs-header mb-8">
                        <h3 class="text-2xl font-semibold text-gray-900 mb-4">Список документов компании</h3>
                        <p class="text-gray-600 mb-4 max-w-3xl content-center">
                            Здесь собраны все действующие документы компании, структурированные по категориям.
                            Выберите нужную категорию и документ для просмотра.
                        </p>
                    </div>

                    <!-- Поиск документов -->
                    <div class="mb-8">
                        <div class="search-container">
                            <div class="flex rounded-md shadow-sm">
                                <div class="relative flex-grow focus-within:z-10">
                                    <input
                                        type="text"
                                        id="search-input"
                                        placeholder="Поиск документов по названию..."
                                        class="search-input"
                                    >
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                        <svg class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Результаты поиска -->
                            <div id="search-results" class="search-results" style="display: none;">
                                <ul id="results-list" class="max-h-60 overflow-auto py-2 text-base">
                                    <!-- Результаты будут добавлены через JavaScript -->
                                </ul>
                                
                                <div id="results-counter" class="border-t border-gray-200 py-2 px-3 text-xs text-gray-500" style="display: none;">
                                    Показано <span id="results-count">0</span> результатов
                                </div>
                            </div>
                            
                            <!-- Ничего не найдено -->
                            <div id="no-results" class="absolute z-10 mt-1 w-full rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5 overflow-hidden" style="display: none;">
                                <div class="py-4 px-6 text-center">
                                    <p class="text-gray-500">Ничего не найдено по запросу "<span id="search-term-display"></span>"</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-6 divide-y divide-gray-100">
                        @forelse($categories->where('parent_id', null) as $category)
                            <div class="pt-4" x-data="{ open: false }">
                                <!-- Категория -->
                                <div @click="open = !open" class="flex items-center justify-between cursor-pointer rounded-lg hover:bg-gray-50 p-2 transition duration-150">
                                    <div class="flex items-center space-x-3">
                                        <div class="flex-shrink-0">
                                            <div class="w-10 h-10 rounded-lg bg-indigo-100 flex items-center justify-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                                                </svg>
                                            </div>
                                        </div>
                                        <div>
                                            <h4 class="text-lg font-medium text-gray-900">
                                                {{ $category->name }}
                                            </h4>
                                            <p class="text-sm text-gray-500">
                                                {{ $category->documents->count() }} документов, {{ $category->children->count() }} подкатегорий
                                            </p>
                                        </div>
                                    </div>
                                    <div class="flex-shrink-0">
                                        <svg x-bind:class="open ? 'rotate-180 transform' : ''" class="w-5 h-5 text-gray-500 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </div>
                                </div>

                                <!-- Контент категории -->
                                <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="mt-3 ml-4 pl-9 border-l-2 border-indigo-100">
                                    <!-- Документы категории -->
                                    @if($category->documents->count() > 0)
                                        <div class="mb-4">
                                            <h5 class="text-md font-medium text-gray-700 mb-2">Документы:</h5>
                                            <ul class="space-y-2">
                                                @foreach($category->documents as $document)
                                                    <li class="flex items-center group">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-gray-400 group-hover:text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                                        </svg>
                                                        <a href="{{ route('docs.show', [$category->slug, $document->slug]) }}" class="text-gray-700 hover:text-indigo-600 hover:underline transition-colors">
                                                            {{ $document->title }}
                                                        </a>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif

                                    <!-- Подкатегории -->
                                    @if($category->children->count() > 0)
                                        <div class="space-y-4">
                                            @foreach($category->children as $subcategory)
                                                <div x-data="{ subOpen: false }">
                                                    <!-- Заголовок подкатегории -->
                                                    <div @click="subOpen = !subOpen" class="flex items-center justify-between cursor-pointer rounded hover:bg-gray-50 p-1 transition duration-150">
                                                        <div class="flex items-center">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                                                            </svg>
                                                            <h5 class="text-md font-medium text-gray-800">
                                                                {{ $subcategory->name }}
                                                                <span class="text-xs text-gray-500 ml-2">({{ $subcategory->subcategoryDocuments->count() }} документов)</span>
                                                            </h5>
                                                        </div>
                                                        <div>
                                                            <svg x-bind:class="subOpen ? 'rotate-180 transform' : ''" class="w-4 h-4 text-gray-500 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                                            </svg>
                                                        </div>
                                                    </div>

                                                    <!-- Контент подкатегории -->
                                                    <div x-show="subOpen" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="mt-2 ml-6 pl-2 border-l border-gray-200">
                                                        @if($subcategory->subcategoryDocuments->count() > 0)
                                                            <ul class="space-y-2">
                                                                @foreach($subcategory->subcategoryDocuments as $document)
                                                                    <li class="flex items-center group">
                                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-gray-400 group-hover:text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                                                        </svg>
                                                                        <a href="{{ route('docs.subcategory.show', [$category->slug, $subcategory->slug, $document->slug]) }}" class="text-gray-700 hover:text-indigo-600 hover:underline transition-colors">
                                                                            {{ $document->title }}
                                                                        </a>
                                                                    </li>
                                                                @endforeach
                                                            </ul>
                                                        @else
                                                            <p class="text-sm text-gray-500 italic">В этой подкатегории нет документов.</p>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif

                                    @if($category->documents->count() === 0 && $category->children->count() === 0)
                                        <p class="text-sm text-gray-500 italic">В этой категории пока нет документов.</p>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="py-4 text-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900">Нет документов</h3>
                                <p class="mt-1 text-sm text-gray-500">Документы еще не добавлены в систему.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Данные документов для поиска
        const allDocuments = JSON.parse('{!! json_encode([
            // Генерируем массив документов из всех категорий и подкатегорий
            ...$categories->where("parent_id", null)->flatMap(function($category) {
                $docs = [];
                
                // Документы из основной категории
                foreach($category->documents as $document) {
                    $docs[] = [
                        "title" => $document->title,
                        "url" => route("docs.show", [$category->slug, $document->slug]),
                        "category" => $category->name,
                        "titleLower" => mb_strtolower($document->title)
                    ];
                }
                
                // Документы из подкатегорий
                foreach($category->children as $subcategory) {
                    foreach($subcategory->subcategoryDocuments as $document) {
                        $docs[] = [
                            "title" => $document->title,
                            "url" => route("docs.subcategory.show", [$category->slug, $subcategory->slug, $document->slug]),
                            "category" => $category->name . " > " . $subcategory->name,
                            "titleLower" => mb_strtolower($document->title)
                        ];
                    }
                }
                
                return $docs;
            })
        ], JSON_UNESCAPED_UNICODE | JSON_HEX_APOS | JSON_HEX_QUOT) !!}');

        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('search-input');
            const searchResults = document.getElementById('search-results');
            const resultsList = document.getElementById('results-list');
            const noResults = document.getElementById('no-results');
            const searchTermDisplay = document.getElementById('search-term-display');
            const resultsCounter = document.getElementById('results-counter');
            const resultsCount = document.getElementById('results-count');

            // Выводим все документы для отладки
            console.log('Всего документов:', allDocuments.length);
            allDocuments.forEach(doc => {
                console.log(`Документ: "${doc.title}" (нижний регистр: "${doc.titleLower}")`);
            });

            // Функция поиска
            function performSearch() {
                const searchTerm = searchInput.value.trim().toLowerCase();
                
                // Скрываем все результаты, если строка поиска короче 3 символов
                if (searchTerm.length < 3) {
                    searchResults.style.display = 'none';
                    noResults.style.display = 'none';
                    return;
                }

                console.log('Поисковый запрос:', searchTerm);

                // Очищаем предыдущие результаты
                resultsList.innerHTML = '';
                
                // Разбиваем поисковый запрос на слова
                const searchWords = searchTerm.split(/\s+/).filter(word => word.length > 2);
                console.log('Поисковые слова:', searchWords);
                
                // Поиск документов
                const matchedDocuments = allDocuments.filter(doc => {
                    console.log(`Проверяем документ: "${doc.title}"`);
                    
                    // Простая проверка по включению для каждого слова
                    if (searchWords.length > 0) {
                        // Проверяем каждое поисковое слово
                        return searchWords.some(searchWord => {
                            const found = doc.titleLower.indexOf(searchWord) !== -1;
                            if (found) {
                                console.log(`Совпадение найдено: "${searchWord}" в "${doc.title}"`);
                            }
                            return found;
                        });
                    } else {
                        // Для короткого запроса проверяем прямое вхождение
                        const found = doc.titleLower.indexOf(searchTerm) !== -1;
                        if (found) {
                            console.log(`Короткое совпадение найдено: "${searchTerm}" в "${doc.title}"`);
                        }
                        return found;
                    }
                });

                console.log('Найдено документов:', matchedDocuments.length);

                // Сортировка результатов по релевантности
                matchedDocuments.sort((a, b) => {
                    const aTitle = a.titleLower;
                    const bTitle = b.titleLower;
                    const aTitleWords = aTitle.split(/[\s-]+/);
                    const bTitleWords = bTitle.split(/[\s-]+/);
                    
                    // Если первое слово в названии совпадает с поисковым запросом
                    const aFirstWordMatch = aTitleWords[0].startsWith(searchTerm);
                    const bFirstWordMatch = bTitleWords[0].startsWith(searchTerm);
                    
                    if (aFirstWordMatch && !bFirstWordMatch) return -1;
                    if (!aFirstWordMatch && bFirstWordMatch) return 1;
                    
                    // Если точное совпадение с запросом
                    if (aTitle.includes(searchTerm) && !bTitle.includes(searchTerm)) {
                        return -1;
                    }
                    if (!aTitle.includes(searchTerm) && bTitle.includes(searchTerm)) {
                        return 1;
                    }
                    
                    // Иначе по алфавиту
                    return aTitle.localeCompare(bTitle);
                });

                // Отображаем результаты или сообщение "ничего не найдено"
                if (matchedDocuments.length > 0) {
                    // Добавляем каждый найденный документ в список
                    matchedDocuments.forEach(doc => {
                        const li = document.createElement('li');
                        li.className = 'search-result-item';
                        li.innerHTML = `
                            <a href="${doc.url}" class="block">
                                <div class="flex flex-col">
                                    <span class="search-result-title">${doc.title}</span>
                                    <span class="search-result-category">${doc.category}</span>
                                </div>
                            </a>
                        `;
                        resultsList.appendChild(li);
                    });

                    // Показываем список результатов
                    searchResults.style.display = 'block';
                    noResults.style.display = 'none';
                    
                    // Обновляем счетчик результатов
                    resultsCount.textContent = matchedDocuments.length;
                    resultsCounter.style.display = matchedDocuments.length > 5 ? 'block' : 'none';
                } else {
                    // Показываем сообщение "ничего не найдено"
                    searchTermDisplay.textContent = searchTerm;
                    searchResults.style.display = 'none';
                    noResults.style.display = 'block';
                }
            }

            // Добавляем обработчик событий
            searchInput.addEventListener('input', performSearch);
            
            // Закрытие поиска при клике вне
            document.addEventListener('click', function(event) {
                if (!searchInput.contains(event.target) && !searchResults.contains(event.target) && !noResults.contains(event.target)) {
                    searchResults.style.display = 'none';
                    noResults.style.display = 'none';
                }
            });
        });
    </script>
    @endpush
</x-app-layout>
