import { defineStore } from "pinia";
import api from "../api";

export const useAuthStore = defineStore("auth", {
    state: () => ({
        user: null as any,
        token: localStorage.getItem("token") || "",
    }),
    getters: {
        isAuthenticated: (state) => !!state.token,
    },
    actions: {
        async login(payload: any) {
            const res: any = await api.post("/login/login", payload);
            this.token = res.token;
            this.user = res.user;
            localStorage.setItem("token", res.token);
        },
        logout() {
            this.token = "";
            this.user = null;
            localStorage.removeItem("token");
        },
        async fetchUser() {
            if (this.token) {
                try {
                    const res: any = await api.get("/user/edit"); // Using edit endpoint to get current user as per backend
                    this.user = res.data;
                } catch (e) {
                    this.logout();
                }
            }
        },
    },
});
