# 升级指南 - 新功能说明

## 📋 本次更新内容

### 1. ✅ 问题管理增强

-   **指派人功能**：创建问题时可指派处理人，问题详情中可修改指派人
-   **评论历史**：问题详情显示完整评论记录（聊天记录样式）
-   **富文本支持**：问题描述和评论支持 HTML 格式（未来可升级为富文本编辑器）
-   **筛选增强**：支持按开发环境和 Bug 类型筛选问题

### 2. ✅ 项目管理增强

-   **项目 Logo**：创建项目时可上传 Logo 图片

### 3. ✅ 数据库字段更新

-   `issue` 表新增 `bug_type` 和 `environment` 字段

---

## 🚀 升级步骤

### 第一步：更新数据库

#### 方式 1：使用增量更新 SQL（推荐，保留现有数据）

```bash
cd /Users/ss/web/LOCAL/fix-now.git/think
mysql -u root -p < update_issue_table.sql
```

#### 方式 2：重建表（会清空所有数据）

```bash
cd /Users/ss/web/LOCAL/fix-now.git/think
mysql -u root -p < data.sql
```

#### 方式 3：手动执行 SQL

```sql
USE mybug;

-- 添加 bug_type 字段
ALTER TABLE `issue`
ADD COLUMN `bug_type` VARCHAR(20) NULL DEFAULT '' COMMENT 'Bug类型: bug/style/experience/design'
AFTER `priority`;

-- 添加 environment 字段
ALTER TABLE `issue`
ADD COLUMN `environment` VARCHAR(20) NULL DEFAULT '' COMMENT '开发环境: test/dev/prod'
AFTER `bug_type`;

-- 验证字段是否添加成功
SHOW COLUMNS FROM `issue`;
```

---

### 第二步：安装前端依赖

```bash
cd /Users/ss/web/LOCAL/fix-now.git/vue3

# 安装富文本编辑器（未来使用）
npm install

# 或者使用 yarn
yarn install
```

**依赖说明**：

-   `@wangeditor/editor`：富文本编辑器核心库
-   `@wangeditor/editor-for-vue`：Vue3 适配器

> ⚠️ **注意**：目前评论功能使用普通文本框+HTML 支持，富文本编辑器依赖已添加但未启用。如需启用，请参考后续说明。

---

### 第三步：重启服务

#### 前端服务

```bash
cd /Users/ss/web/LOCAL/fix-now.git/vue3
npm run dev
```

#### 后端服务

无需重启，PHP 代码自动生效。

---

## 📚 新功能使用说明

### 1. 创建问题 - 指派人功能

1. 点击【提交新问题】按钮
2. 填写问题信息
3. 在【指派给】下拉框中选择处理人（可选）
4. 提交问题

### 2. 问题详情 - 修改指派人

1. 点击问题列表中的任一问题
2. 在右侧问题详情中找到【指派给】下拉框
3. 选择新的处理人或清空（取消指派）
4. 自动保存

### 3. 问题评论功能

#### 查看评论历史

-   选择任一问题后，评论记录会自动加载
-   评论以聊天记录样式展示（评论人 + 评论时间 + 评论内容）

#### 添加评论

1. 在问题详情底部找到【添加评论】文本框
2. 输入评论内容（支持 HTML 格式，如 `<strong>加粗</strong>`）
3. 点击【提交评论】按钮
4. 评论记录会自动刷新

### 4. 筛选问题

在项目详情页顶部筛选区域：

-   **状态**：11 个状态选项
-   **开发环境**：测试/开发/正式
-   **Bug 类型**：Bug/样式问题/体验问题/产品设计

### 5. 创建项目 - 上传 Logo

1. 点击【创建项目】按钮
2. 在【项目 Logo】区域点击【选择 Logo】
3. 选择图片文件
4. 上传成功后可预览
5. 如需更换，点击【删除】后重新上传

---

## 🔧 富文本编辑器启用指南（可选）

如需将评论框升级为富文本编辑器，可按以下步骤操作：

### 1. 创建富文本编辑器组件

创建文件：`/Users/ss/web/LOCAL/fix-now.git/vue3/src/components/RichEditor.vue`

```vue
<template>
    <div class="rich-editor">
        <Toolbar :editor="editorRef" :defaultConfig="toolbarConfig" :mode="mode" class="toolbar" />
        <Editor :defaultConfig="editorConfig" :mode="mode" v-model="valueHtml" @onChange="handleChange" @onCreated="handleCreated" class="editor" />
    </div>
</template>

<script setup lang="ts">
import { ref, onBeforeUnmount } from "vue";
import { Editor, Toolbar } from "@wangeditor/editor-for-vue";
import "@wangeditor/editor/dist/css/style.css";

const props = defineProps<{
    modelValue: string;
}>();

const emit = defineEmits<{
    "update:modelValue": [value: string];
}>();

const editorRef = ref();
const valueHtml = ref(props.modelValue);
const mode = "simple"; // 简洁模式

const toolbarConfig = {
    toolbarKeys: ["bold", "italic", "through", "underline", "|", "uploadImage", "insertLink", "|", "undo", "redo"],
};

const editorConfig = {
    placeholder: "请输入内容...",
    MENU_CONF: {
        uploadImage: {
            server: "/think/upload/image",
            fieldName: "file",
        },
    },
};

const handleCreated = (editor: any) => {
    editorRef.value = editor;
};

const handleChange = () => {
    emit("update:modelValue", valueHtml.value);
};

onBeforeUnmount(() => {
    const editor = editorRef.value;
    if (editor) {
        editor.destroy();
    }
});
</script>

<style scoped>
.rich-editor {
    border: 1px solid #d1d5db;
    border-radius: 4px;
}

.toolbar {
    border-bottom: 1px solid #d1d5db;
}

.editor {
    height: 200px;
    overflow-y: auto;
}
</style>
```

### 2. 在 ProjectDetail.vue 中使用

```vue
<!-- 替换普通文本框 -->
<RichEditor v-model="commentContent" />
```

---

## 🎯 数据字段说明

### issue 表新字段

| 字段名        | 类型        | 说明     | 可选值                         |
| ------------- | ----------- | -------- | ------------------------------ |
| `bug_type`    | VARCHAR(20) | Bug 类型 | bug, style, experience, design |
| `environment` | VARCHAR(20) | 开发环境 | test, dev, prod                |

### 字段对应关系

#### Bug 类型

-   `bug` - Bug
-   `style` - 样式问题
-   `experience` - 体验问题
-   `design` - 产品设计

#### 开发环境

-   `test` - 测试环境
-   `dev` - 开发环境
-   `prod` - 正式环境

---

## ⚠️ 注意事项

1. **数据库更新**：必须先执行数据库更新，否则创建问题会报错
2. **依赖安装**：执行 `npm install` 后才能使用所有新功能
3. **图片上传**：确保后端 `/upload/image` 接口可用
4. **评论内容**：当前支持 HTML 格式，建议用户输入纯文本或简单 HTML

---

## 🐛 故障排除

### 问题 1：创建问题时报错 "Unknown column 'bug_type'"

**原因**：数据库未更新

**解决**：执行第一步的数据库更新 SQL

### 问题 2：评论历史不显示

**原因**：可能是后端接口问题

**检查**：

1. 确认 `/issue/logList` 接口正常
2. 查看浏览器控制台是否有错误

### 问题 3：指派人下拉框为空

**原因**：用户列表未加载

**检查**：

1. 确认 `/issue/edit` 接口返回 `userList`
2. 查看网络请求是否正常

---

## 📞 技术支持

如遇到问题，请检查：

1. 浏览器控制台错误信息
2. 网络请求是否正常（F12 -> Network）
3. 后端日志错误信息
