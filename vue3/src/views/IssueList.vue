<script setup lang="ts">
import { ref, onMounted, watch } from "vue";
import api from "../api";

interface Issue {
    id: number;
    title: string;
    status: number;
    priority: number;
    user: { nickname: string };
    curUser: { nickname: string };
    module: { name: string };
}

const issues = ref<Issue[]>([]);
const loading = ref(false);
const total = ref(0);
const page = ref(1);
const limit = ref(10);

const filters = ref({
    status: "",
    priority: "",
    title: "",
});

const fetchIssues = async () => {
    loading.value = true;
    try {
        const res: any = await api.get("/issue/list", {
            params: {
                page: page.value,
                limit: limit.value,
                ...filters.value,
            },
        });
        issues.value = res.data.data;
        total.value = res.data.total;
    } catch (e) {
        console.error(e);
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    fetchIssues();
});

watch(
    filters,
    () => {
        page.value = 1;
        fetchIssues();
    },
    { deep: true }
);

const getStatusColor = (status: number) => {
    switch (status) {
        case 1:
            return "bg-red-100 text-red-800"; // Not Solved
        case 2:
            return "bg-yellow-100 text-yellow-800"; // Audit
        case 8:
            return "bg-green-100 text-green-800"; // Finish
        default:
            return "bg-gray-100 text-gray-800";
    }
};

const getStatusText = (status: number) => {
    switch (status) {
        case 1:
            return "Open";
        case 2:
            return "Audit";
        case 8:
            return "Closed";
        default:
            return "Unknown";
    }
};
</script>

<template>
    <div>
        <h1 class="text-2xl font-semibold text-gray-900 mb-6">Issues</h1>

        <!-- Filters -->
        <div class="bg-white p-4 rounded-lg shadow mb-6 flex gap-4 flex-wrap">
            <input v-model="filters.title" type="text" placeholder="Search title..." class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-teal-500 focus:border-teal-500" />
            <select v-model="filters.status" class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-teal-500 focus:border-teal-500">
                <option value="">All Status</option>
                <option value="1">Open</option>
                <option value="2">Audit</option>
                <option value="8">Closed</option>
            </select>
            <button @click="fetchIssues" class="btn">Search</button>
        </div>

        <!-- List -->
        <div class="bg-white shadow overflow-hidden sm:rounded-md">
            <ul role="list" class="divide-y divide-gray-200">
                <li v-for="issue in issues" :key="issue.id">
                    <a href="#" class="block hover:bg-gray-50">
                        <div class="px-4 py-4 sm:px-6">
                            <div class="flex items-center justify-between">
                                <p class="text-sm font-medium text-teal-600 truncate">{{ issue.title }}</p>
                                <div class="ml-2 flex-shrink-0 flex">
                                    <p class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" :class="getStatusColor(issue.status)">
                                        {{ getStatusText(issue.status) }}
                                    </p>
                                </div>
                            </div>
                            <div class="mt-2 sm:flex sm:justify-between">
                                <div class="sm:flex">
                                    <p class="flex items-center text-sm text-gray-500">
                                        <span class="i-carbon-user mr-1.5 h-5 w-5 text-gray-400"></span>
                                        {{ issue.user?.nickname || "Unknown" }}
                                    </p>
                                    <p class="mt-2 flex items-center text-sm text-gray-500 sm:mt-0 sm:ml-6">
                                        <span class="i-carbon-folder mr-1.5 h-5 w-5 text-gray-400"></span>
                                        {{ issue.module?.name || "No Module" }}
                                    </p>
                                </div>
                                <div class="mt-2 flex items-center text-sm text-gray-500 sm:mt-0">
                                    <span class="i-carbon-warning mr-1.5 h-5 w-5 text-gray-400" v-if="issue.priority > 0"></span>
                                    Priority: {{ issue.priority }}
                                </div>
                            </div>
                        </div>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</template>
