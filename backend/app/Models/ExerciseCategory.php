<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExerciseCategory extends Model
{
    use HasFactory;

    protected $table = 'exercise_categories';
    protected $primaryKey = 'category_id';

    protected $fillable = [
        'category_name',
        'category_type',
        'description',
    ];

    protected $casts = [
        'category_type' => 'string',
    ];

    // Relationships
    public function exercises(): HasMany
    {
        return $this->hasMany(ExerciseDatabase::class, 'category_id', 'category_id');
    }

    // Scopes
    public function scopeByType($query, string $type)
    {
        return $query->where('category_type', $type);
    }

    public function scopeCardiovascular($query)
    {
        return $query->where('category_type', 'cardiovascular');
    }

    public function scopeStrength($query)
    {
        return $query->where('category_type', 'strength');
    }
}
