<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * I-create ang videos table — para sa training video library.
     */
    public function up(): void
    {
        Schema::create('videos', function (Blueprint $table): void {
            $table->id();

            // Basic info
            $table->string('title');
            $table->text('description')->nullable();

            // YouTube o Vimeo embed URL
            $table->string('embed_url');

            // Video ID gikan sa YouTube/Vimeo para sa thumbnail
            $table->string('video_id')->nullable();

            // Source platform — PostgreSQL compatible string
            $table->string('source', 20)->default('youtube');

            // Category para sa pag-organize — PostgreSQL compatible string
            $table->string('category', 30)->default('general');

            // Kung published o draft pa
            $table->boolean('is_published')->default(true);

            // Kinsa ang nag-post
            $table->foreignId('posted_by')->constrained('users')->cascadeOnDelete();

            $table->timestamps();
        });
    }

    /**
     * I-drop ang videos table.
     */
    public function down(): void
    {
        Schema::dropIfExists('videos');
    }
};
