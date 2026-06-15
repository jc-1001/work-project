<script setup>
import { ref, watch, onMounted } from "vue";
import api from "../../bootstrap";
import NewAdminDialog from "../../components/newAdmin.vue";
import AdminLayout from "../../layouts/AdminLayout.vue";
import { useAdminAuth } from "../../composables/useAdminAuth";

const window = globalThis;
const { user } = useAdminAuth();

const search = ref("");
const administrators = ref([]);
const snackbar = ref({ show: false, text: "", color: "success" });
const loading = ref(true);
const dialog = ref(false);
const confirm = ref({ show: false, item: null });

const selectedIsActive = ref(null);
const statusOptions = [
    { title: "啟用中", value: 1 },
    { title: "已停用", value: 0 },
];

watch(selectedIsActive, () => fetchAdministrators());

const headers = [
    { title: "管理員姓名", key: "name", align: "start", sortable: false },
    { title: "電子信箱", key: "email", align: "center", sortable: false },
    { title: "身分", key: "role", align: "center", sortable: false },
    { title: "狀態", key: "is_active", align: "center", sortable: false },
    { title: "創建時間", key: "created_at", align: "center" },
    {
        title: "",
        key: "actions",
        align: "center",
        sortable: false,
        width: "80px",
    },
];

const itemsPerPageOptions = [
    { value: 10, title: "10 筆" },
    { value: 15, title: "15 筆" },
    { value: 25, title: "25 筆" },
    { value: 50, title: "50 筆" },
];

function showMessage(text, color = "success") {
    snackbar.value = { show: true, text, color };
}

function openConfirm(item) {
    confirm.value = { show: true, item };
}

function fetchAdministrators() {
    loading.value = true;
    const params = {};
    if (
        selectedIsActive.value !== null &&
        selectedIsActive.value !== undefined
    ) {
        params.is_active = selectedIsActive.value;
    }
    api.get("/api/admin/administrators", { params })
        .then((res) => {
            administrators.value = res.data.data ?? res.data;
        })
        .catch(() => {
            showMessage("載入列表失敗", "error");
        })
        .finally(() => {
            loading.value = false;
        });
}

function toggleActive(item) {
    api.patch(`/api/admin/administrators/${item.id}/toggle-active`)
        .then((res) => {
            item.is_active = res.data.user.is_active;
            showMessage(item.is_active ? "已啟用帳號" : "已停用帳號");
        })
        .catch(() => {
            showMessage("操作失敗", "error");
        });
}

async function confirmToggle() {
    const item = confirm.value.item;
    confirm.value = { show: false, item: null };
    await toggleActive(item);
}

function onAdminCreated(admin, errMsg) {
    if (errMsg) {
        showMessage(errMsg, "error");
        return;
    }
    administrators.value.unshift(admin);
    showMessage("管理員新增成功");
}

onMounted(fetchAdministrators);
</script>

<template>
    <AdminLayout>
        <div class="d-flex justify-space-between align-center">
            <h1>管理員列表</h1>
            <v-btn
                color="primary"
                prepend-icon="mdi-plus"
                @click="dialog = true"
            >
                新增管理員
            </v-btn>
        </div>

        <v-data-table
            :headers="headers"
            :items="administrators"
            :search="search"
            :loading="loading"
            :items-per-page-options="itemsPerPageOptions"
            items-per-page="15"
            loading-text="載入中..."
            no-data-text="尚無管理員資料"
            items-per-page-text="每頁顯示"
            class="mt-4"
            hover
        >
            <template #top>
                <div class="d-flex mb-4 ga-3">
                    <v-text-field
                        v-model="search"
                        density="compact"
                        label="搜尋管理員姓名"
                        prepend-inner-icon="mdi-magnify"
                        clearable
                        variant="outlined"
                        class="mb-2"
                        style="max-width: 320px"
                    />

                    <v-select
                        v-model="selectedIsActive"
                        prepend-inner-icon="mdi-account-badge"
                        :items="statusOptions"
                        item-title="title"
                        item-value="value"
                        :menu-props="{ scrim: true, scrollStrategy: 'close' }"
                        label="篩選帳號使用狀態"
                        clearable
                        density="compact"
                        variant="outlined"
                        class="mb-2"
                        style="max-width: 320px"
                    />
                </div>
            </template>

            <template #item.role="{ item }">
                <v-chip
                    :color="item.role === 'super_admin' ? 'red' : 'blue'"
                    size="small"
                >
                    {{
                        item.role === "super_admin"
                            ? "超級管理員"
                            : "一般管理員"
                    }}
                </v-chip>
            </template>

            <template #item.is_active="{ item }">
                <v-tooltip
                    :text="item.id === user?.id ? '無法停用自己的帳號' : ''"
                    :disabled="item.id !== user?.id"
                >
                    <template #activator="{ props }">
                        <v-chip
                            v-bind="props"
                            :color="item.is_active ? 'success' : 'error'"
                            size="small"
                            :style="
                                item.id === user?.id
                                    ? 'cursor: not-allowed'
                                    : 'cursor: pointer'
                            "
                            :disabled="item.id === user?.id"
                            @click="item.id !== user?.id && openConfirm(item)"
                        >
                            {{ item.is_active ? "啟用" : "停用" }}
                        </v-chip>
                    </template>
                </v-tooltip>
            </template>

            <template #item.created_at="{ item }">
                {{ item.created_at?.slice(0, 10) }}
            </template>

            <template #item.actions="{ item }">
                <v-btn
                    icon="mdi-square-edit-outline"
                    variant="text"
                    size="small"
                    @click="
                        window.location.href = `/admin/administrators/${item.id}`
                    "
                />
            </template>
        </v-data-table>

        <NewAdminDialog v-model="dialog" @created="onAdminCreated" />

        <v-dialog v-model="confirm.show" max-width="360">
            <v-card rounded="lg">
                <v-card-title
                    class="text-subtitle-1 font-weight-bold pt-5 px-5"
                >
                    確認{{ confirm.item?.is_active ? "停用" : "啟用" }}帳號
                </v-card-title>
                <v-card-text class="px-5 pb-2">
                    確定要{{ confirm.item?.is_active ? "停用" : "啟用" }}
                    <strong>{{ confirm.item?.name }}</strong> 的帳號嗎？
                </v-card-text>
                <v-card-actions class="px-5 pb-4">
                    <v-spacer />
                    <v-btn variant="text" @click="confirm.show = false"
                        >取消</v-btn
                    >
                    <v-btn
                        :color="confirm.item?.is_active ? 'error' : 'success'"
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
:deep(.v-data-table-footer__items-per-page .v-select) {
    min-width: 110px;
}
</style>
