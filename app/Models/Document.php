<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Document extends Model
{
    use HasFactory;

    protected $table = 'documents';

    protected $fillable = [
        'title',
        'file_path',
        'category_id',
        'subcategory_id',
        'slug',
    ];

    protected $appends = ['content'];

    public function category(): BelongsTo
    {
        return $this->belongsTo(DocumentCategory::class, 'category_id');
    }

    public function subcategory(): BelongsTo
    {
        return $this->belongsTo(DocumentCategory::class, 'subcategory_id');
    }

    public function getContentAttribute()
    {
        if (!$this->file_path || !Storage::exists($this->file_path)) {
            return '';
        }

        $fileContent = Storage::get($this->file_path);
        $parts = explode('---', $fileContent, 3);

        return isset($parts[2]) ? trim($parts[2]) : '';
    }

    public function setContentAttribute($value)
    {
        $this->attributes['content'] = $value;
    }

    public function setCategoryIdAttribute($value)
    {
        $selectedCategory = DocumentCategory::find($value);

        if ($selectedCategory && $selectedCategory->parent_id !== null) {
            if (!array_key_exists('subcategory_id', $this->attributes) || $this->attributes['subcategory_id'] === null) {
                $this->attributes['subcategory_id'] = $value;
                $this->attributes['category_id'] = $selectedCategory->parent_id;
            } else {
                $this->attributes['category_id'] = $value;
            }
        } else {
            $this->attributes['category_id'] = $value;
        }
    }
}
