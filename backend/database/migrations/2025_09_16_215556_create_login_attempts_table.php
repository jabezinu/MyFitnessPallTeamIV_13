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
        Schema::create('login_attempts', function (Blueprint $table) {
            $table->id('attempt_id');
            $table->string('email'); // Email that attempted login
            $table->ipAddress('ip_address'); // Source IP
            $table->boolean('success')->default(false); // Login success/failure
            $table->timestamp('attempted_at')->useCurrent();
            $table->text('user_agent')->nullable();
            $table->string('failure_reason')->nullable(); // Why did login fail?
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            
            // Indexes for security analysis
            $table->index(['email', 'attempted_at']);
            $table->index(['ip_address', 'attempted_at']);
            $table->index(['success', 'attempted_at']);
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('login_attempts');
    }
};
