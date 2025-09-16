<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FoodItem extends Model
{
    use HasFactory;

    protected $table = 'food_items';
    protected $primaryKey = 'food_id';

    protected $fillable = [
        'food_name',
        'brand',
        'category_id',
        'serving_size',
        'serving_unit',
        'calories_per_serving',
        'protein_per_serving',
        'carbs_per_serving',
        'fat_per_serving',
        'fiber_per_serving',
        'sugar_per_serving',
        'sodium_per_serving',
        'is_verified',
        'is_public',
        'created_by_user_id',
    ];

    protected $casts = [
        'calories_per_serving' => 'decimal:2',
        'protein_per_serving' => 'decimal:2',
        'carbs_per_serving' => 'decimal:2',
        'fat_per_serving' => 'decimal:2',
        'fiber_per_serving' => 'decimal:2',
        'sugar_per_serving' => 'decimal:2',
        'sodium_per_serving' => 'decimal:2',
        'is_verified' => 'boolean',
        'is_public' => 'boolean',
    ];

    // Relationships
    public function category(): BelongsTo
    {
        return $this->belongsTo(FoodCategory::class, 'category_id', 'category_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function diaryEntries(): HasMany
    {
        return $this->hasMany(FoodDiaryEntry::class, 'food_id', 'food_id');
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

    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('food_name', 'LIKE', "%{$search}%")
              ->orWhere('brand', 'LIKE', "%{$search}%");
        });
    }

    public function scopeByCategory($query, int $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    // Helper methods
    public function canBeEditedBy(User $user): bool
    {
        return $user->isAdmin() || $this->created_by_user_id === $user->id;
    }

    /**
     * Calculate nutrition values for a given serving amount
     */
    public function calculateNutrition(float $servingAmount): array
    {
        return [
            'calories' => round($this->calories_per_serving * $servingAmount, 2),
            'protein' => round($this->protein_per_serving * $servingAmount, 2),
            'carbs' => round($this->carbs_per_serving * $servingAmount, 2),
            'fat' => round($this->fat_per_serving * $servingAmount, 2),
            'fiber' => round($this->fiber_per_serving * $servingAmount, 2),
            'sugar' => round(($this->sugar_per_serving ?? 0) * $servingAmount, 2),
            'sodium' => round(($this->sodium_per_serving ?? 0) * $servingAmount, 2),
        ];
    }
}
