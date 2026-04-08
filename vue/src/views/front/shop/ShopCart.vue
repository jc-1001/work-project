<script setup>
import { ref, onMounted, patchProp, watch, computed } from "vue";
import { ElImage, ElButton, ElTable, ElTableColumn, ElMessage } from "element-plus";
import { useRouter } from "vue-router";
import api from "../../../api";

const cartItem = ref([]);
const router = useRouter();
const num = ref(1);

// 從 localStorage 讀取購物車資料
const loadCart = () => {
  const saveCart = localStorage.getItem("cart");
  if (saveCart) {
    // 將字串轉回物件
    cartItem.value = JSON.parse(saveCart);
  }
};

// 計算總金額(computed)
const totalPrice = computed(() => {
  return cartItem.value.reduce((sum, item) => {
    // 數量 + (單價 * 單一商品數量)
    return sum + item.price * item.quantity;
  }, 0);
});

// 刪除商品
const removeFromCart = (id) => {
  // 使用 filter 過濾掉該 ID，產生新陣列
  cartItem.value = cartItem.value.filter(item => item.id !== id);
  // 同步到 localStorage
  updateLocalStorage();
  ElMessage.success("已移除商品");
};

// 數量改變同時更新local Storage
const updateLocalStorage = () =>{
    // 將購物車資料轉成字串並儲存到 localStorage
    localStorage.setItem("cart", JSON.stringify(cartItem.value));
}

onMounted(() => {
  loadCart();
});

// 點前往結帳邏輯
const checkout = () => {
  router.push('/order');
};
</script>
<template>
  <div class="cart-container">
    <h1>我的購物車</h1>
    <div class="back-shop">
      <el-button @click="$router.push('/Shop')">返回商城繼續購物</el-button>
    </div>

    <!-- 購物車項目內容 -->
    <div v-if="cartItem.length > 0">
      <el-table :data="cartItem" style="width: 100%">
        <el-table-column label="商品縮圖">
          <template #default="scope">
            <el-image
              :src="scope.row.image"
              fit="cover"
              style="width: 80px; height: 60px"
            />
          </template>
        </el-table-column>

        <el-table-column prop="name" label="商品名稱"></el-table-column>
        <el-table-column prop="price" label="單價"></el-table-column>
        <el-table-column label="數量">
          <template #default="scope">
            <el-input-number
              v-model="scope.row.quantity"
              :min="1"
              :max="10"
              size="small"
              @change="updateLocalStorage"
            />
          </template>
        </el-table-column>

        <el-table-column label="小計">
          <template #default="scope">
            NT {{ scope.row.price * scope.row.quantity }}
          </template>
        </el-table-column>

        <el-table-column label="操作">
          <template #default="scope">
            <el-button
              type="danger"
              size="small"
              @click="removeFromCart(scope.row.id)"
            >
              移除
            </el-button>
          </template>
        </el-table-column>
      </el-table>

      <div class="cart-footer">
        <h3>
          總金額：<span style="color: #f56c6c">NT$ {{ totalPrice }}</span>
        </h3>
        <el-button type="success" size="large" @click="checkout">前往結帳</el-button>
      </div>
    </div>

    <el-empty v-else description="購物車是空的，快去逛逛吧！" />
  </div>
</template>
