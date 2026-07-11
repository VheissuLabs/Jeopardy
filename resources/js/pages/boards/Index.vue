<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import { Play, Trash2 } from '@lucide/vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { destroy as destroyBoard, edit as editBoard, index as boardsIndex, store as storeBoard } from '@/routes/boards';
import { store as storeGame } from '@/routes/games';

defineProps<{
    boards: { id: number; name: string; categoriesCount: number; updatedAt: string | null }[];
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
                <CardDescription>Create a question board, then fill it with categories and clues.</CardDescription>
            </CardHeader>
            <CardContent>
                <Form :action="storeBoard()" reset-on-success #default="{ errors, processing }" class="flex flex-col gap-2 sm:flex-row">
                    <div class="flex-1">
                        <Input name="name" placeholder="Board name (e.g. Family Game Night)" required />
                        <InputError :message="errors.name" class="mt-1" />
                    </div>
                    <Button type="submit" :disabled="processing">Create board</Button>
                </Form>
            </CardContent>
        </Card>

        <Card>
            <CardHeader>
                <CardTitle>Your boards</CardTitle>
            </CardHeader>
            <CardContent class="flex flex-col gap-2">
                <p v-if="boards.length === 0" class="text-sm text-muted-foreground">No boards yet — create one above.</p>

                <div v-for="board in boards" :key="board.id" class="flex items-center justify-between gap-3 rounded-lg border p-3">
                    <Link :href="editBoard(board.id)" class="min-w-0 flex-1">
                        <p class="truncate font-medium">{{ board.name }}</p>
                        <p class="text-sm text-muted-foreground">
                            {{ board.categoriesCount }} {{ board.categoriesCount === 1 ? 'category' : 'categories' }}
                            <span v-if="board.updatedAt"> · updated {{ board.updatedAt }}</span>
                        </p>
                    </Link>

                    <Form :action="storeGame(board.id)" #default="{ processing }">
                        <Button type="submit" :disabled="processing">
                            <Play class="size-4" />
                            Start game
                        </Button>
                    </Form>

                    <Form :action="destroyBoard(board.id)" #default="{ processing }">
                        <Button type="submit" variant="ghost" size="icon" :disabled="processing" aria-label="Delete board" @click="confirmDelete">
                            <Trash2 class="size-4" />
                        </Button>
                    </Form>
                </div>
            </CardContent>
        </Card>
    </div>
</template>
