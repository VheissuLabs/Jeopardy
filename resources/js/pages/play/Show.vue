<script setup lang="ts">
import { Head, useHttp } from '@inertiajs/vue3';
import { computed } from 'vue';
import { Card, CardContent } from '@/components/ui/card';
import { useLiveGameState } from '@/composables/useLiveGameState';
import { buzz } from '@/routes/play';
import type { GamePlayer, GameState } from '@/types/game';

const props = defineProps<{
    state: GameState;
    player: GamePlayer;
}>();

const state = useLiveGameState(() => props.state);

const me = computed<GamePlayer>(
    () =>
        state.value.players.find((player) => player.id === props.player.id) ??
        props.player,
);

const lockedOut = computed<boolean>(() =>
    (state.value.openClue?.lockedOutPlayerIds ?? []).includes(props.player.id),
);

const canBuzz = computed<boolean>(
    () =>
        state.value.status === 'active' &&
        !!state.value.openClue &&
        !state.value.openClue.buzzedPlayer &&
        !lockedOut.value,
);

const http = useHttp({ game_clue_id: 0 });

function buzzIn(): void {
    if (!canBuzz.value || !state.value.openClue) {
        return;
    }

    http.game_clue_id = state.value.openClue.gameClueId;
    // A lost buzz race returns 409 — the state broadcast already told us who won, so ignore it.
    http.post(buzz.url(state.value.code), { onError: () => undefined });
}

const statusLine = computed<string>(() => {
    if (state.value.status === 'lobby') {
        return 'Waiting for the host to start the game…';
    }

    if (state.value.status === 'finished') {
        return 'Game over!';
    }

    const openClue = state.value.openClue;

    if (!openClue) {
        return 'Watch the board — the host is picking a clue.';
    }

    if (openClue.buzzedPlayer) {
        return openClue.buzzedPlayer.id === props.player.id
            ? 'You buzzed in — answer!'
            : `${openClue.buzzedPlayer.name} is answering…`;
    }

    if (lockedOut.value) {
        return "You're locked out of this clue.";
    }

    return 'Buzz now!';
});
</script>

<template>
    <div class="flex min-h-screen flex-col gap-4 bg-background p-4">
        <Head title="Play" />

        <header class="text-center">
            <p class="text-sm text-muted-foreground">{{ me.name }}</p>
            <p
                class="font-mono text-5xl font-bold"
                :class="me.score < 0 ? 'text-destructive' : 'text-foreground'"
            >
                {{ me.score }}
            </p>
        </header>

        <section
            v-if="state.status !== 'finished'"
            class="flex flex-1 flex-col items-center justify-center gap-6"
        >
            <p
                v-if="state.openClue"
                class="max-w-md text-center text-lg text-foreground"
            >
                {{ state.openClue.prompt }}
            </p>

            <button
                type="button"
                class="flex size-56 items-center justify-center rounded-full text-3xl font-bold shadow-lg transition active:scale-95"
                :class="
                    canBuzz
                        ? 'bg-primary text-primary-foreground'
                        : 'bg-muted text-muted-foreground'
                "
                :disabled="!canBuzz"
                @click="buzzIn"
            >
                BUZZ
            </button>

            <p class="text-center text-muted-foreground">{{ statusLine }}</p>
        </section>

        <section
            v-else
            class="flex flex-1 flex-col items-center justify-center gap-4"
        >
            <h2 class="text-3xl font-bold">Final scores</h2>
        </section>

        <Card>
            <CardContent class="flex flex-col gap-1 py-3">
                <div
                    v-for="(player, rank) in state.players"
                    :key="player.id"
                    class="flex justify-between text-sm"
                    :class="
                        player.id === me.id
                            ? 'font-bold'
                            : 'text-muted-foreground'
                    "
                >
                    <span>{{ rank + 1 }}. {{ player.name }}</span>
                    <span class="font-mono">{{ player.score }}</span>
                </div>
            </CardContent>
        </Card>
    </div>
</template>
