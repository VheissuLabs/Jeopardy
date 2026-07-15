<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('buzzes', function (Blueprint $table): void {
            $table->index(['game_clue_id', 'status']);
        });

        Schema::table('categories', function (Blueprint $table): void {
            $table->index(['board_id', 'position']);
        });

        Schema::table('clues', function (Blueprint $table): void {
            $table->index(['category_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::table('buzzes', function (Blueprint $table): void {
            $table->dropIndex(['game_clue_id', 'status']);
        });

        Schema::table('categories', function (Blueprint $table): void {
            $table->dropIndex(['board_id', 'position']);
        });

        Schema::table('clues', function (Blueprint $table): void {
            $table->dropIndex(['category_id', 'position']);
        });
    }
};
