<script setup lang="ts">
import { onMounted, computed } from "vue";
import { useRouter, useRoute } from "vue-router";
import { useAuthStore } from "../stores/auth";
import { SwitchButton, UserFilled, Setting } from "@element-plus/icons-vue";

const router = useRouter();
const route = useRoute();
const authStore = useAuthStore();

onMounted(() => {
    authStore.fetchUser();
});

const handleLogout = () => {
    authStore.logout();
    router.push("/login");
};

const handleProfile = () => {
    router.push("/profile");
};

const getPageTitle = computed(() => {
    if (route.name === "ProjectDetail") {
        return "项目详情";
    }
    if (route.name === "ProjectList") {
        return "项目管理";
    }
    if (route.name === "Profile") {
        return "个人信息";
    }
    return "FixNow";
});
</script>

<template>
    <el-container class="h-screen flex flex-col bg-slate-50">
        <el-header class="bg-gradient-to-r from-[#0f172a] to-[#1e293b] border-b border-white/10 h-[70px] flex items-center justify-between px-8 shadow-xl relative z-50">
            <!-- 顶部光效 -->
            <div class="absolute top-0 left-0 right-0 h-[1px] bg-gradient-to-r from-transparent via-blue-400/50 to-transparent"></div>

            <div class="flex items-center gap-8 relative z-10">
                <div class="flex flex-col gap-0.5 cursor-pointer" @click="router.push('/')">
                    <h1 class="text-2xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-white to-blue-200 m-0 tracking-wide">FixNow</h1>
                </div>
                <h2 class="text-base font-medium text-slate-300 m-0 pl-8 border-l border-white/10">{{ getPageTitle }}</h2>
            </div>
            <div class="flex items-center gap-4 relative z-10">
                <div class="flex items-center gap-3 px-4 py-2 bg-white/5 border border-white/10 rounded-full backdrop-blur-md hover:bg-white/10 transition-colors duration-300" v-if="authStore.user">
                    <el-avatar :size="32" :icon="UserFilled" class="bg-gradient-to-br from-blue-500 to-indigo-600 text-white border-2 border-white/20" />
                    <span class="text-sm text-slate-200 font-medium pr-2">{{ authStore.user.nickname || authStore.user.username }}</span>
                    <el-dropdown trigger="click">
                        <span class="cursor-pointer text-slate-400 hover:text-white flex items-center transition-colors">
                            <el-icon><Setting /></el-icon>
                        </span>
                        <template #dropdown>
                            <el-dropdown-menu>
                                <el-dropdown-item @click="handleProfile">
                                    <el-icon><Setting /></el-icon>
                                    个人设置
                                </el-dropdown-item>
                                <el-dropdown-item divided @click="handleLogout">
                                    <el-icon><SwitchButton /></el-icon>
                                    退出登录
                                </el-dropdown-item>
                            </el-dropdown-menu>
                        </template>
                    </el-dropdown>
                </div>
            </div>
        </el-header>
        <el-main class="bg-[#f8fafc] p-6 flex-1 overflow-y-auto scroll-smooth relative">
            <!-- 全局背景纹理 -->
            <div class="absolute inset-0 opacity-[0.015] pointer-events-none" style="background-image: radial-gradient(#000 1px, transparent 1px); background-size: 20px 20px"></div>
            <router-view />
        </el-main>
    </el-container>
</template>

<style scoped></style>
