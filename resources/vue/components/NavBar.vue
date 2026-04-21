<script setup>
import { ref, computed, onMounted, onUnmounted } from "vue";
import { useRoute, useRouter } from "vue-router";
import { ElMenu, ElMenuItem, ElMessage } from "element-plus";
import api from "../api";
import {
  House,
  ShoppingBag,
  User,
  ShoppingCart,
  SwitchButton,
} from "@element-plus/icons-vue";
const route = useRoute();
const router = useRouter();
const isLoggedin = ref(false);

const checkLoginStatus = async () => {
  try {
    await api.get("/me");
    isLoggedin.value = true;
  } catch {
    isLoggedin.value = false;
  }
};
onMounted(() => {
  checkLoginStatus();
  window.addEventListener("login-status-changed", checkLoginStatus);
});
onUnmounted(() => {
  window.removeEventListener("login-status-changed", checkLoginStatus);
});

const menuItems = ref([
  { name: "首頁", icon: "House", path: "/" },
  { name: "商城", icon: "ShoppingBag", path: "/shop" },
  { name: "會員中心", icon: "User", path: "/profile", requiresAuth: true },
  { name: "購物車", icon: "ShoppingCart", path: "/cart", requiresAuth: true },
  { name: "登出", icon: "SwitchButton", path: "logout", requiresAuth: true },
]);

const displayMenuItem = computed(() => {
  if (isLoggedin.value) {
    return menuItems.value;
  }
  return menuItems.value.filter((item) => !item.requiresAuth);
});

const activePath = computed(() => {
  return route.path;
});

const handleSelect = (index) => {
  if (index === "logout") {
    handleLogout();
  }
};

const handleLogout = async () => {
  try {
    await api.post("/logout");
    ElMessage.success("登出成功");
  } catch (error) {
    console.error("登出失敗:", error);
    ElMessage.error("登出失敗，請稍後再試");
  } finally {
    isLoggedin.value = false;
    router.push("/");
  }
};
</script>
<template>
  <el-menu
    :default-active="activePath"
    class="el-menu-demo"
    mode="horizontal"
    @select="handleSelect"
    router
  >
    <el-menu-item
      v-for="item in displayMenuItem"
      :key="item.path"
      :index="item.path"
    >
      <el-icon>
        <component :is="item.icon" />
      </el-icon>
      <span>{{ item.name }}</span>
    </el-menu-item>
  </el-menu>
</template>
