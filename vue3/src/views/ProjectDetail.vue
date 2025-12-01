<script setup lang="ts">
import { ref, computed, onMounted } from "vue";
import { useRoute, useRouter } from "vue-router";
import api from "../api";
import { ArrowLeft, Plus, User, Monitor, Warning } from "@element-plus/icons-vue";
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
    user: { nickname: string };
    cur_user?: { nickname: string };
    module?: { name: string };
    created_at: string;
}

interface Project {
    id: number;
    name: string;
    description: string;
    img_cover: string;
}

const projectId = computed(() => route.params.id as string);
const project = ref<Project | null>(null);
const issues = ref<Issue[]>([]);
const loading = ref(false);
const selectedIssue = ref<Issue | null>(null);
const total = ref(0);
const page = ref(1);
const limit = ref(20);

const filters = ref({
    status: "",
    priority: "",
    title: "",
    environment: "",
    bug_type: "",
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
    environment: "",
    bug_type: "",
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
        if (res.code === 1) {
            modules.value = res.data.moduleList || [];
            users.value = res.data.userList || [];
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

const fetchProject = async () => {
    try {
        const res: any = await api.get("/project/list");
        if (res.code === 1) {
            project.value = res.data.projectList.find((p: Project) => p.id === Number(projectId.value));
        }
    } catch (e) {
        console.error(e);
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
    filters.value = { status: "", priority: "", title: "", environment: "", bug_type: "" };
    page.value = 1;
    fetchIssues();
};

onMounted(() => {
    fetchProject();
    fetchIssues();
});

// Status Helper
const getStatusTag = (status: number) => {
    switch (status) {
        case 1:
            return { type: "danger", label: "未解决" };
        case 2:
            return { type: "warning", label: "待审核" };
        case 8:
            return { type: "success", label: "已关闭" };
        default:
            return { type: "info", label: "未知" };
    }
};

const getPriorityTag = (priority: number) => {
    return priority > 1 ? "danger" : "info";
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

const getEnvironmentLabel = (env: string) => {
    const labels: Record<string, string> = {
        test: "测试",
        dev: "开发",
        prod: "正式",
    };
    return labels[env] || env;
};

const goBack = () => {
    router.push("/projects");
};
</script>

<template>
    <div class="project-detail-container">
        <!-- 页面头部 -->
        <div class="page-header">
            <div class="header-left">
                <el-button :icon="ArrowLeft" @click="goBack">返回项目列表</el-button>
                <div class="project-info" v-if="project">
                    <h1 class="page-title">{{ project.name }}</h1>
                    <p class="page-subtitle">{{ project.description || "暂无项目描述" }}</p>
                </div>
            </div>
            <el-button type="primary" :icon="Plus" size="large" @click="handleCreateIssue">提交新问题</el-button>
        </div>

        <!-- 筛选区域 -->
        <el-card class="filter-card" shadow="never">
            <el-form :inline="true" :model="filters" class="filter-form">
                <el-form-item label="标题搜索">
                    <el-input v-model="filters.title" placeholder="输入标题关键字..." clearable style="width: 200px" />
                </el-form-item>
                <el-form-item label="状态">
                    <el-select v-model="filters.status" placeholder="全部状态" clearable style="width: 150px">
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
                <el-form-item label="开发环境">
                    <el-select v-model="filters.environment" placeholder="全部环境" clearable style="width: 130px">
                        <el-option label="测试环境" value="test" />
                        <el-option label="开发环境" value="dev" />
                        <el-option label="正式环境" value="prod" />
                    </el-select>
                </el-form-item>
                <el-form-item label="Bug类型">
                    <el-select v-model="filters.bug_type" placeholder="全部类型" clearable style="width: 130px">
                        <el-option label="Bug" value="bug" />
                        <el-option label="样式问题" value="style" />
                        <el-option label="体验问题" value="experience" />
                        <el-option label="产品设计" value="design" />
                    </el-select>
                </el-form-item>
                <el-form-item>
                    <el-button type="primary" @click="fetchIssues">查询</el-button>
                    <el-button @click="resetFilters" :icon="Refresh">重置</el-button>
                </el-form-item>
            </el-form>
        </el-card>

        <!-- 主内容区：左右分栏 -->
        <div class="content-layout">
            <!-- 左侧：问题列表 (60%) -->
            <div class="issue-list-panel">
                <el-card shadow="never" :body-style="{ padding: '0' }">
                    <div class="list-header">
                        <span class="list-title">问题列表</span>
                        <span class="list-count">共 {{ total }} 条</span>
                    </div>
                    <div v-loading="loading" class="issue-list">
                        <div
                            v-for="issue in issues"
                            :key="issue.id"
                            class="issue-item"
                            :class="{ active: selectedIssue?.id === issue.id, urgent: issue.priority > 1 }"
                            @click="handleSelectIssue(issue)">
                            <div class="issue-item-main">
                                <div class="issue-item-left">
                                    <span class="issue-id">#{{ issue.id }}</span>
                                    <span class="issue-title" :title="issue.title">{{ issue.title }}</span>
                                </div>
                                <div class="issue-item-tags">
                                    <el-tag v-if="issue.priority > 1" type="danger" size="small" effect="dark">紧急</el-tag>
                                    <el-tag :type="getStatusTag(issue.status).type" size="small" effect="plain">
                                        {{ getStatusTag(issue.status).label }}
                                    </el-tag>
                                </div>
                            </div>
                            <div class="issue-item-meta">
                                <span class="meta-item" v-if="issue.module">
                                    <el-icon><FolderOpened /></el-icon>
                                    {{ issue.module.name }}
                                </span>
                                <span class="meta-item" v-if="issue.bug_type">
                                    <el-icon><Warning /></el-icon>
                                    {{ getBugTypeLabel(issue.bug_type) }}
                                </span>
                                <span class="meta-item" v-if="issue.environment">
                                    <el-icon><Monitor /></el-icon>
                                    {{ getEnvironmentLabel(issue.environment) }}
                                </span>
                                <span class="meta-item meta-user">
                                    <el-icon><User /></el-icon>
                                    {{ issue.user.nickname }}
                                </span>
                            </div>
                        </div>
                        <el-empty v-if="!loading && issues.length === 0" description="暂无问题数据" />
                    </div>
                    <div class="pagination-container">
                        <el-pagination v-model:current-page="page" :page-size="limit" layout="total, prev, pager, next" :total="total" @current-change="handlePageChange" small />
                    </div>
                </el-card>
            </div>

            <!-- 右侧：问题详情 (40%) -->
            <div class="issue-detail-panel">
                <el-card shadow="never" v-if="selectedIssue" class="detail-card">
                    <template #header>
                        <div class="detail-header">
                            <h3 class="detail-title">问题详情</h3>
                            <el-tag :type="getStatusTag(selectedIssue.status).type" effect="dark">
                                {{ getStatusTag(selectedIssue.status).label }}
                            </el-tag>
                        </div>
                    </template>
                    <div class="detail-content-wrapper">
                        <div class="detail-content">
                            <div class="detail-section">
                                <div class="section-label">问题标题</div>
                                <div class="section-value">{{ selectedIssue.title }}</div>
                            </div>
                            <div class="detail-section">
                                <div class="section-label">问题描述</div>
                                <div class="section-value content-text">{{ selectedIssue.content || "暂无描述" }}</div>
                            </div>
                            <div class="detail-section">
                                <div class="section-label">优先级</div>
                                <div class="section-value">
                                    <el-tag :type="getPriorityTag(selectedIssue.priority)" effect="light">
                                        {{ selectedIssue.priority > 1 ? "紧急" : "普通" }}
                                    </el-tag>
                                </div>
                            </div>
                            <div class="detail-section">
                                <div class="section-label">所属模块</div>
                                <div class="section-value">{{ selectedIssue.module?.name || "未分配" }}</div>
                            </div>
                            <div class="detail-section">
                                <div class="section-label">报告人</div>
                                <div class="section-value">{{ selectedIssue.user.nickname }}</div>
                            </div>
                            <div class="detail-section">
                                <div class="section-label">指派给</div>
                                <div class="section-value">
                                    <el-select v-model="selectedIssue.cur_user_id" placeholder="选择处理人" size="small" @change="handleAssignChange" clearable style="width: 200px">
                                        <el-option v-for="user in users" :key="user.id" :label="user.nickname" :value="user.id" />
                                    </el-select>
                                </div>
                            </div>
                            <div class="detail-section">
                                <div class="section-label">创建时间</div>
                                <div class="section-value">{{ selectedIssue.created_at }}</div>
                            </div>

                            <!-- 评论历史 -->
                            <div class="comment-history-section" v-if="comments.length > 0">
                                <div class="section-label">评论记录（{{ comments.length }}）</div>
                                <div class="comment-list" v-loading="commentsLoading">
                                    <div v-for="comment in comments" :key="comment.id" class="comment-item">
                                        <div class="comment-header">
                                            <span class="comment-user">{{ comment.user.nickname }}</span>
                                            <span class="comment-time">{{ comment.created_at }}</span>
                                        </div>
                                        <div class="comment-content" v-html="comment.content"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- 评论区 -->
                            <div class="comment-section">
                                <div class="section-label">添加评论</div>
                                <RichEditor v-model="commentContent" height="150px" placeholder="任何人都可以评论...（支持粘贴图片）" />
                                <div class="comment-actions">
                                    <el-button type="primary" size="small" :loading="commentSubmitting" @click="submitComment">
                                        {{ commentSubmitting ? "提交中..." : "提交评论" }}
                                    </el-button>
                                </div>
                            </div>
                        </div>
                    </div>
                </el-card>
                <el-card shadow="never" v-else class="detail-card empty">
                    <el-empty description="请从左侧选择一个问题查看详情" />
                </el-card>
            </div>
        </div>

        <!-- 创建问题抽屉 -->
        <el-drawer v-model="createIssueDialogVisible" title="提交新问题" size="700px" direction="rtl">
            <el-form ref="issueFormRef" :model="issueForm" :rules="issueRules" label-width="100px">
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
.project-detail-container {
    height: 100%;
    display: flex;
    flex-direction: column;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 20px;
}

.header-left {
    flex: 1;
}

.project-info {
    margin-top: 12px;
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

.filter-card {
    margin-bottom: 20px;
    border: none;
}

.filter-form :deep(.el-form-item) {
    margin-bottom: 0;
    margin-right: 16px;
}

.content-layout {
    display: flex;
    gap: 20px;
    flex: 1;
    overflow: hidden;
    align-items: stretch;
}

.issue-list-panel {
    width: 60%;
    display: flex;
    flex-direction: column;
    height: 100%;
}

.issue-detail-panel {
    width: 40%;
    display: flex;
    flex-direction: column;
    height: 100%;
}

.issue-list-panel > .el-card {
    height: 100%;
    display: flex;
    flex-direction: column;
}

.issue-detail-panel > .el-card {
    height: 100%;
}

.list-header {
    padding: 16px 20px;
    border-bottom: 1px solid #f1f5f9;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.list-title {
    font-size: 16px;
    font-weight: 600;
    color: #1e293b;
}

.list-count {
    font-size: 14px;
    color: #64748b;
}

.issue-list {
    flex: 1;
    overflow-y: auto;
}

.issue-item {
    padding: 10px 16px;
    border-bottom: 1px solid #f1f5f9;
    cursor: pointer;
    transition: all 0.2s;
    border-left: 3px solid transparent;
}

.issue-item:hover {
    background-color: #f8fafc;
}

.issue-item.active {
    background-color: #eff6ff;
    border-left-color: #3b82f6;
}

.issue-item.urgent {
    border-left-color: #ef4444;
    background-color: #fef2f2;
}

.issue-item.urgent.active {
    background-color: #fee2e2;
}

.issue-item-main {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 6px;
}

.issue-item-left {
    display: flex;
    align-items: center;
    flex: 1;
    min-width: 0;
}

.issue-id {
    font-size: 12px;
    font-weight: 600;
    color: #64748b;
    margin-right: 8px;
    flex-shrink: 0;
}

.issue-title {
    font-size: 14px;
    font-weight: 500;
    color: #1e293b;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.issue-item-tags {
    display: flex;
    gap: 6px;
    flex-shrink: 0;
}

.issue-item-meta {
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 12px;
    color: #64748b;
    flex-wrap: wrap;
}

.meta-item {
    display: flex;
    align-items: center;
    gap: 4px;
}

.meta-item .el-icon {
    font-size: 14px;
}

.meta-user {
    margin-left: auto;
}

.pagination-container {
    padding: 16px 20px;
    display: flex;
    justify-content: center;
    border-top: 1px solid #f1f5f9;
}

.detail-card {
    height: 100%;
    display: flex;
    flex-direction: column;
}

.detail-card.empty {
    display: flex;
    align-items: center;
    justify-content: center;
}

.detail-content-wrapper {
    overflow-y: auto;
    max-height: calc(100vh - 200px);
    padding-right: 8px;
}

.detail-content-wrapper::-webkit-scrollbar {
    width: 6px;
}

.detail-content-wrapper::-webkit-scrollbar-thumb {
    background-color: #cbd5e1;
    border-radius: 3px;
}

.detail-content-wrapper::-webkit-scrollbar-track {
    background-color: #f1f5f9;
}

.detail-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.detail-title {
    font-size: 16px;
    font-weight: 600;
    color: #1e293b;
    margin: 0;
}

.detail-content {
    flex: 1;
    overflow-y: auto;
}

.detail-section {
    margin-bottom: 24px;
}

.section-label {
    font-size: 13px;
    color: #64748b;
    margin-bottom: 8px;
    font-weight: 500;
}

.section-value {
    font-size: 14px;
    color: #1e293b;
}

.content-text {
    line-height: 1.6;
    white-space: pre-wrap;
    word-break: break-word;
}

.detail-footer {
    display: flex;
    gap: 12px;
}

.upload-tip {
    font-size: 12px;
    color: #94a3b8;
    margin-top: 8px;
}

.image-preview-list {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    margin-top: 12px;
}

.image-preview-item {
    position: relative;
    width: 100px;
    height: 100px;
    border-radius: 8px;
    overflow: hidden;
    border: 1px solid #e5e7eb;
}

.preview-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.remove-icon {
    position: absolute;
    top: 4px;
    right: 4px;
    width: 20px;
    height: 20px;
    background-color: rgba(0, 0, 0, 0.6);
    color: white;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
}

.remove-icon:hover {
    background-color: rgba(0, 0, 0, 0.8);
}

.comment-history-section {
    margin-top: 24px;
    padding-top: 24px;
    border-top: 1px solid #e5e7eb;
}

.comment-list {
    margin-top: 12px;
    max-height: 300px;
    overflow-y: auto;
}

.comment-item {
    margin-bottom: 16px;
    padding: 12px;
    background-color: #f8fafc;
    border-radius: 8px;
    border-left: 3px solid #3b82f6;
}

.comment-item:last-child {
    margin-bottom: 0;
}

.comment-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;
}

.comment-user {
    font-weight: 600;
    font-size: 14px;
    color: #1e293b;
}

.comment-time {
    font-size: 12px;
    color: #94a3b8;
}

.comment-content {
    font-size: 14px;
    color: #475569;
    line-height: 1.6;
    word-break: break-word;
}

.comment-section {
    margin-top: 24px;
    padding-top: 24px;
    border-top: 1px solid #e5e7eb;
}

.comment-actions {
    margin-top: 12px;
    display: flex;
    justify-content: flex-end;
}
</style>
