<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * I-create ang conversations table.
     * Usa ka conversation = duha ka users nga nag-chat.
     */
    public function up(): void
    {
        Schema::create('conversations', function (Blueprint $table): void {
            $table->id();

            // Duha ka participants — always sorted by ID para avoid duplicates
            $table->foreignId('user_one')->constrained('users')->cascadeOnDelete();
            $table->foreignId('user_two')->constrained('users')->cascadeOnDelete();

            // Last message time — para ma-sort ang conversations
            $table->timestamp('last_message_at')->nullable();

            $table->timestamps();

            // I-prevent ang duplicate conversations tali sa same users
            $table->unique(['user_one', 'user_two']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conversations');
    }
};
