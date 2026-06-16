<script setup>
import { ref, onMounted, onUnmounted, computed, nextTick } from "vue";
import api from "../../../bootstrap";
import { getImageUrl } from "../../../utils/image";
import { useAuth } from "../../../composables/useAuth";
import FrontLayout from "../../../layouts/FrontLayout.vue";

const cartItems = ref([]);
const totalPrice = ref(0);

const isSubmitting = ref(false);
const SHIPPING_FEE = Number(import.meta.env.VITE_SHIPPING_FEE ?? 60);
const checkoutFormRef = ref(null);
const nameRef = ref(null);
const phoneRef = ref(null);
const addressRef = ref(null);
const cardNumberRef = ref(null);
const dateRef = ref(null);
const cvcRef = ref(null);
const taxIdRef = ref(null);
const carrierRef = ref(null);
const userInfo = ref(false);
const { fetchUser } = useAuth();

const focusNext = async (target) => {
    await nextTick();
    target?.focus();
};

const couponInput = ref("");
const appliedCoupon = ref(null);
const discountAmount = ref(0);
const couponError = ref("");
const validatingCoupon = ref(false);

function applyCoupon() {
    if (!couponInput.value.trim()) return;
    couponError.value = "";
    validatingCoupon.value = true;
    api.post("/api/coupons/validate", {
        code: couponInput.value.trim(),
        subtotal: totalPrice.value,
    })
        .then((res) => {
            appliedCoupon.value = res.data.coupon;
            discountAmount.value = res.data.discount_amount;
        })
        .catch((err) => {
            couponError.value = err.response?.data?.message ?? "折扣碼無效";
            appliedCoupon.value = null;
            discountAmount.value = 0;
        })
        .finally(() => {
            validatingCoupon.value = false;
        });
}

function removeCoupon() {
    appliedCoupon.value = null;
    discountAmount.value = 0;
    couponInput.value = "";
    couponError.value = "";
}

const snackbar = ref({ show: false, text: "", color: "success" });
const notify = (text, color = "success") => {
    snackbar.value = { show: true, text, color };
};

const form = ref({
    name: "",
    phone: "",
    address: "",
    cardNumber: "",
    cvc: "",
    date: "",
    bill: "",
    taxId: "",
    carrier: "",
    paymentMethod: "Credit card",
});

const invoiceOptions = [
    { value: "個人電子發票", title: "個人電子發票 (二聯式)" },
    { value: "公司行號", title: "公司行號 (三聯式)" },
    { value: "手機載具", title: "手機載具" },
];

const nameRules = [(v) => !!v || "請輸入真實姓名"];
const phoneRules = [
    (v) => !!v || "請輸入聯絡電話",
    (v) => /^09\d{8}$/.test(v) || "請輸入正確的手機格式",
];
const addressRules = [(v) => !!v || "請輸入收件地址"];
const billRules = [(v) => !!v || "請選擇發票類型"];

const taxIdRules = computed(() =>
    form.value.bill === "公司行號"
        ? [
              (v) => !!v || "請輸入統一編號",
              (v) => /^\d{8}$/.test(v) || "統編格式應為 8 位數字",
          ]
        : [],
);
const carrierRules = computed(() =>
    form.value.bill === "手機載具"
        ? [
              (v) => !!v || "請輸入載具號碼 (例: /ABC1234)",
              (v) =>
                  /^\/[A-Z0-9.+-]{7}$/.test(v) || "載具格式錯誤 (例: /ABC1234)",
          ]
        : [],
);
const cardNumberRules = computed(() =>
    form.value.paymentMethod === "Credit card"
        ? [
              (v) => !!v || "請輸入信用卡號",
              (v) => (v && v.length === 16) || "卡號應為 16 位數",
          ]
        : [],
);
const cvcRules = computed(() =>
    form.value.paymentMethod === "Credit card"
        ? [
              (v) => !!v || "請輸入安全碼",
              (v) => (v && v.length === 3) || "應為 3 位數",
          ]
        : [],
);
const dateRules = computed(() =>
    form.value.paymentMethod === "Credit card"
        ? [(v) => !!v || "請輸入卡片有效日期"]
        : [],
);

const loadCartItems = () => {
    try {
        const savedCart = localStorage.getItem("cart");
        if (savedCart) {
            cartItems.value = JSON.parse(savedCart);
            totalPrice.value = cartItems.value.reduce(
                (sum, item) => sum + item.price * item.quantity,
                0,
            );
        }
    } catch {
        localStorage.removeItem("cart");
        cartItems.value = [];
    }
};

const handleTabChange = () => {
    checkoutFormRef.value?.resetValidation();
};

const clearSensitiveFields = () => {
    form.value.cardNumber = "";
    form.value.cvc = "";
    form.value.date = "";
};

const submitOrder = async () => {
    if (!checkoutFormRef.value) return;

    const { valid } = await checkoutFormRef.value.validate();
    if (!valid) {
        notify("資訊填寫有誤，請檢查紅字標示欄位", "error");
        return;
    }
    if (cartItems.value.length === 0) {
        notify("購物車是空的，無法下單喔！", "warning");
        return;
    }

    isSubmitting.value = true;
    const payload = {
        items: cartItems.value.map(({ id, quantity }) => ({
            id,
            quantity,
        })),
        customer: {
            name: form.value.name,
            phone: form.value.phone,
            address: form.value.address,
        },
        paymentMethod: form.value.paymentMethod,
        bill: form.value.bill,
        taxId: form.value.taxId,
        carrier: form.value.carrier,
        coupon_code: appliedCoupon.value?.code ?? null,
    };

    api.post("/orders", payload)
        .then(() => {
            clearSensitiveFields();
            notify("訂單已提交成功！");
            localStorage.removeItem("cart");
            window.location.href = "/shop";
        })
        .catch((error) => {
            clearSensitiveFields();
            notify(
                error.response?.data?.message || "訂單提交失敗，請稍後再試",
                "error",
            );
        })
        .finally(() => {
            isSubmitting.value = false;
        });
};

const goTo = (url) => {
    window.location.href = url;
};

const fillFromLastOrder = (checked) => {
    if (!checked) {
        form.value.name = "";
        form.value.phone = "";
        form.value.address = "";
        return;
    }
    api.get("/orders/latest")
        .then((res) => {
            if (!res.data.order) {
                notify("查無上次購買記錄", "warning");
                userInfo.value = false;
                return;
            }
            form.value.name = res.data.order.name;
            form.value.phone = res.data.order.phone;
            form.value.address = res.data.order.address;
        })
        .catch(() => {
            notify("無法載入上次購買資料", "error");
            userInfo.value = false;
        });
};

onMounted(() => {
    fetchUser();
    loadCartItems();
});

onUnmounted(() => {
    clearSensitiveFields();
});
</script>

<template>
    <FrontLayout>
        <v-container style="max-width: 1200px" class="py-6 px-4">
            <h1 class="text-h5 font-weight-bold mb-4">結帳頁面</h1>

            <v-btn
                variant="outlined"
                color="primary"
                class="mb-6"
                prepend-icon="mdi-arrow-left"
                @click="goTo('/cart')"
            >
                返回購物車
            </v-btn>

            <v-form ref="checkoutFormRef">
                <v-row>
                    <v-col cols="12" md="8">
                        <v-card rounded="xl" elevation="2" class="mb-5">
                            <div
                                class="text-subtitle-1 font-weight-medium d-flex align-center ga-2 pt-4 pb-1 px-4"
                            >
                                <v-icon
                                    icon="mdi-format-list-bulleted"
                                    size="20"
                                />
                                購買清單
                            </div>
                            <v-card-text>
                                <v-table density="compact">
                                    <thead>
                                        <tr>
                                            <th>商品圖片</th>
                                            <th>商品名稱</th>
                                            <th>數量</th>
                                            <th>單價</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            v-for="item in cartItems"
                                            :key="item.id"
                                        >
                                            <td>
                                                <v-img
                                                    :src="
                                                        getImageUrl(item.image)
                                                    "
                                                    width="80"
                                                    height="60"
                                                    cover
                                                    rounded="sm"
                                                />
                                            </td>
                                            <td>{{ item.name }}</td>
                                            <td>{{ item.quantity }}</td>
                                            <td>NT$ {{ item.price }}</td>
                                        </tr>
                                    </tbody>
                                </v-table>
                            </v-card-text>
                        </v-card>

                        <v-card rounded="xl" elevation="2" class="mb-5">
                            <div
                                class="text-subtitle-1 font-weight-medium d-flex align-center ga-2 pt-4 pb-1 px-4"
                            >
                                <v-icon icon="mdi-account" size="20" />
                                購買人訊息
                                <v-checkbox
                                    v-model="userInfo"
                                    color="info"
                                    label="與上次購買資料同"
                                    hide-details
                                    @update:model-value="fillFromLastOrder"
                                ></v-checkbox>
                            </div>
                            <v-card-text>
                                <v-text-field
                                    ref="nameRef"
                                    v-model="form.name"
                                    label="姓名"
                                    variant="outlined"
                                    density="compact"
                                    placeholder="請輸入真實姓名"
                                    :rules="nameRules"
                                    class="mb-2"
                                    @keydown.enter.prevent="focusNext(phoneRef)"
                                />
                                <v-text-field
                                    ref="phoneRef"
                                    v-model="form.phone"
                                    label="手機號碼"
                                    variant="outlined"
                                    density="compact"
                                    placeholder="09xxxxxxxx"
                                    :rules="phoneRules"
                                    class="mb-2"
                                    @keydown.enter.prevent="
                                        focusNext(addressRef)
                                    "
                                />
                                <v-text-field
                                    ref="addressRef"
                                    v-model="form.address"
                                    label="收件地址"
                                    variant="outlined"
                                    density="compact"
                                    placeholder="請輸入詳細配送地址"
                                    :rules="addressRules"
                                />
                            </v-card-text>
                        </v-card>

                        <v-card rounded="xl" elevation="2" class="mb-5">
                            <div
                                class="text-subtitle-1 font-weight-medium d-flex align-center ga-2 pt-4 pb-1 px-4"
                            >
                                <v-icon icon="mdi-wallet" size="20" />
                                付款資訊與方式
                            </div>
                            <v-card-text>
                                <v-tabs
                                    v-model="form.paymentMethod"
                                    color="primary"
                                    density="compact"
                                    class="mb-4"
                                    @update:model-value="handleTabChange"
                                >
                                    <v-tab value="Credit card">信用卡</v-tab>
                                    <v-tab value="ATM">ATM轉帳</v-tab>
                                </v-tabs>

                                <v-tabs-window v-model="form.paymentMethod">
                                    <v-tabs-window-item value="Credit card">
                                        <v-text-field
                                            ref="cardNumberRef"
                                            v-model="form.cardNumber"
                                            label="信用卡號"
                                            variant="outlined"
                                            density="compact"
                                            placeholder="16位信用卡號"
                                            maxlength="16"
                                            :rules="cardNumberRules"
                                            class="my-2"
                                            @keydown.enter.prevent="
                                                focusNext(dateRef)
                                            "
                                        />
                                        <v-row>
                                            <v-col cols="6">
                                                <v-text-field
                                                    ref="dateRef"
                                                    v-model="form.date"
                                                    label="有效日期"
                                                    variant="outlined"
                                                    density="compact"
                                                    type="month"
                                                    :rules="dateRules"
                                                    @keydown.enter.prevent="
                                                        focusNext(cvcRef)
                                                    "
                                                />
                                            </v-col>
                                            <v-col cols="6">
                                                <v-text-field
                                                    ref="cvcRef"
                                                    v-model="form.cvc"
                                                    label="安全碼"
                                                    variant="outlined"
                                                    density="compact"
                                                    placeholder="3 碼"
                                                    maxlength="3"
                                                    :rules="cvcRules"
                                                />
                                            </v-col>
                                        </v-row>
                                    </v-tabs-window-item>

                                    <v-tabs-window-item value="ATM">
                                        <div class="pa-2 text-body-2">
                                            <p class="mb-2">
                                                <b>銀行代碼：</b>玉山銀行 (808)
                                            </p>
                                            <p>
                                                <b>銀行帳號：</b
                                                >1234-5678-901234
                                            </p>
                                        </div>
                                    </v-tabs-window-item>
                                </v-tabs-window>
                            </v-card-text>
                        </v-card>

                        <v-card rounded="xl" elevation="2" class="mb-5">
                            <div
                                class="text-subtitle-1 font-weight-medium d-flex align-center ga-2 pt-4 pb-1 px-4"
                            >
                                <v-icon icon="mdi-ticket-outline" size="20" />
                                發票資訊
                            </div>
                            <v-card-text>
                                <v-select
                                    v-model="form.bill"
                                    :items="invoiceOptions"
                                    item-title="title"
                                    item-value="value"
                                    label="發票類型"
                                    variant="outlined"
                                    density="compact"
                                    placeholder="請選擇發票類型"
                                    :rules="billRules"
                                    class="mb-2"
                                />
                                <v-text-field
                                    v-if="form.bill === '公司行號'"
                                    ref="taxIdRef"
                                    v-model="form.taxId"
                                    label="統一編號"
                                    variant="outlined"
                                    density="compact"
                                    placeholder="請輸入 8 位統一編號"
                                    maxlength="8"
                                    :rules="taxIdRules"
                                    class="mb-2"
                                />
                                <v-text-field
                                    v-if="form.bill === '手機載具'"
                                    ref="carrierRef"
                                    v-model="form.carrier"
                                    label="載具號碼"
                                    variant="outlined"
                                    density="compact"
                                    placeholder="請輸入手機載具 (需包含 /) (例: /ABC1234)"
                                    :rules="carrierRules"
                                />
                            </v-card-text>
                        </v-card>
                    </v-col>

                    <v-col cols="12" md="4">
                        <v-card rounded="xl" elevation="3" class="sticky-card">
                            <div
                                class="text-subtitle-1 font-weight-medium d-flex align-center ga-2 pt-4 pb-1 px-4"
                            >
                                <v-icon
                                    icon="mdi-check-circle-outline"
                                    size="20"
                                />
                                訂單摘要
                            </div>

                            <v-card
                                class="ma-4"
                                rounded="lg"
                                variant="elevated"
                            >
                                <div
                                    v-if="appliedCoupon"
                                    class="pa-4 bg-lime-lighten-4"
                                    variant="tonal"
                                >
                                    <div
                                        class="d-flex align-center justify-space-between"
                                    >
                                        <div class="d-flex align-center ga-2">
                                            <v-icon color="success" size="18"
                                                >mdi-tag-check-outline</v-icon
                                            >
                                            <span
                                                class="text-body-2 font-weight-medium text-success"
                                                >{{ appliedCoupon.code }}</span
                                            >
                                        </div>
                                        <v-btn
                                            icon="mdi-close"
                                            size="x-small"
                                            variant="text"
                                            @click="removeCoupon"
                                        />
                                    </div>
                                    <div
                                        class="text-caption text-medium-emphasis mt-1"
                                    >
                                        已折抵 NT$ {{ discountAmount }}
                                    </div>
                                </div>

                                <template v-else>
                                    <v-text-field
                                        v-model="couponInput"
                                        label="請輸入折扣碼"
                                        variant="outlined"
                                        density="compact"
                                        :error-messages="couponError"
                                        class="ma-4 mb-2"
                                        @keydown.enter.prevent="applyCoupon"
                                    />
                                    <div class="px-4 pb-4">
                                        <v-btn
                                            block
                                            variant="outlined"
                                            color="primary"
                                            rounded="md"
                                            :loading="validatingCoupon"
                                            prepend-icon="mdi-ticket-percent"
                                            @click="applyCoupon"
                                        >
                                            套用折扣碼
                                        </v-btn>
                                    </div>
                                </template>
                            </v-card>

                            <v-card-text>
                                <div
                                    class="d-flex justify-space-between mb-2 text-body-2"
                                >
                                    <span>商品總計</span>
                                    <span>NT$ {{ totalPrice }}</span>
                                </div>
                                <div
                                    class="d-flex justify-space-between mb-2 text-body-2"
                                >
                                    <span>運費</span>
                                    <span>NT$ {{ SHIPPING_FEE }}</span>
                                </div>
                                <div
                                    v-if="appliedCoupon"
                                    class="d-flex justify-space-between mb-2 text-body-2"
                                >
                                    <span class="text-success">優惠碼折扣</span>
                                    <span class="text-success"
                                        >- NT$ {{ discountAmount }}</span
                                    >
                                </div>
                                <v-divider class="my-3" />
                                <div
                                    class="d-flex justify-space-between text-body-2"
                                >
                                    <b>應付總額</b>
                                    <b class="text-error"
                                        >NT$
                                        {{
                                            totalPrice +
                                            SHIPPING_FEE -
                                            discountAmount
                                        }}</b
                                    >
                                </div>
                            </v-card-text>
                            <v-card-actions class="px-4 pb-4">
                                <v-btn
                                    block
                                    variant="outlined"
                                    color="error"
                                    size="large"
                                    rounded="lg"
                                    :loading="isSubmitting"
                                    @click="submitOrder"
                                >
                                    確認下單
                                </v-btn>
                            </v-card-actions>
                        </v-card>
                    </v-col>
                </v-row>
            </v-form>
        </v-container>

        <v-snackbar
            v-model="snackbar.show"
            :color="snackbar.color"
            location="top"
            timeout="3000"
        >
            {{ snackbar.text }}
        </v-snackbar>
    </FrontLayout>
</template>

<style scoped>
.sticky-card {
    position: sticky;
    top: 80px;
}
</style>
