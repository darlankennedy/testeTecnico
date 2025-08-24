import { createRouter, createWebHistory } from 'vue-router'
import MainLayout from '@/ui/layouts/MainLayout.vue'
import AuthLayout from '@/ui/layouts/AuthLayout.vue'
import HomePage from '@/ui/pages/HomePage.vue'
import LoginPage from '@/modules/auth/pages/LoginPage.vue'
import { useAuthStore } from "@/app/stores/useAuthStore.ts"
import UserListPage from "@/modules/users/UserListPage.vue";
import ProductListPage from "@/modules/products/ProductListPage.vue";
import RegisterPage from "@/modules/auth/pages/RegisterPage.vue";
import UserCreatePage from "@/modules/users/UserCreatePage.vue";
import ProductCreatePage from "@/modules/products/ProductCreatePage.vue";
import UserEditPage from "@/modules/users/UserEditPage.vue";
import UserShowPage from "@/modules/users/UserShowPage.vue";
import ProductShowPage from "@/modules/products/ProductShowPage.vue";
import ProductEditPage from "@/modules/products/ProductEditPage.vue";
import ReportsPage from "@/modules/reports/ReportsPage.vue";

const routes = [
  {
    path: '/',
    component: MainLayout,
    children: [
      { path: '', redirect: { name: 'home' } },
      { path: 'home', name: 'home', component: HomePage, meta: { requiresAuth: true } },
      { path: 'users', name: 'users.list', component: UserListPage, meta: { requiresAuth: true } },
      { path: 'users/new', name: 'users.create', component: UserCreatePage, meta: { requiresAuth: true } },
      { path: 'user/:id/edit', name: 'users.edit', component: UserEditPage, meta: { requiresAuth: true } },
      { path: 'user/:id', name: 'users.view', component: UserShowPage, meta: { requiresAuth: true } },


      { path: 'products', name: 'products.list', component: ProductListPage, meta: { requiresAuth: true } },
      { path: 'products/new', name: 'products.create', component: ProductCreatePage, meta: { requiresAuth: true } },
      { path: 'products/:id',  name: 'products.show', component: ProductShowPage, meta: { requiresAuth: true } },
      { path: '/products/:id/edit', name: 'products.edit',  component: ProductEditPage, meta: { requiresAuth: true } },


      { path: '/reports', name: 'reports', component: ReportsPage , meta: { requiresAuth: true } },


    ],
  },
  {
    path: '/auth',
    component: AuthLayout,
    children: [
      { path: 'login', name: 'login', component: LoginPage },
      { path: 'register', name: 'register', component: RegisterPage },
    ],
  },
]

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes,
})

router.beforeEach((to) => {
  const auth = useAuthStore()
  console.log(to, auth)
  console.log(auth.isAuthenticated)

  if (to.meta.requiresAuth && !auth.isAuthenticated) {
    return { name: 'login', query: { redirect: to.fullPath } }
  }
  if (to.name === 'login' && auth.isAuthenticated) {
    return { name: 'home' }
  }
})

export default router
