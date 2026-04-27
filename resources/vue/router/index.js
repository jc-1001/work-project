import { createRouter, createWebHistory } from 'vue-router'

const routes = [
  {
    path: '/',
    name: 'Home',
    component: () => import('../views/front/Home.vue')
  },
  {
    path: '/login',
    name: 'Login',
    component: () => import('../views/front/Login.vue')
  },
  {
    path: '/register',
    name: 'Register',
    component: () => import('../views/front/Register.vue')
  },
  // 後臺路由
  {
    path: '/admin',
    component: () => import('../AdminLayout.vue'),
    children: [
      {
        path: '',
        name: 'AdminLogin',
        component: () => import('../views/admin/adminLogin.vue')
      },
    ]
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

export default router
