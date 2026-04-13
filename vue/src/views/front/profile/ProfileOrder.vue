<script setup>
import { ref, onMounted } from "vue";
import { ElMessage } from "element-plus";
import api from "../../../api";

const orders = ref([]);
const loading = ref(false);

// 抓取訂單資料
const fetchOrders = async () => {
  loading.value = true;
  try {
    const res = await api.get("/orders");
    orders.value = res.data;
  } catch (err) {
    console.error("取得訂單失敗", err);
  } finally {
    loading.value = false;
  }
};

// 日期邏輯
const formatDate = (dateStr) => {
  if (!dateStr) return "";
  const d = new Date(dateStr);
  const pad = (n) => String(n).padStart(2, "0");
  return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())} ${pad(d.getHours())}:${pad(d.getMinutes())}:${pad(d.getSeconds())}`;
};

// 再買一次邏輯:訂單的商品塞回 localStorage 的購物車
const buyOneTime = (order) => {
  // 轉換型別加入localstorage中
  const cart = JSON.parse(localStorage.getItem("cart")) || [];

  order.items.forEach((item) => {
    // 用 product_id 在現有購物車裡找有沒有一樣的商品
    // findIndex 找到回傳該筆的索引值（0、1、2...），找不到回傳 -1
    const index = cart.findIndex((c) => c.id === item.product_id);

    // 如果回傳不是-1則跑下面迴圈
    if (index !== -1) {
      // 不是 -1 → 直接把數量加上去，不重複建立
      cart[index].quantity += item.quantity;
    } else {
      // 找不到 → 這是新商品，整筆推進購物車陣列
      cart.push({
        id: item.product_id,
        name: item.product_name,
        price: item.price,
        image: item.product?.image || "",
        quantity: item.quantity,
      });
    }
  });

  // 再轉換型別
  localStorage.setItem("cart", JSON.stringify(cart));
  ElMessage.success("已加入購物車！");
};

onMounted(() => {
  fetchOrders();
});
</script>

<template>
  <div class="orders-container" v-loading="loading">
    <h2>我的訂單紀錄</h2>

    <!-- 沒有訂單 -->
    <el-empty
      v-if="!loading && orders.length === 0"
      description="尚無訂單紀錄"
    />

    <div class="order-card" v-for="order in orders" :key="order.id">
      <div class="order-header">
        <span class="order-no">編號：{{ order.order_number }}</span>
        <span class="order-date">{{ formatDate(order.created_at) }}</span>
      </div>

      <el-divider />

      <div class="product-item" v-for="item in order.items" :key="item.id">
        <div class="product-info">
          <img
            :src="item.product?.image || 'https://picsum.photos/60/60'"
            class="item-img"
          />
          <div class="item-detail">
            <p class="name">{{ item.product_name }}</p>
            <p class="price">NT$ {{ item.price.toLocaleString() }}</p>
          </div>
        </div>
        <div class="item-qty">x {{ item.quantity }}</div>
      </div>

      <el-divider />

      <el-col :sm="24" class="text-right">
        <p class="total-label">總計金額</p>
        <p class="total-amount">
          NT$ {{ order.total_amount.toLocaleString() }}
        </p>
      </el-col>
      <el-divider />

      <el-row :gutter="20" class="order-footer">
        <el-col :sm="24">
          <div class="info-group">
            <h3>購買人資訊</h3>
            <p>購買人：{{ order.name }}</p>
            <p>電話：{{ order.phone }}</p>
            <p>地址：{{ order.address }}</p>
          </div>
        </el-col>
        <el-col :sm="24">
          <div class="info-group">
            <h3>付款資訊</h3>
            <p>付款方式：{{ order.payment_method }}</p>
            <p>發票類型：{{ order.invoice_type }}</p>
          </div>
        </el-col>

        <el-col :sm="24">
          <div class="onetime">
            <el-button type="success" size="large" @click="buyOneTime(order)"
              >再買一次</el-button
            >
          </div>
        </el-col>
      </el-row>
    </div>
  </div>
</template>
<style scoped>
.order-card {
  background: #fff;
  border: 1px solid #504f4f;
  border-radius: 8px;
  padding: 20px;
  margin-bottom: 25px;
}

.order-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-weight: bold;
  color: #666;
}
.order-date {
  text-align: end;
}
.product-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 5px 0;
}

.product-info {
  display: flex;
  gap: 15px;
  align-items: center;
}

.item-img {
  width: 60px;
  height: 60px;
  border-radius: 4px;
  object-fit: cover;
}
.order-footer {
  display: flex;
  flex-direction: column;
}
.info-group {
  border: 1px solid #ddd;
  border-radius: 4px;
  margin: 10px 0;
  padding: 10px;
  display: flex;
  flex-direction: column;
  align-items: start;
  justify-content: center;
  p {
    margin: 5px 0;
    font-size: 0.9rem;
    color: #666;
  }
}
.text-right {
  text-align: right;
}

.total-amount {
  font-size: 1.4rem;
  color: #d51717;
  font-weight: bold;
}
.onetime {
  text-align: right;
}
</style>
