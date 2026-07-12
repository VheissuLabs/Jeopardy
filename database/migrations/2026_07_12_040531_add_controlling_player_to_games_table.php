<?php

use App\Models\Player;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('games', function (Blueprint $table): void {
            $table->foreignIdFor(Player::class, 'controlling_player_id')
                ->nullable()
                ->after('host_token')
                ->comment('Player with board control — picks the next clue.')
                ->constrained('players')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('games', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('controlling_player_id');
        });
    }
};
