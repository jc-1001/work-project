<script setup>
import { ref } from "vue";

const isLoading = ref(true);

// Api資料加載完成後，將isLoading設置為false
setTimeout(() => {
  isLoading.value = false;
}, 2000); // 模擬2秒的加載時間
</script>
<template>
  <div v-if="isLoading" class="loading-container">
    <p>資料加載中...</p>
  </div>
</template>
<style scoped>
.loading-container {
  background-color: rgba(225, 225, 225, 0.8);
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100vh;
  z-index: 9999;
}
</style>
