<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('game_clues', function (Blueprint $table): void {
            $table->unsignedInteger('value')->default(0)->after('clue_id')
                ->comment('Dollar value dealt to this clue for this game (shuffled per game).');
        });

        Schema::table('clues', function (Blueprint $table): void {
            $table->dropColumn('value');
        });
    }

    public function down(): void
    {
        Schema::table('clues', function (Blueprint $table): void {
            $table->unsignedInteger('value')->default(0)->after('correct_response');
        });

        Schema::table('game_clues', function (Blueprint $table): void {
            $table->dropColumn('value');
        });
    }
};
