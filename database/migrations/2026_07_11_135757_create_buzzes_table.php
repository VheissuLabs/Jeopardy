<?php

use App\Models\GameClue;
use App\Models\Player;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('buzzes', function (Blueprint $table): void {
            $table->id();
            $table->foreignIdFor(GameClue::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Player::class)->constrained()->cascadeOnDelete();
            $table->string('status', 32)->default('waiting');
            $table->timestamps();
            $table->unique(['game_clue_id', 'player_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('buzzes');
    }
};
