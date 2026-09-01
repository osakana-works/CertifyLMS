<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\EnrollmentGoalFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

/**
 * 受講登録(Enrollment)配下の個人目標。受講生本人が資格ごとに自由入力で立てる。
 *
 * achieved_at が非null = 達成済み。
 *
 * 関連: Enrollment(所属する受講登録)
 */
class EnrollmentGoal extends Model
{
    /** @use HasFactory<EnrollmentGoalFactory> */
    use HasFactory, HasUlids;

    protected $fillable = [
        'enrollment_id',
        'title',
        'description',
        'target_date',
        'achieved_at',
    ];

    protected $casts = [
        'target_date' => 'date',
        'achieved_at' => 'datetime',
    ];

    /**
     * @return BelongsTo<Enrollment, $this>
     */
    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    /**
     * @param Builder<EnrollmentGoal> $query
     *
     * @return Builder<EnrollmentGoal>
     */
    public function scopeDisplayOrder(Builder $query): Builder
    {
        return $query->orderBy('achieved_at')->orderByDesc('target_date');
    }

    public function isAchieved(): bool
    {
        return $this->achieved_at !== null;
    }
}