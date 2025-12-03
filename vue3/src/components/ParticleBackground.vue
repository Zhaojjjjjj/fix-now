<script setup lang="ts">
import { onMounted, onUnmounted, ref } from "vue";

const canvasRef = ref<HTMLCanvasElement | null>(null);
let animationFrameId: number;
let particles: Particle[] = [];
let mouse = { x: -1000, y: -1000 };

interface Particle {
    x: number;
    y: number;
    vx: number;
    vy: number;
    size: number;
    color: string;
    baseX: number;
    baseY: number;
    density: number;
}

const initParticles = (width: number, height: number) => {
    particles = [];
    const particleCount = Math.min(Math.floor((width * height) / 15000), 150); // 根据屏幕面积调整粒子数量

    for (let i = 0; i < particleCount; i++) {
        const size = Math.random() * 2 + 1;
        const x = Math.random() * width;
        const y = Math.random() * height;
        const vx = (Math.random() - 0.5) * 0.5;
        const vy = (Math.random() - 0.5) * 0.5;

        particles.push({
            x,
            y,
            baseX: x,
            baseY: y,
            vx,
            vy,
            size,
            color: `rgba(255, 255, 255, ${Math.random() * 0.3 + 0.1})`, // 半透明白色
            density: Math.random() * 30 + 1,
        });
    }
};

const drawParticles = (ctx: CanvasRenderingContext2D, width: number, height: number) => {
    ctx.clearRect(0, 0, width, height);

    // 更新和绘制粒子
    for (let i = 0; i < particles.length; i++) {
        const p = particles[i];
        if (!p) continue;

        // 鼠标互动
        const dx = mouse.x - p.x;
        const dy = mouse.y - p.y;
        const distance = Math.sqrt(dx * dx + dy * dy);
        const forceDirectionX = dx / distance;
        const forceDirectionY = dy / distance;
        const maxDistance = 150;
        const force = (maxDistance - distance) / maxDistance;

        if (distance < maxDistance) {
            p.x -= forceDirectionX * force * p.density * 0.6;
            p.y -= forceDirectionY * force * p.density * 0.6;
        } else {
            // 回到原位或者继续自然运动
            if (p.x !== p.baseX) {
                const dx = p.x - p.baseX;
                p.x -= dx / 20;
            }
            if (p.y !== p.baseY) {
                const dy = p.y - p.baseY;
                p.y -= dy / 20;
            }
        }

        // 加上自然漂浮
        p.x += p.vx;
        p.y += p.vy;

        // 边界检查
        if (p.x < 0 || p.x > width) p.vx = -p.vx;
        if (p.y < 0 || p.y > height) p.vy = -p.vy;

        // 绘制粒子
        ctx.fillStyle = p.color;
        ctx.beginPath();
        ctx.arc(p.x, p.y, p.size, 0, Math.PI * 2);
        ctx.fill();

        // 绘制连线
        for (let j = i; j < particles.length; j++) {
            const p2 = particles[j];
            if (!p2) continue;

            const dx = p.x - p2.x;
            const dy = p.y - p2.y;
            const distance = Math.sqrt(dx * dx + dy * dy);

            if (distance < 120) {
                ctx.beginPath();
                ctx.strokeStyle = `rgba(255, 255, 255, ${0.15 - distance / 1200})`;
                ctx.lineWidth = 0.5;
                ctx.moveTo(p.x, p.y);
                ctx.lineTo(p2.x, p2.y);
                ctx.stroke();
            }
        }
    }
};

const animate = () => {
    if (!canvasRef.value) return;
    const canvas = canvasRef.value;
    const ctx = canvas.getContext("2d");
    if (!ctx) return;

    drawParticles(ctx, canvas.width, canvas.height);
    animationFrameId = requestAnimationFrame(animate);
};

const handleResize = () => {
    if (!canvasRef.value) return;
    canvasRef.value.width = window.innerWidth;
    canvasRef.value.height = window.innerHeight;
    initParticles(window.innerWidth, window.innerHeight);
};

const handleMouseMove = (e: MouseEvent) => {
    const rect = canvasRef.value?.getBoundingClientRect();
    if (rect) {
        mouse.x = e.clientX - rect.left;
        mouse.y = e.clientY - rect.top;
    }
};

onMounted(() => {
    if (canvasRef.value) {
        canvasRef.value.width = window.innerWidth;
        canvasRef.value.height = window.innerHeight;
        initParticles(window.innerWidth, window.innerHeight);
        animate();
        window.addEventListener("resize", handleResize);
        window.addEventListener("mousemove", handleMouseMove);
    }
});

onUnmounted(() => {
    cancelAnimationFrame(animationFrameId);
    window.removeEventListener("resize", handleResize);
    window.removeEventListener("mousemove", handleMouseMove);
});
</script>

<template>
    <canvas ref="canvasRef" class="fixed inset-0 pointer-events-none z-0 block"></canvas>
</template>
