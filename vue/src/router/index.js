import { createRouter, createWebHistory } from 'vue-router'

const routes = [
  // 前台路由
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
  {
    path: '/shop',
    name: 'Shop',
    component: () => import('../views/front/Shop.vue')
  }, 
  // 后台路由
  {
    path: '/admin',
    name: 'Admin',
    component: () => import('../views/admin/admin.vue')
  },
  {
    path: '/admin/products',
    name: 'AdminProducts',
    component: () => import('../views/admin/products.vue')    
  },
  {
    path: '/admin/products/:id',
    name: 'AdminProductDetail',
    component: () => import('../views/admin/productDetail.vue')
  },
  {
    path: '/admin/orders',
    name: 'AdminOrders',
    component: () => import('../views/admin/orders.vue')
  },
  {
    path: '/admin/orders/:id',
    name: 'AdminOrderDetail',
    component: () => import('../views/admin/orderDetail.vue')
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

export default router