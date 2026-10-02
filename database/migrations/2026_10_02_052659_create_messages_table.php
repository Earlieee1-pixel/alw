<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * I-create ang messages table.
     * Matag message kay belong sa usa ka conversation.
     */
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table): void {
            $table->id();

            // Kinsa ang conversation niini nga message
            $table->foreignId('conversation_id')->constrained()->cascadeOnDelete();

            // Kinsa ang nag-send
            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();

            // Sulod sa message
            $table->text('body');

            // Kung nabasa na sa receiver
            $table->timestamp('read_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
