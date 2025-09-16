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
        Schema::create('exercise_categories', function (Blueprint $table) {
            $table->id('category_id');
            $table->string('category_name', 100)->unique();
            $table->enum('category_type', ['cardiovascular', 'strength', 'flexibility', 'sports']);
            $table->text('description')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index('category_type');
            $table->index('category_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exercise_categories');
    }
};
