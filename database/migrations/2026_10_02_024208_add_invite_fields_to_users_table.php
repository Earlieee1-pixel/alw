<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * I-add ang invite_code ug referred_by sa users table.
     * invite_code  — unique code sa matag member para ma-invite ang uban
     * referred_by  — ID sa nag-invite sa user (upline)
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            // Unique invite code — gigamit para mag-register ang bag-ong member
            $table->string('invite_code', 12)->unique()->nullable()->after('email');

            // Foreign key ngadto sa users table — kinsa ang nag-invite
            $table->foreignId('referred_by')->nullable()->constrained('users')->nullOnDelete()->after('invite_code');

            // Status sa account — active, inactive, suspended
            // PostgreSQL compatible — string instead of enum
            $table->string('status', 20)->default('active')->after('referred_by');
        });
    }

    /**
     * I-reverse ang migration.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropForeign(['referred_by']);
            $table->dropColumn(['invite_code', 'referred_by', 'status']);
        });
    }
};
