<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PullRequest extends Model
{
    use HasFactory;

    // Статусы PR
    public const STATUS_CREATED = 'created';

    public const STATUS_IN_REVIEW = 'in_review';

    public const STATUS_CHANGES_REQUESTED = 'changes_requested';

    public const STATUS_DISPUTED = 'disputed';

    public const STATUS_UPDATED = 'updated';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_RETURNED = 'returned';

    protected $fillable = [
        'url',
        'author_id',
        'team_id',
        'status',
        'returns_count',
        'approvals_count',
        'required_approvals',
    ];

    protected $casts = [
        'approvals_count' => 'integer',
        'required_approvals' => 'integer',
    ];

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(PullRequestReview::class);
    }

    public function isFullyApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function canBeReviewed(): bool
    {
        // PR не может быть проверен если:
        // 1. Он уже одобрен
        // 2. У него нет требуемых аппрувов (нет ревьюверов в команде)
        // 3. Он уже получил все необходимые аппрувы
        return ! $this->isFullyApproved() &&
            $this->required_approvals > 0 &&
            $this->approvals_count < $this->required_approvals;
    }

    public function incrementReturnsCount(): void
    {
        $this->increment('returns_count');
    }

    public function updateStatus(): void
    {
        if ($this->approvals_count >= $this->required_approvals) {
            $this->update(['status' => self::STATUS_APPROVED]);
        }
    }

    public function dispute(): void
    {
        $this->update(['status' => self::STATUS_DISPUTED]);
    }

    public function markAsUpdated(): void
    {
        $this->update(['status' => self::STATUS_UPDATED]);
    }

    public function markAsInReview(): void
    {
        $this->update(['status' => self::STATUS_IN_REVIEW]);
    }

    public function updateRequiredApprovals(): void
    {
        // Получаем количество ревьюверов в команде (исключая автора PR)
        $reviewersCount = User::where('team_id', $this->team_id)
            ->where('is_reviewer', true)
            ->where('id', '!=', $this->author_id)
            ->count();

        // Устанавливаем требуемое количество аппрувов
        // Если ревьювер один, то достаточно одного аппрува
        $this->required_approvals = min($reviewersCount, 2);
        $this->save();
    }
}
