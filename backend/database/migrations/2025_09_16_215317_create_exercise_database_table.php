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
        Schema::create('exercise_database', function (Blueprint $table) {
            $table->id('exercise_id');
            $table->string('exercise_name');
            $table->foreignId('category_id')
                ->references('category_id')->on('exercise_categories')
                ->onDelete('cascade');
            $table->enum('exercise_type', ['cardiovascular', 'strength', 'flexibility', 'sports']);
            $table->decimal('calories_per_minute', 5, 2)->nullable(); // Base calorie burn per minute
            $table->text('description')->nullable();
            $table->text('instructions')->nullable();
            $table->json('muscle_groups')->nullable(); // ["chest", "shoulders", "triceps"]
            $table->string('equipment_needed')->nullable();
            $table->enum('difficulty_level', ['beginner', 'intermediate', 'advanced'])->default('beginner');
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_public')->default(true);
            $table->foreignId('created_by_user_id')->nullable()
                ->constrained('users')->onDelete('set null');
            $table->timestamps();
            
            // Indexes
            $table->index('exercise_name');
            $table->index('exercise_type');
            $table->index(['is_verified', 'is_public']);
            $table->index('difficulty_level');
            $table->index('created_by_user_id');
            // Note: Fulltext index not supported in SQLite
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exercise_database');
    }
};
