<script setup lang="ts">
import { ref, onMounted } from "vue";
import { useRouter } from "vue-router";
import { useAuthStore } from "../stores/auth";
import api from "../api";
import { UserFilled, Upload as UploadIcon } from "@element-plus/icons-vue";
import { ElMessage } from "element-plus";
import type { FormInstance, FormRules, UploadProps } from "element-plus";

const router = useRouter();
const authStore = useAuthStore();

const userFormRef = ref<FormInstance>();
const passwordFormRef = ref<FormInstance>();

const userForm = ref({
    nickname: "",
    username: "",
    avatar: "",
});

const passwordForm = ref({
    old_password: "",
    new_password: "",
    confirm_password: "",
});

const userRules = ref<FormRules>({
    nickname: [{ required: true, message: "请输入昵称", trigger: "blur" }],
    username: [{ required: true, message: "请输入用户名", trigger: "blur" }],
});

const passwordRules = ref<FormRules>({
    old_password: [{ required: true, message: "请输入当前密码", trigger: "blur" }],
    new_password: [
        { required: true, message: "请输入新密码", trigger: "blur" },
        { min: 6, message: "密码长度不能少于6位", trigger: "blur" },
    ],
    confirm_password: [
        { required: true, message: "请确认新密码", trigger: "blur" },
        {
            validator: (rule: any, value: any, callback: any) => {
                if (value !== passwordForm.value.new_password) {
                    callback(new Error("两次输入的密码不一致"));
                } else {
                    callback();
                }
            },
            trigger: "blur",
        },
    ],
});

const uploadLoading = ref(false);

onMounted(async () => {
    await authStore.fetchUser();
    if (authStore.user) {
        userForm.value = {
            nickname: authStore.user.nickname || "",
            username: authStore.user.username || "",
            avatar: authStore.user.avatar || "",
        };
    }
});

const handleAvatarUpload: UploadProps["onChange"] = async (file) => {
    const formData = new FormData();
    formData.append("file", file.raw!);

    uploadLoading.value = true;
    try {
        const res: any = await api.post("/common/upload", formData, {
            headers: {
                "Content-Type": "multipart/form-data",
            },
        });
        if (res.code === 1) {
            userForm.value.avatar = res.data.url;
            ElMessage.success("头像上传成功");
        } else {
            ElMessage.error(res.msg || "上传失败");
        }
    } catch (e: any) {
        ElMessage.error(e.response?.data?.msg || "上传失败");
    } finally {
        uploadLoading.value = false;
    }
};

const submitUserInfo = async (formEl: FormInstance | undefined) => {
    if (!formEl) return;
    await formEl.validate(async (valid) => {
        if (valid) {
            try {
                const res: any = await api.post("/user/edit", userForm.value);
                if (res.code === 1) {
                    ElMessage.success("个人信息更新成功");
                    await authStore.fetchUser();
                } else {
                    ElMessage.error(res.msg || "更新失败");
                }
            } catch (e: any) {
                ElMessage.error(e.response?.data?.msg || "更新失败");
            }
        }
    });
};

const submitPassword = async (formEl: FormInstance | undefined) => {
    if (!formEl) return;
    await formEl.validate(async (valid) => {
        if (valid) {
            try {
                const res: any = await api.post("/user/pwd", {
                    old_pwd: passwordForm.value.old_password,
                    new_pwd: passwordForm.value.new_password,
                });
                if (res.code === 1) {
                    ElMessage.success("密码修改成功，请重新登录");
                    passwordForm.value = {
                        old_password: "",
                        new_password: "",
                        confirm_password: "",
                    };
                    setTimeout(() => {
                        authStore.logout();
                        router.push("/login");
                    }, 1500);
                } else {
                    ElMessage.error(res.msg || "修改失败");
                }
            } catch (e: any) {
                ElMessage.error(e.response?.data?.msg || "修改失败");
            }
        }
    });
};
</script>

<template>
    <div class="max-w-[1200px] mx-auto">
        <el-row :gutter="24">
            <!-- 个人信息 -->
            <el-col :span="12">
                <el-card shadow="never">
                    <template #header>
                        <div class="flex justify-between items-center">
                            <span class="text-base font-semibold text-slate-800">个人信息</span>
                        </div>
                    </template>
                    <div class="flex flex-col items-center gap-4 mb-8 pb-8 border-b border-slate-100">
                        <el-avatar :size="100" :src="userForm.avatar" :icon="UserFilled" class="bg-blue-50 text-blue-500" />
                        <el-upload :show-file-list="false" :on-change="handleAvatarUpload" :auto-upload="false" accept="image/*">
                            <el-button :loading="uploadLoading" :icon="UploadIcon" size="small">
                                {{ uploadLoading ? "上传中..." : "更换头像" }}
                            </el-button>
                        </el-upload>
                    </div>
                    <el-form ref="userFormRef" :model="userForm" :rules="userRules" label-width="100px" class="mt-5">
                        <el-form-item label="用户名" prop="username">
                            <el-input v-model="userForm.username" placeholder="请输入用户名" />
                        </el-form-item>
                        <el-form-item label="昵称" prop="nickname">
                            <el-input v-model="userForm.nickname" placeholder="请输入昵称" />
                        </el-form-item>
                        <el-form-item>
                            <el-button type="primary" @click="submitUserInfo(userFormRef)">保存修改</el-button>
                        </el-form-item>
                    </el-form>
                </el-card>
            </el-col>

            <!-- 修改密码 -->
            <el-col :span="12">
                <el-card shadow="never">
                    <template #header>
                        <div class="flex justify-between items-center">
                            <span class="text-base font-semibold text-slate-800">修改密码</span>
                        </div>
                    </template>
                    <el-form ref="passwordFormRef" :model="passwordForm" :rules="passwordRules" label-width="100px" class="mt-5">
                        <el-form-item label="当前密码" prop="old_password">
                            <el-input v-model="passwordForm.old_password" type="password" placeholder="请输入当前密码" show-password />
                        </el-form-item>
                        <el-form-item label="新密码" prop="new_password">
                            <el-input v-model="passwordForm.new_password" type="password" placeholder="请输入新密码" show-password />
                        </el-form-item>
                        <el-form-item label="确认新密码" prop="confirm_password">
                            <el-input v-model="passwordForm.confirm_password" type="password" placeholder="请再次输入新密码" show-password />
                        </el-form-item>
                        <el-form-item>
                            <el-button type="primary" @click="submitPassword(passwordFormRef)">修改密码</el-button>
                        </el-form-item>
                    </el-form>
                </el-card>
            </el-col>
        </el-row>
    </div>
</template>

<style scoped></style>
