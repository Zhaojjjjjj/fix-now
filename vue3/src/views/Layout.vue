<script setup lang="ts">
import { onMounted, computed } from "vue";
import { useRouter, useRoute } from "vue-router";
import { useAuthStore } from "../stores/auth";
import { SwitchButton, UserFilled, Setting, ArrowDown } from "@element-plus/icons-vue";

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
    <el-container class="layout-container">
        <el-header class="app-header">
            <div class="header-left">
                <div class="logo">
                    <h1 class="logo-text">FixNow</h1>
                </div>
                <h2 class="page-title">{{ getPageTitle }}</h2>
            </div>
            <div class="header-right">
                <div class="user-info" v-if="authStore.user">
                    <el-avatar :size="36" :icon="UserFilled" class="user-avatar" />
                    <span class="user-name">{{ authStore.user.nickname || authStore.user.username }}</span>
                    <el-dropdown>
                        <span class="el-dropdown-link">
                            <!-- <el-icon class="el-icon--right">
                                <arrow-down />
                            </el-icon> -->
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
        <el-main class="app-main">
            <router-view />
        </el-main>
    </el-container>
</template>

<style scoped>
.layout-container {
    height: 100vh;
    display: flex;
    flex-direction: column;
}

.app-header {
    background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
    border-bottom: none;
    height: 70px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 32px;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.1);
}

.header-left {
    display: flex;
    align-items: center;
    gap: 32px;
}

.logo {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.logo-text {
    font-size: 24px;
    font-weight: 700;
    color: white;
    margin: 0;
    letter-spacing: 1px;
}

.logo-subtitle {
    font-size: 12px;
    color: rgba(255, 255, 255, 0.8);
    letter-spacing: 2px;
}

.page-title {
    font-size: 16px;
    font-weight: 500;
    color: rgba(255, 255, 255, 0.95);
    margin: 0;
    padding-left: 32px;
    border-left: 1px solid rgba(255, 255, 255, 0.2);
}

.header-right {
    display: flex;
    align-items: center;
    gap: 16px;
}

.user-info {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 8px 16px;
    background-color: rgba(255, 255, 255, 0.1);
    border-radius: 24px;
    backdrop-filter: blur(10px);
}

.user-avatar {
    background-color: white;
    color: #3b82f6;
}

.user-name {
    font-size: 14px;
    color: white;
    font-weight: 500;
}

.el-dropdown-link {
    cursor: pointer;
    color: white;
    display: flex;
    align-items: center;
}

.app-main {
    background-color: #f8fafc;
    padding: 24px;
    flex: 1;
    overflow-y: auto;
}
</style>
