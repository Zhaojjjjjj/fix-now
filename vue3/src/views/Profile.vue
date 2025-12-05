<script setup lang="ts">
import { ref, onMounted } from "vue";
import { useRouter } from "vue-router";
import { useAuthStore } from "../stores/auth";
import api from "../api";
import { UserFilled, Upload as UploadIcon, User, Lock, Key } from "@element-plus/icons-vue";
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
            validator: (_rule: any, value: any, callback: any) => {
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

            // 自动保存头像更新
            try {
                await api.post("/user/edit", {
                    avatar: res.data.url,
                    nickname: userForm.value.nickname,
                    username: userForm.value.username,
                });
                await authStore.fetchUser();
                ElMessage.success("头像上传并保存成功");
            } catch (error) {
                console.error(error);
                ElMessage.warning("头像上传成功但保存失败，请点击保存按钮");
            }
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
    <div class="max-w-5xl mx-auto">
        <h1 class="text-3xl font-bold text-slate-800 mb-8 tracking-tight">个人中心</h1>

        <el-row :gutter="24">
            <!-- 个人信息 -->
            <el-col :xs="24" :md="12" class="mb-6">
                <div class="glass-card h-full p-8">
                    <div class="flex flex-col items-center gap-6 mb-10">
                        <div class="relative group">
                            <el-avatar
                                :size="120"
                                :src="userForm.avatar"
                                :icon="UserFilled"
                                class="bg-gradient-to-br from-blue-500 to-indigo-600 text-white shadow-xl group-hover:scale-105 transition-transform duration-300 ring-4 ring-white/50" />
                            <div class="absolute bottom-0 right-0">
                                <el-upload :show-file-list="false" :on-change="handleAvatarUpload" :auto-upload="false" accept="image/*">
                                    <el-button circle type="primary" :loading="uploadLoading" :icon="UploadIcon" class="shadow-lg border-2 border-white" />
                                </el-upload>
                            </div>
                        </div>
                        <div class="text-center">
                            <h2 class="text-xl font-bold text-slate-800 m-0">{{ userForm.nickname || "未设置昵称" }}</h2>
                            <p class="text-slate-500 m-0 mt-1">@{{ userForm.username }}</p>
                        </div>
                    </div>

                    <el-form ref="userFormRef" :model="userForm" :rules="userRules" label-position="top" size="large">
                        <el-form-item label="用户名" prop="username">
                            <el-input v-model="userForm.username" placeholder="用户名不可修改" :prefix-icon="User" disabled />
                        </el-form-item>
                        <el-form-item label="昵称" prop="nickname">
                            <el-input v-model="userForm.nickname" placeholder="请输入昵称" />
                        </el-form-item>
                        <el-form-item class="mt-8">
                            <el-button type="primary" class="w-full" @click="submitUserInfo(userFormRef)">保存个人信息</el-button>
                        </el-form-item>
                    </el-form>
                </div>
            </el-col>

            <!-- 修改密码 -->
            <el-col :xs="24" :md="12" class="mb-6">
                <div class="glass-card h-full p-8">
                    <h3 class="text-xl font-bold text-slate-800 mb-6 flex items-center gap-2">
                        <el-icon class="text-blue-500"><Lock /></el-icon>
                        修改密码
                    </h3>
                    <el-form ref="passwordFormRef" :model="passwordForm" :rules="passwordRules" label-position="top" size="large">
                        <el-form-item label="当前密码" prop="old_password">
                            <el-input v-model="passwordForm.old_password" type="password" placeholder="请输入当前密码" show-password :prefix-icon="Lock" />
                        </el-form-item>
                        <el-form-item label="新密码" prop="new_password">
                            <el-input v-model="passwordForm.new_password" type="password" placeholder="请输入新密码" show-password :prefix-icon="Key" />
                        </el-form-item>
                        <el-form-item label="确认新密码" prop="confirm_password">
                            <el-input v-model="passwordForm.confirm_password" type="password" placeholder="请再次输入新密码" show-password :prefix-icon="Key" />
                        </el-form-item>
                        <el-form-item class="mt-8">
                            <el-button type="primary" plain class="w-full" @click="submitPassword(passwordFormRef)">修改密码</el-button>
                        </el-form-item>
                    </el-form>
                </div>
            </el-col>
        </el-row>
    </div>
</template>

<style scoped></style>
