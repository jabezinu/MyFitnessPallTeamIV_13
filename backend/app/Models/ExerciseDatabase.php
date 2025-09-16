<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExerciseDatabase extends Model
{
    use HasFactory;

    protected $table = 'exercise_database';
    protected $primaryKey = 'exercise_id';

    protected $fillable = [
        'exercise_name',
        'category_id',
        'exercise_type',
        'calories_per_minute',
        'description',
        'instructions',
        'muscle_groups',
        'equipment_needed',
        'difficulty_level',
        'is_verified',
        'is_public',
        'created_by_user_id',
    ];

    protected $casts = [
        'calories_per_minute' => 'decimal:2',
        'muscle_groups' => 'array',
        'is_verified' => 'boolean',
        'is_public' => 'boolean',
    ];

    // Relationships
    public function category(): BelongsTo
    {
        return $this->belongsTo(ExerciseCategory::class, 'category_id', 'category_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function cardioEntries(): HasMany
    {
        return $this->hasMany(CardioExerciseEntry::class, 'exercise_id', 'exercise_id');
    }

    public function strengthEntries(): HasMany
    {
        return $this->hasMany(StrengthExerciseEntry::class, 'exercise_id', 'exercise_id');
    }

    // Scopes
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('exercise_type', $type);
    }

    public function scopeByDifficulty($query, string $difficulty)
    {
        return $query->where('difficulty_level', $difficulty);
    }

    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('exercise_name', 'LIKE', "%{$search}%")
              ->orWhere('description', 'LIKE', "%{$search}%");
        });
    }

    // Helper methods
    public function canBeEditedBy(User $user): bool
    {
        return $user->isAdmin() || $this->created_by_user_id === $user->id;
    }
}
