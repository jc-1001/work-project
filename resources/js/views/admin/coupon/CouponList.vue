<script setup>
import { ref, computed, onMounted } from "vue";
import api from "../../../bootstrap";
import AdminLayout from "../../../layouts/AdminLayout.vue";
import CouponNew from "../../../components/CouponNew.vue";

const window = globalThis;

const coupons = ref([]);
const loading = ref(true);
const search = ref("");
const statusFilter = ref(null);
const selected = ref([]);
const batchUpdating = ref(false);
const showNewDialog = ref(false);
const snackbar = ref({ show: false, text: "", color: "success" });
const confirm = ref({ show: false, item: null });

const headers = [
    { title: "", key: "data-table-select", sortable: false, width: "48px" },
    { title: "折扣碼", key: "code", sortable: false },
    { title: "類型", key: "discount_type", sortable: false, align: "center" },
    {
        title: "折扣值",
        key: "discount_value",
        sortable: false,
        align: "center",
    },
    {
        title: "最低消費",
        key: "min_order_amount",
        sortable: false,
        align: "center",
    },
    {
        title: "最高消費",
        key: "max_discount_amount",
        sortable: false,
        align: "center",
    },
    { title: "已用/上限", key: "usage", sortable: false, align: "center" },
    { title: "到期時間", key: "expires_at", sortable: true, align: "center" },
    { title: "狀態", key: "is_active", sortable: false, align: "center" },
    {
        title: "操作",
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

const statusCounts = computed(() => ({
    active: coupons.value.filter((c) => c.is_active).length,
    inactive: coupons.value.filter((c) => !c.is_active).length,
}));

const filteredCoupons = computed(() => {
    if (statusFilter.value === null) return coupons.value;
    return coupons.value.filter(
        (c) => c.is_active === (statusFilter.value === 1),
    );
});

function notify(text, color = "success") {
    snackbar.value = { show: true, text, color };
}

function fetchCoupons() {
    api.get("/api/admin/coupons")
        .then((res) => {
            coupons.value = res.data.coupons;
        })
        .catch(() => {
            notify("載入優惠碼列表失敗", "error");
        })
        .finally(() => {
            loading.value = false;
        });
}

function openConfirm(item) {
    confirm.value = { show: true, item };
}

function confirmToggle() {
    const item = confirm.value.item;
    confirm.value.show = false;
    api.patch(`/api/admin/coupons/${item.id}/toggle-active`)
        .then((res) => {
            item.is_active = res.data.is_active;
            notify(item.is_active ? "已啟用優惠碼" : "已停用優惠碼");
        })
        .catch(() => {
            notify("操作失敗", "error");
        });
}

function batchSetStatus(status) {
    if (!selected.value.length) return;
    batchUpdating.value = true;
    const count = selected.value.length;
    api.patch("/api/admin/coupons/batch-status", {
        ids: selected.value,
        status,
    })
        .then(() => {
            coupons.value.forEach((c) => {
                if (selected.value.includes(c.id)) c.is_active = status;
            });
            selected.value = [];
            notify(`已批量${status ? "啟用" : "停用"} ${count} 筆優惠碼`);
        })
        .catch((err) => {
            notify(err.response?.data?.message ?? "操作失敗", "error");
        })
        .finally(() => {
            batchUpdating.value = false;
        });
}

function toggleStatusFilter(val) {
    statusFilter.value = statusFilter.value === val ? null : val;
    selected.value = [];
}

function onCouponCreated(coupon) {
    coupons.value.unshift(coupon);
    showNewDialog.value = false;
    notify("優惠碼新增成功");
}

function formatDiscount(item) {
    if (item.discount_type === "percent") return `${item.discount_value}%`;
    return `NT$ ${Number(item.discount_value).toLocaleString()}`;
}

onMounted(fetchCoupons);
</script>

<template>
    <AdminLayout>
        <div
            class="d-flex align-center justify-space-between mb-4 ga-2 flex-wrap"
        >
            <h1>優惠碼列表</h1>
            <div class="d-flex align-center ga-2 flex-wrap">
                <template v-if="selected.length">
                    <v-chip color="primary" size="small"
                        >已選 {{ selected.length }} 項</v-chip
                    >
                    <v-btn
                        color="success"
                        variant="tonal"
                        size="small"
                        :loading="batchUpdating"
                        @click="batchSetStatus(true)"
                        >批量啟用</v-btn
                    >
                    <v-btn
                        color="error"
                        variant="tonal"
                        size="small"
                        :loading="batchUpdating"
                        @click="batchSetStatus(false)"
                        >批量停用</v-btn
                    >
                </template>
                <v-btn
                    color="primary"
                    variant="tonal"
                    prepend-icon="mdi-plus"
                    @click="showNewDialog = true"
                    >新增優惠碼</v-btn
                >
            </div>
        </div>

        <div class="d-flex flex-wrap ga-2 align-center mb-3">
            <v-chip
                :color="statusFilter === null ? 'primary' : undefined"
                :variant="statusFilter === null ? 'tonal' : 'outlined'"
                @click="toggleStatusFilter(null)"
                >全部 ({{ coupons.length }})</v-chip
            >
            <v-chip
                :color="statusFilter === 1 ? 'success' : undefined"
                :variant="statusFilter === 1 ? 'tonal' : 'outlined'"
                @click="toggleStatusFilter(1)"
                >啟用中 ({{ statusCounts.active }})</v-chip
            >
            <v-chip
                :color="statusFilter === 0 ? 'error' : undefined"
                :variant="statusFilter === 0 ? 'tonal' : 'outlined'"
                @click="toggleStatusFilter(0)"
                >已停用 ({{ statusCounts.inactive }})</v-chip
            >

            <v-spacer />

            <v-text-field
                v-model="search"
                density="compact"
                label="搜尋折扣碼"
                prepend-inner-icon="mdi-magnify"
                clearable
                variant="outlined"
                hide-details
                style="max-width: 240px"
            />
        </div>

        <v-data-table
            v-model="selected"
            item-value="id"
            show-select
            :headers="headers"
            :items="filteredCoupons"
            :search="search"
            :loading="loading"
            :items-per-page="15"
            :items-per-page-options="itemsPerPageOptions"
            loading-text="載入中，請稍候..."
            no-data-text="查無優惠碼"
            items-per-page-text="每頁顯示"
            class="clickable-rows"
            hover
        >
            <template #item.discount_type="{ item }">
                <v-chip
                    :color="
                        item.discount_type === 'percent' ? 'info' : 'secondary'
                    "
                    size="small"
                    variant="tonal"
                    >{{
                        item.discount_type === "percent" ? "百分比" : "固定金額"
                    }}</v-chip
                >
            </template>

            <template #item.discount_value="{ item }">
                {{ formatDiscount(item) }}
            </template>

            <template #item.min_order_amount="{ item }">
                {{
                    item.min_order_amount
                        ? `NT$ ${Number(item.min_order_amount).toLocaleString()}`
                        : "-"
                }}
            </template>

            <template #item.max_discount_amount="{ item }">
                {{
                    item.max_discount_amount
                        ? `NT$ ${Number(item.max_discount_amount).toLocaleString()}`
                        : "-"
                }}
            </template>

            <template #item.usage="{ item }">
                {{ item.used_count }} / {{ item.max_uses ?? "無上限" }}
            </template>

            <template #item.expires_at="{ item }">
                {{
                    item.expires_at ? item.expires_at.slice(0, 10) : "永不過期"
                }}
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
                    @click="window.location.href = `/admin/coupons/${item.id}`"
                />
            </template>
        </v-data-table>

        <CouponNew v-model="showNewDialog" @created="onCouponCreated" />

        <v-snackbar
            v-model="snackbar.show"
            :color="snackbar.color"
            timeout="3000"
            location="top"
        >
            {{ snackbar.text }}
        </v-snackbar>

        <v-dialog v-model="confirm.show" max-width="360">
            <v-card rounded="lg">
                <v-card-title
                    class="text-subtitle-1 font-weight-bold pt-5 px-5"
                >
                    確認{{ confirm.item?.is_active ? "停用" : "啟用" }}折扣代碼
                </v-card-title>
                <v-card-text class="px-5 pb-2">
                    確定要{{ confirm.item?.is_active ? "停用" : "啟用" }}優惠碼
                    <strong>{{ confirm.item?.code }}</strong> 嗎？
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
    </AdminLayout>
</template>

<style scoped>
:deep(.clickable-rows tbody tr:hover td) {
    background: #f5f7ff;
}
:deep(.v-data-table-footer__items-per-page .v-select) {
    min-width: 110px;
}
</style>
