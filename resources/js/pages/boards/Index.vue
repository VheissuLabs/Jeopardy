<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import { Play, Trash2 } from '@lucide/vue';
import InputError from '@/components/InputError.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    destroy as destroyBoard,
    edit as editBoard,
    index as boardsIndex,
    store as storeBoard,
} from '@/routes/boards';
import { show as showGame, store as storeGame } from '@/routes/games';

defineProps<{
    boards: {
        id: number;
        name: string;
        categoriesCount: number;
        categories: { id: number; name: string }[];
        updatedAt: string | null;
    }[];
    games: {
        code: string;
        boardName: string;
        status: string;
        playersCount: number;
        createdAt: string | null;
    }[];
}>();

defineOptions({
    layout: () => ({
        breadcrumbs: [{ title: 'Jeopardy Boards', href: boardsIndex().url }],
    }),
});

function confirmDelete(event: Event): void {
    if (!confirm('Delete this board and all of its clues?')) {
        event.preventDefault();
    }
}
</script>

<template>
    <Head title="Jeopardy Boards" />

    <div class="mx-auto flex w-full max-w-3xl flex-col gap-6 p-4">
        <Card>
            <CardHeader>
                <CardTitle>New board</CardTitle>
                <CardDescription
                    >Create a question board, then fill it with categories and
                    clues.</CardDescription
                >
            </CardHeader>
            <CardContent>
                <Form
                    :action="storeBoard()"
                    reset-on-success
                    #default="{ errors, processing }"
                    class="flex flex-col gap-2 sm:flex-row"
                >
                    <div class="flex-1">
                        <Input
                            name="name"
                            placeholder="Board name (e.g. Family Game Night)"
                            required
                        />
                        <InputError :message="errors.name" class="mt-1" />
                    </div>
                    <Button type="submit" :disabled="processing"
                        >Create board</Button
                    >
                </Form>
            </CardContent>
        </Card>

        <Card>
            <CardHeader>
                <CardTitle>Your boards</CardTitle>
            </CardHeader>
            <CardContent class="flex flex-col gap-2">
                <p
                    v-if="boards.length === 0"
                    class="text-sm text-muted-foreground"
                >
                    No boards yet — create one above.
                </p>

                <div
                    v-for="board in boards"
                    :key="board.id"
                    class="flex items-center justify-between gap-3 rounded-lg border p-3"
                >
                    <Link :href="editBoard(board.id)" class="min-w-0 flex-1">
                        <p class="truncate font-medium">{{ board.name }}</p>
                        <p class="text-sm text-muted-foreground">
                            {{ board.categoriesCount }}
                            {{
                                board.categoriesCount === 1
                                    ? 'category'
                                    : 'categories'
                            }}
                            <span v-if="board.updatedAt">
                                · updated {{ board.updatedAt }}</span
                            >
                        </p>
                    </Link>

                    <Dialog>
                        <DialogTrigger as-child>
                            <Button>
                                <Play class="size-4" />
                                Start game
                            </Button>
                        </DialogTrigger>
                        <DialogContent>
                            <Form
                                :action="storeGame(board.id)"
                                #default="{ errors, processing }"
                                class="flex flex-col gap-4"
                            >
                                <DialogHeader>
                                    <DialogTitle
                                        >Start a game of
                                        {{ board.name }}</DialogTitle
                                    >
                                    <DialogDescription
                                        >Pick the categories to play, or leave
                                        them all unchecked for a random draw of
                                        six.</DialogDescription
                                    >
                                </DialogHeader>

                                <div class="flex flex-col gap-2">
                                    <Label
                                        v-for="category in board.categories"
                                        :key="category.id"
                                        class="flex items-center gap-2 font-normal"
                                    >
                                        <input
                                            type="checkbox"
                                            name="categories[]"
                                            :value="category.id"
                                            class="size-4 accent-primary"
                                        />
                                        {{ category.name }}
                                    </Label>
                                </div>
                                <InputError :message="errors.categories" />

                                <DialogFooter>
                                    <Button
                                        type="submit"
                                        :disabled="processing"
                                    >
                                        <Play class="size-4" />
                                        Start game
                                    </Button>
                                </DialogFooter>
                            </Form>
                        </DialogContent>
                    </Dialog>

                    <Form
                        :action="destroyBoard(board.id)"
                        #default="{ processing }"
                    >
                        <Button
                            type="submit"
                            variant="ghost"
                            size="icon"
                            :disabled="processing"
                            aria-label="Delete board"
                            @click="confirmDelete"
                        >
                            <Trash2 class="size-4" />
                        </Button>
                    </Form>
                </div>
            </CardContent>
        </Card>

        <Card v-if="games.length > 0">
            <CardHeader>
                <CardTitle>Recent games</CardTitle>
                <CardDescription
                    >Reopen a game to get back to its host QR and big-screen
                    link.</CardDescription
                >
            </CardHeader>
            <CardContent class="flex flex-col gap-2">
                <Link
                    v-for="game in games"
                    :key="game.code"
                    :href="showGame(game.code)"
                    class="flex items-center justify-between gap-3 rounded-lg border p-3"
                >
                    <div class="min-w-0">
                        <p class="truncate font-medium">
                            {{ game.boardName }}
                            <span
                                class="ml-1 font-mono text-sm text-muted-foreground"
                                >{{ game.code }}</span
                            >
                        </p>
                        <p class="text-sm text-muted-foreground">
                            {{ game.playersCount }}
                            {{ game.playersCount === 1 ? 'player' : 'players' }}
                            <span v-if="game.createdAt">
                                · started {{ game.createdAt }}</span
                            >
                        </p>
                    </div>
                    <Badge
                        :variant="
                            game.status === 'active' ? 'default' : 'secondary'
                        "
                    >
                        {{ game.status }}
                    </Badge>
                </Link>
            </CardContent>
        </Card>
    </div>
</template>
