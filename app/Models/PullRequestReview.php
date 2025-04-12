<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PullRequestReview extends Model {
    use HasFactory;

    protected $fillable = ['pull_request_id', 'reviewer_id', 'status', 'comment'];

    protected static function booted() {
        static::created(function ($review) {
            if ($review->status === 'approved') {
                $review->pullRequest->increment('approvals_count');
            }
            $review->pullRequest->updateStatus();
        });

        static::updated(function ($review) {
            $review->pullRequest->updateStatus();
        });
    }

    public function pullRequest(): BelongsTo {
        return $this->belongsTo(PullRequest::class);
    }

    public function reviewer(): BelongsTo {
        return $this->belongsTo(User::class, 'reviewer_id');
    }
}
