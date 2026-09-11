<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['category_id', 'title', 'slug', 'video_url', 'content', 'starter_code', 'order'])]
class Lesson extends Model
{
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'order' => 'integer',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function progressTrackers(): HasMany
    {
        return $this->hasMany(ProgressTracker::class);
    }

    /**
     * Users who have (or are tracking progress toward) completing this lesson.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'progress_trackers')
            ->withPivot('completed', 'completed_at')
            ->withTimestamps();
    }
}
