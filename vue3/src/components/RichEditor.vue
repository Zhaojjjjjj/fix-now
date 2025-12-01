<template>
    <div class="rich-editor-wrapper">
        <Toolbar :editor="editorRef" :defaultConfig="toolbarConfig" :mode="mode" class="editor-toolbar" />
        <Editor :defaultConfig="editorConfig" :mode="mode" v-model="valueHtml" @onCreated="handleCreated" @onChange="handleChange" class="editor-content" :style="{ height: height }" />
    </div>
</template>

<script setup lang="ts">
import { ref, onBeforeUnmount, watch } from "vue";
import { Editor, Toolbar } from "@wangeditor/editor-for-vue";
import "@wangeditor/editor/dist/css/style.css";

const props = defineProps<{
    modelValue: string;
    height?: string;
    placeholder?: string;
}>();

const emit = defineEmits<{
    "update:modelValue": [value: string];
}>();

const editorRef = ref();
const valueHtml = ref(props.modelValue || "");
const mode = "simple"; // 简洁模式

// 监听外部值变化
watch(
    () => props.modelValue,
    (newVal) => {
        if (newVal !== valueHtml.value) {
            valueHtml.value = newVal || "";
        }
    }
);

const toolbarConfig = {
    toolbarKeys: ["bold", "italic", "through", "underline", "|", "color", "bgColor", "|", "bulletedList", "numberedList", "|", "uploadImage", "|", "undo", "redo"],
};

const editorConfig = {
    placeholder: props.placeholder || "请输入内容...",
    MENU_CONF: {
        uploadImage: {
            server: "/think/upload/image",
            fieldName: "file",
            maxFileSize: 5 * 1024 * 1024, // 5MB
            allowedFileTypes: ["image/*"],
            customInsert(res: any, insertFn: any) {
                if (res.code === 1 && res.data && res.data.url) {
                    insertFn(res.data.url, "", res.data.url);
                }
            },
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
.rich-editor-wrapper {
    border: 1px solid #d1d5db;
    border-radius: 4px;
    overflow: hidden;
}

.editor-toolbar {
    border-bottom: 1px solid #e5e7eb;
    background-color: #f9fafb;
}

.editor-content {
    overflow-y: auto;
}

.editor-content :deep(.w-e-text-container) {
    background-color: #fff;
}

.editor-content :deep(.w-e-text-placeholder) {
    color: #9ca3af;
    font-style: normal;
}
</style>
