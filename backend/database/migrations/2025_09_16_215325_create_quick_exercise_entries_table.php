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
        Schema::create('quick_exercise_entries', function (Blueprint $table) {
            $table->id('quick_entry_id');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('exercise_name'); // "Basketball pickup game"
            $table->enum('exercise_type', ['cardiovascular', 'strength', 'flexibility', 'sports', 'other'])
                ->default('other');
            $table->integer('duration_minutes'); // How long?
            $table->decimal('calories_burned', 8, 2); // User's estimate
            $table->date('entry_date'); // When was this performed?
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['user_id', 'entry_date']);
            $table->index('exercise_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quick_exercise_entries');
    }
};
