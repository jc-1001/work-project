<script setup>
import { ref } from "vue";
import api from "../../bootstrap";

const window = globalThis;

const email = ref("");
const isSubmitting = ref(false);
const submitted = ref(false);
const snackbar = ref({ show: false, text: "", color: "success" });

const notify = (text, color = "success") => {
    snackbar.value = { show: true, text, color };
};

const submit = () => {
    if (!email.value) {
        notify("請輸入電子郵件", "warning");
        return;
    }
    isSubmitting.value = true;

    api.post("/forgot-password", { email: email.value })
        .then(() => {
            submitted.value = true;
        })
        .catch(() => {
            notify("請求失敗，請稍後再試", "error");
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
                            v-if="!submitted"
                            rounded="xl"
                            elevation="6"
                            class="pa-2"
                        >
                            <v-card-title class="text-h5 text-center pt-6 pb-1"
                                >忘記密碼</v-card-title
                            >
                            <v-card-subtitle class="text-center pb-4 text-wrap">
                                輸入您的註冊 Email，我們將寄送重設連結
                            </v-card-subtitle>
                            <v-card-text class="pt-2">
                                <v-text-field
                                    v-model="email"
                                    label="電子郵件"
                                    variant="outlined"
                                    prepend-inner-icon="mdi-email-outline"
                                    type="email"
                                    autocomplete="email"
                                    @keyup.enter="submit"
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
                                    @click="submit"
                                >
                                    寄送重設連結
                                </v-btn>
                                <v-btn
                                    block
                                    variant="outlined"
                                    color="primary"
                                    size="large"
                                    rounded="lg"
                                    @click="window.location.href = '/login'"
                                >
                                    返回登入
                                </v-btn>
                            </v-card-actions>
                        </v-card>

                        <v-card
                            v-else
                            rounded="xl"
                            elevation="6"
                            class="pa-2 text-center"
                        >
                            <v-card-text class="pt-10 pb-4">
                                <v-icon size="64" color="success"
                                    >mdi-email-check-outline</v-icon
                                >
                                <div class="text-h6 mt-4 mb-3">信件已寄出</div>
                                <div class="text-body-2 text-medium-emphasis">
                                    若
                                    <strong>{{ email }}</strong> 已註冊，<br />
                                    重設密碼連結已寄至您的信箱。<br />
                                    連結將在 60 分鐘後失效。
                                </div>

                                <div class="text-center mt-3">
                                    <v-btn
                                        variant="plain"
                                        size="medium"
                                        color="primary"
                                        class="text-caption px-1"
                                        @click="
                                            window.location.href =
                                                'http://localhost:8025/'
                                        "
                                    >
                                        前往 MailHog 查看信件
                                    </v-btn>
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
                                    返回登入
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
