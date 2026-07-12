<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import {
    ChevronDown,
    ChevronRight,
    Eye,
    EyeOff,
    Plus,
    Trash2,
} from '@lucide/vue';
import { ref, watch } from 'vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import {
    edit as editBoard,
    index as boardsIndex,
    update as updateBoard,
} from '@/routes/boards';
import {
    destroy as destroyCategory,
    store as storeCategory,
    update as updateCategory,
} from '@/routes/categories';
import {
    destroy as destroyClue,
    store as storeClue,
    update as updateClue,
} from '@/routes/clues';

interface BoardClue {
    id: number;
    prompt: string;
    correctResponse: string;
    position: number;
}

interface BoardCategory {
    id: number;
    name: string;
    position: number;
    clues: BoardClue[];
}

const props = defineProps<{
    board: { id: number; name: string; categories: BoardCategory[] };
}>();

defineOptions({
    layout: (props: { board: { id: number; name: string } }) => ({
        breadcrumbs: [
            { title: 'Jeopardy Boards', href: boardsIndex().url },
            { title: props.board.name, href: editBoard(props.board.id).url },
        ],
    }),
});

const editingClueId = ref<number | null>(null);

const collapsedCategoryIds = ref<Set<number>>(new Set());

function toggleCategory(categoryId: number): void {
    if (collapsedCategoryIds.value.has(categoryId)) {
        collapsedCategoryIds.value.delete(categoryId);
    } else {
        collapsedCategoryIds.value.add(categoryId);
    }

    collapsedCategoryIds.value = new Set(collapsedCategoryIds.value);
}

const answersHidden = ref(
    localStorage.getItem('board-editor-answers-hidden') === 'true',
);

watch(answersHidden, (hidden) => {
    localStorage.setItem('board-editor-answers-hidden', String(hidden));

    if (hidden) {
        editingClueId.value = null;
    }
});
</script>

<template>
    <Head :title="`Edit · ${props.board.name}`" />

    <div class="mx-auto flex w-full max-w-4xl flex-col gap-6 p-4">
        <div class="flex justify-end">
            <Button
                variant="outline"
                size="sm"
                @click="answersHidden = !answersHidden"
            >
                <EyeOff v-if="!answersHidden" class="size-4" />
                <Eye v-else class="size-4" />
                {{ answersHidden ? 'Show answers' : 'Hide answers' }}
            </Button>
        </div>

        <Card>
            <CardHeader>
                <CardTitle>Board name</CardTitle>
            </CardHeader>
            <CardContent>
                <Form
                    :action="updateBoard(board.id)"
                    #default="{ errors, processing }"
                    class="flex flex-col gap-2 sm:flex-row"
                >
                    <div class="flex-1">
                        <Input
                            name="name"
                            :default-value="board.name"
                            required
                        />
                        <InputError :message="errors.name" class="mt-1" />
                    </div>
                    <Button
                        type="submit"
                        variant="secondary"
                        :disabled="processing"
                        >Rename</Button
                    >
                </Form>
            </CardContent>
        </Card>

        <Card v-for="category in board.categories" :key="category.id">
            <CardHeader>
                <div class="flex items-center gap-2">
                    <Button
                        variant="ghost"
                        size="icon"
                        :aria-label="
                            collapsedCategoryIds.has(category.id)
                                ? 'Expand category'
                                : 'Collapse category'
                        "
                        @click="toggleCategory(category.id)"
                    >
                        <ChevronRight
                            v-if="collapsedCategoryIds.has(category.id)"
                            class="size-4"
                        />
                        <ChevronDown v-else class="size-4" />
                    </Button>

                    <span
                        v-if="collapsedCategoryIds.has(category.id)"
                        class="text-sm text-muted-foreground"
                    >
                        {{ category.clues.length }}
                        {{ category.clues.length === 1 ? 'clue' : 'clues' }}
                    </span>

                    <Form
                        :action="updateCategory(category.id)"
                        :options="{ preserveScroll: true }"
                        #default="{ errors, processing }"
                        class="flex flex-1 items-center gap-2"
                    >
                        <Input
                            name="name"
                            :default-value="category.name"
                            class="max-w-xs text-base font-semibold"
                            required
                        />
                        <Button
                            type="submit"
                            variant="ghost"
                            size="sm"
                            :disabled="processing"
                            >Save</Button
                        >
                        <InputError :message="errors.name" />
                    </Form>

                    <Form
                        :action="destroyCategory(category.id)"
                        :options="{ preserveScroll: true }"
                        #default="{ processing: deleting }"
                    >
                        <Button
                            type="submit"
                            variant="ghost"
                            size="icon"
                            :disabled="deleting"
                            aria-label="Delete category"
                        >
                            <Trash2 class="size-4" />
                        </Button>
                    </Form>
                </div>
            </CardHeader>

            <CardContent
                v-if="!collapsedCategoryIds.has(category.id)"
                class="flex flex-col gap-3"
            >
                <div
                    v-for="clue in category.clues"
                    :key="clue.id"
                    class="rounded-lg border"
                >
                    <button
                        v-if="editingClueId !== clue.id"
                        type="button"
                        class="flex w-full items-start gap-3 p-3 text-left"
                        :class="{ 'cursor-default': answersHidden }"
                        @click="answersHidden || (editingClueId = clue.id)"
                    >
                        <span class="min-w-0 flex-1">
                            <span class="block truncate">{{
                                clue.prompt
                            }}</span>
                            <span
                                class="block truncate text-sm text-muted-foreground"
                                >{{
                                    answersHidden
                                        ? '••••••••'
                                        : clue.correctResponse
                                }}</span
                            >
                        </span>
                    </button>

                    <div v-else class="p-3">
                        <Form
                            :action="updateClue(clue.id)"
                            :options="{ preserveScroll: true }"
                            #default="{ errors, processing }"
                            class="flex flex-col gap-2"
                            @success="editingClueId = null"
                        >
                            <Input
                                name="prompt"
                                :default-value="clue.prompt"
                                placeholder="Clue (read aloud)"
                                required
                            />
                            <InputError :message="errors.prompt" />
                            <div>
                                <Input
                                    name="correct_response"
                                    :default-value="clue.correctResponse"
                                    placeholder="Correct response"
                                    required
                                />
                                <InputError
                                    :message="errors.correct_response"
                                    class="mt-1"
                                />
                            </div>
                            <div class="flex gap-2">
                                <Button
                                    type="submit"
                                    size="sm"
                                    :disabled="processing"
                                    >Save clue</Button
                                >
                                <Button
                                    type="button"
                                    size="sm"
                                    variant="ghost"
                                    @click="editingClueId = null"
                                    >Cancel</Button
                                >
                            </div>
                        </Form>

                        <Form
                            :action="destroyClue(clue.id)"
                            :options="{ preserveScroll: true }"
                            #default="{ processing: deleting }"
                            class="mt-2 flex justify-end"
                        >
                            <Button
                                type="submit"
                                size="sm"
                                variant="destructive"
                                :disabled="deleting"
                                >Delete clue</Button
                            >
                        </Form>
                    </div>
                </div>

                <Form
                    :action="storeClue(category.id)"
                    :options="{ preserveScroll: true }"
                    reset-on-success
                    #default="{ errors, processing }"
                    class="flex flex-col gap-2 rounded-lg border border-dashed p-3"
                >
                    <p class="text-sm font-medium text-muted-foreground">
                        Quick add clue
                    </p>
                    <Input
                        name="prompt"
                        placeholder="Clue (read aloud)"
                        required
                    />
                    <InputError :message="errors.prompt" />
                    <div class="flex flex-col gap-2 sm:flex-row">
                        <div class="flex-1">
                            <Input
                                name="correct_response"
                                placeholder="Correct response"
                                required
                            />
                            <InputError
                                :message="errors.correct_response"
                                class="mt-1"
                            />
                        </div>
                        <Button type="submit" :disabled="processing">
                            <Plus class="size-4" />
                            Add
                        </Button>
                    </div>
                    <p class="text-xs text-muted-foreground">
                        Dollar values are shuffled fresh for every game.
                    </p>
                </Form>
            </CardContent>
        </Card>

        <Card>
            <CardHeader>
                <CardTitle>Add category</CardTitle>
            </CardHeader>
            <CardContent>
                <Form
                    :action="storeCategory(board.id)"
                    :options="{ preserveScroll: true }"
                    reset-on-success
                    #default="{ errors, processing }"
                    class="flex flex-col gap-2 sm:flex-row"
                >
                    <div class="flex-1">
                        <Input
                            name="name"
                            placeholder="Category name (e.g. 90s Movies)"
                            required
                        />
                        <InputError :message="errors.name" class="mt-1" />
                    </div>
                    <Button type="submit" :disabled="processing">
                        <Plus class="size-4" />
                        Add category
                    </Button>
                </Form>
            </CardContent>
        </Card>
    </div>
</template>
