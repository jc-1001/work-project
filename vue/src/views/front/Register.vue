<script setup>
import { ref } from "vue";
import { ElInput, ElButton } from "element-plus";

const input = ref("");
const password = ref("");
const confirmPassword = ref("");

</script>

<template>
  <main>
    <div class="signin-block">
      <h1>註冊</h1>
      <el-input
        v-model="input"
        size="large"
        style="width: fit-content"
        placeholder="請輸入姓名"
      />
      <el-input
        v-model="input"
        size="large"
        style="width: fit-content"
        placeholder="請輸入EMAIL"
      />
      <el-input
        v-model="password"
        size="large"
        placeholder="請輸入密碼"
        type="password"
        style="width: fit-content"
      />
      <el-input
        v-model="confirmPassword"
        size="large"
        placeholder="請再次確認密碼"
        type="password"
        style="width: fit-content"
      />
      <div class="signin-button">
        <el-button type="primary" size="large">完成填寫</el-button>
      </div>
    </div>
  </main>
</template>
<style scoped>
main {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100vh;
}
.signin-block {
  width: 60%;
  border: 1px solid #ccc;
  border-radius: 20px;
  padding: 20px;
  text-align: center;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 20px;
}

.signin-button {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 20px;
}
</style>
