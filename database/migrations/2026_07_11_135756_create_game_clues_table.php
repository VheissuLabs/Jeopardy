<?php

use App\Models\Clue;
use App\Models\Game;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_clues', function (Blueprint $table): void {
            $table->id();
            $table->foreignIdFor(Game::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Clue::class)->constrained()->cascadeOnDelete();
            $table->string('status', 32)->default('hidden')->index();
            $table->timestamps();
            $table->unique(['game_id', 'clue_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_clues');
    }
};
