<script setup>
import { ref, watch, onMounted } from "vue";
import api from "../../bootstrap";
import AdminLayout from "../../layouts/AdminLayout.vue";

const window = globalThis;

const search = ref("");
const users = ref([]);
const snackbar = ref({ show: false, text: "", color: "success" });
const confirm = ref({ show: false, item: null });
const loading = ref(true);
const selectedIsActive = ref(null);
const page = ref(1);
const perPage = ref(15);
const total = ref(0);

const statusOptions = [
    { title: "啟用中", value: 1 },
    { title: "已停用", value: 0 },
];

const headers = [
    { title: "使用者姓名", key: "name", sortable: false },
    { title: "電子信箱", key: "email", sortable: false, align: "center" },
    { title: "創建時間", key: "created_at", sortable: false, align: "center" },
    { title: "狀態", key: "is_active", sortable: false, align: "center" },
    {
        title: "",
        key: "actions",
        sortable: false,
        align: "center",
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

async function confirmToggle() {
    const item = confirm.value.item;
    confirm.value = { show: false, item: null };
    await toggleActive(item);
}

function fetchUsers() {
    loading.value = true;
    const params = { page: page.value, per_page: perPage.value };
    if (search.value) params.search = search.value;
    if (selectedIsActive.value !== null)
        params.is_active = selectedIsActive.value;

    api.get("/api/admin/users", { params })
        .then((res) => {
            users.value = res.data.data;
            total.value = res.data.total;
        })
        .catch(() => {
            showMessage("載入列表失敗", "error");
        })
        .finally(() => {
            loading.value = false;
        });
}

function toggleActive(item) {
    api.patch(`/api/admin/users/${item.id}/toggle-active`)
        .then((res) => {
            item.is_active = res.data.is_active;
            showMessage(item.is_active ? "已啟用帳號" : "已停用帳號");
        })
        .catch(() => {
            showMessage("操作失敗", "error");
        });
}

watch([page, perPage], fetchUsers);

let searchDebounce = null;
watch([search, selectedIsActive], () => {
    clearTimeout(searchDebounce);
    searchDebounce = setTimeout(() => {
        if (page.value === 1) {
            fetchUsers();
        } else {
            page.value = 1;
        }
    }, 300);
});

onMounted(fetchUsers);
</script>

<template>
    <AdminLayout>
        <div class="d-flex justify-space-between align-center mb-4">
            <h1>會員列表</h1>
        </div>

        <v-data-table-server
            :headers="headers"
            :items="users"
            :items-length="total"
            :loading="loading"
            v-model:page="page"
            v-model:items-per-page="perPage"
            :items-per-page-options="itemsPerPageOptions"
            loading-text="載入中，請稍候..."
            no-data-text="查無會員"
            items-per-page-text="每頁顯示"
        >
            <template #top>
                <div class="d-flex ga-3 mb-4">
                    <v-text-field
                        v-model="search"
                        density="compact"
                        label="搜尋使用者姓名或電子信箱"
                        prepend-inner-icon="mdi-magnify"
                        clearable
                        variant="outlined"
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
                        style="max-width: 320px"
                    />
                </div>
            </template>

            <template #item.created_at="{ item }">
                {{ item.created_at?.slice(0, 10) }}
            </template>

            <template #item.is_active="{ item }">
                <v-chip
                    :color="item.is_active ? 'success' : 'error'"
                    size="small"
                    style="cursor: pointer"
                    @click="openConfirm(item)"
                >
                    {{ item.is_active ? "啟用" : "停用" }}
                </v-chip>
            </template>

            <template #item.actions="{ item }">
                <v-btn
                    icon="mdi-square-edit-outline"
                    variant="text"
                    size="small"
                    @click.stop="
                        window.location.href = `/admin/user/${item.id}`
                    "
                />
            </template>
        </v-data-table-server>

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
