<script setup>
import { ref, nextTick } from "vue"
import api from "../../bootstrap"

const window = globalThis

const name = ref("")
const email = ref("")
const password = ref("")
const confirmPassword = ref("")
const showPassword = ref(false)
const showConfirmPassword = ref(false)
const isSubmitting = ref(false)
const snackbar = ref({ show: false, text: "", color: "success" })

const emailRef = ref(null)
const passwordRef = ref(null)
const confirmPasswordRef = ref(null)

const notify = (text, color = "success") => {
  snackbar.value = { show: true, text, color }
}

const focusNext = (nextRef) => {
  nextTick(() => nextRef.value?.focus())
}

const register = async () => {
  if (!name.value || !email.value || !password.value || !confirmPassword.value) {
    notify("請完整填寫所有欄位", "warning")
    return
  }
  if (password.value !== confirmPassword.value) {
    notify("兩次輸入的密碼不一致", "error")
    return
  }
  isSubmitting.value = true
  try {
    await api.post("/register", {
      name: name.value,
      email: email.value,
      password: password.value,
      password_confirmation: confirmPassword.value,
    })
    window.location.href = "/"
  } catch (error) {
    if (!error.response) console.error("Register network error:", error)
    notify(error.response?.data?.message || "註冊失敗，請檢查您的輸入資訊", "error")
    isSubmitting.value = false
  }
}
</script>

<template>
  <v-app>
    <v-main>
      <v-container fluid class="fill-height">
        <v-row justify="center" align="center" class="fill-height">
          <v-col cols="12" sm="8" md="6" lg="5">
            <v-card rounded="xl" elevation="6" class="pa-2">
              <v-card-title class="text-h5 text-center pt-6 pb-2">會員註冊</v-card-title>
              <v-card-text class="pt-4">
                <v-text-field
                  v-model="name"
                  label="使用者名稱"
                  variant="outlined"
                  prepend-inner-icon="mdi-account-outline"
                  autocomplete="username"
                  @keyup.enter="focusNext(emailRef)"
                  class="mb-2"
                  autofocus
                />
                <v-text-field
                  ref="emailRef"
                  v-model="email"
                  label="電子郵件"
                  variant="outlined"
                  prepend-inner-icon="mdi-email-outline"
                  type="email"
                  autocomplete="email"
                  @keyup.enter="focusNext(passwordRef)"
                  class="mb-2"
                />
                <v-text-field
                  ref="passwordRef"
                  v-model="password"
                  label="密碼"
                  variant="outlined"
                  prepend-inner-icon="mdi-lock-outline"
                  :type="showPassword ? 'text' : 'password'"
                  :append-inner-icon="showPassword ? 'mdi-eye-off' : 'mdi-eye'"
                  @click:append-inner="showPassword = !showPassword"
                  autocomplete="new-password"
                  @keyup.enter="focusNext(confirmPasswordRef)"
                  class="mb-2"
                />
                <v-text-field
                  ref="confirmPasswordRef"
                  v-model="confirmPassword"
                  label="確認密碼"
                  variant="outlined"
                  prepend-inner-icon="mdi-lock-check-outline"
                  :type="showConfirmPassword ? 'text' : 'password'"
                  :append-inner-icon="showConfirmPassword ? 'mdi-eye-off' : 'mdi-eye'"
                  @click:append-inner="showConfirmPassword = !showConfirmPassword"
                  autocomplete="new-password"
                  @keyup.enter="register"
                />
              </v-card-text>
              <v-card-actions class="flex-column px-6 pb-6 ga-3">
                <v-btn
                  block
                  variant="tonal"
                  color="primary"
                  size="large"
                  rounded="lg"
                  :loading="isSubmitting"
                  @click="register"
                >
                  完成填寫並送出
                </v-btn>
                <v-btn
                  block
                  variant="outlined"
                  color="primary"
                  size="large"
                  rounded="lg"
                  @click="window.location.href = '/login'"
                >
                  返回登入頁
                </v-btn>
              </v-card-actions>
            </v-card>
          </v-col>
        </v-row>
      </v-container>
    </v-main>

    <v-snackbar v-model="snackbar.show" :color="snackbar.color" location="top" timeout="3000">
      {{ snackbar.text }}
    </v-snackbar>
  </v-app>
</template>
