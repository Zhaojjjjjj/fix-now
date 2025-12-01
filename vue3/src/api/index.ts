import axios from "axios";

const api = axios.create({
    baseURL: "/api",
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
