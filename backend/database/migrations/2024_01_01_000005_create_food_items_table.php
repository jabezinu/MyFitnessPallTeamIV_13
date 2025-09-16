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
        Schema::create('food_items', function (Blueprint $table) {
            $table->id('food_id');
            $table->string('food_name');
            $table->string('brand')->nullable();
            $table->foreignId('category_id')->nullable()
                ->references('category_id')->on('food_categories')
                ->onDelete('set null');
            
            // Serving information
            $table->string('serving_size');
            $table->string('serving_unit');
            
            // Nutritional information per serving
            $table->decimal('calories_per_serving', 8, 2);
            $table->decimal('protein_per_serving', 8, 2);
            $table->decimal('carbs_per_serving', 8, 2);
            $table->decimal('fat_per_serving', 8, 2);
            $table->decimal('fiber_per_serving', 8, 2)->default(0);
            $table->decimal('sugar_per_serving', 8, 2)->nullable();
            $table->decimal('sodium_per_serving', 8, 2)->nullable();
            
            // Meta information
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_public')->default(true);
            $table->foreignId('created_by_user_id')->nullable()
                ->constrained('users')->onDelete('set null');
            
            $table->timestamps();
            
            // Indexes
            $table->index('food_name');
            $table->index('brand');
            $table->index(['is_verified', 'is_public']);
            $table->index('created_by_user_id');
            // Note: Fulltext index not supported in SQLite
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('food_items');
    }
};