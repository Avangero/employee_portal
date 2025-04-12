<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Project extends Model {
    use HasFactory;

    protected $fillable = ['name', 'description', 'team_id', 'leader_id'];

    public function team(): BelongsTo {
        return $this->belongsTo(Team::class);
    }

    public function leader(): BelongsTo {
        return $this->belongsTo(User::class, 'leader_id');
    }

    public function members(): BelongsToMany {
        return $this->belongsToMany(User::class);
    }
}
