import { createRouter, createWebHistory } from "vue-router";
import Login from "../views/Login.vue";
import Layout from "../views/Layout.vue";
import ProjectList from "../views/ProjectList.vue";
import IssueList from "../views/IssueList.vue";
import { useAuthStore } from "../stores/auth";

const router = createRouter({
    history: createWebHistory(),
    routes: [
        {
            path: "/login",
            name: "Login",
            component: Login,
        },
        {
            path: "/",
            component: Layout,
            meta: { requiresAuth: true },
            children: [
                {
                    path: "",
                    redirect: "/projects",
                },
                {
                    path: "projects",
                    name: "ProjectList",
                    component: ProjectList,
                },
                {
                    path: "issues",
                    name: "IssueList",
                    component: IssueList,
                },
            ],
        },
    ],
});

router.beforeEach((to, _from, next) => {
    const authStore = useAuthStore();
    if (to.meta.requiresAuth && !authStore.isAuthenticated) {
        next("/login");
    } else {
        next();
    }
});

export default router;
