<?php

namespace App\Events;

use App\Models\Game;
use App\Support\GameState;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

class ClueClosed implements ShouldBroadcastNow
{
    use Dispatchable;

    public function __construct(
        public Game $game,
        public ?string $revealedResponse = null,
    ) {}

    public function broadcastOn(): Channel
    {
        return new Channel('game.'.$this->game->code);
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'state' => GameState::for($this->game),
            'revealedResponse' => $this->revealedResponse,
        ];
    }
}
