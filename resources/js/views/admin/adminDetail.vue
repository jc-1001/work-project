<script setup>
import { ref, computed, onMounted } from "vue";
import api from "../../bootstrap";
import AdminLayout from "../../layouts/AdminLayout.vue";
import { useAdminAuth } from "../../composables/useAdminAuth";

const window = globalThis;
const administratorId = document.getElementById("app").dataset.id;
const { user } = useAdminAuth();

const isSelf = computed(() => Number(administratorId) === user.value?.id);

const switchButton = ref(false);
const confirm = ref(false);
const administrator = ref(null);
const loading = ref(true);
const snackbar = ref({ show: false, text: "", color: "success" });

const editing = ref(false);
const saving = ref(false);
const editForm = ref({ name: "", role: "admin" });

const roleOptions = [
    { title: "一般管理員", value: "admin" },
    { title: "超級管理員", value: "super_admin" },
];

const roleLabel = {
    admin: "一般管理員",
    super_admin: "超級管理員",
};

function startEdit() {
    editForm.value = {
        name: administrator.value.name,
        role: administrator.value.role,
    };
    editing.value = true;
}

function cancelEdit() {
    editing.value = false;
}

function saveEdit() {
    if (!editForm.value.name.trim()) return;
    saving.value = true;
    api.patch(`/api/admin/administrators/${administratorId}`, {
        name: editForm.value.name,
        role: editForm.value.role,
    })
        .then((res) => {
            administrator.value.name = res.data.admin.name;
            administrator.value.role = res.data.admin.role;
            editing.value = false;
            showMessage("更新成功");
        })
        .catch((err) => {
            showMessage(err.response?.data?.message || "更新失敗", "error");
        })
        .finally(() => {
            saving.value = false;
        });
}

function fetchAdministrator() {
    api.get(`/api/admin/administrators/${administratorId}`)
        .then((res) => {
            administrator.value = res.data;
            switchButton.value = !!res.data.is_active;
        })
        .catch(() => {
            console.error("載入管理員失敗");
        })
        .finally(() => {
            loading.value = false;
        });
}

const formatDate = (dateStr) => {
    if (!dateStr) return "";
    const d = new Date(dateStr);
    const pad = (n) => String(n).padStart(2, "0");
    return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())} ${pad(d.getHours())}:${pad(d.getMinutes())}:${pad(d.getSeconds())}`;
};

function toggleActive() {
    api.patch(`/api/admin/administrators/${administratorId}/toggle-active`)
        .then((res) => {
            switchButton.value = res.data.user.is_active;
            showMessage(res.data.user.is_active ? "帳號已啟用" : "帳號已停用");
        })
        .catch(() => {
            showMessage("更新狀態失敗", "error");
        });
}

function openConfirm() {
    confirm.value = true;
}

async function confirmToggle() {
    confirm.value = false;
    await toggleActive();
}

function showMessage(text, color = "success") {
    snackbar.value = { show: true, text, color };
}

onMounted(fetchAdministrator);
</script>

<template>
    <AdminLayout>
        <div class="d-flex justify-space-between align-center mb-6">
            <h1>管理員詳情</h1>
            <div class="d-flex ga-2">
                <v-btn
                    variant="tonal"
                    prepend-icon="mdi-arrow-left"
                    @click="
                        () => (window.location.href = '/admin/administrators')
                    "
                    >返回</v-btn
                >
                <template v-if="!editing">
                    <v-btn
                        color="primary"
                        variant="tonal"
                        prepend-icon="mdi-pencil"
                        @click="startEdit"
                        >編輯</v-btn
                    >
                </template>
                <template v-else>
                    <v-btn
                        variant="tonal"
                        prepend-icon="mdi-close"
                        @click="cancelEdit"
                        >取消</v-btn
                    >
                    <v-btn
                        color="primary"
                        variant="tonal"
                        prepend-icon="mdi-check"
                        :loading="saving"
                        @click="saveEdit"
                        >儲存</v-btn
                    >
                </template>
            </div>
        </div>

        <div v-if="loading" class="text-center pa-8">
            <v-progress-circular indeterminate color="primary" />
        </div>

        <v-row v-else-if="administrator">
            <v-col cols="12" md="4">
                <v-card elevation="1" rounded="lg" class="mb-3">
                    <template v-if="!editing">
                        <div
                            class="d-flex flex-column align-center pt-7 px-4 pb-6"
                            style="gap: 6px"
                        >
                            <div
                                class="rounded-circle d-flex align-center justify-center text-white font-weight-bold mb-2"
                                style="
                                    width: 84px;
                                    height: 84px;
                                    font-size: 36px;
                                "
                                :style="
                                    administrator.role === 'super_admin'
                                        ? 'background: linear-gradient(135deg, #f5576c, #f093fb); box-shadow: 0 4px 14px rgba(245, 87, 108, 0.4)'
                                        : 'background: linear-gradient(135deg, #667eea, #764ba2); box-shadow: 0 4px 14px rgba(102, 126, 234, 0.4)'
                                "
                            >
                                {{
                                    administrator.name?.charAt(0).toUpperCase()
                                }}
                            </div>
                            <div
                                class="font-weight-bold"
                                style="font-size: 20px; color: #333"
                            >
                                {{ administrator.name }}
                            </div>

                            <v-divider
                                style="align-self: stretch; margin: 12px 0"
                            />
                            <v-chip
                                :color="
                                    administrator.role === 'super_admin'
                                        ? 'red'
                                        : 'blue'
                                "
                                variant="tonal"
                                size="small"
                                class="mt-1"
                                >{{
                                    roleLabel[administrator.role] ?? "—"
                                }}</v-chip
                            >
                            <v-chip
                                :color="switchButton ? 'success' : 'error'"
                                variant="tonal"
                                size="small"
                                class="mt-1"
                                >{{
                                    switchButton ? "帳號啟用中" : "帳號已停用"
                                }}</v-chip
                            >
                        </div>
                    </template>

                    <template v-else>
                        <div class="pt-6 pt-5">
                            <div
                                class="mb-4 text-center text-black font-weight-bold text-h4"
                            >
                                編輯管理員資料
                            </div>
                            <v-text-field
                                v-model="editForm.name"
                                label="管理員姓名"
                                variant="outlined"
                                density="compact"
                                class="mb-3 mx-4"
                            />
                            <v-select
                                v-model="editForm.role"
                                :items="roleOptions"
                                label="管理員身分"
                                variant="outlined"
                                density="compact"
                                :disabled="isSelf"
                                :hint="isSelf ? '無法修改自己的身分' : ''"
                                persistent-hint
                                class="mb-3 mx-4"
                            />
                        </div>
                    </template>
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
                        管理員資訊
                    </v-card-title>
                    <v-divider />

                    <v-list class="pa-0">
                        <v-list-item
                            prepend-icon="mdi-shield-account-outline"
                            style="font-size: 12px"
                        >
                            <v-list-item-title
                                class="text-grey"
                                style="font-size: 16px"
                                >身分</v-list-item-title
                            >
                            <template #append>
                                <v-chip
                                    :color="
                                        administrator.role === 'super_admin'
                                            ? 'red'
                                            : 'blue'
                                    "
                                    variant="tonal"
                                    >{{
                                        roleLabel[administrator.role] ?? "—"
                                    }}</v-chip
                                >
                            </template>
                        </v-list-item>
                        <v-divider />
                        <v-list-item
                            prepend-icon="mdi-email-outline"
                            style="font-size: 12px"
                        >
                            <v-list-item-title
                                class="text-grey"
                                style="font-size: 16px"
                                >電子郵箱</v-list-item-title
                            >
                            <template #append>
                                <span
                                    class="font-weight-medium text-grey-darken-3"
                                    style="font-size: 16px"
                                    >{{ administrator.email }}</span
                                >
                            </template>
                        </v-list-item>
                        <v-divider />
                        <v-list-item
                            prepend-icon="mdi-clock-outline"
                            style="font-size: 12px"
                        >
                            <v-list-item-title
                                class="text-grey"
                                style="font-size: 16px"
                                >建立時間</v-list-item-title
                            >
                            <template #append>
                                <span
                                    class="font-weight-medium text-grey-darken-3"
                                    style="font-size: 16px"
                                    >{{
                                        formatDate(administrator.created_at)
                                    }}</span
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
                            >mdi-shield-lock-outline</v-icon
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
                                style="font-size: 18px; color: #444"
                            >
                                啟用 / 停用帳號
                            </div>
                            <div
                                class="text-grey"
                                style="margin-top: 2px; font-size: 16px"
                            >
                                關閉後該管理員將無法登入後台
                            </div>
                        </div>
                        <v-tooltip
                            :text="isSelf ? '無法停用自己的帳號' : ''"
                            :disabled="!isSelf"
                        >
                            <template #activator="{ props }">
                                <v-switch
                                    v-bind="props"
                                    :model-value="switchButton"
                                    :label="switchButton ? '啟用' : '停用'"
                                    :color="switchButton ? 'success' : 'error'"
                                    :disabled="isSelf"
                                    hide-details
                                    inset
                                    @click="!isSelf && openConfirm()"
                                />
                            </template>
                        </v-tooltip>
                    </div>
                </v-card>
            </v-col>
        </v-row>

        <v-dialog v-model="confirm" max-width="360">
            <v-card rounded="lg">
                <v-card-title
                    class="text-subtitle-1 font-weight-bold pt-5 px-5"
                >
                    確認{{ switchButton ? "停用" : "啟用" }}帳號
                </v-card-title>
                <v-card-text class="px-5 pb-2">
                    確定要{{ switchButton ? "停用" : "啟用" }}
                    <strong>{{ administrator?.name }}</strong> 的帳號嗎？
                </v-card-text>
                <v-card-actions class="px-5 pb-4">
                    <v-spacer />
                    <v-btn variant="text" @click="confirm = false">取消</v-btn>
                    <v-btn
                        :color="switchButton ? 'error' : 'success'"
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

<style scoped>
:deep(.v-list-item__prepend) {
    width: auto;
    margin-inline-end: -20px;
}
</style>
