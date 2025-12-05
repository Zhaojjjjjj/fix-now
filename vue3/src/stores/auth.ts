import { defineStore } from "pinia";
import api from "../api";

export const useAuthStore = defineStore("auth", {
    state: () => ({
        user: null as any,
        isLoggedIn: localStorage.getItem("isLoggedIn") === "true",
    }),
    getters: {
        isAuthenticated: (state) => state.isLoggedIn,
    },
    actions: {
        async login(payload: any) {
            const res: any = await api.post("/login/login", payload);
            if (res.code === 1) {
                // 登录成功
                this.isLoggedIn = true;
                localStorage.setItem("isLoggedIn", "true");
                await this.fetchUser();
            } else {
                throw new Error(res.msg || "登录失败");
            }
        },
        async logout() {
            try {
                await api.post("/login/logout");
            } catch (e) {
                // 忽略退出错误
            }
            this.isLoggedIn = false;
            this.user = null;
            localStorage.removeItem("isLoggedIn");
        },
        async fetchUser() {
            if (this.isLoggedIn) {
                try {
                    // 添加时间戳避免缓存，确保获取最新数据
                    const res: any = await api.get("/user/edit", {
                        params: { _t: Date.now() },
                        headers: { "Cache-Control": "no-cache" },
                    });
                    if (res.code === 1) {
                        // 确保响应式更新
                        this.user = { ...res.data };
                        console.log("用户信息已更新:", this.user);
                    }
                } catch (e) {
                    console.error("获取用户信息失败:", e);
                    this.logout();
                }
            }
        },
    },
});
