<script setup>
import { ref, onMounted, computed } from "vue";
import { useRouter } from "vue-router";
import {
  ElButton,
  ElCard,
  ElRow,
  ElCol,
  ElDivider,
  ElTabs,
  ElTabPane,
  ElInput,
  ElMessage,
} from "element-plus";
import api from "../../../api";

const cartItems = ref([]);
const totalPrice = ref(0);
const router = useRouter();
const checkoutFormRef = ref(null);

// 表單資料模型
const form = ref({
  name: "",
  phone: "",
  address: "",
  cardNumber: "",
  cvc: "",
  date: "",
  bill: "",
  taxId: "", // 統一編號
  carrier: "", // 手機載具
  paymentMethod: "Credit card", // 對應 tabs 的 name
});

// 驗證規則定義必填提示
const rules = computed(() => {
  // 基礎規則（不論選什麼都要填）
  const baseRules = {
    name: [{ required: true, message: "請輸入真實姓名", trigger: "blur" }],
    phone: [
      { required: true, message: "請輸入聯絡電話", trigger: "blur" },
      {
        pattern: /^09\d{8}$/,
        message: "請輸入正確的手機格式",
        trigger: "blur",
      },
    ],
    address: [{ required: true, message: "請輸入收件地址", trigger: "blur" }],
    bill: [{ required: true, message: "請選擇發票類型", trigger: "change" }],
  };

  // 發票額外資訊的驗證
  if (form.value.bill === "Option2") {
    // 公司行號：統編必填且須為 8 位數字
    baseRules.taxId = [
      { required: true, message: "請輸入統一編號", trigger: "blur" },
      { pattern: /^\d{8}$/, message: "統編格式應為 8 位數字", trigger: "blur" },
    ];
  } else if (form.value.bill === "Option3") {
    // 手機載具：必填且須符合斜線開頭格式
    baseRules.carrier = [
      {
        required: true,
        message: "請輸入載具號碼 (例: /ABC1234)",
        trigger: "blur",
      },
      {
        pattern: /^\/[A-Z0-9.+-]{7}$/,
        message: "載具格式錯誤 (例: /ABC1234)",
        trigger: "blur",
      },
    ];
  }

  // 如果選擇的是信用卡 (Credit card)，則加上信用卡的驗證規則
  if (form.value.paymentMethod === "Credit card") {
    return {
      ...baseRules,
      cardNumber: [
        { required: true, message: "請輸入信用卡號", trigger: "blur" },
        { min: 16, max: 16, message: "卡號應為 16 位數", trigger: "blur" },
      ],
      cvc: [
        { required: true, message: "請輸入安全碼", trigger: "blur" },
        { min: 3, max: 3, message: "應為 3 位數", trigger: "blur" },
      ],
      date: [
        { required: true, message: "請輸入卡片有效日期", trigger: "blur" },
      ],
    };
  }

  // 如果是 ATM (ATM)，則只返回基礎規則
  return baseRules;
});
// 發票
const options = [
  {
    value: "個人電子發票",
    label: "個人電子發票 (二聯式)",
  },
  {
    value: "公司行號",
    label: "公司行號 (三聯式)",
  },
  {
    value: "手機載具",
    label: "手機載具",
  },
];
// 抓取會員購物車中之商品(localstorage)
const loadCartItems = () => {
  const savedCart = localStorage.getItem("cart");
  if (savedCart) {
    cartItems.value = JSON.parse(savedCart);
    totalPrice.value = cartItems.value.reduce(
      (sum, item) => sum + item.price * item.quantity,
      0,
    );
  }
};
onMounted(() => {
  loadCartItems();
});

const handleTabChange = () => {
  if (checkoutFormRef.value) {
    // 切換分頁時，清除所有目前的驗證紅字提示
    checkoutFormRef.value.clearValidate(["cardNumber", "cvc", "date"]);
  }
};

// 表單驗證與提交邏輯
const submitOrder = async () => {
  if (!checkoutFormRef.value) return;

  // 1. 執行 Element Plus 的內建驗證
  await checkoutFormRef.value.validate(async (valid) => {
    if (!valid) {
      ElMessage.error("資訊填寫有誤，請檢查紅字標示欄位");
      return;
    }

    // 2. 驗證購物車是否有東西
    if (cartItems.value.length === 0) {
      ElMessage.warning("購物車是空的，無法下單喔！");
      return;
    }

    // 3. 提交訂單 API
    try {
      // 後端的驗證規則（Validation）必須跟前端的資料結構（Payload）完全對齊。
      const payload = {
        items: cartItems.value,
        total: totalPrice.value + 60, // 記得加運費
        customer: {
          name: form.value.name,
          phone: form.value.phone,
          address: form.value.address,
        },
        paymentMethod: form.value.paymentMethod,
        bill: form.value.bill,
        taxId: form.value.taxId,
        carrier: form.value.carrier,
      };

      const res = await api.post("/order_items", payload);

      // 4. 成功處理
      ElMessage.success("訂單已提交成功！");
      localStorage.removeItem("cart"); // 清空本地購物車
      router.push("/Shop");
    } catch (error) {
      console.error("訂單提交失敗:", error);
      ElMessage.error("伺服器連線失敗，請檢查網路或後端狀態");
    }
  });
};
</script>
<template>
  <div class="checkout-container">
    <h1 class="page-title">結帳頁面</h1>
    <div class="back-button">
      <el-button @click="$router.push('/cart')">返回購物車</el-button>
    </div>

    <el-form
      ref="checkoutFormRef"
      :model="form"
      :rules="rules"
      label-position="top"
    >
      <el-row :gutter="20">
        <el-col :xs="24" :sm="24" :md="16">
          <div class="left-section">
            <el-card class="mb-20">
              <template #header>
                <div class="card-header">
                  <span class="header-title">購買清單</span>
                </div>
              </template>
              <el-table :data="cartItems" style="width: 100%">
                <el-table-column prop="image" label="商品圖片">
                  <template #default="item">
                    <el-image
                      :src="item.row.image"
                      fit="cover"
                      style="width: 80px; height: 60px"
                    />
                  </template>
                </el-table-column>
                <el-table-column prop="name" label="商品名稱" />
                <el-table-column prop="quantity" label="數量" width="80" />
                <el-table-column prop="price" label="單價">
                  <template #default="item">NT$ {{ item.row.price }}</template>
                </el-table-column>
              </el-table>
            </el-card>

            <el-card class="mb-20">
              <template #header>
                <div class="card-header">
                  <span class="header-title">購買人訊息</span>
                </div>
              </template>
              <el-form-item label="姓名" prop="name">
                <el-input placeholder="請輸入真實姓名" v-model="form.name" />
              </el-form-item>
              <el-form-item label="手機號碼" prop="phone">
                <el-input placeholder="09xxxxxxxx" v-model="form.phone" />
              </el-form-item>
              <el-form-item label="收件地址" prop="address">
                <el-input
                  placeholder="請輸入詳細配送地址"
                  v-model="form.address"
                />
              </el-form-item>
            </el-card>

            <el-card class="mb-20">
              <template #header>
                <div class="card-header">
                  <span class="header-title">付款資訊與方式</span>
                </div>
              </template>
              <el-tabs
                v-model="form.paymentMethod"
                type="card"
                @tab-change="handleTabChange"
              >
                <el-tab-pane label="信用卡" name="Credit card">
                  <el-form-item label="信用卡號" prop="cardNumber">
                    <el-input
                      v-model="form.cardNumber"
                      maxlength="16"
                      placeholder="16位信用卡號"
                    />
                  </el-form-item>

                  <el-row :gutter="10">
                    <el-col :span="12">
                      <el-form-item label="有效日期" prop="date">
                        <el-date-picker
                          v-model="form.date"
                          type="month"
                          placeholder="MM/YY"
                          style="width: 100%"
                          value-format="YYYY-MM"
                        />
                      </el-form-item>
                    </el-col>
                    <el-col :span="12">
                      <el-form-item label="安全碼" prop="cvc">
                        <el-input
                          v-model="form.cvc"
                          maxlength="3"
                          placeholder="3 碼"
                        />
                      </el-form-item>
                    </el-col>
                  </el-row>
                </el-tab-pane>

                <el-tab-pane label="ATM轉帳" name="ATM">
                  <div class="atm-info">
                    <p><b>銀行代碼：</b>玉山銀行 (808)</p>
                    <p><b>銀行帳號：</b>1234-5678-901234</p>
                  </div>
                </el-tab-pane>
              </el-tabs>
            </el-card>

            <el-card class="mb-20">
              <template #header>
                <div class="card-header">
                  <span class="header-title">發票資訊</span>
                </div>
              </template>
              <el-form-item label="發票類型" prop="bill">
                <el-select
                  v-model="form.bill"
                  placeholder="請選擇發票類型"
                  style="width: 100%"
                >
                  <el-option
                    v-for="item in options"
                    :key="item.value"
                    :label="item.label"
                    :value="item.value"
                  />
                </el-select>
              </el-form-item>
              <el-form-item
                v-if="form.bill === '公司行號'"
                label="統一編號"
                prop="taxId"
              >
                <el-input
                  v-model="form.taxId"
                  placeholder="請輸入 8 位統一編號"
                  maxlength="8"
                />
              </el-form-item>
              <el-form-item
                v-if="form.bill === '手機載具'"
                label="載具號碼"
                prop="carrier"
              >
                <el-input
                  v-model="form.carrier"
                  placeholder="請輸入手機載具 (需包含 /) (例: /ABC1234)"
                />
              </el-form-item>
            </el-card>
          </div>
        </el-col>

        <el-col :xs="24" :sm="24" :md="8">
          <el-card shadow="always" class="sticky-card">
            <template #header>
              <div class="card-header">
                <span class="header-title">訂單摘要</span>
              </div>
            </template>
            <div class="summary-item">
              <span>商品總計</span>
              <span>NT$ {{ totalPrice }}</span>
            </div>
            <div class="summary-item">
              <span>運費</span>
              <span>NT$ 60</span>
            </div>
            <el-divider />
            <div class="total-price">
              <b><span>應付總額</span></b>
              <b class="amount">NT$ {{ totalPrice + 60 }}</b>
            </div>
            <el-button
              type="danger"
              size="large"
              class="checkout-btn"
              @click="submitOrder"
            >
              確認下單
            </el-button>
          </el-card>
        </el-col>
      </el-row>
    </el-form>
  </div>
</template>

<style scoped>
.mb-20 {
  margin-bottom: 20px;
}
.checkout-btn {
  width: 100%;
  margin-top: 20px;
}
.back-button {
  margin-bottom: 20px;
}
.form-item {
  margin-bottom: 20px;
}
label {
  display: block;
  margin-bottom: 10px;
  font-weight: bold;
}
.summary-item,
.total-price {
  display: flex;
  justify-content: space-between;
}
</style>
