<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'email',
        'password',
        'first_name',
        'last_name',
        'date_of_birth',
        'gender',
        'height_cm',
        'activity_level',
        'role',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'failed_login_attempts',
        'account_locked_until',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'date_of_birth' => 'date',
            'last_login' => 'datetime',
            'account_locked_until' => 'datetime',
            'is_active' => 'boolean',
            'failed_login_attempts' => 'integer',
            'height_cm' => 'integer',
        ];
    }

    // Relationships
    public function goals(): HasMany
    {
        return $this->hasMany(UserGoal::class);
    }

    public function activeGoal(): HasOne
    {
        return $this->hasOne(UserGoal::class)->where('is_active', true);
    }

    public function weightCheckins(): HasMany
    {
        return $this->hasMany(WeightCheckin::class);
    }

    public function foodDiaryEntries(): HasMany
    {
        return $this->hasMany(FoodDiaryEntry::class);
    }

    public function quickFoodEntries(): HasMany
    {
        return $this->hasMany(QuickFoodEntry::class);
    }

    public function cardioExercises(): HasMany
    {
        return $this->hasMany(CardioExerciseEntry::class);
    }

    public function strengthExercises(): HasMany
    {
        return $this->hasMany(StrengthExerciseEntry::class);
    }

    public function quickExercises(): HasMany
    {
        return $this->hasMany(QuickExerciseEntry::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(UserNotification::class);
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(UserSession::class);
    }

    public function loginAttempts(): HasMany
    {
        return $this->hasMany(LoginAttempt::class);
    }

    // Helper methods
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isActive(): bool
    {
        return $this->is_active;
    }

    public function isAccountLocked(): bool
    {
        return $this->account_locked_until && $this->account_locked_until->isFuture();
    }

    /**
     * Calculate BMR (Basal Metabolic Rate) using Mifflin-St Jeor Equation
     */
    public function calculateBMR(): ?float
    {
        if (!$this->height_cm || !$this->date_of_birth || !$this->gender) {
            return null;
        }

        $age = $this->date_of_birth->diffInYears(now());
        $weight = $this->weightCheckins()->latest('checkin_date')->first()?->weight_kg;
        
        if (!$weight) {
            return null;
        }

        // Mifflin-St Jeor Equation
        if ($this->gender === 'male') {
            return (10 * $weight) + (6.25 * $this->height_cm) - (5 * $age) + 5;
        } else {
            return (10 * $weight) + (6.25 * $this->height_cm) - (5 * $age) - 161;
        }
    }

    /**
     * Calculate daily calorie needs based on activity level
     */
    public function calculateDailyCalories(): ?float
    {
        $bmr = $this->calculateBMR();
        if (!$bmr) {
            return null;
        }

        $multipliers = [
            'sedentary' => 1.2,
            'light' => 1.375,
            'moderate' => 1.55,
            'active' => 1.725,
            'very_active' => 1.9,
        ];

        return $bmr * ($multipliers[$this->activity_level] ?? 1.55);
    }
}
