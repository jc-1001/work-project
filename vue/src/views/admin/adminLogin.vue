<script setup>
import { ref, nextTick } from "vue";
import { ElInput, ElButton, ElMessage } from "element-plus";
import { useRouter } from "vue-router";
import api from "../../api";

const router = useRouter();

const email = ref("");
const password = ref("");
const isSubmitting = ref(false);

const emailRef = ref(null);
const passwordRef = ref(null);

//  跳轉到下一個輸入框
const focusNext = () => {
  if (!email.value) {
    ElMessage.warning("請先輸入 EMAIL");
    return;
  }
  nextTick(() => {
    passwordRef.value?.focus();
  });
};

// 帳密登入

const login = async () => {
  // 基本前端驗證
  if (!email.value || !password.value) {
    ElMessage.warning("請完整填寫所有欄位");
    return;
  }
  isSubmitting.value = true;
  try {
    const res = await api.post("/login", {
      email: email.value,
      password: password.value,
    });
    // 儲存token到localStorage
    localStorage.setItem("token", res.data.access_token);
    // 儲存會員資料物件!!轉成字串
    // 這樣結帳頁就可以用localstorage的user拿到id
    if (res.data.user) {
      localStorage.setItem("user", JSON.stringify(res.data.user));
    }
    

    // 登入成功跳轉到後台user頁面
    ElMessage.success("登入成功");
    router.push("/admin/user");
  } catch (error) {
    ElMessage.error("登入失敗，請檢查您的帳號和密碼");
  } finally {
    isSubmitting.value = false;
  }
};
</script>

<template>
  <main>
    <div class="signin-block">
      <h1>後臺管理員登入</h1>
      <el-input
        v-model="email"
        size="large"
        style="width: fit-content"
        placeholder="請輸入EMAIL"
        ref="emailRef"
        @keyup.enter="focusNext"
      />
      <el-input
        v-model="password"
        size="large"
        placeholder="請輸入密碼"
        type="password"
        style="width: fit-content"
        ref="passwordRef"
        @keyup.enter="login"
        show-password
      />
      <div class="signin-button">
        
        <el-button
          type="primary"
          size="large"
          @click="login"
          :loading="isSubmitting"
          >登入</el-button
        >
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
