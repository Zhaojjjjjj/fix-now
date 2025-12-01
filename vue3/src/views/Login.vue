<script setup lang="ts">
import { ref } from "vue";
import { useRouter } from "vue-router";
import { useAuthStore } from "../stores/auth";

const router = useRouter();
const authStore = useAuthStore();

const username = ref("");
const password = ref("");
const error = ref("");
const loading = ref(false);

const handleLogin = async () => {
    loading.value = true;
    error.value = "";
    try {
        await authStore.login({ username: username.value, password: password.value });
        router.push("/");
    } catch (e: any) {
        error.value = e.response?.data?.error || "Login failed";
    } finally {
        loading.value = false;
    }
};
</script>

<template>
    <div class="min-h-screen flex items-center justify-center bg-gray-100">
        <div class="bg-white p-8 rounded-lg shadow-md w-96">
            <h2 class="text-2xl font-bold mb-6 text-center text-gray-800">Login</h2>
            <form @submit.prevent="handleLogin" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Username</label>
                    <input
                        v-model="username"
                        type="text"
                        required
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-teal-500 focus:border-teal-500" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Password</label>
                    <input
                        v-model="password"
                        type="password"
                        required
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-teal-500 focus:border-teal-500" />
                </div>
                <div v-if="error" class="text-red-500 text-sm">{{ error }}</div>
                <button
                    type="submit"
                    :disabled="loading"
                    class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-teal-600 hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500 disabled:opacity-50">
                    {{ loading ? "Logging in..." : "Login" }}
                </button>
            </form>
        </div>
    </div>
</template>
