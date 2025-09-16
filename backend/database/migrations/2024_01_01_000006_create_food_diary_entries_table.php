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
        Schema::create('food_diary_entries', function (Blueprint $table) {
            $table->id('entry_id');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('food_id')
                ->references('food_id')->on('food_items')
                ->onDelete('cascade');
            
            $table->enum('meal_type', ['breakfast', 'lunch', 'dinner', 'snack']);
            $table->decimal('serving_amount', 8, 2);
            $table->date('entry_date');
            
            // Calculated nutritional values (cached for performance)
            $table->decimal('calories_consumed', 10, 2);
            $table->decimal('protein_consumed', 8, 2);
            $table->decimal('carbs_consumed', 8, 2);
            $table->decimal('fat_consumed', 8, 2);
            $table->decimal('fiber_consumed', 8, 2)->default(0);
            
            $table->timestamps();
            
            // Indexes for common queries
            $table->index(['user_id', 'entry_date']);
            $table->index(['user_id', 'entry_date', 'meal_type']);
            $table->index('entry_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('food_diary_entries');
    }
};