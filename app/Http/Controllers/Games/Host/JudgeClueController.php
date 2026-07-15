<?php

namespace App\Http\Controllers\Games\Host;

use App\Actions\Games\JudgeAnswerAction;
use App\Enums\BuzzStatus;
use App\Enums\GameClueStatus;
use App\Events\AnswerJudged;
use App\Http\Controllers\Controller;
use App\Http\Requests\Games\JudgeGameClueRequest;
use App\Models\Game;
use App\Models\GameClue;
use Illuminate\Http\RedirectResponse;

class JudgeClueController extends Controller
{
    public function __invoke(JudgeGameClueRequest $request, Game $game, GameClue $gameClue, JudgeAnswerAction $judgeAnswer): RedirectResponse
    {
        abort_unless($gameClue->status === GameClueStatus::Open, 422);
        abort_unless($gameClue->buzzes()->where('status', BuzzStatus::Waiting)->exists(), 422);

        $correct = $request->boolean('correct');
        $player = $judgeAnswer->run($gameClue, $correct);

        AnswerJudged::dispatch($game, $correct, $player);

        return back();
    }
}
