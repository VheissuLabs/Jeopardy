<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { store as joinStore } from '@/routes/join';

defineProps<{
    code: string;
    boardName: string;
}>();
</script>

<template>
    <Head title="Join game" />

    <div
        class="flex min-h-screen flex-col items-center justify-center gap-6 bg-blue-950 p-6 text-white"
    >
        <h1 class="text-center font-serif text-4xl font-bold text-amber-400">
            {{ boardName }}
        </h1>
        <p class="text-blue-200">Game {{ code }} — what's your name?</p>

        <Form
            :action="joinStore(code)"
            #default="{ errors, processing }"
            class="flex w-full max-w-sm flex-col gap-3"
        >
            <input
                name="name"
                placeholder="Your name"
                maxlength="40"
                required
                autofocus
                class="rounded-xl border border-blue-700 bg-blue-900 px-4 py-4 text-center text-2xl text-white placeholder:text-blue-400 focus:ring-2 focus:ring-amber-400 focus:outline-none"
            />
            <p v-if="errors.name" class="text-center text-rose-400">
                {{ errors.name }}
            </p>
            <button
                type="submit"
                class="rounded-xl bg-amber-500 py-4 text-2xl font-bold text-blue-950 disabled:opacity-50"
                :disabled="processing"
            >
                Join game
            </button>
        </Form>
    </div>
</template>
