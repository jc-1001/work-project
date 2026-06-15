<script setup>
import { ref, computed, onMounted } from "vue";
import api from "../../bootstrap";
import AdminLayout from "../../layouts/AdminLayout.vue";

const window = globalThis;

const search = ref("");
const orders = ref([]);
const snackbar = ref(false);
const snackbarText = ref("");
const snackbarColor = ref("success");
const loading = ref(true);
const batchUpdating = ref(false);
const selected = ref([]);
const statusFilter = ref(null);
const advancing = ref(null);

const SHIPPING_FEE = Number(import.meta.env.VITE_SHIPPING_FEE ?? 60);

const STATUS_CONFIG = {
    pending: { label: "訂單已成立", color: "warning" },
    shipping: { label: "出貨中", color: "info" },
    completed: { label: "已完成", color: "success" },
};

const NEXT_STATUS = { pending: "shipping", shipping: "completed" };
const NEXT_ACTION = {
    pending: {
        label: "確認出貨",
        icon: "mdi-truck-fast-outline",
        color: "info",
    },
    shipping: {
        label: "確認完成",
        icon: "mdi-check-circle-outline",
        color: "success",
    },
};

const headers = [
    { title: "", key: "data-table-select", sortable: false, width: "48px" },
    { title: "訂單編號", key: "order_number", sortable: false },
    { title: "訂購人", key: "name", sortable: false, align: "center" },
    { title: "狀態", key: "status", sortable: false, align: "center" },
    { title: "訂購時間", key: "created_at", sortable: true, align: "center" },
    {
        title: "訂單總金額",
        key: "total_amount",
        sortable: true,
        align: "center",
    },
    {
        title: "操作",
        key: "actions",
        sortable: false,
        align: "center",
        width: "100px",
    },
];

const itemsPerPageOptions = [
    { value: 10, title: "10 筆" },
    { value: 15, title: "15 筆" },
    { value: 25, title: "25 筆" },
    { value: 50, title: "50 筆" },
];

const statusCounts = computed(() =>
    orders.value.reduce(
        (acc, o) => {
            acc[o.status] = (acc[o.status] ?? 0) + 1;
            return acc;
        },
        { pending: 0, shipping: 0, completed: 0 },
    ),
);

const filteredOrders = computed(() => {
    if (!statusFilter.value) return orders.value;
    return orders.value.filter((o) => o.status === statusFilter.value);
});

function showMessage(text, color = "success") {
    snackbarText.value = text;
    snackbarColor.value = color;
    snackbar.value = true;
}

const selectedOrders = computed(() =>
    orders.value.filter((o) => selected.value.includes(o.id)),
);

const batchAction = computed(() => {
    if (!selectedOrders.value.length) return null;
    const statuses = [...new Set(selectedOrders.value.map((o) => o.status))];
    if (statuses.length > 1) return "mixed";
    if (statuses[0] === "pending") return "shipping";
    if (statuses[0] === "shipping") return "completed";
    return null;
});

function fetchOrders() {
    api.get("/api/admin/orders")
        .then((res) => {
            orders.value = res.data.orders;
        })
        .catch(() => {
            showMessage("載入訂單列表失敗", "error");
        })
        .finally(() => {
            loading.value = false;
        });
}

function batchSetStatus(status) {
    if (!selected.value.length) return;
    batchUpdating.value = true;
    const count = selected.value.length;
    api.patch("/api/admin/orders/batch-status", {
        ids: selected.value,
        status,
    })
        .then(() => {
            selected.value.forEach((id) => {
                const o = orders.value.find((o) => o.id === id);
                if (o) o.status = status;
            });
            selected.value = [];
            showMessage(
                `${count} 筆訂單已更新為「${STATUS_CONFIG[status].label}」`,
            );
        })
        .catch((err) => {
            showMessage(
                err.response?.data?.message ?? "操作失敗，請稍後再試",
                "error",
            );
        })
        .finally(() => {
            batchUpdating.value = false;
        });
}

function advanceSingle(order) {
    const next = NEXT_STATUS[order.status];
    if (!next) return;
    advancing.value = order.id;
    api.patch(`/api/admin/orders/${order.id}/status`, {
        status: next,
    })
        .then(() => {
            order.status = next;
            showMessage(
                `訂單 ${order.order_number} 已更新為「${STATUS_CONFIG[next].label}」`,
            );
        })
        .catch((err) => {
            showMessage(
                err.response?.data?.message ?? "更新失敗，請稍後再試",
                "error",
            );
        })
        .finally(() => {
            advancing.value = null;
        });
}

function handleRowClick(_, { item }) {
    window.location.href = `/admin/orders/${item.id}`;
}

function toggleStatusFilter(key) {
    statusFilter.value = statusFilter.value === key ? null : key;
    selected.value = [];
}

onMounted(fetchOrders);
</script>

<template>
    <AdminLayout>
        <div class="d-flex align-center justify-space-between mb-4 ga-2">
            <h1>訂單列表</h1>

            <div class="d-flex align-center ga-2">
                <v-chip v-if="selected.length" color="primary" size="small">
                    已選 {{ selected.length }} 項
                </v-chip>
                <template v-if="batchAction === 'mixed'">
                    <v-chip
                        color="warning"
                        size="small"
                        prepend-icon="mdi-alert-circle-outline"
                    >
                        請選擇相同狀態的訂單
                    </v-chip>
                </template>

                <template v-else-if="batchAction === 'shipping'">
                    <v-btn
                        color="info"
                        variant="tonal"
                        prepend-icon="mdi-truck-outline"
                        :loading="batchUpdating"
                        @click="batchSetStatus('shipping')"
                        >批次出貨</v-btn
                    >
                </template>

                <template v-else-if="batchAction === 'completed'">
                    <v-btn
                        color="success"
                        variant="tonal"
                        prepend-icon="mdi-check-circle-outline"
                        :loading="batchUpdating"
                        @click="batchSetStatus('completed')"
                        >批次完成</v-btn
                    >
                </template>
            </div>
        </div>

        <div class="mb-4 d-flex ga-3 justify-center">
            <v-card
                class="stat-card cursor-pointer"
                elevation="1"
                rounded="lg"
                style="width: 180px; min-width: 140px"
                :style="
                    statusFilter === 'pending'
                        ? 'border: 2px solid rgb(var(--v-theme-warning))'
                        : 'border: 2px solid transparent'
                "
                @click="toggleStatusFilter('pending')"
            >
                <v-card-text class="d-flex align-center ga-3 py-3 px-4">
                    <v-icon size="20" color="warning"
                        >mdi-clock-alert-outline</v-icon
                    >
                    <div class="d-flex flex-column ga-1">
                        <div
                            class="text-caption text-grey"
                            style="line-height: 1"
                        >
                            待出貨
                        </div>
                        <div
                            class="font-weight-bold text-warning"
                            style="font-size: 20px; line-height: 1.2"
                        >
                            {{ statusCounts.pending }} 筆
                        </div>
                    </div>
                </v-card-text>
            </v-card>

            <v-card
                class="stat-card cursor-pointer"
                elevation="1"
                rounded="lg"
                style="width: 180px; min-width: 140px"
                :style="
                    statusFilter === 'shipping'
                        ? 'border: 2px solid rgb(var(--v-theme-info))'
                        : 'border: 2px solid transparent'
                "
                @click="toggleStatusFilter('shipping')"
            >
                <v-card-text class="d-flex align-center ga-3 py-3 px-4">
                    <v-icon size="20" color="info">mdi-truck-outline</v-icon>
                    <div class="d-flex flex-column ga-1">
                        <div
                            class="text-caption text-grey"
                            style="line-height: 1"
                        >
                            出貨中
                        </div>
                        <div
                            class="font-weight-bold text-info"
                            style="font-size: 20px; line-height: 1.2"
                        >
                            {{ statusCounts.shipping }} 筆
                        </div>
                    </div>
                </v-card-text>
            </v-card>
        </div>

        <div class="d-flex flex-wrap ga-2 justify-center mb-3">
            <v-chip
                :color="!statusFilter ? 'primary' : undefined"
                :variant="!statusFilter ? 'tonal' : 'outlined'"
                @click="toggleStatusFilter(null)"
                >全部 ({{ orders.length }})</v-chip
            >

            <v-chip
                v-for="(cfg, key) in STATUS_CONFIG"
                :key="key"
                :color="statusFilter === key ? cfg.color : undefined"
                :variant="statusFilter === key ? 'tonal' : 'outlined'"
                @click="toggleStatusFilter(key)"
                >{{ cfg.label }} ({{ statusCounts[key] }})</v-chip
            >
        </div>

        <div class="mb-3">
            <v-text-field
                v-model="search"
                density="compact"
                label="搜尋訂單編號或訂購人"
                prepend-inner-icon="mdi-magnify"
                clearable
                variant="outlined"
                hide-details
                style="max-width: 280px"
            />
        </div>

        <v-data-table
            v-model="selected"
            item-value="id"
            show-select
            :headers="headers"
            :items="filteredOrders"
            :search="search"
            :loading="loading"
            :items-per-page="15"
            :items-per-page-options="itemsPerPageOptions"
            loading-text="載入中，請稍候..."
            no-data-text="查無訂單"
            items-per-page-text="每頁顯示"
            class="clickable-rows"
            hover
            @click:row="handleRowClick"
        >
            <template #item.status="{ item }">
                <v-chip
                    :color="STATUS_CONFIG[item.status]?.color ?? 'default'"
                    size="small"
                    variant="tonal"
                    >{{
                        STATUS_CONFIG[item.status]?.label ?? item.status
                    }}</v-chip
                >
            </template>

            <template #item.created_at="{ item }">
                {{ item.created_at?.slice(0, 10) }}
            </template>

            <template #item.total_amount="{ item }">
                NT$
                {{
                    (
                        Number(item.subtotal_amount ?? item.total_amount) +
                        Number(item.shipping_fee ?? SHIPPING_FEE) -
                        Number(item.discount_amount ?? 0)
                    ).toLocaleString()
                }}
            </template>

            <template #item.actions="{ item }">
                <div class="d-flex align-center justify-center" @click.stop>
                    <v-tooltip
                        v-if="NEXT_ACTION[item.status]"
                        :text="NEXT_ACTION[item.status].label"
                        location="top"
                    >
                        <template #activator="{ props }">
                            <v-btn
                                v-bind="props"
                                :icon="NEXT_ACTION[item.status].icon"
                                :color="NEXT_ACTION[item.status].color"
                                variant="text"
                                size="small"
                                :loading="advancing === item.id"
                                @click="advanceSingle(item)"
                            />
                        </template>
                    </v-tooltip>

                    <v-btn
                        icon="mdi-square-edit-outline"
                        variant="text"
                        size="small"
                        @click.stop="
                            window.location.href = `/admin/orders/${item.id}`
                        "
                    />
                </div>
            </template>
        </v-data-table>

        <v-snackbar
            v-model="snackbar"
            :color="snackbarColor"
            timeout="3000"
            location="top"
        >
            {{ snackbarText }}
        </v-snackbar>
    </AdminLayout>
</template>

<style scoped>
.stat-card {
    transition: box-shadow 0.15s;
}
.stat-card:hover {
    box-shadow: 0 4px 14px rgba(0, 0, 0, 0.1) !important;
}
:deep(.clickable-rows tbody tr) {
    cursor: pointer;
}
:deep(.clickable-rows tbody tr:hover td) {
    background: #f5f7ff;
}
:deep(.v-data-table-footer__items-per-page .v-select) {
    min-width: 110px;
}
</style>
