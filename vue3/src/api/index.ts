import axios from "axios";

const api = axios.create({
    // 开发环境使用 /api 走代理，生产环境直接使用 / 访问后端
    baseURL: import.meta.env.DEV ? "/api" : "/",
    timeout: 5000,
    withCredentials: true, // 允许携带cookie
});

api.interceptors.response.use(
    (response) => {
        return response.data;
    },
    (error) => {
        // 如果返回登录过期，跳转到登录页
        if (error.response?.data?.code === 0 && error.response?.data?.msg === "登录已过期") {
            localStorage.removeItem("isLoggedIn");
            window.location.href = "/login";
        }
        return Promise.reject(error);
    }
);

export default api;
