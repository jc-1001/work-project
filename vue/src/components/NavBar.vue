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
const isLoggedin = ref(false); // 這裡可以根據實際情況來判斷使用者是否已經登入，檢查 localStorage 中是否有 token

// 檢查登入狀態
const checkLoginStatus = () => {
  isLoggedin.value = !!localStorage.getItem("token"); // 如果 localStorage 中有 token，則表示已登入
};
onMounted(() => {
  checkLoginStatus(); // 在組件掛載時檢查登入狀態
  // 監聽我們自訂的事件
  window.addEventListener("login-status-changed", checkLoginStatus);
});
onUnmounted(() => {
  // 組件卸載時移除事件監聽器
  window.removeEventListener("login-status-changed", checkLoginStatus);
});


// requiresAuth 標記：我在 allMenuItems 裡幫項目加了這個屬性，這樣 filter 時只要過濾掉它，就能輕鬆控制誰該出現。
const menuItems = ref([
  { name: "首頁", icon: "House", path: "/" },
  { name: "商城", icon: "ShoppingBag", path: "/shop" },
  { name: "會員中心", icon: "User", path: "/profile", requiresAuth: true },
  { name: "購物車", icon: "ShoppingCart", path: "/cart", requiresAuth: true },
  { name: "登出", icon: "SwitchButton", path: "logout", requiresAuth: true },
]);

// 登入之後要顯示在列表的項目
// 登入前只顯示(首頁、商城)
const displayMenuItem = computed(() => {
  if (isLoggedin.value) {
    return menuItems.value; // 已登入，顯示全部項目
  }
  // 未登入，過濾掉需要認證的項目
  return menuItems.value.filter((item) => !item.requiresAuth);
});

const activePath = computed(() => {
  return route.path;
});

// 登出點擊
const handleSelect = (index) => {
  if (index === "logout") {
    handleLogout();
  }
};

// 登出邏輯
const handleLogout = async () => {
  try {
    // 呼叫後端登出API
    await api.post("/logout");
    ElMessage.success("登出成功");
  } catch (error) {
    console.error("登出失敗:", error);
    ElMessage.error("登出失敗，請稍後再試");
  } finally {
    // 清除 localStorage 中的 token 和會員資料
    localStorage.removeItem("token");
    localStorage.removeItem("user");

    // 更新當前登入狀態(這會讓 computed 重新計算，隱藏選單)
    isLoggedin.value = false;

    // 跳轉到首頁
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
