<script setup lang="ts">
import { ref, onMounted } from "vue";
import api from "../api";

interface Project {
    id: number;
    name: string;
    description: string;
    img_cover: string;
    issueCount: number;
    userIssueCount: number;
}

const projects = ref<Project[]>([]);
const loading = ref(false);

const fetchProjects = async () => {
    loading.value = true;
    try {
        const res: any = await api.get("/project/list");
        projects.value = res.data.projectList;
    } catch (e) {
        console.error(e);
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    fetchProjects();
});
</script>

<template>
    <div>
        <h1 class="text-2xl font-semibold text-gray-900 mb-6">Projects</h1>
        <div v-if="loading" class="text-center py-10">Loading...</div>
        <div v-else class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <div v-for="project in projects" :key="project.id" class="bg-white overflow-hidden shadow rounded-lg hover:shadow-md transition-shadow cursor-pointer">
                <div class="px-4 py-5 sm:p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 h-12 w-12 rounded-md overflow-hidden bg-gray-100">
                            <img v-if="project.img_cover" :src="project.img_cover" alt="" class="h-full w-full object-cover" />
                            <div v-else class="h-full w-full flex items-center justify-center text-gray-400">
                                <span class="i-carbon-image text-2xl"></span>
                            </div>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">{{ project.name }}</h3>
                            <p class="text-sm text-gray-500 truncate">{{ project.description }}</p>
                        </div>
                    </div>
                    <div class="mt-4 flex justify-between text-sm text-gray-500">
                        <span>Total Issues: {{ project.issueCount }}</span>
                        <span>My Issues: {{ project.userIssueCount }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
