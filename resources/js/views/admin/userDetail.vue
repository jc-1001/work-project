<script setup>
import { ref, onMounted } from "vue";
import api from "../../bootstrap";
import AdminLayout from "../../layouts/AdminLayout.vue";

const window = globalThis;
const userId = document.getElementById("app").dataset.id;

const isActive = ref(false);
const user = ref(null);
const loading = ref(true);
const loadFailed = ref(false);
const snackbar = ref({ show: false, text: "", color: "success" });
const confirm = ref(false);

function openConfirm() {
    confirm.value = true;
}

async function confirmToggle() {
    confirm.value = false;
    await toggleActive();
}

function fetchUser() {
    api.get(`/api/admin/users/${userId}`)
        .then((res) => {
            user.value = res.data.user;
            isActive.value = !!res.data.user.is_active;
        })
        .catch((err) => {
            loadFailed.value = true;
            const msg =
                err.response?.data?.message ?? "載入使用者失敗，請稍後再試";
            showMessage(msg, "error");
        })
        .finally(() => {
            loading.value = false;
        });
}

const monthsSince = (dateStr) => {
    if (!dateStr) return 0;
    const created = new Date(dateStr);
    const now = new Date();
    return (
        (now.getFullYear() - created.getFullYear()) * 12 +
        (now.getMonth() - created.getMonth())
    );
};

const formatDate = (dateStr) => {
    if (!dateStr) return "";
    const d = new Date(dateStr);
    const pad = (n) => String(n).padStart(2, "0");
    return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())} ${pad(d.getHours())}:${pad(d.getMinutes())}:${pad(d.getSeconds())}`;
};

function toggleActive() {
    api.patch(`/api/admin/users/${userId}/toggle-active`)
        .then((res) => {
            isActive.value = res.data.is_active;
            showMessage(res.data.is_active ? "帳號已啟用" : "帳號已停用");
        })
        .catch(() => {
            isActive.value = !isActive.value;
            showMessage("更新狀態失敗", "error");
        });
}

function showMessage(text, color = "success") {
    snackbar.value = { show: true, text, color };
}

onMounted(fetchUser);
</script>

<template>
    <AdminLayout>
        <div class="d-flex justify-space-between align-center mb-6">
            <h1>使用者詳情</h1>
            <div class="d-flex ga-2">
                <v-btn
                    variant="tonal"
                    prepend-icon="mdi-arrow-left"
                    @click.stop="window.location.href = '/admin/user'"
                    >返回</v-btn
                >
            </div>
        </div>

        <div v-if="loading" class="text-center pa-8">
            <v-progress-circular indeterminate color="primary" />
        </div>

        <v-alert
            v-else-if="loadFailed"
            type="error"
            variant="tonal"
            class="mt-4"
            title="無法載入使用者資料"
            text="請確認網路連線後重新整理，或返回會員列表。"
        />

        <v-row v-else-if="user">
            <v-col cols="12" md="4">
                <v-card elevation="1" rounded="lg" class="mb-3">
                    <div
                        class="d-flex flex-column align-center px-4 pt-7 pb-5 ga-2"
                    >
                        <div
                            class="d-flex align-center justify-center mb-2 rounded-circle text-white font-weight-bold"
                            style="
                                width: 84px;
                                height: 84px;
                                font-size: 36px;
                                background: linear-gradient(
                                    135deg,
                                    #667eea,
                                    #764ba2
                                );
                                box-shadow: 0 4px 14px rgba(102, 126, 234, 0.4);
                            "
                        >
                            {{ user.name?.charAt(0).toUpperCase() }}
                        </div>
                        <div
                            class="font-weight-bold"
                            style="font-size: 20px; color: #333"
                        >
                            {{ user.name }}
                        </div>
                        <v-chip
                            :color="isActive ? 'success' : 'error'"
                            variant="tonal"
                            size="small"
                            class="mt-2"
                            >{{
                                isActive ? "帳號啟用中" : "帳號已停用"
                            }}</v-chip
                        >
                    </div>

                    <v-divider />
                    <v-card-text
                        class="d-flex flex-column align-center"
                        style="padding: 18px 16px"
                    >
                        <div
                            style="
                                font-size: 42px;
                                font-weight: 800;
                                color: #667eea;
                                line-height: 1;
                            "
                        >
                            {{ monthsSince(user.created_at) }}
                        </div>
                        <div class="text-body-2 text-grey mt-1">已註冊月數</div>
                    </v-card-text>
                </v-card>
            </v-col>

            <v-col cols="12" md="8">
                <v-card elevation="1" rounded="lg">
                    <v-card-title
                        class="d-flex align-center"
                        style="
                            font-size: 20px;
                            font-weight: 600;
                            color: #555;
                            padding: 14px 16px;
                        "
                    >
                        <v-icon size="20" class="mr-1"
                            >mdi-account-circle-outline</v-icon
                        >
                        使用者資訊
                    </v-card-title>
                    <v-divider />

                    <v-list class="pa-0">
                        <v-list-item prepend-icon="mdi-email-outline">
                            <v-list-item-title class="text-grey"
                                >電子郵箱</v-list-item-title
                            >
                            <template #append>
                                <span
                                    class="font-weight-medium text-grey-darken-3 text-body-2"
                                    >{{ user.email }}</span
                                >
                            </template>
                        </v-list-item>
                        <v-divider />
                        <v-list-item prepend-icon="mdi-clock-outline">
                            <v-list-item-title class="text-grey"
                                >建立時間</v-list-item-title
                            >
                            <template #append>
                                <span
                                    class="font-weight-medium text-grey-darken-3 text-body-2"
                                    >{{ formatDate(user.created_at) }}</span
                                >
                            </template>
                        </v-list-item>
                    </v-list>

                    <v-divider />

                    <v-card-title
                        class="d-flex align-center"
                        style="
                            font-size: 20px;
                            font-weight: 600;
                            color: #555;
                            padding: 14px 16px;
                        "
                    >
                        <v-icon size="20" class="mr-1"
                            >mdi-shield-account-outline</v-icon
                        >
                        帳號狀態
                    </v-card-title>
                    <v-divider />

                    <div
                        class="d-flex justify-space-between align-center"
                        style="padding: 14px 16px"
                    >
                        <div>
                            <div
                                class="font-weight-medium"
                                style="font-size: 17px; color: #444"
                            >
                                啟用 / 停用帳號
                            </div>
                            <div
                                class="text-body-2 text-grey"
                                style="margin-top: 2px"
                            >
                                關閉後該使用者將無法登入
                            </div>
                        </div>
                        <v-switch
                            :model-value="isActive"
                            :label="isActive ? '啟用' : '停用'"
                            :color="isActive ? 'success' : 'error'"
                            hide-details
                            inset
                            @click="openConfirm"
                        />
                    </div>
                </v-card>
            </v-col>
        </v-row>

        <v-dialog v-model="confirm" max-width="360">
            <v-card rounded="lg">
                <v-card-title
                    class="text-subtitle-1 font-weight-bold pt-5 px-5"
                >
                    確認{{ isActive ? "停用" : "啟用" }}帳號
                </v-card-title>
                <v-card-text class="px-5 pb-2">
                    確定要{{ isActive ? "停用" : "啟用" }}
                    <strong>{{ user?.name }}</strong> 的帳號嗎？
                </v-card-text>
                <v-card-actions class="px-5 pb-4">
                    <v-spacer />
                    <v-btn variant="text" @click="confirm = false">取消</v-btn>
                    <v-btn
                        :color="isActive ? 'error' : 'success'"
                        variant="tonal"
                        @click="confirmToggle"
                    >
                        確認
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>

        <v-snackbar
            v-model="snackbar.show"
            :color="snackbar.color"
            timeout="3000"
            location="top"
        >
            {{ snackbar.text }}
        </v-snackbar>
    </AdminLayout>
</template>
