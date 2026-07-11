<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { store as joinStore } from '@/routes/join';

defineProps<{
    code: string;
    boardName: string;
}>();
</script>

<template>
    <div
        class="flex min-h-screen flex-col items-center justify-center bg-background p-6"
    >
        <Head title="Join game" />

        <Card class="w-full max-w-sm">
            <CardHeader class="text-center">
                <CardTitle class="text-2xl">{{ boardName }}</CardTitle>
                <CardDescription
                    >Game {{ code }} — what's your name?</CardDescription
                >
            </CardHeader>
            <CardContent>
                <Form
                    :action="joinStore(code)"
                    #default="{ errors, processing }"
                    class="flex flex-col gap-4"
                >
                    <Input
                        name="name"
                        placeholder="Your name"
                        maxlength="40"
                        required
                        autofocus
                        class="h-12 text-center text-lg"
                    />
                    <InputError :message="errors.name" class="text-center" />
                    <Button
                        type="submit"
                        size="lg"
                        class="h-12 text-lg"
                        :disabled="processing"
                    >
                        Join game
                    </Button>
                </Form>
            </CardContent>
        </Card>
    </div>
</template>
