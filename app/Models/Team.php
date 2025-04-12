<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Team extends Model {
    use HasFactory;

    protected $fillable = ['name', 'description', 'leader_id'];

    public function leader(): BelongsTo {
        return $this->belongsTo(User::class, 'leader_id');
    }

    public function members(): HasMany {
        return $this->hasMany(User::class);
    }

    public function projects(): HasMany {
        return $this->hasMany(Project::class);
    }
}
