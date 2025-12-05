<script setup lang="ts">
import { ref, computed, onMounted } from "vue";
import { useRoute, useRouter } from "vue-router";
import api from "../api";
import { ArrowLeft, Plus, Monitor, FolderOpened, ArrowDown, ArrowUp, Check, Warning } from "@element-plus/icons-vue";
import { ElMessage } from "element-plus";
import type { FormInstance, FormRules } from "element-plus";
import RichEditor from "../components/RichEditor.vue";

const route = useRoute();
const router = useRouter();

interface Issue {
    id: number;
    title: string;
    content: string;
    status: number;
    priority: number;
    bug_type?: string;
    environment?: string;
    cur_user_id?: number;
    module_id?: number;
    user: { nickname: string };
    cur_user?: { nickname: string };
    module?: { name: string; id: number };
    created_at: string;
}

interface Project {
    id: number;
    name: string;
    description: string;
    img_cover: string;
    status?: number; // 1: 进行中, 2: 已归档
}

const projectId = computed(() => route.params.id as string);
const project = ref<Project | null>(null);
const issues = ref<Issue[]>([]);
const loading = ref(false);
const selectedIssue = ref<Issue | null>(null);
const total = ref(0);
const page = ref(1);
const limit = ref(20);
const isFilterExpanded = ref(false);

const filters = ref({
    status: "",
    priority: "",
    title: "",
    environment: "",
    bug_type: "",
    cur_user_id: "",
    user_id: "",
});

// 创建问题弹窗
const createIssueDialogVisible = ref(false);
const issueFormRef = ref<FormInstance>();
const issueForm = ref({
    title: "",
    content: "",
    priority: 1,
    module_id: "",
    cur_user_id: "",
    type: 1,
    environment: "test",
    bug_type: "bug",
    status: "1",
});

const issueRules = ref<FormRules>({
    title: [{ required: true, message: "请输入问题标题", trigger: "blur" }],
    content: [{ required: true, message: "请输入问题描述", trigger: "blur" }],
});

// 模块列表和用户列表
const modules = ref<any[]>([]);
const users = ref<any[]>([]);

const fetchModules = async () => {
    try {
        const res: any = await api.get("/issue/edit", {
            params: { project_id: projectId.value },
        });
        console.log("fetchModules", res);
        if (res.code === 1) {
            modules.value = res.data.moduleList || [];
            users.value = res.data.userList ? JSON.parse(res.data.userList) : [];
        }
    } catch (e) {
        console.error(e);
    }
};

// 评论历史
interface Comment {
    id: number;
    user: { nickname: string };
    content: string;
    created_at: string;
}

const comments = ref<Comment[]>([]);
const commentsLoading = ref(false);

const fetchComments = async () => {
    if (!selectedIssue.value) return;

    commentsLoading.value = true;
    try {
        const res: any = await api.get("/issue/logList", {
            params: { id: selectedIssue.value.id },
        });
        if (res.code === 1) {
            // 只显示评论类型的日志（type=5）
            comments.value = res.data.filter((log: any) => log.type === 5);
        }
    } catch (e) {
        console.error(e);
    } finally {
        commentsLoading.value = false;
    }
};

// 评论功能
const commentContent = ref("");
const commentSubmitting = ref(false);

const submitComment = async () => {
    if (!commentContent.value.trim()) {
        ElMessage.warning("请输入评论内容");
        return;
    }

    if (!selectedIssue.value) {
        return;
    }

    commentSubmitting.value = true;
    try {
        const res: any = await api.post("/issue/deal", {
            id: selectedIssue.value.id,
            type: 5, // 5表示评论
            content: commentContent.value,
        });
        if (res.code === 1) {
            ElMessage.success("评论成功");
            commentContent.value = "";
            // 刷新评论列表
            fetchComments();
        } else {
            ElMessage.error(res.msg || "评论失败");
        }
    } catch (e: any) {
        ElMessage.error(e.response?.data?.msg || "评论失败");
    } finally {
        commentSubmitting.value = false;
    }
};

// 处理指派人变更
const handleAssignChange = async (userId: number) => {
    if (!selectedIssue.value) return;

    try {
        const res: any = await api.post("/issue/deal", {
            id: selectedIssue.value.id,
            type: 6, // 6表示指派
            cur_user_id: userId || 0,
            content: userId ? `指派给处理` : "取消指派",
        });
        if (res.code === 1) {
            ElMessage.success("指派成功");
            fetchIssues(); // 刷新问题列表
        } else {
            ElMessage.error(res.msg || "指派失败");
        }
    } catch (e: any) {
        ElMessage.error(e.response?.data?.msg || "指派失败");
    }
};

// 处理状态变更
const handleStatusChange = async (status: number) => {
    if (!selectedIssue.value) return;
    try {
        const res: any = await api.post("/issue/edit", {
            id: selectedIssue.value.id,
            status: status,
        });
        if (res.code === 1) {
            ElMessage.success("状态更新成功");
            fetchIssues();
        } else {
            ElMessage.error(res.msg || "状态更新失败");
        }
    } catch (e: any) {
        ElMessage.error(e.response?.data?.msg || "状态更新失败");
    }
};

// 处理优先级变更
const handlePriorityChange = async (priority: number) => {
    if (!selectedIssue.value) return;
    try {
        const res: any = await api.post("/issue/edit", {
            id: selectedIssue.value.id,
            priority: priority,
        });
        if (res.code === 1) {
            ElMessage.success("优先级更新成功");
            fetchIssues();
        } else {
            ElMessage.error(res.msg || "优先级更新失败");
        }
    } catch (e: any) {
        ElMessage.error(e.response?.data?.msg || "优先级更新失败");
    }
};

// 处理模块变更
const handleModuleChange = async (moduleId: number) => {
    if (!selectedIssue.value) return;
    try {
        const res: any = await api.post("/issue/edit", {
            id: selectedIssue.value.id,
            module_id: moduleId,
        });
        if (res.code === 1) {
            ElMessage.success("模块更新成功");
            fetchIssues();
        } else {
            ElMessage.error(res.msg || "模块更新失败");
        }
    } catch (e: any) {
        ElMessage.error(e.response?.data?.msg || "模块更新失败");
    }
};

// 处理Bug类型变更
const handleBugTypeChange = async (bugType: string) => {
    if (!selectedIssue.value) return;
    try {
        const res: any = await api.post("/issue/edit", {
            id: selectedIssue.value.id,
            bug_type: bugType,
        });
        if (res.code === 1) {
            ElMessage.success("Bug类型更新成功");
            fetchIssues();
        } else {
            ElMessage.error(res.msg || "Bug类型更新失败");
        }
    } catch (e: any) {
        ElMessage.error(e.response?.data?.msg || "Bug类型更新失败");
    }
};

const fetchProject = async () => {
    try {
        const res: any = await api.get("/project/list");
        if (res.code === 1) {
            const foundProject = res.data.projectList.find((p: Project) => p.id === Number(projectId.value));
            if (foundProject) {
                project.value = {
                    ...foundProject,
                    status: foundProject.status || 1,
                };
            }
        }
    } catch (e) {
        console.error(e);
    }
};

const handleArchiveProject = async () => {
    if (!project.value) return;
    try {
        const res: any = await api.post("/project/editStatus", {
            id: project.value.id,
            status: 2,
        });
        if (res.code === 1) {
            project.value.status = 2;
            ElMessage.success("项目已归档");
        } else {
            ElMessage.error(res.msg || "归档失败");
        }
    } catch (e: any) {
        ElMessage.error(e.response?.data?.msg || "归档失败");
    }
};

const handleActivateProject = async () => {
    if (!project.value) return;
    try {
        const res: any = await api.post("/project/editStatus", {
            id: project.value.id,
            status: 1,
        });
        if (res.code === 1) {
            project.value.status = 1;
            ElMessage.success("项目已重新激活");
        } else {
            ElMessage.error(res.msg || "激活失败");
        }
    } catch (e: any) {
        ElMessage.error(e.response?.data?.msg || "激活失败");
    }
};

const fetchIssues = async () => {
    loading.value = true;
    try {
        const res: any = await api.get("/issue/list", {
            params: {
                page: page.value,
                limit: limit.value,
                project_id: projectId.value,
                ...filters.value,
            },
        });
        if (res.code === 1) {
            issues.value = res.data.rows;
            total.value = res.data.total;
            // 如果有选中的问题，更新它的数据
            if (selectedIssue.value) {
                const updated = issues.value.find((i) => i.id === selectedIssue.value!.id);
                if (updated) {
                    selectedIssue.value = updated;
                }
            }
        } else {
            ElMessage.error(res.msg || "加载问题列表失败");
        }
    } catch (e) {
        console.error(e);
        ElMessage.error("加载问题列表失败");
    } finally {
        loading.value = false;
    }
};

const handleSelectIssue = (issue: Issue) => {
    selectedIssue.value = issue;
    // 确保用户列表已加载
    if (users.value.length === 0) {
        fetchModules();
    }
    // 加载评论历史
    fetchComments();
};

const handlePageChange = (val: number) => {
    page.value = val;
    fetchIssues();
};

const handleCreateIssue = () => {
    fetchModules();
    createIssueDialogVisible.value = true;
};

const submitIssue = async (formEl: FormInstance | undefined) => {
    if (!formEl) return;
    await formEl.validate(async (valid) => {
        if (valid) {
            try {
                const res: any = await api.post("/issue/edit", {
                    ...issueForm.value,
                    project_id: projectId.value,
                });
                if (res.code === 1) {
                    ElMessage.success("问题创建成功");
                    createIssueDialogVisible.value = false;
                    issueForm.value = {
                        title: "",
                        content: "",
                        priority: 1,
                        module_id: "",
                        cur_user_id: "",
                        type: 1,
                        environment: "",
                        bug_type: "",
                        status: "1",
                    };
                    fetchIssues();
                } else {
                    ElMessage.error(res.msg || "创建失败");
                }
            } catch (e: any) {
                ElMessage.error(e.response?.data?.msg || "创建失败");
            }
        }
    });
};

const resetFilters = () => {
    filters.value = { status: "", priority: "", title: "", environment: "", bug_type: "", cur_user_id: "", user_id: "" };
    page.value = 1;
    fetchIssues();
};

onMounted(() => {
    fetchProject();
    fetchIssues();
});

// Status Helper
const getStatusTag = (status: number) => {
    const statusMap: Record<number, { type: string; label: string }> = {
        1: { type: "danger", label: "未修改" },
        2: { type: "warning", label: "未复现" },
        3: { type: "info", label: "不是问题" },
        4: { type: "info", label: "转下期需求" },
        5: { type: "primary", label: "已修改" },
        6: { type: "success", label: "已上线" },
        7: { type: "success", label: "验收通过" },
        8: { type: "info", label: "暂不解决" },
        9: { type: "warning", label: "无法解决" },
        10: { type: "warning", label: "有异议需讨论" },
        11: { type: "info", label: "重复提交" },
    };
    return statusMap[status] || { type: "info", label: "未知" };
};

const getBugTypeLabel = (type: string) => {
    const labels: Record<string, string> = {
        bug: "Bug",
        style: "样式",
        experience: "体验",
        design: "产品",
    };
    return labels[type] || type;
};

const getBugTypeColor = (type: string) => {
    const colors: Record<string, string> = {
        bug: "danger",
        style: "warning",
        experience: "info",
        design: "success",
    };
    return colors[type] || "info";
};

const getEnvironmentLabel = (env: string) => {
    const labels: Record<string, string> = {
        test: "测试环境",
        dev: "开发环境",
        prod: "正式环境",
    };
    return labels[env] || env;
};

const goBack = () => {
    router.push("/projects");
};
</script>

<template>
    <div class="h-full flex flex-col">
        <!-- 页面头部 -->
        <div class="flex justify-between items-start mb-3">
            <div class="flex-1">
                <el-button :icon="ArrowLeft" @click="goBack">返回项目列表</el-button>
                <div class="mt-3 flex items-center gap-3" v-if="project">
                    <h1 class="text-2xl font-semibold text-slate-800 m-0 mb-1">{{ project.name }}</h1>
                    <el-tag v-if="project.status === 2" type="info" effect="dark" size="small" round>已归档</el-tag>
                    <p class="text-sm text-slate-500 m-0 border-l border-slate-300 pl-3 ml-3" v-if="project.description">{{ project.description }}</p>
                </div>
            </div>
            <div class="flex gap-3">
                <el-button v-if="project?.status !== 2" type="warning" :icon="Warning" plain @click="handleArchiveProject">归档项目</el-button>
                <el-button v-else type="success" :icon="Check" plain @click="handleActivateProject">重新激活</el-button>
                <el-button type="primary" :icon="Plus" @click="handleCreateIssue" :disabled="project?.status === 2">提交新问题</el-button>
            </div>
        </div>

        <!-- 筛选区域 -->
        <el-card class="mb-3 border-none" shadow="never">
            <div class="flex justify-between items-start">
                <el-form :inline="true" :model="filters" class="filter-form flex-1 grid grid-cols-4 gap-4">
                    <el-form-item label="标题搜索" class="mr-0 w-full">
                        <el-input v-model="filters.title" placeholder="输入标题关键字..." clearable />
                    </el-form-item>
                    <el-form-item label="指派给" class="mr-0 w-full">
                        <el-select v-model="filters.cur_user_id" placeholder="全部" clearable filterable>
                            <el-option v-for="user in users" :key="user.id" :label="user.nickname" :value="user.id" />
                        </el-select>
                    </el-form-item>
                    <el-form-item label="报告人" class="mr-0 w-full">
                        <el-select v-model="filters.user_id" placeholder="全部" clearable filterable>
                            <el-option v-for="user in users" :key="user.id" :label="user.nickname" :value="user.id" />
                        </el-select>
                    </el-form-item>
                    <el-form-item label="状态" class="mr-0 w-full">
                        <el-select v-model="filters.status" placeholder="全部状态" clearable>
                            <el-option label="未修改" value="1" />
                            <el-option label="未复现" value="2" />
                            <el-option label="不是问题" value="3" />
                            <el-option label="转下期需求" value="4" />
                            <el-option label="已修改" value="5" />
                            <el-option label="已上线" value="6" />
                            <el-option label="验收通过" value="7" />
                            <el-option label="暂不解决" value="8" />
                            <el-option label="无法解决" value="9" />
                            <el-option label="有异议需讨论" value="10" />
                            <el-option label="重复提交" value="11" />
                        </el-select>
                    </el-form-item>

                    <template v-if="isFilterExpanded">
                        <el-form-item label="开发环境" class="mr-0 w-full">
                            <el-select v-model="filters.environment" placeholder="全部环境" clearable>
                                <el-option label="测试环境" value="test" />
                                <el-option label="开发环境" value="dev" />
                                <el-option label="正式环境" value="prod" />
                            </el-select>
                        </el-form-item>
                        <el-form-item label="Bug类型" class="mr-0 w-full">
                            <el-select v-model="filters.bug_type" placeholder="全部类型" clearable>
                                <el-option label="Bug" value="bug" />
                                <el-option label="样式问题" value="style" />
                                <el-option label="体验问题" value="experience" />
                                <el-option label="产品设计" value="design" />
                            </el-select>
                        </el-form-item>
                    </template>
                </el-form>
                <div class="flex gap-4 ml-8">
                    <el-button type="primary" @click="fetchIssues">查询</el-button>
                    <el-button @click="resetFilters">重置</el-button>
                    <el-button link type="primary" @click="isFilterExpanded = !isFilterExpanded">
                        {{ isFilterExpanded ? "收起" : "展开" }}
                        <el-icon class="el-icon--right"><component :is="isFilterExpanded ? ArrowUp : ArrowDown" /></el-icon>
                    </el-button>
                </div>
            </div>
        </el-card>

        <!-- 主内容区：左右分栏 -->
        <div class="flex gap-3 flex-1 min-h-0 items-stretch">
            <!-- 左侧：问题列表 (60%) -->
            <div class="w-3/5 flex flex-col h-full">
                <el-card shadow="never" :body-style="{ padding: '0' }" class="h-full flex flex-col">
                    <div class="px-5 py-4 border-b border-slate-100 flex justify-between items-center">
                        <span class="text-base font-semibold text-slate-800">问题列表</span>
                        <span class="text-sm text-slate-500">共 {{ total }} 条</span>
                    </div>
                    <div v-loading="loading" class="flex-1 overflow-y-auto">
                        <div
                            v-for="issue in issues"
                            :key="issue.id"
                            class="px-4 py-2.5 border-b border-slate-100 cursor-pointer transition-all border-l-3 border-l-transparent"
                            :class="{
                                'bg-blue-50 border-l-blue-500': selectedIssue?.id === issue.id,
                                'border-l-red-500 bg-red-50': issue.priority > 1,
                                'bg-red-100': issue.priority > 1 && selectedIssue?.id === issue.id,
                                'hover:bg-slate-50': selectedIssue?.id !== issue.id,
                            }"
                            @click="handleSelectIssue(issue)">
                            <div class="flex justify-between items-center mb-1.5">
                                <div class="flex items-center flex-1 min-w-0">
                                    <span class="text-xs font-semibold text-slate-500 mr-2 flex-shrink-0">#{{ issue.id }}</span>
                                    <span class="text-sm font-medium text-slate-800 overflow-hidden text-ellipsis whitespace-nowrap" :title="issue.title">{{ issue.title }}</span>
                                </div>
                                <div class="flex gap-1.5 flex-shrink-0">
                                    <el-tag v-if="issue.priority > 1" type="danger" size="small" effect="dark">紧急</el-tag>
                                    <el-tag v-if="issue.bug_type" :type="getBugTypeColor(issue.bug_type)" size="small" effect="plain">
                                        {{ getBugTypeLabel(issue.bug_type) }}
                                    </el-tag>
                                    <el-tag :type="getStatusTag(issue.status).type" size="small" effect="plain">
                                        {{ getStatusTag(issue.status).label }}
                                    </el-tag>
                                </div>
                            </div>
                            <div class="flex items-center gap-3 text-xs text-slate-500 flex-wrap">
                                <span class="flex items-center gap-1" v-if="issue.environment">
                                    <el-icon><Monitor /></el-icon>
                                    {{ getEnvironmentLabel(issue.environment) }}
                                </span>
                                <span class="flex items-center gap-1" v-if="issue.module">
                                    <el-icon><FolderOpened /></el-icon>
                                    {{ issue.module.name }}
                                </span>

                                <span class="flex items-center gap-1 ml-auto text-slate-600">
                                    {{ issue.user.nickname }}
                                    <template v-if="issue.cur_user">
                                        <span class="text-slate-400 mx-1">→</span>
                                        <span class="font-medium">{{ issue.cur_user.nickname }}</span>
                                    </template>
                                </span>
                            </div>
                        </div>
                        <el-empty v-if="!loading && issues.length === 0" description="暂无问题数据" />
                    </div>
                    <div class="px-5 py-4 flex justify-center border-t border-slate-100">
                        <el-pagination v-model:current-page="page" :page-size="limit" layout="total, prev, pager, next" :total="total" @current-change="handlePageChange" small />
                    </div>
                </el-card>
            </div>

            <!-- 右侧：问题详情 (40%) -->
            <div class="w-2/5 flex flex-col h-full">
                <el-card shadow="never" v-if="selectedIssue" class="flex-1 flex flex-col overflow-hidden" :body-style="{ flex: 1, minHeight: 0, display: 'flex', flexDirection: 'column', padding: 0 }">
                    <template #header>
                        <div class="flex justify-between items-center">
                            <h3 class="text-base font-semibold text-slate-800 m-0">问题详情 #{{ selectedIssue.id }}</h3>
                            <span class="text-sm text-slate-500">{{ selectedIssue.created_at ? selectedIssue.created_at : "" }}</span>
                        </div>
                    </template>
                    <div class="flex-1 overflow-y-auto">
                        <div class="p-5">
                            <!-- 快速编辑区域：2x2网格布局 -->
                            <div class="grid grid-cols-2 gap-4 mb-6 p-4 bg-slate-50 rounded-lg border border-slate-200">
                                <div>
                                    <div class="text-[13px] text-slate-500 mb-2 font-medium">状态</div>
                                    <el-select v-model="selectedIssue.status" placeholder="选择状态" size="small" @change="handleStatusChange" class="w-full">
                                        <el-option label="未修改" :value="1" />
                                        <el-option label="未复现" :value="2" />
                                        <el-option label="不是问题" :value="3" />
                                        <el-option label="转下期需求" :value="4" />
                                        <el-option label="已修改" :value="5" />
                                        <el-option label="已上线" :value="6" />
                                        <el-option label="验收通过" :value="7" />
                                        <el-option label="暂不解决" :value="8" />
                                        <el-option label="无法解决" :value="9" />
                                        <el-option label="有异议需讨论" :value="10" />
                                        <el-option label="重复提交" :value="11" />
                                    </el-select>
                                </div>
                                <div>
                                    <div class="text-[13px] text-slate-500 mb-2 font-medium">优先级</div>
                                    <el-select v-model="selectedIssue.priority" placeholder="选择优先级" size="small" @change="handlePriorityChange" class="w-full">
                                        <el-option label="普通" :value="1" />
                                        <el-option label="紧急" :value="2" />
                                    </el-select>
                                </div>
                                <div>
                                    <div class="text-[13px] text-slate-500 mb-2 font-medium">所属模块</div>
                                    <el-select v-model="selectedIssue.module_id" placeholder="选择模块" size="small" @change="handleModuleChange" clearable class="w-full">
                                        <el-option v-for="module in modules" :key="module.id" :label="module.name" :value="module.id" />
                                    </el-select>
                                </div>
                                <div>
                                    <div class="text-[13px] text-slate-500 mb-2 font-medium">Bug类型</div>
                                    <el-select v-model="selectedIssue.bug_type" placeholder="选择类型" size="small" @change="handleBugTypeChange" clearable class="w-full">
                                        <el-option label="Bug" value="bug" />
                                        <el-option label="样式问题" value="style" />
                                        <el-option label="体验问题" value="experience" />
                                        <el-option label="产品设计" value="design" />
                                    </el-select>
                                </div>
                                <div class="">
                                    <div class="text-[13px] text-slate-500 mb-2 font-medium">指派给</div>
                                    <div class="text-sm text-slate-800">
                                        <el-select v-model="selectedIssue.cur_user_id" placeholder="选择处理人" size="small" @change="handleAssignChange" clearable style="width: 200px">
                                            <el-option v-for="user in users" :key="user.id" :label="user.nickname" :value="user.id" />
                                        </el-select>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-4">
                                <div class="text-[13px] text-slate-500 mb-2 font-medium">问题标题</div>
                                <div class="text-sm text-slate-800">{{ selectedIssue.title }}</div>
                            </div>
                            <div class="mb-4">
                                <div class="text-[13px] text-slate-500 mb-2 font-medium">问题描述</div>
                                <div class="text-sm text-slate-800 leading-relaxed whitespace-pre-wrap break-words" v-html="selectedIssue.content"></div>
                            </div>
                            <div class="mb-4">
                                <div class="text-[13px] text-slate-500 mb-2 font-medium">报告人</div>
                                <div class="text-sm text-slate-800">{{ selectedIssue.user.nickname }}</div>
                            </div>

                            <!-- 评论历史 -->
                            <div class="mt-6 pt-6 border-t border-gray-200" v-if="comments.length > 0">
                                <div class="text-[13px] text-slate-500 mb-2 font-medium">评论记录（{{ comments.length }}）</div>
                                <div class="mt-3 max-h-[600px] overflow-y-auto" v-loading="commentsLoading">
                                    <div v-for="comment in comments" :key="comment.id" class="mb-4 last:mb-0 p-3 bg-slate-50 rounded-lg border-l-3 border-l-blue-500">
                                        <div class="flex justify-between items-center mb-2">
                                            <span class="font-semibold text-sm text-slate-800">{{ comment.user.nickname }}</span>
                                            <span class="text-xs text-slate-400">{{ comment.created_at }}</span>
                                        </div>
                                        <div class="text-sm text-slate-600 leading-relaxed break-words" v-html="comment.content"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- 评论区 -->
                            <div class="mt-6 pt-6 pb-6 border-t border-gray-200">
                                <div class="text-[13px] text-slate-500 mb-2 font-medium">添加评论</div>
                                <RichEditor v-model="commentContent" height="120px" placeholder="任何人都可以评论...(支持粘贴图片)" />
                                <div class="mt-3 flex justify-end pb-4">
                                    <el-button type="primary" size="small" :loading="commentSubmitting" @click="submitComment">
                                        {{ commentSubmitting ? "提交中..." : "提交评论" }}
                                    </el-button>
                                </div>
                            </div>
                        </div>
                    </div>
                </el-card>
                <el-card shadow="never" v-else class="flex-1 flex items-center justify-center">
                    <el-empty description="请从左侧选择一个问题查看详情" />
                </el-card>
            </div>
        </div>

        <!-- 创建问题抽屉 -->
        <el-drawer v-model="createIssueDialogVisible" title="提交新问题" size="700px" direction="rtl">
            <el-form ref="issueFormRef" :model="issueForm" :rules="issueRules" label-width="90px" class="pr-4">
                <el-form-item label="问题标题" prop="title">
                    <el-input v-model="issueForm.title" placeholder="请输入问题标题" />
                </el-form-item>
                <el-form-item label="问题描述" prop="content">
                    <RichEditor v-model="issueForm.content" height="200px" placeholder="请详细描述问题...（支持粘贴图片）" />
                </el-form-item>
                <el-row :gutter="16">
                    <el-col :span="12">
                        <el-form-item label="开发环境">
                            <el-select v-model="issueForm.environment" placeholder="请选择" style="width: 100%">
                                <el-option label="测试环境" value="test" />
                                <el-option label="开发环境" value="dev" />
                                <el-option label="正式环境" value="prod" />
                            </el-select>
                        </el-form-item>
                    </el-col>
                    <el-col :span="12">
                        <el-form-item label="Bug类型">
                            <el-select v-model="issueForm.bug_type" placeholder="请选择" style="width: 100%">
                                <el-option label="Bug" value="bug" />
                                <el-option label="样式问题" value="style" />
                                <el-option label="体验问题" value="experience" />
                                <el-option label="产品设计" value="design" />
                            </el-select>
                        </el-form-item>
                    </el-col>
                </el-row>
                <el-row :gutter="16">
                    <el-col :span="12">
                        <el-form-item label="优先级">
                            <el-select v-model="issueForm.priority" style="width: 100%">
                                <el-option label="普通" :value="1" />
                                <el-option label="紧急" :value="2" />
                            </el-select>
                        </el-form-item>
                    </el-col>
                    <el-col :span="12">
                        <el-form-item label="状态">
                            <el-select v-model="issueForm.status" placeholder="请选择" style="width: 100%">
                                <el-option label="未修改" value="1" />
                                <el-option label="未复现" value="2" />
                                <el-option label="不是问题" value="3" />
                                <el-option label="转下期需求" value="4" />
                                <el-option label="已修改" value="5" />
                                <el-option label="已上线" value="6" />
                                <el-option label="验收通过" value="7" />
                                <el-option label="暂不解决" value="8" />
                                <el-option label="无法解决" value="9" />
                                <el-option label="有异议需讨论" value="10" />
                                <el-option label="重复提交" value="11" />
                            </el-select>
                        </el-form-item>
                    </el-col>
                </el-row>
                <el-row :gutter="16">
                    <el-col :span="12">
                        <el-form-item label="所属模块">
                            <el-select v-model="issueForm.module_id" placeholder="请选择模块" style="width: 100%">
                                <el-option v-for="module in modules" :key="module.id" :label="module.name" :value="module.id" />
                            </el-select>
                        </el-form-item>
                    </el-col>
                    <el-col :span="12">
                        <el-form-item label="指派给">
                            <el-select v-model="issueForm.cur_user_id" placeholder="请选择处理人" clearable style="width: 100%">
                                <el-option v-for="user in users" :key="user.id" :label="user.nickname" :value="user.id" />
                            </el-select>
                        </el-form-item>
                    </el-col>
                </el-row>
                <div class="drawer-footer" style="margin-top: 24px; text-align: right">
                    <el-button @click="createIssueDialogVisible = false">取消</el-button>
                    <el-button type="primary" @click="submitIssue(issueFormRef)">提交</el-button>
                </div>
            </el-form>
        </el-drawer>
    </div>
</template>

<style scoped>
/* 覆盖 Element Plus 表单项样式以适应 Grid */
:deep(.el-form-item) {
    margin-bottom: 16px;
    margin-right: 0;
    display: flex;
}
:deep(.el-form-item__content) {
    flex: 1;
}
:deep(.el-select),
:deep(.el-input) {
    width: 100%;
}

/* 滚动条样式 */
.overflow-y-auto::-webkit-scrollbar {
    width: 6px;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
    background-color: #cbd5e1;
    border-radius: 3px;
}

.overflow-y-auto::-webkit-scrollbar-track {
    background-color: #f1f5f9;
}
</style>
