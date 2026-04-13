<script setup>
import { ref, computed, onMounted, onUnmounted } from "vue";
import { useRoute, useRouter } from "vue-router";
import { ElMenu, ElMenuItem, ElMessage } from "element-plus";
import api from "../api";
import {
  Avatar,
  Shop,
  Finished
} from "@element-plus/icons-vue";
const route = useRoute();
const router = useRouter();
const isLoggedin = ref(false); 

// 檢查登入狀態(寫死???)
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


const menuItems = ref([
  { name: "會員列表", icon: "Avatar", path: "/admin/user" },
  { name: "商品管理", icon: "Shop", path: "/admin/products" },
  { name: "訂單管理", icon: "Finished", path: "/admin/orders"},
]);


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

    // 跳轉到後臺登入頁
    router.push("/admin");
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
