<?php

namespace App\Http\Controllers\Games\Host;

use App\Actions\Games\JudgeAnswerAction;
use App\Enums\BuzzStatus;
use App\Enums\GameClueStatus;
use App\Enums\GameStatus;
use App\Events\AnswerJudged;
use App\Events\ClueClosed;
use App\Events\ClueOpened;
use App\Http\Controllers\Controller;
use App\Http\Requests\Games\JudgeGameClueRequest;
use App\Models\Game;
use App\Models\GameClue;
use Illuminate\Http\RedirectResponse;

class GameClueController extends Controller
{
    public function open(Game $game, GameClue $gameClue): RedirectResponse
    {
        abort_unless($game->status === GameStatus::Active, 422);
        abort_unless($gameClue->status === GameClueStatus::Hidden, 422);

        $gameClue->update(['status' => GameClueStatus::Open]);

        ClueOpened::dispatch($game);

        return back();
    }

    public function judge(JudgeGameClueRequest $request, Game $game, GameClue $gameClue, JudgeAnswerAction $judgeAnswer): RedirectResponse
    {
        abort_unless($gameClue->status === GameClueStatus::Open, 422);
        abort_unless($gameClue->buzzes()->where('status', BuzzStatus::Waiting)->exists(), 422);

        $correct = $request->boolean('correct');
        $player = $judgeAnswer->run($gameClue, $correct);

        AnswerJudged::dispatch($game, $correct, $player);

        return back();
    }

    public function skip(Game $game, GameClue $gameClue): RedirectResponse
    {
        abort_unless($gameClue->status === GameClueStatus::Open, 422);

        $gameClue->buzzes()->where('status', BuzzStatus::Waiting)->delete();
        $gameClue->update(['status' => GameClueStatus::Answered]);
        $gameClue->load('clue');

        ClueClosed::dispatch($game, $gameClue->clue->correct_response);

        return back();
    }
}
