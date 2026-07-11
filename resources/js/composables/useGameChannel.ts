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
    // If Echo is misconfigured (e.g. Reverb not provisioned yet), render the
    // page without realtime updates instead of crashing to a blank screen.
    try {
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
    } catch (error) {
        console.warn('Realtime disabled — Echo failed to initialize:', error);

        return null;
    }
}
