<?php

namespace App\Actions\Games;

use App\Enums\BuzzStatus;
use App\Enums\GameClueStatus;
use App\Models\Buzz;
use App\Models\GameClue;
use App\Models\Player;
use Illuminate\Support\Facades\DB;

class JudgeAnswerAction
{
    public function run(GameClue $gameClue, bool $correct): Player
    {
        return DB::transaction(function () use ($gameClue, $correct): Player {
            /** @var Buzz $buzz */
            $buzz = $gameClue->buzzes()
                ->where('status', BuzzStatus::Waiting)
                ->with('player')
                ->lockForUpdate()
                ->firstOrFail();

            $player = $buzz->player;
            $value = $gameClue->value;

            if ($correct) {
                $player->increment('score', $value);
                $buzz->delete();
                $gameClue->update(['status' => GameClueStatus::Answered]);

                return $player;
            }

            $player->decrement('score', $value);
            $buzz->update(['status' => BuzzStatus::Incorrect]);

            $lockedOutCount = $gameClue->buzzes()->where('status', BuzzStatus::Incorrect)->count();

            if ($lockedOutCount >= $gameClue->game->players()->count()) {
                $gameClue->update(['status' => GameClueStatus::Answered]);
            }

            return $player;
        });
    }
}
