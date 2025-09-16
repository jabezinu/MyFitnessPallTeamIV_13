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
        Schema::create('weight_checkins', function (Blueprint $table) {
            $table->id('checkin_id');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->decimal('weight_kg', 5, 2);
            $table->date('checkin_date');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index(['user_id', 'checkin_date']);
            $table->unique(['user_id', 'checkin_date']); // Only one weight check-in per day
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('weight_checkins');
    }
};