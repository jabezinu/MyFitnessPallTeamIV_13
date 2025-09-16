<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class FoodDiaryEntry extends Model
{
    use HasFactory;

    protected $table = 'food_diary_entries';
    protected $primaryKey = 'entry_id';

    protected $fillable = [
        'user_id',
        'food_id',
        'meal_type',
        'serving_amount',
        'entry_date',
        'calories_consumed',
        'protein_consumed',
        'carbs_consumed',
        'fat_consumed',
    ];

    protected $casts = [
        'serving_amount' => 'decimal:2',
        'entry_date' => 'date',
        'calories_consumed' => 'decimal:2',
        'protein_consumed' => 'decimal:2',
        'carbs_consumed' => 'decimal:2',
        'fat_consumed' => 'decimal:2',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function foodItem(): BelongsTo
    {
        return $this->belongsTo(FoodItem::class, 'food_id', 'food_id');
    }

    // Scopes
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForDate($query, Carbon $date)
    {
        return $query->whereDate('entry_date', $date);
    }

    public function scopeForDateRange($query, Carbon $startDate, Carbon $endDate)
    {
        return $query->whereBetween('entry_date', [$startDate, $endDate]);
    }

    public function scopeForMeal($query, string $mealType)
    {
        return $query->where('meal_type', $mealType);
    }

    public function scopeBreakfast($query)
    {
        return $query->where('meal_type', 'breakfast');
    }

    public function scopeLunch($query)
    {
        return $query->where('meal_type', 'lunch');
    }

    public function scopeDinner($query)
    {
        return $query->where('meal_type', 'dinner');
    }

    public function scopeSnacks($query)
    {
        return $query->where('meal_type', 'snack');
    }

    // Boot method to auto-calculate nutrition values
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($entry) {
            $entry->calculateNutritionValues();
        });

        static::updating(function ($entry) {
            if ($entry->isDirty(['food_id', 'serving_amount'])) {
                $entry->calculateNutritionValues();
            }
        });
    }

    /**
     * Calculate and set nutrition values based on food item and serving amount
     */
    protected function calculateNutritionValues(): void
    {
        if ($this->foodItem && $this->serving_amount) {
            $nutrition = $this->foodItem->calculateNutrition($this->serving_amount);
            
            $this->calories_consumed = $nutrition['calories'];
            $this->protein_consumed = $nutrition['protein'];
            $this->carbs_consumed = $nutrition['carbs'];
            $this->fat_consumed = $nutrition['fat'];
        }
    }
}
