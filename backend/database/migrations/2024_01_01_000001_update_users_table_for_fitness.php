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
        Schema::table('users', function (Blueprint $table) {
            // Personal Information
            $table->string('first_name')->after('email');
            $table->string('last_name')->after('first_name');
            $table->date('date_of_birth')->nullable()->after('last_name');
            $table->enum('gender', ['male', 'female', 'other'])->nullable()->after('date_of_birth');
            $table->integer('height_cm')->nullable()->after('gender');
            
            // Activity and Account
            $table->enum('activity_level', ['sedentary', 'light', 'moderate', 'active', 'very_active'])
                ->default('moderate')->after('height_cm');
            $table->enum('role', ['user', 'admin'])->default('user')->after('activity_level');
            $table->boolean('is_active')->default(true)->after('role');
            
            // Security fields
            $table->timestamp('last_login')->nullable()->after('remember_token');
            $table->integer('failed_login_attempts')->default(0)->after('last_login');
            $table->timestamp('account_locked_until')->nullable()->after('failed_login_attempts');
            
            // Indexes for performance
            $table->index('email');
            $table->index('is_active');
            $table->index('role');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'first_name',
                'last_name',
                'date_of_birth',
                'gender',
                'height_cm',
                'activity_level',
                'role',
                'is_active',
                'last_login',
                'failed_login_attempts',
                'account_locked_until'
            ]);
            
            $table->dropIndex(['email']);
            $table->dropIndex(['is_active']);
            $table->dropIndex(['role']);
        });
    }
};