<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DocumentViewController extends Controller
{
    /**
     * Отображает список документов
     */
    public function index()
    {
        $categories = DocumentCategory::whereNull('parent_id')->get();

        foreach ($categories as $category) {
            $category->load(['documents']);
            $category->load(['children']);

            foreach ($category->children as $subcategory) {
                $subcategory->load(['subcategoryDocuments']);
            }
        }

        return view('docs.index', compact('categories'));
    }

    public function show($categorySlug, $subcategoryOrSlug = null, $slug = null)
    {
        $category = DocumentCategory::where('slug', $categorySlug)->first();

        if (!$category) {
            abort(404, 'Категория не найдена');
        }

        if ($subcategoryOrSlug && $slug) {
            return $this->showDocumentInSubcategory($category, $subcategoryOrSlug, $slug);
        }
        elseif ($subcategoryOrSlug) {
            $potentialSubcategory = DocumentCategory::where('slug', $subcategoryOrSlug)
                ->where('parent_id', $category->id)
                ->first();

            if ($potentialSubcategory) {
                $documents = Document::where('subcategory_id', $potentialSubcategory->id)->get();

                return view('docs.category', [
                    'category' => $potentialSubcategory,
                    'subcategories' => collect(),
                    'documents' => $documents,
                    'parentCategory' => $category
                ]);
            }

            return $this->showDocumentInCategory($category, $subcategoryOrSlug);
        }

        $category->load(['documents', 'children']);
        $subcategories = $category->children;

        foreach ($subcategories as $subcategory) {
            $subcategory->load(['subcategoryDocuments']);
        }

        $documents = $category->documents;

        return view('docs.category', compact('category', 'subcategories', 'documents'));
    }

    private function showDocumentInSubcategory($category, $subcategorySlug, $slug)
    {
        $subcategory = DocumentCategory::where('slug', $subcategorySlug)
            ->where('parent_id', $category->id)
            ->first();

        if (!$subcategory) {
            abort(404, 'Подкатегория не найдена');
        }

        $document = Document::where('slug', $slug)
            ->where(function($query) use ($subcategory) {
                $query->where('subcategory_id', $subcategory->id)
                    ->orWhere('category_id', $subcategory->id);
            })
            ->first();

        if (!$document) {
            $anyDocument = Document::where('slug', $slug)->first();

            if ($anyDocument) {
                $documentCategory = DocumentCategory::find($anyDocument->category_id);
                if ($documentCategory && $documentCategory->parent_id !== null) {
                    $anyDocument->subcategory_id = $anyDocument->category_id;
                    $anyDocument->category_id = $documentCategory->parent_id;
                    $anyDocument->save();

                    if ($anyDocument->subcategory_id == $subcategory->id && $anyDocument->category_id == $category->id) {
                        $document = $anyDocument;
                    }
                }
            }

            if (!$document) {
                $possibleDocument = Document::where('slug', $slug)->first();

                if ($possibleDocument) {
                    $possibleDocument->subcategory_id = $subcategory->id;
                    $possibleDocument->category_id = $category->id;
                    $possibleDocument->save();

                    $document = $possibleDocument;
                } else {
                    abort(404, 'Документ не найден');
                }
            }
        }

        if ($document->category_id !== $category->id) {
            $document->category_id = $category->id;
            $document->save();

        }

        if ($document->subcategory_id !== $subcategory->id) {
            $document->subcategory_id = $subcategory->id;
            $document->save();
        }

        return view('docs.show', compact('category', 'subcategory', 'document'));
    }

    private function showDocumentInCategory($category, $slug)
    {
        $document = Document::where('slug', $slug)
            ->where('category_id', $category->id)
            ->whereNull('subcategory_id')
            ->first();

        if (!$document) {
            abort(404, 'Документ не найден');
        }

        return view('docs.show', compact('category', 'document'));
    }
}
