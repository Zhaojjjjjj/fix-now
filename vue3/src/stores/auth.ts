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
                    const res: any = await api.get("/user/edit");
                    if (res.code === 1) {
                        this.user = res.data;
                    }
                } catch (e) {
                    this.logout();
                }
            }
        },
    },
});
