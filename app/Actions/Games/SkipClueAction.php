<?php

namespace App\Actions\Games;

use App\Enums\BuzzStatus;
use App\Enums\GameClueStatus;
use App\Models\GameClue;
use Illuminate\Support\Facades\DB;

class SkipClueAction
{
    public function run(GameClue $gameClue): string
    {
        return DB::transaction(function () use ($gameClue): string {
            $gameClue->buzzes()
                ->where('status', BuzzStatus::Waiting)
                ->delete();

            $gameClue->update([
                'status' => GameClueStatus::Answered,
            ]);

            return $gameClue->loadMissing('clue')->clue->correct_response;
        });
    }
}
