<script setup>
import { ref, onMounted, computed } from "vue";
import api from "../../bootstrap.js";
import { getImageUrl } from "../../utils/image.js";
import AdminLayout from "../../layouts/AdminLayout.vue";

const window = globalThis;
const orderId = document.getElementById("app").dataset.id;

const order = ref(null);
const loading = ref(true);
const loadFailed = ref(false);
const updating = ref(false);
const snackbar = ref({ show: false, text: "", color: "success" });
const SHIPPING_FEE = Number(import.meta.env.VITE_SHIPPING_FEE ?? 60);

const STATUS_CONFIG = {
    pending: { label: "訂單已成立", color: "warning" },
    shipping: { label: "出貨中", color: "info" },
    completed: { label: "已完成", color: "success" },
};

const NEXT_STATUS = { pending: "shipping", shipping: "completed" };
const NEXT_ACTION = { pending: "確認出貨", shipping: "確認完成" };

const nextStatus = computed(() => NEXT_STATUS[order.value?.status]);
const nextAction = computed(() => NEXT_ACTION[order.value?.status]);

function notify(text, color = "success") {
    snackbar.value = { show: true, text, color };
}

function fetchOrder() {
    api.get(`/api/admin/orders/${orderId}`)
        .then((res) => {
            order.value = res.data.order;
        })
        .catch((err) => {
            loadFailed.value = true;
            const msg =
                err.response?.data?.message ?? "載入訂單失敗，請稍後再試";
            notify(msg, "error");
        })
        .finally(() => {
            loading.value = false;
        });
}

async function advanceStatus() {
    if (!nextStatus.value) return;
    updating.value = true;
    const newStatus = nextStatus.value;
    try {
        await api.patch(`/api/admin/orders/${orderId}/status`, {
            status: newStatus,
        });
        order.value.status = newStatus;
        notify(`訂單已更新為「${STATUS_CONFIG[newStatus].label}」`);
    } catch (err) {
        notify(err.response?.data?.message ?? "更新失敗，請稍後再試", "error");
    } finally {
        updating.value = false;
    }
}

function subtotal(item) {
    return item.price * item.quantity;
}

const itemsSubtotal = computed(
    () => order.value?.items?.reduce((sum, i) => sum + subtotal(i), 0) ?? 0,
);

const orderFinalAmount = computed(() => {
    if (!order.value) return 0;
    const shipping = Number(order.value.shipping_fee ?? SHIPPING_FEE);
    const discount = Number(order.value.discount_amount ?? 0);
    return itemsSubtotal.value + shipping - discount;
});

const paymentMethodLabel = {
    "Credit card": "信用卡",
    ATM: "ATM轉帳",
};
const invoiceTypeLabel = {
    個人電子發票: "個人電子發票",
    公司行號: "公司行號統編",
    手機載具: "手機載具",
};

onMounted(fetchOrder);
</script>

<template>
    <AdminLayout>
        <div class="d-flex justify-space-between align-center mb-6">
            <h1>訂單詳情</h1>
            <div class="d-flex align-center ga-2">
                <v-chip
                    v-if="order"
                    :color="STATUS_CONFIG[order.status]?.color"
                    variant="tonal"
                    size="default"
                >
                    {{ STATUS_CONFIG[order.status]?.label ?? order.status }}
                </v-chip>
                <v-btn
                    v-if="nextStatus"
                    color="primary"
                    variant="tonal"
                    :loading="updating"
                    @click="advanceStatus"
                    >{{ nextAction }}</v-btn
                >
                <v-btn
                    variant="tonal"
                    prepend-icon="mdi-arrow-left"
                    @click="window.location.href = '/admin/orders'"
                    >返回</v-btn
                >
            </div>
        </div>

        <div v-if="loading" class="text-center pa-8">
            <v-progress-circular
                :size="70"
                :width="7"
                color="primary"
                indeterminate
            />
        </div>

        <v-alert
            v-else-if="loadFailed"
            type="error"
            variant="tonal"
            class="mt-4"
            title="無法載入訂單資料"
            text="請確認網路連線後重新整理，或返回訂單列表。"
        />

        <template v-else-if="order">
            <v-card
                class="mb-4 border-sm border-gray"
                elevation="0"
                style="
                    background: linear-gradient(
                        135deg,
                        #f8f9ff 0%,
                        #eef1ff 100%
                    );
                "
            >
                <div
                    class="d-flex justify-space-between align-center"
                    style="padding: 18px 24px"
                >
                    <div class="d-flex flex-column ga-1">
                        <span
                            class="text-body-2 text-uppercase text-grey"
                            style="letter-spacing: 0.5px"
                            >訂單編號</span
                        >
                        <span
                            class="font-weight-bold"
                            style="
                                font-size: 22px;
                                color: #3a3a5c;
                                letter-spacing: 0.5px;
                            "
                            >{{ order.order_number }}</span
                        >
                    </div>
                    <div class="d-flex align-center">
                        <span class="text-body-2 text-grey d-flex align-center">
                            <v-icon size="14" class="mr-1"
                                >mdi-clock-outline</v-icon
                            >
                            {{ order.created_at?.slice(0, 10) }}
                        </span>
                    </div>
                </div>
            </v-card>

            <v-row>
                <v-col cols="12" md="4">
                    <v-card class="mb-3" elevation="1" rounded="lg">
                        <v-card-title
                            class="d-flex align-center"
                            style="
                                padding: 14px 16px;
                                font-size: 20px;
                                font-weight: 600;
                                color: #555;
                            "
                        >
                            <v-icon size="20" class="mr-1"
                                >mdi-account-circle-outline</v-icon
                            >
                            訂購人資訊
                        </v-card-title>
                        <v-divider />
                        <v-card-text class="pa-0">
                            <div
                                class="d-flex justify-space-between align-start py-3 px-4"
                                style="font-size: 15px"
                            >
                                <span
                                    class="text-grey d-flex align-center flex-shrink-0 mr-2"
                                >
                                    <v-icon size="14" class="mr-1"
                                        >mdi-account</v-icon
                                    >姓名
                                </span>
                                <span>{{ order.name }}</span>
                            </div>
                            <v-divider />
                            <div
                                class="d-flex justify-space-between align-start py-3 px-4"
                                style="font-size: 15px"
                            >
                                <span
                                    class="text-grey d-flex align-center flex-shrink-0 mr-2"
                                >
                                    <v-icon size="14" class="mr-1"
                                        >mdi-phone</v-icon
                                    >電話
                                </span>
                                <span>{{ order.phone }}</span>
                            </div>
                            <v-divider />
                            <div
                                class="d-flex justify-space-between align-start py-3 px-4"
                                style="font-size: 15px"
                            >
                                <span
                                    class="text-grey d-flex align-center flex-shrink-0 mr-2"
                                >
                                    <v-icon size="14" class="mr-1"
                                        >mdi-map-marker</v-icon
                                    >地址
                                </span>
                                <span
                                    class="text-right"
                                    style="line-height: 1.5"
                                    >{{ order.address }}</span
                                >
                            </div>
                        </v-card-text>
                    </v-card>

                    <v-row>
                        <v-col cols="12" sm="6">
                            <v-card elevation="1" rounded="lg" class="mb-3">
                                <v-card-title
                                    class="d-flex align-center"
                                    style="
                                        padding: 14px 16px;
                                        font-size: 20px;
                                        font-weight: 600;
                                        color: #555;
                                    "
                                >
                                    <v-icon size="20" class="mr-1"
                                        >mdi-credit-card-outline</v-icon
                                    >
                                    付款方式
                                </v-card-title>
                                <v-divider />
                                <v-card-text class="pt-2 pb-3">
                                    {{
                                        paymentMethodLabel[
                                            order.payment_method
                                        ] ?? order.payment_method
                                    }}
                                </v-card-text>
                            </v-card>
                        </v-col>

                        <v-col cols="12" sm="6">
                            <v-card elevation="1" rounded="lg" class="mb-3">
                                <v-card-title
                                    class="d-flex align-center"
                                    style="
                                        padding: 14px 16px;
                                        font-size: 20px;
                                        font-weight: 600;
                                        color: #555;
                                    "
                                >
                                    <v-icon size="20" class="mr-1"
                                        >mdi-receipt-text-outline</v-icon
                                    >
                                    發票類型
                                </v-card-title>
                                <v-divider />
                                <v-card-text class="pt-2 pb-3">
                                    <div>
                                        {{
                                            invoiceTypeLabel[
                                                order.invoice_type
                                            ] ?? "—"
                                        }}
                                    </div>
                                    <div
                                        v-if="
                                            order.invoice_type === '公司行號' &&
                                            order.tax_id
                                        "
                                        class="text-caption text-grey mt-1"
                                    >
                                        統編：{{ order.tax_id }}
                                    </div>
                                    <div
                                        v-if="
                                            order.invoice_type === '手機載具' &&
                                            order.carrier
                                        "
                                        class="text-caption text-grey mt-1"
                                    >
                                        載具：{{ order.carrier }}
                                    </div>
                                </v-card-text>
                            </v-card>
                        </v-col>
                    </v-row>
                </v-col>

                <v-col cols="12" md="8">
                    <v-card elevation="1" rounded="lg">
                        <v-card-title
                            class="d-flex align-center"
                            style="
                                padding: 14px 16px;
                                font-size: 20px;
                                font-weight: 600;
                                color: #555;
                            "
                        >
                            <v-icon size="20" class="mr-1"
                                >mdi-cart-outline</v-icon
                            >
                            訂購商品
                        </v-card-title>
                        <v-divider />

                        <v-table density="comfortable">
                            <thead>
                                <tr>
                                    <th
                                        class="bg-grey-lighten-5 text-grey font-weight-bold"
                                        style="font-size: 15px"
                                    >
                                        商品名稱
                                    </th>
                                    <th
                                        class="text-center bg-grey-lighten-5 text-grey font-weight-bold"
                                        style="font-size: 15px"
                                    >
                                        單價
                                    </th>
                                    <th
                                        class="text-center bg-grey-lighten-5 text-grey font-weight-bold"
                                        style="font-size: 15px"
                                    >
                                        數量
                                    </th>
                                    <th
                                        class="text-right bg-grey-lighten-5 text-grey font-weight-bold"
                                        style="font-size: 15px"
                                    >
                                        小計
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="item in order.items"
                                    :key="item.id"
                                    class="product-row"
                                >
                                    <td>
                                        <div class="d-flex align-center ga-3">
                                            <div
                                                class="flex-shrink-0 rounded-sm bg-grey-lighten-5 overflow-hidden"
                                                style="
                                                    width: 52px;
                                                    height: 52px;
                                                    min-width: 52px;
                                                    border: 1px solid #eee;
                                                "
                                            >
                                                <v-img
                                                    :src="
                                                        getImageUrl(
                                                            item.product?.image,
                                                        )
                                                    "
                                                    cover
                                                    rounded="sm"
                                                    height="100%"
                                                    width="100%"
                                                >
                                                    <template #placeholder>
                                                        <v-icon
                                                            color="grey-lighten-1"
                                                            >mdi-image</v-icon
                                                        >
                                                    </template>
                                                </v-img>
                                            </div>
                                            <span class="text-body-2">{{
                                                item.product_name
                                            }}</span>
                                        </div>
                                    </td>
                                    <td class="text-center text-body-2">
                                        NT$ {{ item.price }}
                                    </td>
                                    <td class="text-center text-body-2">
                                        {{ item.quantity }}
                                    </td>
                                    <td
                                        class="text-right text-body-2 font-weight-medium"
                                    >
                                        NT$ {{ subtotal(item) }}
                                    </td>
                                </tr>
                            </tbody>
                        </v-table>

                        <v-divider />

                        <div class="px-6 pt-4" style="padding-bottom: 18px">
                            <div
                                class="d-flex justify-space-between align-center"
                                style="
                                    padding: 7px 0;
                                    font-size: 15px;
                                    color: #555;
                                "
                            >
                                <span class="d-flex align-center"
                                    >商品小計</span
                                >
                                <span>NT$ {{ itemsSubtotal }}</span>
                            </div>
                            <div
                                v-if="Number(order.discount_amount) > 0"
                                class="d-flex justify-space-between align-center"
                                style="
                                    padding: 7px 0;
                                    font-size: 15px;
                                    color: #555;
                                "
                            >
                                <span class="text-grey d-flex align-center">
                                    <v-icon size="13" class="mr-1"
                                        >mdi-tag-outline</v-icon
                                    >代碼折扣
                                </span>
                                <span class="text-success"
                                    >- NT$
                                    {{
                                        Number(
                                            order.discount_amount,
                                        ).toLocaleString()
                                    }}</span
                                >
                            </div>
                            <div
                                class="d-flex justify-space-between align-center"
                                style="
                                    padding: 7px 0;
                                    font-size: 15px;
                                    color: #555;
                                "
                            >
                                <span class="text-grey d-flex align-center">
                                    <v-icon size="13" class="mr-1"
                                        >mdi-truck-outline</v-icon
                                    >運費
                                </span>
                                <span
                                    >NT$
                                    {{
                                        Number(
                                            order.shipping_fee ?? SHIPPING_FEE,
                                        ).toLocaleString()
                                    }}</span
                                >
                            </div>
                            <v-divider class="my-2" />
                            <div
                                class="d-flex justify-space-between align-center"
                                style="
                                    padding-top: 4px;
                                    font-size: 17px;
                                    color: #555;
                                "
                            >
                                <span class="font-weight-bold">總金額</span>
                                <span
                                    class="font-weight-bold text-red-darken-1"
                                    style="font-size: 22px"
                                    >NT$
                                    {{
                                        orderFinalAmount.toLocaleString()
                                    }}</span
                                >
                            </div>
                        </div>
                    </v-card>
                </v-col>
            </v-row>
        </template>

        <v-snackbar
            v-model="snackbar.show"
            :color="snackbar.color"
            location="top"
            timeout="3000"
        >
            {{ snackbar.text }}
        </v-snackbar>
    </AdminLayout>
</template>

<style scoped>
.product-row {
    transition: background 0.15s;
}
.product-row:hover {
    background: #f5f7ff;
}
</style>
