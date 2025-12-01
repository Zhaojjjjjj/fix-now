<script setup lang="ts">
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
    <div class="login-wrapper">
        <div class="login-box">
            <div class="login-header">
                <div class="logo-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-8 h-8">
                        <path
                            fill-rule="evenodd"
                            d="M12 2.25a.75.75 0 01.75.75v.756a49.106 49.106 0 019.152 1 .75.75 0 01-.152 1.485h-1.918l2.478 10.125a.75.75 0 01-.375.845 6.751 6.751 0 01-9.772 4.12l-.57.236a3.25 3.25 0 00-1.106 1.463l1.276 5.96a.75.75 0 01-1.463.314l-1.228-5.958a1.75 1.75 0 01.574-1.633l.57-.236a5.252 5.252 0 002.058-1.08l-6.758-6.758a.75.75 0 01.53-1.28h9a2.25 2.25 0 002.25-2.25v-.385a47.631 47.631 0 00-9.375 1 .75.75 0 01-.152-1.485V3a.75.75 0 01.75-.75zm4.5 6a.75.75 0 00-.75.75v3a.75.75 0 001.5 0v-3a.75.75 0 00-.75-.75z"
                            clip-rule="evenodd" />
                    </svg>
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
</template>

<style scoped>
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
