<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * I-create ang tree_nodes table.
     * Matag node kay usa ka slot sa binary tree.
     * position_key — unique path string (e.g. "1", "1-L", "1-L-R")
     * parent_key   — position_key sa parent node
     * name         — ngalan nga gi-fill sa admin/member
     */
    public function up(): void
    {
        Schema::create('tree_nodes', function (Blueprint $table): void {
            $table->id();

            // Unique key para sa position — infinite depth support
            // Root = "root", children = "root-L" / "root-R", etc.
            $table->string('position_key')->unique();

            // Parent key — null kung root
            $table->string('parent_key')->nullable()->index();

            // Display number sa node — 1 to 31 within current view
            $table->unsignedInteger('display_number');

            // Depth level — 0 = root, 1 = level 2, etc.
            $table->unsignedInteger('depth')->default(0);

            // Side — L or R (null for root)
            $table->enum('side', ['L', 'R'])->nullable();

            // Ngalan nga gi-fill sa slot
            $table->string('name')->nullable();

            // Kinsa ang nag-fill — optional
            $table->foreignId('filled_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // Kung kanus-a gi-fill
            $table->timestamp('filled_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * I-drop ang tree_nodes table.
     */
    public function down(): void
    {
        Schema::dropIfExists('tree_nodes');
    }
};
