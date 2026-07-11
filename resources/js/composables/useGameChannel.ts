import { useEchoPublic } from '@laravel/echo-vue';
import type {
    AnswerJudgedEvent,
    GameState,
    GameStateEvent,
} from '@/types/game';

export const GAME_EVENTS = [
    'PlayerJoined',
    'GameStarted',
    'ClueOpened',
    'ClueClosed',
    'PlayerBuzzed',
    'AnswerJudged',
    'GameFinished',
] as const;

export function useGameChannel(
    code: string,
    onState: (state: GameState) => void,
    onJudged?: (event: AnswerJudgedEvent) => void,
) {
    return useEchoPublic<GameStateEvent | AnswerJudgedEvent>(
        `game.${code}`,
        [...GAME_EVENTS],
        (event) => {
            onState(event.state);

            if (onJudged && 'correct' in event) {
                onJudged(event);
            }
        },
    );
}
