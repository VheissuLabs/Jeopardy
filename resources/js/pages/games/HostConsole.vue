<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Check, SkipForward, X } from '@lucide/vue';
import { computed, ref } from 'vue';
import { useGameChannel } from '@/composables/useGameChannel';
import { begin, finish, judge, open, skip } from '@/routes/host';
import type { GameState, HostClueDetail } from '@/types/game';

const props = defineProps<{
    state: GameState;
    clues: Record<number, HostClueDetail>;
}>();

const state = ref<GameState>(props.state);

useGameChannel(props.state.code, (next) => (state.value = next));

const openClueDetail = computed<HostClueDetail | null>(() =>
    state.value.openClue
        ? (props.clues[state.value.openClue.gameClueId] ?? null)
        : null,
);

const busy = ref(false);

function post(url: { url: string; method: string }): void {
    busy.value = true;
    router.post(
        url.url,
        {},
        { preserveScroll: true, onFinish: () => (busy.value = false) },
    );
}

function judgeAnswer(correct: boolean): void {
    if (!state.value.openClue) {
        return;
    }

    busy.value = true;
    router.post(
        judge.url({
            game: state.value.code,
            gameClue: state.value.openClue.gameClueId,
        }),
        { correct },
        { preserveScroll: true, onFinish: () => (busy.value = false) },
    );
}

function endGame(): void {
    if (confirm('End the game and show final scores?')) {
        post(finish(state.value.code));
    }
}
</script>

<template>
    <Head title="Host console" />

    <div
        class="flex min-h-screen flex-col gap-4 bg-slate-950 p-4 text-slate-100"
    >
        <header class="flex items-center justify-between">
            <div>
                <h1 class="text-lg font-bold">{{ state.boardName }}</h1>
                <p class="text-sm text-slate-400">
                    Game {{ state.code }} · you're hosting
                </p>
            </div>
            <button
                v-if="state.status === 'active'"
                type="button"
                class="rounded-lg border border-slate-700 px-3 py-2 text-sm text-slate-300"
                @click="endGame"
            >
                End game
            </button>
        </header>

        <!-- Lobby -->
        <section
            v-if="state.status === 'lobby'"
            class="flex flex-1 flex-col gap-4"
        >
            <div class="rounded-xl border border-slate-800 p-4">
                <h2 class="mb-2 font-semibold">Players in the lobby</h2>
                <p
                    v-if="state.players.length === 0"
                    class="text-sm text-slate-400"
                >
                    Waiting for contestants to scan the QR on the big screen…
                </p>
                <ul class="flex flex-wrap gap-2">
                    <li
                        v-for="player in state.players"
                        :key="player.id"
                        class="rounded-full bg-slate-800 px-3 py-1 text-sm"
                    >
                        {{ player.name }}
                    </li>
                </ul>
            </div>
            <button
                type="button"
                class="rounded-xl bg-blue-600 py-4 text-lg font-bold disabled:opacity-50"
                :disabled="busy || state.players.length === 0"
                @click="post(begin(state.code))"
            >
                Begin game
            </button>
        </section>

        <!-- Open clue: judge it -->
        <section
            v-else-if="
                state.status === 'active' && state.openClue && openClueDetail
            "
            class="flex flex-1 flex-col gap-4"
        >
            <div class="rounded-xl border border-blue-800 bg-blue-950 p-4">
                <p class="text-sm font-semibold text-blue-300">
                    {{ state.openClue.category }} · ${{ state.openClue.value }}
                </p>
                <p class="mt-2 text-xl leading-snug font-medium">
                    {{ openClueDetail.prompt }}
                </p>
                <p class="mt-3 rounded-lg bg-emerald-950 p-2 text-emerald-300">
                    <span class="text-xs uppercase">Answer</span><br />
                    {{ openClueDetail.correctResponse }}
                </p>
            </div>

            <div class="rounded-xl border border-slate-800 p-4 text-center">
                <p v-if="state.openClue.buzzedPlayer" class="text-lg">
                    <span class="font-bold text-amber-400">{{
                        state.openClue.buzzedPlayer.name
                    }}</span>
                    buzzed in!
                </p>
                <p v-else class="text-slate-400">Waiting for a buzz…</p>
                <p
                    v-if="state.openClue.lockedOutPlayerIds.length"
                    class="mt-1 text-xs text-slate-500"
                >
                    {{ state.openClue.lockedOutPlayerIds.length }} player(s)
                    locked out
                </p>
            </div>

            <div class="mt-auto grid grid-cols-2 gap-3">
                <button
                    type="button"
                    class="flex items-center justify-center gap-2 rounded-xl bg-emerald-600 py-6 text-xl font-bold disabled:opacity-40"
                    :disabled="busy || !state.openClue.buzzedPlayer"
                    @click="judgeAnswer(true)"
                >
                    <Check class="size-6" /> Correct
                </button>
                <button
                    type="button"
                    class="flex items-center justify-center gap-2 rounded-xl bg-rose-600 py-6 text-xl font-bold disabled:opacity-40"
                    :disabled="busy || !state.openClue.buzzedPlayer"
                    @click="judgeAnswer(false)"
                >
                    <X class="size-6" /> Incorrect
                </button>
                <button
                    type="button"
                    class="col-span-2 flex items-center justify-center gap-2 rounded-xl border border-slate-700 py-3 text-slate-300 disabled:opacity-40"
                    :disabled="busy"
                    @click="
                        post(
                            skip({
                                game: state.code,
                                gameClue: state.openClue.gameClueId,
                            }),
                        )
                    "
                >
                    <SkipForward class="size-4" /> Skip / reveal
                </button>
            </div>
        </section>

        <!-- Board: pick a clue -->
        <section
            v-else-if="state.status === 'active'"
            class="flex flex-1 flex-col gap-4"
        >
            <div
                class="grid gap-3"
                :style="{
                    gridTemplateColumns: `repeat(${Math.min(state.categories.length, 3)}, minmax(0, 1fr))`,
                }"
            >
                <div
                    v-for="category in state.categories"
                    :key="category.id"
                    class="flex flex-col gap-2"
                >
                    <p
                        class="min-h-10 text-center text-xs font-bold text-blue-300 uppercase"
                    >
                        {{ category.name }}
                    </p>
                    <button
                        v-for="cell in category.clues"
                        :key="cell.gameClueId"
                        type="button"
                        class="rounded-lg bg-blue-900 py-3 font-mono font-bold text-amber-400 disabled:bg-slate-900 disabled:text-slate-700"
                        :disabled="busy || cell.status !== 'hidden'"
                        @click="
                            post(
                                open({
                                    game: state.code,
                                    gameClue: cell.gameClueId,
                                }),
                            )
                        "
                    >
                        ${{ cell.value }}
                    </button>
                </div>
            </div>
        </section>

        <!-- Finished -->
        <section
            v-else-if="state.status === 'finished'"
            class="flex flex-1 flex-col gap-2"
        >
            <h2 class="text-xl font-bold">Final scores</h2>
            <ol class="flex flex-col gap-2">
                <li
                    v-for="(player, rank) in state.players"
                    :key="player.id"
                    class="flex justify-between rounded-lg border border-slate-800 p-3"
                >
                    <span>{{ rank + 1 }}. {{ player.name }}</span>
                    <span class="font-mono font-bold">{{ player.score }}</span>
                </li>
            </ol>
        </section>

        <!-- Scores strip -->
        <footer
            v-if="state.status === 'active'"
            class="flex gap-2 overflow-x-auto border-t border-slate-800 pt-3"
        >
            <div
                v-for="player in state.players"
                :key="player.id"
                class="shrink-0 rounded-lg bg-slate-900 px-3 py-2 text-center"
            >
                <p class="text-xs text-slate-400">{{ player.name }}</p>
                <p
                    class="font-mono font-bold"
                    :class="
                        player.score < 0 ? 'text-rose-400' : 'text-emerald-400'
                    "
                >
                    {{ player.score }}
                </p>
            </div>
        </footer>
    </div>
</template>
