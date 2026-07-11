<script setup lang="ts">
import QRCode from 'qrcode';
import { onMounted, ref, watch } from 'vue';

const props = withDefaults(defineProps<{ value: string; size?: number }>(), {
    size: 256,
});

const canvas = ref<HTMLCanvasElement>();

function draw(): void {
    if (canvas.value) {
        QRCode.toCanvas(canvas.value, props.value, { width: props.size, margin: 1 });
    }
}

onMounted(draw);
watch(() => props.value, draw);
</script>

<template>
    <canvas ref="canvas" class="rounded-lg bg-white p-2" />
</template>
