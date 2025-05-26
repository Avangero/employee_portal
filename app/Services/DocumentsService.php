<?php

namespace App\Services;

use App\Models\Document;
use App\Models\DocumentCategory;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentsService
{
    protected string $storagePath = 'docs';

    public function store(array $data): Document
    {
        $categoryId = $data['category_id'] ?? null;
        $subcategoryId = $data['subcategory_id'] ?? null;

        if ($categoryId && !$subcategoryId) {
            $selectedCategory = DocumentCategory::find($categoryId);
            if ($selectedCategory && $selectedCategory->parent_id !== null) {
                $subcategoryId = $categoryId;
                $categoryId = $selectedCategory->parent_id;
            }
        }

        $document = Document::create([
            'title' => $data['title'],
            'slug' => $data['slug'],
            'category_id' => $categoryId,
            'subcategory_id' => $subcategoryId,
        ]);

        $filePath = $this->saveToFile($document, $data['content']);
        $document->update(['file_path' => $filePath]);

        return $document;
    }

    /**
     * Обновить существующий документ
     */
    public function update(Document $document, array $data): Document
    {
        $categoryId = $data['category_id'] ?? null;
        $subcategoryId = $data['subcategory_id'] ?? null;

        if ($categoryId && !$subcategoryId) {
            $selectedCategory = DocumentCategory::find($categoryId);
            if ($selectedCategory && $selectedCategory->parent_id !== null) {
                $subcategoryId = $categoryId;
                $categoryId = $selectedCategory->parent_id;
            }
        }

        $document->update([
            'title' => $data['title'],
            'slug' => $data['slug'],
            'category_id' => $categoryId,
            'subcategory_id' => $subcategoryId,
        ]);

        $oldPath = $document->file_path;
        $newPath = $this->getFilePath($document);

        if ($oldPath && $oldPath !== $newPath && Storage::exists($oldPath)) {
            Storage::delete($oldPath);
        }

        $filePath = $this->saveToFile($document, $data['content']);
        $document->update(['file_path' => $filePath]);

        return $document;
    }

    public function delete(Document $document): void
    {
        $this->deleteFile($document);
        $document->delete();
    }

    protected function saveToFile(Document $document, string $content): string
    {
        $path = $this->getFilePath($document);
        $directory = dirname($path);

        if (!Storage::exists($directory)) {
            Storage::makeDirectory($directory);
        }

        $fileContent = "---\n";
        $fileContent .= "id: {$document->slug}\n";
        $fileContent .= "title: {$document->title}\n";
        $fileContent .= "description: {$document->title}\n";
        $fileContent .= "---\n\n";
        $fileContent .= $content;

        Storage::put($path, $fileContent);

        return $path;
    }

    protected function deleteFile(Document $document): void
    {
        if ($document->file_path && Storage::exists($document->file_path)) {
            Storage::delete($document->file_path);
        }
    }

    protected function getFilePath(Document $document): string
    {
        $categorySlug = $document->category->slug;

        if ($document->subcategory) {
            return "{$this->storagePath}/{$categorySlug}/{$document->subcategory->slug}/{$document->slug}.md";
        }

        return "{$this->storagePath}/{$categorySlug}/{$document->slug}.md";
    }
}
