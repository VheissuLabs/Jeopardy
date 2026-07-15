import { usePoll } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import type { Ref } from 'vue';
import { useGameChannel } from '@/composables/useGameChannel';
import type { GameEvent, GameState } from '@/types/game';

/**
 * Live game state: updates via Reverb when available, and falls back to
 * polling the page props when the websocket connection can't be established.
 */
export function useLiveGameState(
    getPropState: () => GameState,
    onEvent?: (event: GameEvent) => void,
): Ref<GameState> {
    const state = ref<GameState>(getPropState());

    watch(getPropState, (next) => (state.value = next));

    const channel = useGameChannel(
        state.value.code,
        (next) => (state.value = next),
        onEvent,
    );

    const { start } = usePoll(
        3000,
        { only: ['state'] },
        { autoStart: false, keepAlive: true },
    );

    if (!channel) {
        start();
    }

    return state;
}
