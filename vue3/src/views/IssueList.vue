<script setup lang="ts">
import { ref, onMounted, watch } from "vue";
import api from "../api";
import { Search, Refresh, Plus, Filter } from "@element-plus/icons-vue";
import { ElMessage } from "element-plus";

interface Issue {
    id: number;
    title: string;
    status: number;
    priority: number;
    user: { nickname: string };
    curUser: { nickname: string };
    module: { name: string };
    created_at: string;
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
        if (res.code === 1) {
            issues.value = res.data.rows;
            total.value = res.data.total;
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

watch(
    filters,
    () => {
        page.value = 1;
        fetchIssues();
    },
    { deep: true }
);

const handlePageChange = (val: number) => {
    page.value = val;
    fetchIssues();
};

const handleSizeChange = (val: number) => {
    limit.value = val;
    fetchIssues();
};

const resetFilters = () => {
    filters.value = { status: "", priority: "", title: "" };
    fetchIssues();
};

onMounted(() => {
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
</script>

<template>
    <div>
        <div class="page-header">
            <div>
                <h1 class="page-title">问题中心</h1>
                <p class="page-subtitle">追踪和处理系统中的所有缺陷与需求</p>
            </div>
            <el-button type="primary" :icon="Plus" size="large">提交新问题</el-button>
        </div>

        <el-card class="filter-card" shadow="never">
            <div class="filter-header">
                <span class="filter-title"
                    ><el-icon><Filter /></el-icon> 筛选条件</span
                >
            </div>
            <el-form :inline="true" :model="filters" class="filter-form">
                <el-form-item label="标题搜索">
                    <el-input v-model="filters.title" placeholder="输入标题关键字..." :prefix-icon="Search" clearable />
                </el-form-item>
                <el-form-item label="状态">
                    <el-select v-model="filters.status" placeholder="全部状态" clearable style="width: 150px">
                        <el-option label="未解决" value="1" />
                        <el-option label="待审核" value="2" />
                        <el-option label="已关闭" value="8" />
                    </el-select>
                </el-form-item>
                <el-form-item>
                    <el-button type="primary" @click="fetchIssues" :icon="Search">查询</el-button>
                    <el-button @click="resetFilters" :icon="Refresh">重置</el-button>
                </el-form-item>
            </el-form>
        </el-card>

        <el-card shadow="never" :body-style="{ padding: '0' }" class="table-card">
            <el-table :data="issues" v-loading="loading" style="width: 100%" :header-cell-style="{ background: '#f8fafc', color: '#475569', fontWeight: '600' }">
                <el-table-column prop="id" label="ID" width="80" align="center" />
                <el-table-column prop="title" label="问题标题" min-width="200">
                    <template #default="{ row }">
                        <div class="issue-title-cell">
                            <span class="issue-title">{{ row.title }}</span>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column prop="module.name" label="所属模块" width="150" />
                <el-table-column prop="priority" label="优先级" width="100" align="center">
                    <template #default="{ row }">
                        <el-tag :type="getPriorityTag(row.priority)" effect="light" size="small" round>{{ row.priority > 1 ? "紧急" : "普通" }}</el-tag>
                    </template>
                </el-table-column>
                <el-table-column prop="status" label="状态" width="100" align="center">
                    <template #default="{ row }">
                        <el-tag :type="getStatusTag(row.status).type" size="small" effect="dark">{{ getStatusTag(row.status).label }}</el-tag>
                    </template>
                </el-table-column>
                <el-table-column prop="user.nickname" label="报告人" width="120" align="center" />
                <el-table-column prop="created_at" label="创建时间" width="180" align="center" />
                <el-table-column label="操作" width="150" align="center" fixed="right">
                    <template #default>
                        <el-button link type="primary" size="small">查看详情</el-button>
                        <el-button link type="primary" size="small">编辑</el-button>
                    </template>
                </el-table-column>
            </el-table>
            <div class="pagination-container">
                <el-pagination
                    v-model:current-page="page"
                    v-model:page-size="limit"
                    :page-sizes="[10, 20, 50, 100]"
                    layout="total, sizes, prev, pager, next, jumper"
                    :total="total"
                    @size-change="handleSizeChange"
                    @current-change="handlePageChange" />
            </div>
        </el-card>
    </div>
</template>

<style scoped>
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
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
    margin-bottom: 24px;
    border: none;
}

.filter-header {
    margin-bottom: 16px;
    display: flex;
    align-items: center;
}

.filter-title {
    font-size: 14px;
    font-weight: 600;
    color: #475569;
    display: flex;
    align-items: center;
    gap: 6px;
}

.filter-form :deep(.el-form-item) {
    margin-bottom: 0;
    margin-right: 24px;
}

.table-card {
    border: none;
    overflow: hidden;
    border-radius: 8px;
}

.issue-title {
    font-weight: 500;
    color: #2563eb;
    cursor: pointer;
}

.issue-title:hover {
    text-decoration: underline;
}

.pagination-container {
    padding: 16px 24px;
    display: flex;
    justify-content: flex-end;
    background-color: white;
    border-top: 1px solid #f1f5f9;
}
</style>
