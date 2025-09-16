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
        Schema::create('strength_exercise_entries', function (Blueprint $table) {
            $table->id('entry_id');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('exercise_id')
                ->references('exercise_id')->on('exercise_database')
                ->onDelete('cascade');
            $table->date('entry_date');
            $table->integer('sets'); // Number of sets performed
            $table->json('reps_per_set'); // [12, 10, 8] - reps for each set
            $table->json('weight_per_set'); // [50, 55, 60] - weight for each set
            $table->enum('weight_unit', ['kg', 'lbs'])->default('kg');
            $table->integer('rest_time_seconds')->nullable(); // Rest between sets
            $table->decimal('calories_burned', 8, 2)->nullable(); // Estimated calories
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['user_id', 'entry_date']);
            $table->index('exercise_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('strength_exercise_entries');
    }
};
