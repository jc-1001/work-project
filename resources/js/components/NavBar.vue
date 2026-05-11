<script setup>
import { computed, ref } from "vue"
import api from "../bootstrap"
import { useAuth } from "../composables/useAuth"

const { user, clearUser } = useAuth()
const isLoggedIn  = computed(() => !!user.value)
const currentPath = window.location.pathname
const drawer      = ref(false)
const snackbar    = ref({ show: false, text: "", color: "success" })

const notify = (text, color = "success") => {
  snackbar.value = { show: true, text, color }
}

const menuItems = [
  { name: "首頁",    icon: "mdi-home",     path: "/" },
  { name: "商城",    icon: "mdi-shopping",  path: "/shop" },
  { name: "會員中心", icon: "mdi-account",   path: "/profile", requiresAuth: true },
  { name: "購物車",  icon: "mdi-cart",      path: "/cart",    requiresAuth: true },
]

const displayMenuItems = computed(() =>
  isLoggedIn.value ? menuItems : menuItems.filter(item => !item.requiresAuth)
)

const navigate = (path) => {
  window.location.href = path
}

const handleLogout = async () => {
  drawer.value = false
  try {
    await api.post("/logout")
    notify("登出成功")
  } catch {
    notify("登出失敗，請稍後再試", "error")
  } finally {
    clearUser()
    window.location.href = "/"
  }
}
</script>

<template>
  <!-- 手機側拉選單 -->
  <v-navigation-drawer v-model="drawer" temporary>
    <v-list-item
      title="購物網站"
      prepend-icon="mdi-storefront-outline"
      base-color="white"
      class="py-4 bg-primary"
    />
    <v-divider />
    <v-list nav>
      <v-list-item
        v-for="item in displayMenuItems"
        :key="item.path"
        :prepend-icon="item.icon"
        :title="item.name"
        :active="currentPath === item.path"
        rounded="lg"
        @click="navigate(item.path)"
      />
      <v-list-item
        v-if="isLoggedIn"
        prepend-icon="mdi-logout"
        title="登出"
        rounded="lg"
        @click="handleLogout"
      />
      <v-list-item
        v-else
        prepend-icon="mdi-login"
        title="登入"
        rounded="lg"
        @click="navigate('/login')"
      />
    </v-list>
  </v-navigation-drawer>

  <!-- 頂部導覽列 -->
  <v-app-bar color="primary" elevation="2">
    <!-- 漢堡按鈕：僅手機顯示 -->
    <v-app-bar-nav-icon
      color="white"
      class="d-md-none"
      @click="drawer = !drawer"
    />

    <v-app-bar-title class="font-weight-bold">購物網站</v-app-bar-title>

    <!-- 導覽連結：僅桌機顯示 -->
    <template #append>
      <div class="d-none d-md-flex align-center">
        <v-btn
          v-for="item in displayMenuItems"
          :key="item.path"
          :prepend-icon="item.icon"
          variant="text"
          color="white"
          :active="currentPath === item.path"
          @click="navigate(item.path)"
        >
          {{ item.name }}
        </v-btn>

        <v-btn
          v-if="isLoggedIn"
          prepend-icon="mdi-logout"
          variant="text"
          color="white"
          @click="handleLogout"
        >
          登出
        </v-btn>
        <v-btn
          v-else
          prepend-icon="mdi-login"
          variant="text"
          color="white"
          @click="navigate('/login')"
        >
          登入
        </v-btn>
      </div>
    </template>
  </v-app-bar>

  <v-snackbar v-model="snackbar.show" :color="snackbar.color" location="top" timeout="3000">
    {{ snackbar.text }}
  </v-snackbar>
</template>

<style scoped>
/* ── Desktop ── */
.nav-desktop {
    display: flex;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    z-index: 1000;
}
.nav-mobile {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    z-index: 1000;
}

@media (max-width: 767px) {
    .nav-desktop {
        display: none;
    }
    .nav-mobile {
        display: block;
    }
}

/* ── Mobile bar ── */
.nav-mobile-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0 16px;
    height: 56px;
    background: #fff;
    border-bottom: 1px solid #e8e8e8;
    position: relative;
    z-index: 100;
}

.nav-brand {
    font-size: 1.1rem;
    font-weight: 700;
    color: #303133;
    cursor: pointer;
    user-select: none;
}

/* ── 漢堡按鈕 ── */
.hamburger {
    display: flex;
    flex-direction: column;
    justify-content: center;
    gap: 5px;
    background: none;
    border: none;
    cursor: pointer;
    padding: 6px;
}

.bar {
    display: block;
    width: 24px;
    height: 2px;
    background: #303133;
    border-radius: 2px;
    transition: transform 0.25s ease, opacity 0.25s ease;
    transform-origin: center;
}

/* 開啟時變成 X */
.hamburger.is-open .bar:nth-child(1) {
    transform: translateY(7px) rotate(45deg);
}
.hamburger.is-open .bar:nth-child(2) {
    opacity: 0;
    transform: scaleX(0);
}
.hamburger.is-open .bar:nth-child(3) {
    transform: translateY(-7px) rotate(-45deg);
}

/* ── 下拉選單 ── */
.nav-mobile-menu {
    background: #fff;
    border-bottom: 1px solid #e8e8e8;
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
}

.nav-mobile-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 15px 20px;
    font-size: 15px;
    color: #303133;
    border-bottom: 1px solid #f5f5f5;
    cursor: pointer;
    transition: background 0.15s, color 0.15s;
}

.nav-mobile-item:last-child {
    border-bottom: none;
}

.nav-mobile-item:hover,
.nav-mobile-item.is-active {
    background: #f0f7ff;
    color: #409eff;
}

/* ── 滑入動畫 ── */
.slide-down-enter-active,
.slide-down-leave-active {
    overflow: hidden;
    max-height: 400px;
    transition: max-height 0.3s ease, opacity 0.25s ease;
}
.slide-down-enter-from,
.slide-down-leave-to {
    max-height: 0;
    opacity: 0;
}
</style>
