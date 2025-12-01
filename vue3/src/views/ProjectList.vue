<script setup lang="ts">
import { ref, onMounted } from "vue";
import { useRouter } from "vue-router";
import api from "../api";
import axios from "axios";
import { Picture as IconPicture, Plus, FolderOpened } from "@element-plus/icons-vue";
import { ElMessage } from "element-plus";
import type { FormInstance, FormRules } from "element-plus";

const router = useRouter();

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

// 创建项目弹窗
const createProjectDialogVisible = ref(false);
const projectFormRef = ref<FormInstance>();
const projectForm = ref({
    name: "",
    description: "",
    img_cover: "",
    modules: [""],
});

const projectRules = ref<FormRules>({
    name: [{ required: true, message: "请输入项目名称", trigger: "blur" }],
});

const uploadingLogo = ref(false);

const addModule = () => {
    projectForm.value.modules.push("");
};

const removeModule = (index: number) => {
    if (projectForm.value.modules.length > 1) {
        projectForm.value.modules.splice(index, 1);
    }
};

// 处理logo上传
const handleLogoUpload = async (file: any) => {
    const formData = new FormData();
    formData.append("file", file.raw);

    uploadingLogo.value = true;
    try {
        const res: any = await axios.post("/think/upload/image", formData, {
            headers: { "Content-Type": "multipart/form-data" },
        });
        if (res.data.code === 1) {
            projectForm.value.img_cover = res.data.data.url;
            ElMessage.success("Logo上传成功");
        } else {
            ElMessage.error(res.data.msg || "Logo上传失败");
        }
    } catch (e: any) {
        ElMessage.error(e.response?.data?.msg || "Logo上传失败");
    } finally {
        uploadingLogo.value = false;
    }
};

// 移除logo
const removeLogo = () => {
    projectForm.value.img_cover = "";
};

const fetchProjects = async () => {
    loading.value = true;
    try {
        const res: any = await api.get("/project/list");
        if (res.code === 1) {
            projects.value = res.data.projectList;
        } else {
            ElMessage.error(res.msg || "加载项目列表失败");
        }
    } catch (e) {
        console.error(e);
        ElMessage.error("加载项目列表失败");
    } finally {
        loading.value = false;
    }
};

const handleCreateProject = () => {
    createProjectDialogVisible.value = true;
};

const submitProject = async (formEl: FormInstance | undefined) => {
    if (!formEl) return;
    await formEl.validate(async (valid) => {
        if (valid) {
            try {
                // 过滤空模块
                const modules = projectForm.value.modules.filter((m) => m.trim() !== "");
                const res: any = await api.post("/project/edit", {
                    name: projectForm.value.name,
                    description: projectForm.value.description,
                    img_cover: projectForm.value.img_cover,
                    modules: modules,
                });
                if (res.code === 1) {
                    ElMessage.success("项目创建成功");
                    createProjectDialogVisible.value = false;
                    projectForm.value = {
                        name: "",
                        description: "",
                        img_cover: "",
                        modules: [""],
                    };
                    fetchProjects();
                } else {
                    ElMessage.error(res.msg || "创建失败");
                }
            } catch (e: any) {
                ElMessage.error(e.response?.data?.msg || "创建失败");
            }
        }
    });
};

const goToProjectDetail = (projectId: number) => {
    router.push(`/projects/${projectId}`);
};

onMounted(() => {
    fetchProjects();
});
</script>

<template>
    <div>
        <div class="page-header">
            <div>
                <h1 class="page-title">项目管理</h1>
                <p class="page-subtitle">查看和管理您的所有项目进度</p>
            </div>
            <el-button type="primary" :icon="Plus" size="large" @click="handleCreateProject">创建新项目</el-button>
        </div>

        <el-row :gutter="20" v-if="loading">
            <el-col :xs="24" :sm="12" :md="8" :lg="6" :xl="4.8" v-for="i in 5" :key="i" class="mb-6">
                <el-card class="project-card" shadow="never">
                    <el-skeleton animated>
                        <template #template>
                            <el-skeleton-item variant="image" style="width: 100%; height: 160px" />
                            <div style="padding: 20px">
                                <el-skeleton-item variant="h3" style="width: 60%" />
                                <el-skeleton-item variant="text" style="width: 90%; margin-top: 10px" />
                                <div class="flex justify-between mt-6">
                                    <el-skeleton-item variant="text" style="width: 30%" />
                                    <el-skeleton-item variant="text" style="width: 30%" />
                                </div>
                            </div>
                        </template>
                    </el-skeleton>
                </el-card>
            </el-col>
        </el-row>

        <el-row :gutter="20" v-else-if="projects.length > 0">
            <el-col :xs="24" :sm="12" :md="8" :lg="6" :xl="4.8" v-for="project in projects" :key="project.id" class="mb-6">
                <el-card :body-style="{ padding: '0px' }" shadow="hover" class="project-card cursor-pointer" @click="goToProjectDetail(project.id)">
                    <div class="card-cover">
                        <img v-if="project.img_cover" :src="project.img_cover" class="cover-image" />
                        <div v-else class="cover-placeholder">
                            <el-icon size="48"><IconPicture /></el-icon>
                        </div>
                        <div class="cover-overlay">
                            <h3 class="project-name">{{ project.name }}</h3>
                        </div>
                    </div>
                    <div class="card-content">
                        <p class="project-desc">{{ project.description || "暂无项目描述..." }}</p>
                        <div class="project-stats">
                            <div class="stat-item">
                                <div class="stat-label">总问题数</div>
                                <div class="stat-value text-blue">{{ project.issueCount }}</div>
                            </div>
                            <div class="stat-divider"></div>
                            <div class="stat-item">
                                <div class="stat-label">我的待办</div>
                                <div class="stat-value text-orange">{{ project.userIssueCount }}</div>
                            </div>
                        </div>
                    </div>
                </el-card>
            </el-col>
        </el-row>

        <div v-else class="empty-state">
            <el-empty description="暂无项目数据">
                <template #image>
                    <el-icon size="60" color="#cbd5e1"><FolderOpened /></el-icon>
                </template>
                <el-button type="primary" :icon="Plus" @click="handleCreateProject">立即创建项目</el-button>
            </el-empty>
        </div>

        <!-- 创建项目弹窗 -->
        <el-dialog v-model="createProjectDialogVisible" title="创建新项目" width="600px">
            <el-form ref="projectFormRef" :model="projectForm" :rules="projectRules" label-width="100px">
                <el-form-item label="项目名称" prop="name">
                    <el-input v-model="projectForm.name" placeholder="请输入项目名称" />
                </el-form-item>
                <el-form-item label="项目描述">
                    <el-input v-model="projectForm.description" type="textarea" :rows="4" placeholder="请输入项目描述..." />
                </el-form-item>
                <el-form-item label="项目Logo">
                    <div class="logo-upload-container">
                        <el-upload v-if="!projectForm.img_cover" :show-file-list="false" :on-change="handleLogoUpload" :auto-upload="false" accept="image/*" class="logo-uploader">
                            <el-button :loading="uploadingLogo" type="primary">
                                {{ uploadingLogo ? "上传中..." : "选择Logo" }}
                            </el-button>
                        </el-upload>
                        <div v-else class="logo-preview">
                            <img :src="projectForm.img_cover" class="preview-logo" />
                            <el-button type="danger" size="small" @click="removeLogo">删除</el-button>
                        </div>
                    </div>
                </el-form-item>
                <el-form-item label="项目模块">
                    <div class="module-list">
                        <div v-for="(module, index) in projectForm.modules" :key="index" class="module-item">
                            <el-input v-model="projectForm.modules[index]" placeholder="请输入模块名称" />
                            <el-button v-if="projectForm.modules.length > 1" type="danger" text @click="removeModule(index)"> 删除 </el-button>
                        </div>
                        <el-button type="primary" text @click="addModule">+ 添加模块</el-button>
                    </div>
                </el-form-item>
            </el-form>
            <template #footer>
                <el-button @click="createProjectDialogVisible = false">取消</el-button>
                <el-button type="primary" @click="submitProject(projectFormRef)">创建</el-button>
            </template>
        </el-dialog>
    </div>
</template>

<style scoped>
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 32px;
}

.page-title {
    font-size: 24px;
    font-weight: 600;
    color: #1e293b;
    margin: 0 0 4px 0;
}

.page-subtitle {
    font-size: 14px;
    color: #64748b;
    margin: 0;
}

.project-card {
    height: 100%;
    border: none;
    border-radius: 12px;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    display: flex;
    flex-direction: column;
}

.project-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
}

.card-cover {
    height: 160px;
    position: relative;
    background-color: #f1f5f9;
    overflow: hidden;
}

.cover-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
}

.project-card:hover .cover-image {
    transform: scale(1.05);
}

.cover-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #94a3b8;
    background-color: #f8fafc;
}

.cover-overlay {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    padding: 20px;
    background: linear-gradient(to top, rgba(0, 0, 0, 0.7), transparent);
}

.project-name {
    color: white;
    font-size: 18px;
    font-weight: 600;
    margin: 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.card-content {
    padding: 20px;
    flex: 1;
    display: flex;
    flex-direction: column;
}

.project-desc {
    font-size: 14px;
    color: #64748b;
    line-height: 1.5;
    margin-bottom: 20px;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    flex: 1;
}

.project-stats {
    display: flex;
    align-items: center;
    border-top: 1px solid #e2e8f0;
    padding-top: 16px;
}

.stat-item {
    flex: 1;
    text-align: center;
}

.stat-label {
    font-size: 12px;
    color: #94a3b8;
    margin-bottom: 4px;
}

.stat-value {
    font-size: 18px;
    font-weight: 700;
}

.text-blue {
    color: #3b82f6;
}

.text-orange {
    color: #f97316;
}

.stat-divider {
    width: 1px;
    height: 30px;
    background-color: #e2e8f0;
}

.empty-state {
    background: white;
    padding: 60px 0;
    border-radius: 12px;
    text-align: center;
}

.module-list {
    width: 100%;
}

.module-item {
    display: flex;
    gap: 12px;
    align-items: center;
    margin-bottom: 12px;
}

.module-item .el-input {
    flex: 1;
}

.module-item .el-button {
    margin-left: 8px;
}

.logo-upload-container {
    display: flex;
    align-items: center;
}

.logo-preview {
    display: flex;
    align-items: center;
    gap: 12px;
}

.preview-logo {
    width: 100px;
    height: 100px;
    object-fit: cover;
    border-radius: 8px;
    border: 1px solid #e5e7eb;
}
</style>
