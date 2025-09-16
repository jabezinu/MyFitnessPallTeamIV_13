<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_goals', function (Blueprint $table) {
            $table->id('goal_id');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Nutrition goals
            $table->integer('daily_calories_target')->nullable();
            $table->decimal('daily_protein_target', 8, 2)->nullable();
            $table->decimal('daily_carbs_target', 8, 2)->nullable();
            $table->decimal('daily_fat_target', 8, 2)->nullable();
            
            // Weight goals
            $table->decimal('weight_goal_kg', 5, 2)->nullable();
            $table->enum('goal_type', ['lose', 'gain', 'maintain'])->default('maintain');
            $table->date('target_date')->nullable();
            
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // Indexes
            $table->index(['user_id', 'is_active']);
            $table->index('target_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_goals');
    }
};