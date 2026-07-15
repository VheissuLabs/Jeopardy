<?php

namespace App\Actions\Games;

use App\Enums\BuzzStatus;
use App\Enums\GameClueStatus;
use App\Models\GameClue;
use App\Models\Player;
use Illuminate\Support\Facades\DB;

class RecordBuzzAction
{
    public function run(GameClue $gameClue, Player $player): bool
    {
        return DB::transaction(function () use ($gameClue, $player): bool {
            $lockedGameClue = GameClue::query()
                ->lockForUpdate()
                ->findOrFail($gameClue->id);

            if ($lockedGameClue->status !== GameClueStatus::Open) {
                return false;
            }

            $existingBuzz = $lockedGameClue->buzzes()->where(
                'status',
                BuzzStatus::Waiting
            )->exists();

            $isLockedOut = $lockedGameClue->buzzes()
                ->where('player_id', $player->id)
                ->where('status', BuzzStatus::Incorrect)
                ->exists();

            if ($existingBuzz || $isLockedOut) {
                return false;
            }

            $lockedGameClue->buzzes()->create([
                'player_id' => $player->id,
                'status' => BuzzStatus::Waiting,
            ]);

            return true;
        });
    }
}
