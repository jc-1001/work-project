<script setup>
import { ref } from "vue";
import { ElInput, ElButton, ElMessage } from "element-plus";
import { useRouter } from "vue-router";
import api from "../../api";

const email = ref("");
const password = ref("");
const router = useRouter();

// 帳密登入
const login =async () =>{
  try {
    const res = await api.post("/api/login", {
      email: email.value,
      password: password.value,
    });
    // 儲存token到localStorage
    localStorage.setItem("token", res.data.access_token); 
    // 跳轉到首頁
    ElMessage.success("登入成功");
    router.push("/");


  } catch (error) {
    ElMessage.error("登入失敗，請檢查您的帳號和密碼");
  }
}
</script>

<template>
  <main>
    <div class="signin-block">
      <h1>登入</h1>
      <el-input
        v-model="email"
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
      <div class="signin-button">
        <el-button type="primary" size="large">登入</el-button>

        <el-button type="primary" plain size="large">註冊</el-button>
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
