<script setup>
import { ref, nextTick, onUnmounted } from "vue";
import api from "../../bootstrap";
const account = ref("");
const password = ref("");
const showPwd = ref(false);
const loading = ref(false);

const retrySeconds = ref(0);
let countdownTimer = null;

const snackbar = ref(false);
const snackbarText = ref("");
const snackbarColor = ref("success");

const accountRef = ref(null);
const passwordRef = ref(null);

const focusNext = async (target) => {
    await nextTick();
    target?.focus();
};

function startCountdown() {
    if (countdownTimer) clearInterval(countdownTimer);
    countdownTimer = setInterval(() => {
        retrySeconds.value -= 1;
        if (retrySeconds.value <= 0) {
            clearInterval(countdownTimer);
            countdownTimer = null;
        }
    }, 1000);
}

onUnmounted(() => {
    if (countdownTimer) clearInterval(countdownTimer);
});

function showMessage(text, color = "success") {
    snackbarText.value = text;
    snackbarColor.value = color;
    snackbar.value = true;
}

function login() {
    if (!account.value || !password.value) {
        showMessage("請輸入帳號與密碼", "warning");
        return;
    }
    loading.value = true;
    api.post("/admin/login", {
        email: account.value,
        password: password.value,
    })

        .then((res) => {
            window.location.href = res.data.redirect ?? "/admin/products";
        })
        .catch((err) => {
            if (err.response?.status === 429) {
                const retryAfter =
                    parseInt(err.response.headers?.["retry-after"]) || 60;
                retrySeconds.value = retryAfter;
                startCountdown();
                showMessage(
                    `嘗試次數過多，請 ${retryAfter} 秒後再試`,
                    "warning",
                );
            } else {
                showMessage(
                    err.response?.data?.message || "帳號或密碼錯誤",
                    "error",
                );
            }
            loading.value = false;
        });
}
</script>

<template>
    <v-container
        class="fill-height d-flex align-center"
        fluid
        style="min-height: 100vh"
    >
        <v-row justify="center" align="center">
            <v-col cols="12" sm="6" md="4">
                <v-card class="pa-6" elevation="4">
                    <v-card-title class="text-center">後台登入</v-card-title>
                    <v-card-text>
                        <v-text-field
                            ref="accountRef"
                            v-model="account"
                            label="帳號"
                            prepend-inner-icon="mdi-account"
                            variant="outlined"
                            @keydown.enter.prevent="focusNext(passwordRef)"
                        />
                        <v-text-field
                            ref="passwordRef"
                            v-model="password"
                            label="密碼"
                            prepend-inner-icon="mdi-lock"
                            :type="showPwd ? 'text' : 'password'"
                            :append-inner-icon="
                                showPwd ? 'mdi-eye-off' : 'mdi-eye'
                            "
                            @click:append-inner="showPwd = !showPwd"
                            variant="outlined"
                            @keydown.enter.prevent="login"
                        />
                    </v-card-text>
                    <v-card-actions>
                        <v-btn
                            block
                            color="primary"
                            :loading="loading"
                            :disabled="retrySeconds > 0"
                            @click="login"
                        >
                            {{
                                retrySeconds > 0
                                    ? `請等待 ${retrySeconds} 秒後重試`
                                    : "登入"
                            }}
                        </v-btn>
                    </v-card-actions>
                </v-card>
            </v-col>
        </v-row>
    </v-container>

    <v-snackbar
        v-model="snackbar"
        :color="snackbarColor"
        timeout="3000"
        location="top"
    >
        {{ snackbarText }}
    </v-snackbar>
</template>
