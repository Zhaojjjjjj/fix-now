<script setup lang="ts">
import DotGrid from "./DotGrid.vue";
import { ref, reactive } from "vue";
import { useRouter } from "vue-router";
import { useAuthStore } from "../stores/auth";
import { User, Lock } from "@element-plus/icons-vue";
import { ElMessage } from "element-plus";
import type { FormInstance, FormRules } from "element-plus";

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
</script>

<template>
    <div class="dot-grid-container">
        <DotGrid
            :dot-size="16"
            :gap="32"
            base-color="#27FF64"
            active-color="#27FF64"
            :proximity="150"
            :speed-trigger="100"
            :shock-radius="250"
            :shock-strength="5"
            :max-speed="5000"
            :resistance="750"
            :return-duration="1.5"
            class-name="custom-dot-grid" />

        <div class="login-wrapper">
            <div class="login-box">
                <div class="login-header">
                    <div class="logo-icon">
                        <img src="/fix.png" alt="FixNow" class="w-5rem" />
                    </div>
                    <h1 class="title">FixNow</h1>
                    <p class="subtitle"></p>
                </div>

                <el-form ref="formRef" :model="form" :rules="rules" size="large" @submit.prevent class="login-form">
                    <el-form-item prop="username">
                        <el-input v-model="form.username" placeholder="用户名" :prefix-icon="User" />
                    </el-form-item>
                    <el-form-item prop="password">
                        <el-input v-model="form.password" type="password" placeholder="密码" :prefix-icon="Lock" show-password @keyup.enter="handleLogin(formRef)" />
                    </el-form-item>
                    <el-form-item>
                        <el-button type="primary" :loading="loading" class="submit-btn" @click="handleLogin(formRef)"> 立即登录 </el-button>
                    </el-form-item>
                </el-form>
            </div>
        </div>
    </div>
</template>

<style scoped>
.dot-grid-container {
    width: 100%;
    height: 100vh;
    position: relative;
    overflow: hidden;
}
.login-wrapper {
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
    background-color: #f0f2f5;
    background-image: radial-gradient(#e2e8f0 1px, transparent 1px);
    background-size: 24px 24px;
    width: 100%;
}

.login-box {
    width: 100%;
    max-width: 400px;
    padding: 40px;
    background: #ffffff;
    border-radius: 16px;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

.login-header {
    text-align: center;
    margin-bottom: 32px;
}

.logo-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 48px;
    height: 48px;
    background-color: #eff6ff;
    color: #2563eb;
    border-radius: 12px;
    margin-bottom: 16px;
}

.title {
    font-size: 24px;
    font-weight: 700;
    color: #1e293b;
    margin: 0;
    line-height: 1.2;
}

.subtitle {
    font-size: 14px;
    color: #64748b;
    margin-top: 8px;
}

.login-form {
    margin-bottom: 24px;
}

.submit-btn {
    width: 100%;
    font-weight: 600;
    padding: 12px 0;
    height: auto;
}

.login-footer {
    text-align: center;
    font-size: 12px;
    color: #94a3b8;
}

/* Override Element Plus Styles for cleaner look */
:deep(.el-input__wrapper) {
    box-shadow: 0 0 0 1px #e2e8f0 inset;
    border-radius: 8px;
    background-color: #f8fafc;
}

:deep(.el-input__wrapper:hover) {
    box-shadow: 0 0 0 1px #cbd5e1 inset;
    background-color: #fff;
}

:deep(.el-input__wrapper.is-focus) {
    box-shadow: 0 0 0 2px #3b82f6 inset !important;
    background-color: #fff;
}

:deep(.el-button--primary) {
    background-color: #2563eb;
    border-color: #2563eb;
    border-radius: 8px;
}

:deep(.el-button--primary:hover) {
    background-color: #1d4ed8;
    border-color: #1d4ed8;
}
</style>
