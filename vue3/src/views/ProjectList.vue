<script setup lang="ts">
import { ref, onMounted, watch } from "vue";
import { useRouter } from "vue-router";
import api from "../api";
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
    unresolvedIssueCount?: number; // 新增未解决数量
    status?: number; // 1: 进行中, 2: 已归档
}

const projects = ref<Project[]>([]);
const loading = ref(false);
const filterStatus = ref(1); // 默认显示进行中
const currentPage = ref(1);
const pageSize = ref(15);
const total = ref(0);

// 处理页码变更
const handleCurrentChange = (val: number) => {
    currentPage.value = val;
    fetchProjects();
};

const fetchProjects = async () => {
    loading.value = true;
    try {
        const res: any = await api.get("/project/list", {
            params: {
                page: currentPage.value,
                limit: pageSize.value,
                status: filterStatus.value,
            },
        });
        if (res.code === 1) {
            projects.value = res.data.projectList;
            total.value = res.data.total;
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

// 监听状态变化
watch(filterStatus, () => {
    currentPage.value = 1;
    fetchProjects();
});

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
        const res: any = await api.post("/common/upload", formData, {
            headers: { "Content-Type": "multipart/form-data" },
        });
        if (res.code === 1) {
            projectForm.value.img_cover = res.data.url;
            ElMessage.success("Logo上传成功");
        } else {
            ElMessage.error(res.msg || "Logo上传失败");
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
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-2xl font-semibold text-slate-800 m-0 mb-1">项目管理</h1>
                <p class="text-sm text-slate-500 m-0">查看和管理您的所有项目进度</p>
            </div>
            <div class="flex gap-4 items-center">
                <el-radio-group v-model="filterStatus" @change="currentPage = 1">
                    <el-radio-button :value="1">进行中</el-radio-button>
                    <el-radio-button :value="2">已归档</el-radio-button>
                    <el-radio-button :value="0">全部</el-radio-button>
                </el-radio-group>
                <el-button type="primary" :icon="Plus" size="large" @click="handleCreateProject">创建新项目</el-button>
            </div>
        </div>

        <el-row :gutter="20" v-if="loading">
            <el-col :xs="24" :sm="12" :md="8" :lg="6" :xl="4.8" v-for="i in 5" :key="i" class="mb-6">
                <el-card class="h-full border-none rounded-xl transition-all duration-300 flex flex-col" shadow="never">
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
                <el-card
                    :body-style="{ padding: '0px' }"
                    shadow="hover"
                    class="h-full border-none rounded-xl transition-all duration-500 flex flex-col cursor-pointer group hover:shadow-2xl hover:shadow-blue-500/20 relative overflow-hidden bg-white transform hover:-translate-y-2"
                    :class="{ 'grayscale opacity-80': project.status === 2 }"
                    @click="goToProjectDetail(project.id)">
                    <div
                        class="absolute inset-0 bg-gradient-to-br from-transparent via-transparent to-blue-50/50 opacity-0 group-hover:opacity-100 transition-opacity duration-500 pointer-events-none z-0"></div>

                    <!-- 归档标签 -->
                    <div v-if="project.status === 2" class="absolute top-3 right-3 z-20 bg-slate-800/80 text-white text-xs px-2 py-1 rounded backdrop-blur-sm">已归档</div>

                    <div class="h-40 relative bg-slate-200 overflow-hidden">
                        <img v-if="project.img_cover" :src="project.img_cover" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110" />
                        <div v-else class="w-full h-full flex items-center justify-center text-slate-400 bg-slate-50">
                            <el-icon size="48" class="transition-transform duration-500 group-hover:scale-110 group-hover:text-blue-400"><IconPicture /></el-icon>
                        </div>
                        <div class="absolute bottom-0 left-0 right-0 px-5 py-5 bg-gradient-to-t from-black/80 via-black/40 to-transparent">
                            <h3 class="text-white text-lg font-semibold m-0 whitespace-nowrap overflow-hidden text-ellipsis transform transition-transform duration-300 group-hover:translate-x-1">
                                {{ project.name }}
                            </h3>
                        </div>
                    </div>
                    <div class="p-5 flex-1 flex flex-col relative z-10 bg-white/95 backdrop-blur-sm">
                        <p class="text-sm text-slate-500 leading-relaxed mb-5 line-clamp-2 flex-1 transition-colors duration-300 group-hover:text-slate-600">
                            {{ project.description || "暂无项目描述..." }}
                        </p>
                        <div class="flex items-center border-t border-slate-100 pt-4 mt-auto">
                            <div class="flex-1 text-center group-hover:bg-blue-50/50 rounded-lg py-1 transition-colors duration-300">
                                <div class="text-xs text-slate-400 mb-1">总问题数</div>
                                <div class="text-lg font-bold text-blue-500">{{ project.issueCount }}</div>
                            </div>
                            <div class="w-px h-[20px] bg-slate-200 mx-1"></div>
                            <div class="flex-1 text-center group-hover:bg-red-50/50 rounded-lg py-1 transition-colors duration-300">
                                <div class="text-xs text-slate-400 mb-1">未解决</div>
                                <div class="text-lg font-bold text-red-500">{{ project.unresolvedIssueCount }}</div>
                            </div>
                            <div class="w-px h-[20px] bg-slate-200 mx-1"></div>
                            <div class="flex-1 text-center group-hover:bg-orange-50/50 rounded-lg py-1 transition-colors duration-300">
                                <div class="text-xs text-slate-400 mb-1">我的待办</div>
                                <div class="text-lg font-bold text-orange-500">{{ project.userIssueCount }}</div>
                            </div>
                        </div>
                    </div>
                </el-card>
            </el-col>
        </el-row>

        <!-- 分页 -->
        <div class="flex justify-center mt-8" v-if="total > 0">
            <el-pagination v-model:current-page="currentPage" :page-size="pageSize" :total="total" background layout="total, prev, pager, next" @current-change="handleCurrentChange" />
        </div>

        <div v-else class="bg-white py-15 rounded-xl text-center">
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
                    <div class="flex items-center">
                        <el-upload v-if="!projectForm.img_cover" :show-file-list="false" :on-change="handleLogoUpload" :auto-upload="false" accept="image/*" class="logo-uploader">
                            <el-button :loading="uploadingLogo" type="primary">
                                {{ uploadingLogo ? "上传中..." : "选择Logo" }}
                            </el-button>
                        </el-upload>
                        <div v-else class="flex items-center gap-3">
                            <img :src="projectForm.img_cover" class="w-[100px] h-[100px] object-cover rounded-lg border border-gray-200" />
                            <el-button type="danger" size="small" @click="removeLogo">删除</el-button>
                        </div>
                    </div>
                </el-form-item>
                <el-form-item label="项目模块">
                    <div class="w-full">
                        <div v-for="(_module, index) in projectForm.modules" :key="index" class="flex gap-3 items-center mb-3">
                            <el-input v-model="projectForm.modules[index]" placeholder="请输入模块名称" class="flex-1" />
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

<style scoped></style>
