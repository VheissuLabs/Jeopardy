<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import { Play } from '@lucide/vue';
import { ref } from 'vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';
import { store as storeGame } from '@/routes/games';

defineProps<{
    board: {
        id: number;
        name: string;
        categories: { id: number; name: string }[];
    };
}>();

const mode = ref<'random' | 'pick'>('random');
const categoryCount = ref(6);
</script>

<template>
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
                    <DialogTitle>Start a game of {{ board.name }}</DialogTitle>
                    <DialogDescription
                        >Draw categories at random or hand-pick
                        them.</DialogDescription
                    >
                </DialogHeader>

                <div class="flex gap-4">
                    <Label class="flex items-center gap-2 font-normal">
                        <input
                            v-model="mode"
                            type="radio"
                            value="random"
                            class="size-4 accent-primary"
                        />
                        Random draw
                    </Label>
                    <Label class="flex items-center gap-2 font-normal">
                        <input
                            v-model="mode"
                            type="radio"
                            value="pick"
                            class="size-4 accent-primary"
                        />
                        Pick categories
                    </Label>
                </div>

                <div v-if="mode === 'random'" class="flex flex-col gap-2">
                    <Label for="category-count" class="font-normal">
                        Categories:
                        <span class="font-medium">{{ categoryCount }}</span>
                    </Label>
                    <input
                        id="category-count"
                        v-model="categoryCount"
                        type="range"
                        name="category_count"
                        min="1"
                        max="6"
                        step="1"
                        class="accent-primary"
                    />
                </div>

                <div v-else class="flex flex-col gap-2">
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

                <InputError
                    :message="errors.categories ?? errors.category_count"
                />

                <DialogFooter>
                    <Button type="submit" :disabled="processing">
                        <Play class="size-4" />
                        Start game
                    </Button>
                </DialogFooter>
            </Form>
        </DialogContent>
    </Dialog>
</template>
