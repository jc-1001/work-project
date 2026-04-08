<script setup>
import { ref, nextTick } from "vue";
import { ElInput, ElButton, ElMessage } from "element-plus";
import { useRouter } from "vue-router";
import api from "../../api";

const router = useRouter();

const name = ref("");
const email = ref("");
const password = ref("");
const confirmPassword = ref("");

const nameRef = ref(null);
const emailRef = ref(null);
const passwordRef = ref(null);
const confirmPasswordRef = ref(null);

// 跳轉到下一個輸入框
const focusNext = (refName) => {
  if (refName === "emailRef" && !name.value) {
    ElMessage.warning("請先填寫使用者名稱");
    return;
  }
  if (refName === "passwordRef" && !email.value) {
    ElMessage.warning("請先填寫EMAIL");
    return;
  }
  if (refName === "confirmPasswordRef" && !password.value) {
    ElMessage.warning("請先填寫密碼");
    return;
  }
  if (
    refName === "confirmPasswordRef" &&
    password.value !== confirmPassword.value
  ) {
    ElMessage.error("兩次輸入的密碼不一致");
    return;
  }
  if (refName === "confirmPasswordRef") {
    register();
    return;
  }
  const nextRef = {
    emailRef,
    passwordRef,
    confirmPasswordRef,
  }[refName];
  nextTick(() => {
    nextRef.value.focus();
  });
};
nextTick(() => {
  nameRef.value.focus();
});

// !!!!按鈕狀態避免重複送出
const isSubmitting = ref(false);

const register = async () => {
  // 基本前端驗證
  if (!name.value || !email.value || !password.value) {
    ElMessage.warning("請完整填寫所有欄位");
    return;
  }
  if (password.value !== confirmPassword.value) {
    ElMessage.error("兩次輸入的密碼不一致");
    return;
  }

  // 開始請求，開啟 loading
  isSubmitting.value = true;

  try {
    const res = await api.post("/register", {
      name: name.value,
      email: email.value,
      password: password.value,
      password_confirmation: confirmPassword.value,
    });
    ElMessage.success("註冊成功");
    router.push("/login"); // 註冊成功後跳轉到登入頁面
  } catch (error) {
    // 這裡可以根據後端返回的錯誤訊息進行更詳細的錯誤處理
    const errorMessage =
      error.response?.data?.message || "註冊失敗，請檢查您的輸入資訊";
    ElMessage.error(errorMessage);
  } finally {
    // 無論成功或失敗都關閉 loading
    isSubmitting.value = false;
  }
};
</script>

<template>
  <main>
    <div class="signin-block">
      <h1>註冊</h1>
      <el-input
        v-model="name"
        size="large"
        style="width: fit-content"
        placeholder="請輸入使用者名稱"
        ref="nameRef"
        @keyup.enter="focusNext('emailRef')"
      />
      <el-input
        v-model="email"
        size="large"
        style="width: fit-content"
        placeholder="請輸入EMAIL"
        ref="emailRef"
        @keyup.enter="focusNext('passwordRef')"
      />
      <el-input
        v-model="password"
        size="large"
        placeholder="請輸入密碼"
        type="password"
        style="width: fit-content"
        ref="passwordRef"
        @keyup.enter="focusNext('confirmPasswordRef')"
        show-password
      />
      <el-input
        v-model="confirmPassword"
        size="large"
        placeholder="請再次確認密碼"
        type="password"
        style="width: fit-content"
        ref="confirmPasswordRef"
        @keyup.enter="register"
        show-password
      />
      <div class="signin-button">
        <el-button @click="$router.back()" type="primary" plain size="large"
          >返回登入頁</el-button
        >
        <el-button
          type="primary"
          size="large"
          @click="register"
          :loading="isSubmitting"
        >
          完成填寫並送出
        </el-button>
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
