<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Check, SkipForward, X } from '@lucide/vue';
import { computed, ref } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { useLiveGameState } from '@/composables/useLiveGameState';
import { begin, finish, judge, open, skip } from '@/routes/host';
import type { GameState, HostClueDetail } from '@/types/game';

const props = defineProps<{
    state: GameState;
    clues: Record<number, HostClueDetail>;
}>();

const state = useLiveGameState(() => props.state);

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
    <div class="flex min-h-screen flex-col gap-4 bg-background p-4">
        <Head title="Host console" />

        <header class="flex items-center justify-between">
            <div>
                <h1 class="text-lg font-bold">{{ state.boardName }}</h1>
                <p class="text-sm text-muted-foreground">
                    Game {{ state.code }} · you're hosting
                </p>
            </div>
            <Button
                v-if="state.status === 'active'"
                variant="outline"
                size="sm"
                @click="endGame"
            >
                End game
            </Button>
        </header>

        <!-- Lobby -->
        <section
            v-if="state.status === 'lobby'"
            class="flex flex-1 flex-col gap-4"
        >
            <Card class="flex-1">
                <CardContent class="p-4">
                    <h2 class="mb-3 font-semibold">Players in the lobby</h2>
                    <p
                        v-if="state.players.length === 0"
                        class="text-sm text-muted-foreground"
                    >
                        Waiting for contestants to scan the QR on the big
                        screen…
                    </p>
                    <div class="flex flex-wrap gap-2">
                        <Badge
                            v-for="player in state.players"
                            :key="player.id"
                            variant="secondary"
                        >
                            {{ player.name }}
                        </Badge>
                    </div>
                </CardContent>
            </Card>
            <Button
                size="lg"
                class="h-14 text-lg"
                :disabled="busy || state.players.length === 0"
                @click="post(begin(state.code))"
            >
                Begin game
            </Button>
        </section>

        <!-- Open clue: judge it -->
        <section
            v-else-if="
                state.status === 'active' && state.openClue && openClueDetail
            "
            class="flex flex-1 flex-col gap-4"
        >
            <Card>
                <CardContent class="p-4">
                    <p class="text-sm font-semibold text-muted-foreground">
                        {{ state.openClue.category }} · ${{
                            state.openClue.value
                        }}
                    </p>
                    <p class="mt-2 text-xl leading-snug font-medium">
                        {{ openClueDetail.prompt }}
                    </p>
                    <div class="mt-4 rounded-lg bg-muted p-3">
                        <p
                            class="text-xs font-semibold text-muted-foreground uppercase"
                        >
                            Answer
                        </p>
                        <p class="font-medium">
                            {{ openClueDetail.correctResponse }}
                        </p>
                    </div>
                </CardContent>
            </Card>

            <Card>
                <CardContent class="p-4 text-center">
                    <p v-if="state.openClue.buzzedPlayer" class="text-lg">
                        <span class="font-bold">{{
                            state.openClue.buzzedPlayer.name
                        }}</span>
                        buzzed in!
                    </p>
                    <p v-else class="text-muted-foreground">
                        Waiting for a buzz…
                    </p>
                    <p
                        v-if="state.openClue.lockedOutPlayerIds.length"
                        class="mt-1 text-xs text-muted-foreground"
                    >
                        {{ state.openClue.lockedOutPlayerIds.length }} player(s)
                        locked out
                    </p>
                </CardContent>
            </Card>

            <div class="mt-auto grid grid-cols-2 gap-3">
                <Button
                    size="lg"
                    class="h-20 text-xl"
                    :disabled="busy || !state.openClue.buzzedPlayer"
                    @click="judgeAnswer(true)"
                >
                    <Check class="size-6" /> Correct
                </Button>
                <Button
                    size="lg"
                    variant="destructive"
                    class="h-20 text-xl"
                    :disabled="busy || !state.openClue.buzzedPlayer"
                    @click="judgeAnswer(false)"
                >
                    <X class="size-6" /> Incorrect
                </Button>
                <Button
                    variant="outline"
                    class="col-span-2"
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
                </Button>
            </div>
        </section>

        <!-- Board: pick a clue -->
        <section
            v-else-if="state.status === 'active'"
            class="flex flex-1 flex-col gap-4"
        >
            <p
                v-if="state.controllingPlayer"
                class="rounded-lg bg-muted p-3 text-center text-sm"
            >
                <span class="font-bold">{{
                    state.controllingPlayer.name
                }}</span>
                has board control — ask them to pick.
            </p>
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
                        class="min-h-10 text-center text-xs font-bold text-muted-foreground uppercase"
                    >
                        {{ category.name }}
                    </p>
                    <Button
                        v-for="cell in category.clues"
                        :key="cell.gameClueId"
                        variant="secondary"
                        class="py-6 font-mono font-bold"
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
                    </Button>
                </div>
            </div>
        </section>

        <!-- Finished -->
        <section
            v-else-if="state.status === 'finished'"
            class="flex flex-1 flex-col gap-2"
        >
            <h2 class="text-xl font-bold">Final scores</h2>
            <Card>
                <CardContent class="flex flex-col gap-2 p-4">
                    <div
                        v-for="(player, rank) in state.players"
                        :key="player.id"
                        class="flex justify-between"
                    >
                        <span>{{ rank + 1 }}. {{ player.name }}</span>
                        <span class="font-mono font-bold">{{
                            player.score
                        }}</span>
                    </div>
                </CardContent>
            </Card>
        </section>

        <!-- Scores strip -->
        <footer
            v-if="state.status === 'active'"
            class="flex gap-2 overflow-x-auto border-t pt-3"
        >
            <div
                v-for="player in state.players"
                :key="player.id"
                class="shrink-0 rounded-lg bg-muted px-3 py-2 text-center"
            >
                <p class="text-xs text-muted-foreground">{{ player.name }}</p>
                <p
                    class="font-mono font-bold"
                    :class="{ 'text-destructive': player.score < 0 }"
                >
                    {{ player.score }}
                </p>
            </div>
        </footer>
    </div>
</template>
