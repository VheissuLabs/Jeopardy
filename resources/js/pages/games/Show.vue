<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ExternalLink } from '@lucide/vue';
import QrCode from '@/components/QrCode.vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { index as boardsIndex } from '@/routes/boards';

const props = defineProps<{
    game: { code: string; status: string; boardName: string };
    screenUrl: string;
    hostUrl: string;
}>();

defineOptions({
    layout: () => ({
        breadcrumbs: [
            { title: 'Jeopardy Boards', href: boardsIndex().url },
            { title: 'Game' },
        ],
    }),
});

const hostUrlAbsolute = new URL(props.hostUrl, window.location.origin).href;
</script>

<template>
    <Head :title="`Game ${game.code}`" />

    <div class="mx-auto flex w-full max-w-3xl flex-col gap-6 p-4">
        <Card>
            <CardHeader>
                <CardTitle
                    >Game {{ game.code }} · {{ game.boardName }}</CardTitle
                >
                <CardDescription
                    >Two steps: put the big screen on your TV, then scan the
                    host QR with your phone.</CardDescription
                >
            </CardHeader>
            <CardContent class="flex flex-col gap-6 md:flex-row">
                <div class="flex flex-1 flex-col items-start gap-3">
                    <h2 class="font-semibold">1. Open the big screen</h2>
                    <p class="text-sm text-muted-foreground">
                        Open this on the TV or projector. It shows the join QR
                        for contestants, then the board.
                    </p>
                    <Button as-child>
                        <a :href="screenUrl" target="_blank" rel="noopener">
                            <ExternalLink class="size-4" />
                            Open big screen
                        </a>
                    </Button>
                </div>

                <div class="flex flex-1 flex-col items-start gap-3">
                    <h2 class="font-semibold">2. Scan with your phone</h2>
                    <p class="text-sm text-muted-foreground">
                        This opens your private host console — the clue, the
                        answer, and the Correct / Incorrect buttons. Don't show
                        it to contestants.
                    </p>
                    <QrCode :value="hostUrlAbsolute" :size="220" />
                    <a
                        :href="hostUrl"
                        class="text-sm text-muted-foreground underline"
                        >…or open the host console here</a
                    >
                </div>
            </CardContent>
        </Card>
    </div>
</template>
