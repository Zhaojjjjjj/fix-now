<script setup lang="ts">
import { onMounted, onUnmounted, ref } from "vue";

const canvasRef = ref<HTMLCanvasElement | null>(null);
let animationFrameId: number;
let width = 0;
let height = 0;
let time = 0;

// 配置参数
const config = {
    lineColor: "rgba(59, 130, 246, 0.15)", // 浅蓝色线条，透明度低
    lineWidth: 1,
    waveHeight: 60, // 波浪高度
    waveSpeed: 0.005, // 波浪速度
    waveCount: 5, // 波浪层数
    pointCount: 100, // 每条线的点数
};

const initCanvas = () => {
    if (!canvasRef.value) return;
    const canvas = canvasRef.value;
    width = window.innerWidth;
    height = window.innerHeight;
    canvas.width = width;
    canvas.height = height;
};

const drawWave = (ctx: CanvasRenderingContext2D, offset: number, amplitude: number, color: string) => {
    ctx.beginPath();
    ctx.lineWidth = config.lineWidth;
    ctx.strokeStyle = color;

    for (let i = 0; i <= config.pointCount; i++) {
        const x = (i / config.pointCount) * width;
        const frequency = i / 30 + time * 2 + offset;
        const y = height / 2 + Math.sin(frequency) * amplitude * Math.sin(time + offset) + Math.cos(time * 0.5 + offset) * 20;

        if (i === 0) {
            ctx.moveTo(x, y);
        } else {
            ctx.lineTo(x, y);
        }
    }
    ctx.stroke();
};

const animate = () => {
    if (!canvasRef.value) return;
    const ctx = canvasRef.value.getContext("2d");
    if (!ctx) return;

    ctx.clearRect(0, 0, width, height);
    time += config.waveSpeed;

    // 绘制多层波浪，营造 3D 景深感
    for (let i = 0; i < config.waveCount; i++) {
        const offset = i * 2;
        const amplitude = config.waveHeight + i * 10;
        // 每一层颜色透明度递减，制造层次
        const alpha = 0.3 - i * 0.05;
        const color = `rgba(37, 99, 235, ${Math.max(alpha, 0.05)})`; // Blue-600 base

        drawWave(ctx, offset, amplitude, color);
    }

    animationFrameId = requestAnimationFrame(animate);
};

const handleResize = () => {
    initCanvas();
};

onMounted(() => {
    initCanvas();
    animate();
    window.addEventListener("resize", handleResize);
});

onUnmounted(() => {
    cancelAnimationFrame(animationFrameId);
    window.removeEventListener("resize", handleResize);
});
</script>

<template>
    <canvas ref="canvasRef" class="fixed inset-0 pointer-events-none z-0 block bg-slate-50"></canvas>
</template>
