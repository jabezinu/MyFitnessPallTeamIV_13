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
        Schema::create('cardio_exercise_entries', function (Blueprint $table) {
            $table->id('entry_id');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('exercise_id')
                ->references('exercise_id')->on('exercise_database')
                ->onDelete('cascade');
            $table->date('entry_date');
            $table->integer('duration_minutes'); // How long did they exercise?
            $table->decimal('calories_burned', 8, 2); // Calculated calories
            $table->decimal('distance', 8, 2)->nullable(); // Distance covered
            $table->enum('distance_unit', ['km', 'miles', 'meters'])->nullable();
            $table->enum('intensity_level', ['low', 'moderate', 'high'])->default('moderate');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['user_id', 'entry_date']);
            $table->index('exercise_id');
            $table->index('intensity_level');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cardio_exercise_entries');
    }
};
