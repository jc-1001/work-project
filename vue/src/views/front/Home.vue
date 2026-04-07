<script setup>
import { ref } from "vue";
import { ElButton, ElCarousel, ElCarouselItem, ElImage } from "element-plus";
import { useRouter } from "vue-router"; 


// 背景輪播圖片
const banners = [
  { id: 1, image: "https://picsum.photos/1200/500?random=1" },
  { id: 2, image: "https://picsum.photos/1200/500?random=2" },
  { id: 3, image: "https://picsum.photos/1200/500?random=3" },
  { id: 4, image: "https://picsum.photos/1200/500?random=4" },
  { id: 5, image: "https://picsum.photos/1200/500?random=5" },
];
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
        <el-button size="large" round @click="$router.push('/login')"
          >登入/註冊</el-button
        >
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
