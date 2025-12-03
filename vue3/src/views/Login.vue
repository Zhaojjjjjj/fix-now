<script setup lang="ts">
import { ref, reactive, onMounted } from "vue";
import { useRouter } from "vue-router";
import { useAuthStore } from "../stores/auth";
import { User, Lock } from "@element-plus/icons-vue";
import { ElMessage } from "element-plus";
import type { FormInstance, FormRules } from "element-plus";
import WaveBackground from "../components/WaveBackground.vue";
import gsap from "gsap";

const router = useRouter();
const authStore = useAuthStore();

const formRef = ref<FormInstance>();
const form = reactive({
    username: "",
    password: "",
});

const rules = reactive<FormRules>({
    username: [{ required: true, message: "请输入用户名", trigger: "blur" }],
    password: [{ required: true, message: "请输入密码", trigger: "blur" }],
});

const loading = ref(false);

const handleLogin = async (formEl: FormInstance | undefined) => {
    if (!formEl) return;
    await formEl.validate(async (valid) => {
        if (valid) {
            loading.value = true;
            try {
                await authStore.login({ username: form.username, password: form.password });
                router.push("/");
            } catch (e: any) {
                ElMessage.error(e.message || e.response?.data?.msg || "登录失败");
            } finally {
                loading.value = false;
            }
        }
    });
};

onMounted(() => {
    // 入场动画
    const tl = gsap.timeline();
    tl.from(".login-card", {
        y: 30,
        opacity: 0,
        duration: 1,
        ease: "power3.out",
    })
        .from(
            ".logo-area",
            {
                y: 20,
                opacity: 0,
                duration: 0.8,
                ease: "back.out(1.7)",
            },
            "-=0.6"
        )
        .from(
            ".form-area",
            {
                y: 20,
                opacity: 0,
                duration: 0.8,
                ease: "power2.out",
            },
            "-=0.6"
        );
});
</script>

<template>
    <div class="relative flex justify-center items-center h-screen bg-slate-50 overflow-hidden">
        <WaveBackground />

        <div class="login-card w-full max-w-[420px] p-8 bg-white/80 backdrop-blur-xl rounded-2xl border border-white/50 shadow-[0_8px_30px_rgb(0,0,0,0.04)] relative z-10">
            <div class="logo-area text-center mb-10">
                <div class="inline-flex items-center justify-center w-40 h-40 bg-blue-50 text-blue-600 rounded-2xl mb-4 shadow-sm transform hover:scale-105 transition-transform duration-300">
                    <img src="/fix.png" alt="FixNow" class="w-30 h-30" />
                </div>
                <h1 class="text-2xl font-bold text-slate-800 m-0 leading-tight tracking-tight">FixNow</h1>
            </div>

            <el-form ref="formRef" :model="form" :rules="rules" size="large" @submit.prevent class="form-area mb-2">
                <el-form-item prop="username">
                    <el-input v-model="form.username" placeholder="用户名" :prefix-icon="User" class="custom-input" />
                </el-form-item>
                <el-form-item prop="password">
                    <el-input v-model="form.password" type="password" placeholder="密码" :prefix-icon="Lock" show-password @keyup.enter="handleLogin(formRef)" class="custom-input" />
                </el-form-item>
                <el-form-item>
                    <el-button
                        type="primary"
                        :loading="loading"
                        class="w-full font-semibold py-3 h-auto bg-blue-600 hover:bg-blue-700 border-none shadow-lg shadow-blue-500/20 transition-all duration-300 transform hover:translate-y-[-1px]"
                        @click="handleLogin(formRef)">
                        立即登录
                    </el-button>
                </el-form-item>
            </el-form>
        </div>
    </div>
</template>

<style scoped>
/* Custom Input Styles for Light Theme */
:deep(.el-input__wrapper) {
    background-color: #f8fafc !important;
    box-shadow: 0 0 0 1px #e2e8f0 inset !important;
    border-radius: 8px;
    color: #334155;
    transition: all 0.2s ease;
    padding: 4px 12px;
}

:deep(.el-input__wrapper:hover) {
    background-color: #fff !important;
    box-shadow: 0 0 0 1px #cbd5e1 inset !important;
}

:deep(.el-input__wrapper.is-focus) {
    background-color: #fff !important;
    box-shadow: 0 0 0 1px #3b82f6 inset, 0 0 0 3px rgba(59, 130, 246, 0.1) inset !important;
}

:deep(.el-input__inner) {
    color: #1e293b !important;
    height: 40px;
}

:deep(.el-input__inner::placeholder) {
    color: #94a3b8;
}

:deep(.el-input__prefix-inner) {
    color: #64748b;
}
</style>
