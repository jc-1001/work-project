<script setup>
import { ref, computed, onMounted } from "vue";
import api from "../../../bootstrap";
import AdminLayout from "../../../layouts/AdminLayout.vue";

const window = globalThis;
const couponId = document.getElementById("app").dataset.id;

const loading = ref(true);
const loadFailed = ref(false);
const storing = ref(false);
const coupon = ref(null);
const formRef = ref(null);
const snackbar = ref({ show: false, text: "", color: "success" });

const form = ref({
    code: "",
    discount_type: "fixed",
    discount_value: null,
    min_order_amount: null,
    max_discount_amount: null,
    max_uses: null,
    expires_at: "",
    is_active: true,
});

const discountTypes = [
    { title: "固定金額 (NT$)", value: "fixed" },
    { title: "百分比 (%)", value: "percent" },
];

const rules = {
    required: (v) => !!v || "此欄位必填",
    positiveNumber: (v) =>
        v === null || v === "" || Number(v) >= 0 || "請輸入正數",
    positiveInt: (v) =>
        !v || (Number.isInteger(Number(v)) && Number(v) >= 1) || "請輸入正整數",
};

const usageRate = computed(() => {
    if (!coupon.value?.max_uses) return 0;
    return Math.round((coupon.value.used_count / coupon.value.max_uses) * 100);
});

function notify(text, color = "success") {
    snackbar.value = { show: true, text, color };
}

function fetchCoupon() {
    loading.value = true;
    api.get(`/api/admin/coupons/${couponId}`)
        .then((res) => {
            const c = res.data.coupon;
            coupon.value = c;
            form.value = {
                code: c.code,
                discount_type: c.discount_type,
                discount_value:
                    c.discount_value !== null
                        ? parseFloat(c.discount_value)
                        : null,
                min_order_amount:
                    c.min_order_amount !== null
                        ? parseFloat(c.min_order_amount)
                        : null,
                max_discount_amount:
                    c.max_discount_amount !== null
                        ? parseFloat(c.max_discount_amount)
                        : null,
                max_uses: c.max_uses ?? null,
                expires_at: c.expires_at ? c.expires_at.slice(0, 10) : "",
                is_active: Boolean(c.is_active),
            };
        })
        .catch((err) => {
            loadFailed.value = true;
            notify(
                err.response?.data?.message ?? "載入優惠碼失敗，請稍後再試",
                "error",
            );
        })
        .finally(() => {
            loading.value = false;
        });
}

async function store() {
    const { valid } = await formRef.value.validate();
    if (!valid) return;

    storing.value = true;
    api.put(`/api/admin/coupons/${couponId}`, {
        code: form.value.code,
        discount_type: form.value.discount_type,
        discount_value: form.value.discount_value,
        min_order_amount: form.value.min_order_amount || null,
        max_discount_amount: form.value.max_discount_amount || null,
        max_uses: form.value.max_uses || null,
        expires_at: form.value.expires_at || null,
        is_active: form.value.is_active,
    })
        .then(() => {
            notify("儲存成功");
            setTimeout(() => (window.location.href = "/admin/coupons"), 1000);
        })
        .catch((err) => {
            if (err.response?.status === 422) {
                const errors = err.response.data?.errors;
                notify(
                    errors
                        ? Object.values(errors).flat().join("、")
                        : "資料驗證失敗",
                    "error",
                );
            } else {
                notify("儲存失敗，請稍後再試", "error");
            }
        })
        .finally(() => {
            storing.value = false;
        });
}

onMounted(fetchCoupon);
</script>

<template>
    <AdminLayout>
        <div class="d-flex justify-space-between align-center mb-6">
            <h1>優惠碼詳情</h1>
            <div class="d-flex ga-2">
                <v-btn
                    variant="tonal"
                    prepend-icon="mdi-arrow-left"
                    @click="window.location.href = '/admin/coupons'"
                    >返回</v-btn
                >
                <v-btn
                    variant="tonal"
                    color="primary"
                    prepend-icon="mdi-check"
                    :loading="storing"
                    :disabled="loading || loadFailed"
                    @click="store"
                    >儲存變更</v-btn
                >
            </div>
        </div>

        <div v-if="loading" class="text-center pa-12">
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
            title="無法載入優惠碼資料"
            text="請確認網路連線後重新整理，或返回優惠碼列表。"
        />

        <v-form v-else ref="formRef">
            <v-row>
                <v-col cols="12" md="7">
                    <v-card rounded="xl" elevation="2" class="pa-4">
                        <div class="text-subtitle-1 font-weight-medium mb-4">
                            基本設定
                        </div>

                        <v-text-field
                            v-model="form.code"
                            label="折扣碼 *"
                            variant="outlined"
                            density="compact"
                            :rules="[rules.required]"
                            class="mb-3"
                        />

                        <v-select
                            v-model="form.discount_type"
                            :items="discountTypes"
                            label="折扣類型 *"
                            variant="outlined"
                            density="compact"
                            :rules="[rules.required]"
                            class="mb-3"
                        />

                        <v-row>
                            <v-col cols="12" sm="6">
                                <v-text-field
                                    v-model="form.discount_value"
                                    :label="
                                        form.discount_type === 'percent'
                                            ? '折扣百分比 (%) *'
                                            : '折扣金額 (NT$) *'
                                    "
                                    variant="outlined"
                                    density="compact"
                                    type="number"
                                    :rules="[
                                        rules.required,
                                        rules.positiveNumber,
                                    ]"
                                />
                            </v-col>
                            <v-col cols="12" sm="6">
                                <v-text-field
                                    v-model="form.max_uses"
                                    label="使用次數上限（空白 = 無限）"
                                    variant="outlined"
                                    density="compact"
                                    type="number"
                                    :rules="[rules.positiveInt]"
                                    hint="一組優惠碼可供多少人使用"
                                    persistent-hint
                                />
                            </v-col>
                        </v-row>

                        <v-row>
                            <v-col cols="12" sm="6">
                                <v-text-field
                                    v-model="form.min_order_amount"
                                    label="最低消費門檻 (NT$)"
                                    variant="outlined"
                                    density="compact"
                                    type="number"
                                    :rules="[rules.positiveNumber]"
                                />
                            </v-col>
                            <v-col
                                v-if="form.discount_type === 'percent'"
                                cols="12"
                                sm="6"
                            >
                                <v-text-field
                                    v-model="form.max_discount_amount"
                                    label="最高折抵金額 (NT$)"
                                    variant="outlined"
                                    density="compact"
                                    type="number"
                                    :rules="[rules.positiveNumber]"
                                />
                            </v-col>
                        </v-row>

                        <v-text-field
                            v-model="form.expires_at"
                            label="到期日（空白 = 永不過期）"
                            variant="outlined"
                            density="compact"
                            type="date"
                            class="mt-1"
                        />
                    </v-card>
                </v-col>

                <v-col cols="12" md="5">
                    <v-card rounded="xl" elevation="2" class="pa-4 mb-4">
                        <div class="text-subtitle-1 font-weight-medium mb-3">
                            狀態
                        </div>
                        <v-switch
                            v-model="form.is_active"
                            :label="form.is_active ? '啟用中' : '已停用'"
                            :color="form.is_active ? 'success' : 'default'"
                            hide-details
                        />
                    </v-card>

                    <v-card rounded="xl" elevation="2" class="pa-4">
                        <div class="text-subtitle-1 font-weight-medium mb-3">
                            使用統計
                        </div>
                        <div
                            class="d-flex justify-space-between text-body-2 mb-1"
                        >
                            <span>已使用次數</span>
                            <strong>{{ coupon?.used_count ?? 0 }} 次</strong>
                        </div>
                        <div
                            class="d-flex justify-space-between text-body-2 mb-3"
                        >
                            <span>使用上限</span>
                            <strong>{{ coupon?.max_uses ?? "無上限" }}</strong>
                        </div>
                        <v-progress-linear
                            v-if="coupon?.max_uses"
                            :model-value="usageRate"
                            color="primary"
                            rounded
                            height="6"
                            class="mb-1"
                        />
                        <div
                            v-if="coupon?.max_uses"
                            class="text-caption text-medium-emphasis text-right"
                        >
                            {{ usageRate }}% 已使用
                        </div>
                        <v-divider class="my-3" />
                        <div class="text-body-2 text-medium-emphasis">
                            建立時間：{{ coupon?.created_at?.slice(0, 10) }}
                        </div>
                    </v-card>
                </v-col>
            </v-row>
        </v-form>

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
