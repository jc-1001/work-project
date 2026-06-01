<script setup>
import { ref, onMounted } from "vue";
import api from "../../bootstrap";

const window = globalThis;

const token = ref("");
const email = ref("");
const password = ref("");
const passwordConfirmation = ref("");
const showPassword = ref(false);
const showPasswordConfirmation = ref(false);
const isSubmitting = ref(false);
const succeeded = ref(false);
const invalidLink = ref(false);
const snackbar = ref({ show: false, text: "", color: "success" });

const notify = (text, color = "success") => {
    snackbar.value = { show: true, text, color };
};

onMounted(() => {
    const params = new URLSearchParams(window.location.search);
    token.value = params.get("token") ?? "";
    email.value = params.get("email") ?? "";

    if (!token.value || !email.value) {
        invalidLink.value = true;
    }
});

const submit = () => {
    if (!password.value || !passwordConfirmation.value) {
        notify("請完整填寫所有欄位", "warning");
        return;
    }
    if (password.value !== passwordConfirmation.value) {
        notify("兩次密碼輸入不一致", "warning");
        return;
    }
    isSubmitting.value = true;
    api.post("/reset-password", {
        token: token.value,
        email: email.value,
        password: password.value,
        password_confirmation: passwordConfirmation.value,
    })
        .then(() => {
            succeeded.value = true;
        })
        .catch((err) => {
            const msg = err.response?.data?.message ?? "重設失敗，請重新申請";
            notify(msg, "error");
        })
        .finally(() => {
            isSubmitting.value = false;
        });
};
</script>

<template>
    <v-app>
        <v-main>
            <v-container fluid class="fill-height">
                <v-row justify="center" align="center" class="fill-height">
                    <v-col cols="12" sm="8" md="5" lg="4">
                        <v-card
                            v-if="invalidLink"
                            rounded="xl"
                            elevation="6"
                            class="pa-2 text-center"
                        >
                            <v-card-text class="pt-10 pb-4">
                                <v-icon size="64" color="error"
                                    >mdi-link-off</v-icon
                                >
                                <div class="text-h6 mt-4 mb-3">連結無效</div>
                                <div class="text-body-2 text-medium-emphasis">
                                    此重設連結已失效或格式錯誤，<br />請重新申請。
                                </div>
                            </v-card-text>
                            <v-card-actions class="px-6 pb-6">
                                <v-btn
                                    block
                                    variant="tonal"
                                    color="primary"
                                    size="large"
                                    rounded="lg"
                                    @click="
                                        window.location.href =
                                            '/forgot-password'
                                    "
                                >
                                    重新申請
                                </v-btn>
                            </v-card-actions>
                        </v-card>

                        <v-card
                            v-else-if="succeeded"
                            rounded="xl"
                            elevation="6"
                            class="pa-2 text-center"
                        >
                            <v-card-text class="pt-10 pb-4">
                                <v-icon size="64" color="success"
                                    >mdi-check-circle-outline</v-icon
                                >
                                <div class="text-h6 mt-4 mb-3">密碼已重設</div>
                                <div class="text-body-2 text-medium-emphasis">
                                    請使用新密碼重新登入。
                                </div>
                            </v-card-text>
                            <v-card-actions class="px-6 pb-6">
                                <v-btn
                                    block
                                    variant="tonal"
                                    color="primary"
                                    size="large"
                                    rounded="lg"
                                    @click="window.location.href = '/login'"
                                >
                                    前往登入
                                </v-btn>
                            </v-card-actions>
                        </v-card>

                        <v-card v-else rounded="xl" elevation="6" class="pa-2">
                            <v-card-title class="text-h5 text-center pt-6 pb-1"
                                >重設密碼</v-card-title
                            >
                            <v-card-subtitle class="text-center pb-4"
                                >請輸入您的新密碼</v-card-subtitle
                            >
                            <v-card-text class="pt-2">
                                <v-text-field
                                    v-model="password"
                                    label="新密碼"
                                    variant="outlined"
                                    prepend-inner-icon="mdi-lock-outline"
                                    :type="showPassword ? 'text' : 'password'"
                                    :append-inner-icon="
                                        showPassword ? 'mdi-eye-off' : 'mdi-eye'
                                    "
                                    @click:append-inner="
                                        showPassword = !showPassword
                                    "
                                    hint="至少 8 個字元"
                                    class="mb-2"
                                />
                                <v-text-field
                                    v-model="passwordConfirmation"
                                    label="確認新密碼"
                                    variant="outlined"
                                    prepend-inner-icon="mdi-lock-check-outline"
                                    :type="
                                        showPasswordConfirmation
                                            ? 'text'
                                            : 'password'
                                    "
                                    :append-inner-icon="
                                        showPasswordConfirmation
                                            ? 'mdi-eye-off'
                                            : 'mdi-eye'
                                    "
                                    @click:append-inner="
                                        showPasswordConfirmation =
                                            !showPasswordConfirmation
                                    "
                                    @keyup.enter="submit"
                                />
                            </v-card-text>
                            <v-card-actions class="px-6 pb-6">
                                <v-btn
                                    block
                                    variant="tonal"
                                    color="primary"
                                    size="large"
                                    rounded="lg"
                                    :loading="isSubmitting"
                                    @click="submit"
                                >
                                    確認重設密碼
                                </v-btn>
                            </v-card-actions>
                        </v-card>
                    </v-col>
                </v-row>
            </v-container>
        </v-main>

        <v-snackbar
            v-model="snackbar.show"
            :color="snackbar.color"
            location="top"
            timeout="3000"
        >
            {{ snackbar.text }}
        </v-snackbar>
    </v-app>
</template>
