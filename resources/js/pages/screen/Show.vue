<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import QrCode from '@/components/QrCode.vue';
import { useGameChannel } from '@/composables/useGameChannel';
import type { AnswerJudgedEvent, GameState } from '@/types/game';

const props = defineProps<{
    state: GameState;
    joinUrl: string;
}>();

const state = ref<GameState>(props.state);
const verdict = ref<{ correct: boolean; playerName: string } | null>(null);

useGameChannel(
    props.state.code,
    (next) => (state.value = next),
    (event: AnswerJudgedEvent) => {
        verdict.value = {
            correct: event.correct,
            playerName: event.playerName,
        };
        setTimeout(() => (verdict.value = null), 2500);
    },
);

const joinUrlAbsolute = new URL(props.joinUrl, window.location.origin).href;
</script>

<template>
    <Head :title="`Jeopardy · ${state.boardName}`" />

    <div class="flex min-h-screen flex-col bg-blue-950 p-6 text-white">
        <!-- Lobby: join QR -->
        <section
            v-if="state.status === 'lobby'"
            class="flex flex-1 flex-col items-center justify-center gap-8 text-center"
        >
            <h1
                class="font-serif text-6xl font-bold tracking-wide text-amber-400 uppercase"
            >
                {{ state.boardName }}
            </h1>
            <p class="text-2xl text-blue-200">Scan to join the game</p>
            <QrCode :value="joinUrlAbsolute" :size="320" />
            <p class="font-mono text-3xl tracking-widest text-blue-200">
                {{ state.code }}
            </p>
            <div class="flex max-w-3xl flex-wrap justify-center gap-3">
                <span
                    v-for="player in state.players"
                    :key="player.id"
                    class="animate-in rounded-full bg-blue-800 px-5 py-2 text-xl fade-in"
                >
                    {{ player.name }}
                </span>
            </div>
        </section>

        <!-- Active game -->
        <section
            v-else-if="state.status === 'active'"
            class="flex flex-1 flex-col gap-6"
        >
            <!-- Open clue -->
            <div
                v-if="state.openClue"
                class="flex flex-1 flex-col items-center justify-center gap-6 text-center"
            >
                <p class="text-3xl font-bold text-amber-400 uppercase">
                    {{ state.openClue.category }} · ${{ state.openClue.value }}
                </p>
                <p
                    class="max-w-5xl font-serif text-6xl leading-tight font-semibold text-balance"
                >
                    {{ state.openClue.prompt }}
                </p>
                <p
                    v-if="state.openClue.buzzedPlayer"
                    class="animate-pulse rounded-xl bg-amber-500 px-8 py-4 text-4xl font-bold text-blue-950"
                >
                    {{ state.openClue.buzzedPlayer.name }} buzzed in!
                </p>
            </div>

            <!-- Board grid -->
            <div
                v-else
                class="grid flex-1 gap-3"
                :style="{
                    gridTemplateColumns: `repeat(${state.categories.length}, minmax(0, 1fr))`,
                }"
            >
                <div
                    v-for="category in state.categories"
                    :key="category.id"
                    class="flex flex-col gap-3"
                >
                    <div
                        class="flex min-h-24 items-center justify-center rounded-lg bg-blue-900 p-2 text-center text-2xl font-bold uppercase"
                    >
                        {{ category.name }}
                    </div>
                    <div
                        v-for="cell in category.clues"
                        :key="cell.gameClueId"
                        class="flex flex-1 items-center justify-center rounded-lg text-5xl font-bold"
                        :class="
                            cell.status === 'answered'
                                ? 'bg-blue-950'
                                : 'bg-blue-900 text-amber-400'
                        "
                    >
                        <span v-if="cell.status !== 'answered'"
                            >${{ cell.value }}</span
                        >
                    </div>
                </div>
            </div>

            <!-- Verdict flash -->
            <div
                v-if="verdict"
                class="fixed inset-x-0 top-8 mx-auto w-fit rounded-xl px-8 py-4 text-3xl font-bold shadow-xl"
                :class="
                    verdict.correct
                        ? 'bg-emerald-500 text-white'
                        : 'bg-rose-600 text-white'
                "
            >
                {{ verdict.playerName }} —
                {{ verdict.correct ? 'Correct!' : 'Incorrect' }}
            </div>

            <!-- Score strip -->
            <footer class="flex justify-center gap-4">
                <div
                    v-for="player in state.players"
                    :key="player.id"
                    class="min-w-36 rounded-xl bg-blue-900 px-6 py-3 text-center"
                >
                    <p class="truncate text-lg text-blue-200">
                        {{ player.name }}
                    </p>
                    <p
                        class="font-mono text-3xl font-bold"
                        :class="
                            player.score < 0
                                ? 'text-rose-400'
                                : 'text-amber-400'
                        "
                    >
                        {{ player.score }}
                    </p>
                </div>
            </footer>
        </section>

        <!-- Finished -->
        <section
            v-else
            class="flex flex-1 flex-col items-center justify-center gap-8"
        >
            <h1 class="font-serif text-6xl font-bold text-amber-400">
                Final Scores
            </h1>
            <ol class="flex flex-col gap-4">
                <li
                    v-for="(player, rank) in state.players"
                    :key="player.id"
                    class="flex min-w-96 items-center justify-between gap-8 rounded-xl bg-blue-900 px-8 py-4"
                    :class="{ 'ring-4 ring-amber-400': rank === 0 }"
                >
                    <span class="text-3xl"
                        >{{ rank === 0 ? '🏆' : `${rank + 1}.` }}
                        {{ player.name }}</span
                    >
                    <span class="font-mono text-4xl font-bold text-amber-400">{{
                        player.score
                    }}</span>
                </li>
            </ol>
        </section>
    </div>
</template>
