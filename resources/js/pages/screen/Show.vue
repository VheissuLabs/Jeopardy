<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import QrCode from '@/components/QrCode.vue';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent } from '@/components/ui/card';
import { useLiveGameState } from '@/composables/useLiveGameState';
import type { GameEvent, GameState } from '@/types/game';

const props = defineProps<{
    state: GameState;
    joinUrl: string;
}>();

const verdict = ref<{ correct: boolean; playerName: string } | null>(null);

interface Reveal {
    category: string;
    value: number;
    response: string;
}

const reveal = ref<Reveal | null>(null);
const lastOpenClue = ref<{ category: string; value: number } | null>(null);

const state = useLiveGameState(
    () => props.state,
    (event: GameEvent) => {
        if ('correct' in event) {
            verdict.value = {
                correct: event.correct,
                playerName: event.playerName,
            };
            setTimeout(() => (verdict.value = null), 2500);
        }

        // On skip, hold the clue view with the answer before the board returns.
        if ('revealedResponse' in event && event.revealedResponse) {
            reveal.value = {
                category: lastOpenClue.value?.category ?? '',
                value: lastOpenClue.value?.value ?? 0,
                response: event.revealedResponse,
            };
            setTimeout(() => (reveal.value = null), 2500);
        }
    },
);

watch(
    () => state.value.openClue,
    (openClue) => {
        if (openClue) {
            lastOpenClue.value = {
                category: openClue.category,
                value: openClue.value,
            };
        }
    },
    { immediate: true },
);

const joinUrlAbsolute = new URL(props.joinUrl, window.location.origin).href;

const maxClueRows = computed<number>(() =>
    Math.max(1, ...state.value.categories.map((c) => c.clues.length)),
);
</script>

<template>
    <div class="flex min-h-screen flex-col bg-background p-8 text-foreground">
        <Head :title="state.boardName" />

        <!-- Lobby: join QR -->
        <section
            v-if="state.status === 'lobby'"
            class="flex flex-1 flex-col items-center justify-center gap-8 text-center"
        >
            <h1 class="text-6xl font-bold tracking-tight">
                {{ state.boardName }}
            </h1>
            <p class="text-2xl text-muted-foreground">Scan to join the game</p>
            <div class="rounded-xl border p-4">
                <QrCode :value="joinUrlAbsolute" :size="320" />
            </div>
            <p class="font-mono text-3xl tracking-widest text-muted-foreground">
                {{ state.code }}
            </p>
            <div class="flex max-w-3xl flex-wrap justify-center gap-3">
                <Badge
                    v-for="player in state.players"
                    :key="player.id"
                    variant="secondary"
                    class="px-5 py-2 text-xl"
                >
                    {{ player.name }}
                </Badge>
            </div>
        </section>

        <!-- Active game -->
        <section
            v-else-if="state.status === 'active'"
            class="flex flex-1 flex-col gap-8"
        >
            <!-- Open clue (or the revealed answer while a skip lingers) -->
            <div
                v-if="state.openClue || reveal"
                class="flex flex-1 flex-col items-center justify-center gap-8 text-center"
            >
                <p
                    class="text-2xl font-semibold tracking-wide text-muted-foreground uppercase"
                >
                    {{ reveal?.category ?? state.openClue?.category }} · ${{
                        reveal?.value ?? state.openClue?.value
                    }}
                </p>
                <p
                    class="max-w-5xl text-6xl leading-tight font-semibold text-balance"
                    :class="{ 'text-primary': reveal }"
                >
                    {{ reveal?.response ?? state.openClue?.prompt }}
                </p>
                <Badge
                    v-if="!reveal && state.openClue?.buzzedPlayer"
                    class="animate-pulse px-8 py-3 text-3xl"
                >
                    {{ state.openClue.buzzedPlayer.name }} buzzed in!
                </Badge>
            </div>

            <!-- Board grid: fixed header row + equal clue rows so all columns align -->
            <div v-else class="flex flex-1 flex-col gap-4">
                <div
                    class="grid flex-1 gap-4"
                    :style="{
                        gridTemplateColumns: `repeat(${state.categories.length}, minmax(0, 1fr))`,
                    }"
                >
                    <div
                        v-for="category in state.categories"
                        :key="category.id"
                        class="grid gap-4"
                        :style="{
                            gridTemplateRows: `6rem repeat(${maxClueRows}, minmax(0, 1fr))`,
                        }"
                    >
                        <div
                            class="flex items-center justify-center rounded-xl border bg-card p-3 text-center"
                        >
                            <span
                                class="line-clamp-2 text-2xl leading-tight font-bold uppercase"
                            >
                                {{ category.name }}
                            </span>
                        </div>
                        <div
                            v-for="cell in category.clues"
                            :key="cell.gameClueId"
                            class="flex items-center justify-center rounded-xl border text-5xl font-bold"
                            :class="
                                cell.status === 'answered'
                                    ? 'border-dashed text-transparent'
                                    : 'bg-primary text-primary-foreground'
                            "
                        >
                            <span v-if="cell.status !== 'answered'"
                                >${{ cell.value }}</span
                            >
                        </div>
                    </div>
                </div>
            </div>

            <!-- Board control indicator -->
            <div
                v-if="state.controllingPlayer"
                class="fixed bottom-8 left-8 rounded-xl border bg-card px-6 py-3 text-2xl shadow-lg"
            >
                <span class="text-muted-foreground">▶</span>
                <span class="ml-2 font-bold">{{
                    state.controllingPlayer.name
                }}</span>
                <span class="text-muted-foreground"> has the board</span>
            </div>

            <!-- Verdict flash, bottom-right -->
            <div
                v-if="verdict"
                class="fixed right-8 bottom-8 rounded-xl border px-8 py-4 text-3xl font-bold shadow-lg"
                :class="
                    verdict.correct
                        ? 'bg-primary text-primary-foreground'
                        : 'bg-destructive text-white'
                "
            >
                {{ verdict.playerName }} —
                {{ verdict.correct ? 'Correct!' : 'Incorrect' }}
            </div>

            <!-- Score strip -->
            <footer class="flex justify-center gap-4">
                <Card v-for="player in state.players" :key="player.id">
                    <CardContent class="min-w-36 px-6 py-3 text-center">
                        <p class="truncate text-lg text-muted-foreground">
                            {{ player.name }}
                        </p>
                        <p
                            class="font-mono text-3xl font-bold"
                            :class="{ 'text-destructive': player.score < 0 }"
                        >
                            {{ player.score }}
                        </p>
                    </CardContent>
                </Card>
            </footer>
        </section>

        <!-- Finished -->
        <section
            v-else
            class="flex flex-1 flex-col items-center justify-center gap-8"
        >
            <h1 class="text-6xl font-bold">Final Scores</h1>
            <ol class="flex flex-col gap-4">
                <li v-for="(player, rank) in state.players" :key="player.id">
                    <Card :class="{ 'border-primary': rank === 0 }">
                        <CardContent
                            class="flex min-w-96 items-center justify-between gap-8 px-8 py-4"
                        >
                            <span class="text-3xl"
                                >{{ rank === 0 ? '🏆' : `${rank + 1}.` }}
                                {{ player.name }}</span
                            >
                            <span class="font-mono text-4xl font-bold">{{
                                player.score
                            }}</span>
                        </CardContent>
                    </Card>
                </li>
            </ol>
        </section>
    </div>
</template>
