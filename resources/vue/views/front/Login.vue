<script setup>
import { ref, nextTick } from "vue"
import { useRouter } from "vue-router"
import api from "../../api"

const router = useRouter()
const email = ref("")
const password = ref("")
const showPassword = ref(false)
const isSubmitting = ref(false)
const passwordRef = ref(null)
const snackbar = ref({ show: false, text: "", color: "success" })

const notify = (text, color = "success") => {
  snackbar.value = { show: true, text, color }
}

const focusNext = () => {
  if (!email.value) {
    notify("請先輸入 EMAIL", "warning")
    return
  }
  nextTick(() => passwordRef.value?.focus())
}

const login = async () => {
  if (!email.value || !password.value) {
    notify("請完整填寫所有欄位", "warning")
    return
  }
  isSubmitting.value = true
  try {
    await api.post("/login", { email: email.value, password: password.value })
    window.dispatchEvent(new Event("login-status-changed"))
    notify("登入成功")
    router.push("/")
  } catch {
    notify("登入失敗，請檢查您的帳號和密碼", "error")
  } finally {
    isSubmitting.value = false
  }
}
</script>

<template>
  <v-container fluid class="fill-height">
    <v-row justify="center" align="center" class="fill-height">
      <v-col cols="12" sm="8" md="5" lg="4">
        <v-card rounded="xl" elevation="6" class="pa-2">
          <v-card-title class="text-h5 text-center pt-6 pb-2">會員登入</v-card-title>
          <v-card-text class="pt-4">
            <v-text-field
              v-model="email"
              label="電子郵件"
              variant="outlined"
              prepend-inner-icon="mdi-email-outline"
              type="email"
              autocomplete="email"
              @keyup.enter="focusNext"
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
              autocomplete="current-password"
              @keyup.enter="login"
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
              @click="login"
            >
              登入
            </v-btn>
            <v-btn
              block
              variant="outlined"
              color="primary"
              size="large"
              rounded="lg"
              @click="router.push('/register')"
            >
              還沒有帳號？前往註冊
            </v-btn>
          </v-card-actions>
        </v-card>
      </v-col>
    </v-row>
  </v-container>

  <v-snackbar v-model="snackbar.show" :color="snackbar.color" location="top" timeout="3000">
    {{ snackbar.text }}
  </v-snackbar>
</template>
