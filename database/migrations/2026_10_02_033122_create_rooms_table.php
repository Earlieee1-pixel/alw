<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * I-create ang rooms table — para sa Jitsi video call rooms.
     */
    public function up(): void
    {
        Schema::create('rooms', function (Blueprint $table): void {
            $table->id();

            // Room title — para sa display
            $table->string('title');

            // Unique room code — gi-generate sa admin, gi-share sa members
            $table->string('room_code', 12)->unique();

            // Jitsi room name — unique identifier para sa Jitsi server
            $table->string('jitsi_room')->unique();

            // Description optional
            $table->text('description')->nullable();

            // Kung active pa ang room o closed na
            $table->enum('status', ['active', 'closed'])->default('active');

            // Scheduled start time — optional
            $table->dateTime('scheduled_at')->nullable();

            // Kinsa ang nag-create sa room
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();

            $table->timestamps();
        });
    }

    /**
     * I-drop ang rooms table.
     */
    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
