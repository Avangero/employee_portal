<x-app-layout>
    <x-slot name="header">
        @if(isset($parentCategory))
            docs/{{ $parentCategory->slug }}/{{ $category->slug }}
        @else
            docs/{{ $category->slug }}
        @endif
    </x-slot>

    <div>
        <div class="max-w-7xl">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="mb-8">
                        <div class="flex items-start">
                            @if(isset($parentCategory))
                                <a href="{{ route('docs.category', $parentCategory->slug) }}" class="mr-3">
                                    <div class="w-12 h-12 rounded-lg bg-indigo-50 flex items-center justify-center flex-shrink-0 hover:bg-indigo-100 transition-colors">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                                        </svg>
                                    </div>
                                </a>
                            @endif
                            <div class="w-12 h-12 rounded-lg bg-indigo-100 flex items-center justify-center mr-4 flex-shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-2xl font-semibold text-gray-900 mb-2">{{ $category->name }}</h3>
                                @if(isset($parentCategory))
                                    <p class="text-sm text-gray-600 mb-1">
                                        <a href="{{ route('docs.category', $parentCategory->slug) }}" class="text-indigo-600 hover:underline">
                                            {{ $parentCategory->name }}
                                        </a>
                                        <span class="mx-2">›</span>
                                        <span>{{ $category->name }}</span>
                                    </p>
                                @endif
                                @if($category->description)
                                    <p class="text-gray-600 max-w-3xl">{{ $category->description }}</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    @if($documents->count() > 0)
                        <div class="mb-8">
                            <div class="flex items-center mb-4">
                                <div class="w-8 h-8 rounded-md bg-indigo-50 flex items-center justify-center mr-3">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                                <h4 class="text-lg font-medium text-gray-900">Документы</h4>
                            </div>
                            <div class="border-l-2 border-indigo-100 pl-8 ml-4 space-y-3">
                                @foreach($documents as $document)
                                    <div class="group">
                                        <a href="{{ isset($parentCategory)
                                                ? route('docs.subcategory.show', [$parentCategory->slug, $category->slug, $document->slug])
                                                : route('docs.show', [$category->slug, $document->slug]) }}"
                                           class="flex items-center p-2 hover:bg-gray-50 rounded-lg transition-colors">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3 text-gray-400 group-hover:text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                            </svg>
                                            <span class="text-gray-700 group-hover:text-indigo-600">{{ $document->title }}</span>
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if($subcategories->count() > 0)
                        <div>
                            <div class="flex items-center mb-4">
                                <div class="w-8 h-8 rounded-md bg-indigo-50 flex items-center justify-center mr-3">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                                    </svg>
                                </div>
                                <h4 class="text-lg font-medium text-gray-900">Подкатегории</h4>
                            </div>

                            <div class="border-l-2 border-indigo-100 pl-8 ml-4 space-y-6">
                                @foreach($subcategories as $subcategory)
                                    <div x-data="{ open: false }">
                                        <div @click="open = !open" class="flex items-center justify-between cursor-pointer p-2 hover:bg-gray-50 rounded-lg transition-colors">
                                            <div class="flex items-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-3 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                                                </svg>
                                                <h5 class="text-md font-medium text-gray-800">
                                                    {{ $subcategory->name }}
                                                    <span class="text-xs text-gray-500 ml-2">({{ $subcategory->subcategoryDocuments->count() }} документов)</span>
                                                </h5>
                                            </div>
                                            <div>
                                                <svg x-bind:class="open ? 'rotate-180 transform' : ''" class="w-4 h-4 text-gray-500 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                                </svg>
                                            </div>
                                        </div>

                                        <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="mt-2 ml-7 pl-2 border-l border-gray-200 space-y-3">
                                            @if($subcategory->subcategoryDocuments->count() > 0)
                                                @foreach($subcategory->subcategoryDocuments as $document)
                                                    <div class="group">
                                                        <a href="{{ route('docs.subcategory.show', [$category->slug, $subcategory->slug, $document->slug]) }}" class="flex items-center p-2 hover:bg-gray-50 rounded-lg transition-colors">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-3 text-gray-400 group-hover:text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                                            </svg>
                                                            <span class="text-gray-700 group-hover:text-indigo-600">{{ $document->title }}</span>
                                                        </a>
                                                    </div>
                                                @endforeach
                                            @else
                                                <p class="py-2 px-3 text-sm text-gray-500 italic">В этой подкатегории нет документов.</p>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if($documents->count() === 0 && $subcategories->count() === 0)
                        <div class="py-8 text-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">Нет документов</h3>
                            <p class="mt-1 text-sm text-gray-500">В этой категории пока нет документов.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
