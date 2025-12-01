<script setup lang="ts">
import { onMounted } from "vue";
import { useRouter } from "vue-router";
import { useAuthStore } from "../stores/auth";

const router = useRouter();
const authStore = useAuthStore();

onMounted(() => {
    authStore.fetchUser();
});

const handleLogout = () => {
    authStore.logout();
    router.push("/login");
};
</script>

<template>
    <div class="min-h-screen flex flex-col bg-gray-50">
        <nav class="bg-white shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex">
                        <div class="flex-shrink-0 flex items-center cursor-pointer" @click="router.push('/')">
                            <span class="text-xl font-bold text-teal-600">MyBug</span>
                        </div>
                        <div class="hidden sm:ml-6 sm:flex sm:space-x-8">
                            <router-link
                                to="/projects"
                                class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium"
                                active-class="border-teal-500 text-gray-900">
                                Projects
                            </router-link>
                            <router-link
                                to="/issues"
                                class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium"
                                active-class="border-teal-500 text-gray-900">
                                Issues
                            </router-link>
                        </div>
                    </div>
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <span class="text-gray-700 mr-4" v-if="authStore.user">Hello, {{ authStore.user.nickname || authStore.user.username }}</span>
                            <button
                                @click="handleLogout"
                                class="relative inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                Logout
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <main class="flex-1 py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <router-view></router-view>
            </div>
        </main>
    </div>
</template>
