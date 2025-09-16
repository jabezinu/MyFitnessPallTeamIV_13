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
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id('setting_id');
            $table->string('setting_key')->unique(); // "max_login_attempts", "session_timeout"
            $table->text('setting_value'); // "5", "24", could be JSON for complex values
            $table->text('description')->nullable(); // What does this setting do?
            $table->enum('setting_type', ['string', 'integer', 'boolean', 'json'])->default('string');
            $table->boolean('is_public')->default(false); // Can regular users see this?
            $table->foreignId('updated_by_user_id')->nullable()
                ->constrained('users')->onDelete('set null');
            $table->timestamps();
            
            // Indexes
            $table->index('setting_key');
            $table->index('is_public');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_settings');
    }
};
