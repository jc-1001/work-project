<script setup>
import { ref , onMounted, onUnmounted} from "vue";
import { ElButton, ElCarousel, ElCarouselItem, ElImage, ElMessage } from "element-plus";
import { useRouter } from "vue-router";
import api from "../../api";
const isLoggedin = ref(false);

// 背景輪播圖片
const banners = [
  { id: 1, image: "https://picsum.photos/1200/500?random=1" },
  { id: 2, image: "https://picsum.photos/1200/500?random=2" },
  { id: 3, image: "https://picsum.photos/1200/500?random=3" },
  { id: 4, image: "https://picsum.photos/1200/500?random=4" },
  { id: 5, image: "https://picsum.photos/1200/500?random=5" },
];

// 檢查監聽事件與登入登出狀態
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

    // 通知NavBar也要變更登入狀態
    window.dispatchEvent(new Event("login-status-changed"));
  }
};
</script>

<template>
  <main>
    <el-carousel
      height="100vh"
      :interval="3000"
      arrow="never"
      indicator-position="none"
      :pause-on-hover="false"
      style="width: 100%"
    >
      <el-carousel-item v-for="banner in banners" :key="banner.id">
        <el-image
          :src="banner.image"
          fit="cover"
          style="width: 100%; height: 100%"
        />
      </el-carousel-item>
    </el-carousel>

    <div class="content">
      <div class="text-button">
        <h1>歡迎來到我的商城</h1>

        <div v-if="!isLoggedin">
          <el-button size="large" round @click="$router.push('/login')"
            >登入/註冊</el-button
          >
        </div>
        <div v-else>
          <el-button size="large" round @click="$router.push('/shop')"
            >前往商城</el-button
          >
          <el-button size="large" round @click="handleLogout">登出</el-button>
        </div>
      </div>
    </div>
  </main>
</template>

<style scoped>
main {
  width: 100%;
  position: inherit;
}

.content {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  padding: 40px;
  z-index: 100;
  background-color: rgba(255, 255, 255, 0.5);
  /* border-radius: 50%; */
}
.text-button {
  display: flex;
  text-align: center;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 20px;
}
</style>
