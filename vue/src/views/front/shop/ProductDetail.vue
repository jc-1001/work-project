<script setup>
import { ref, onMounted } from "vue";
import { ElRow, ElCol, ElCard, ElImage, ElButton, ElMessage } from "element-plus";
import { useRoute } from "vue-router";
import api from "../../../api";

const product = ref({});
const route = useRoute();
const num = ref(1);
// 抓取商品資料
const fetchProductDetail = async () => {
  try {
    const id = route.params.id; // 從網址上取得商品 ID
    const response = await api.get(`/products/${id}`);
    product.value = response.data;
  } catch (error) {
    console.error("無法抓取商品資料:", error);
  }
};
// 商品數量監聽
const handleChange = (value) => {
//   console.log("當前商品選擇的數量:", value); // 測試
  if(value > product.value.stock){
    ElMessage.warning("庫存不足~ 無法再增加數量");
    num.value = product.value.stock; // 將數量限制在庫存範圍內
  }
};

// 加入購物車邏輯
const addToCart = () => {
    // 讀取localstorage中是否有購物車資料，沒有就給空陣列
    const cart = JSON.parse(localStorage.getItem("cart")) || [];

// 檢查是否已經買過這個商品了，如果有就更新數量，沒有就新增一筆
const index = cart.findIndex(item => item.id === product.value.id);

// 如果買過就增加數量
if (index !== -1) {
  cart[index].quantity += num.value;
} else {
  // 沒有就把新商品推進去
  cart.push({ 
    id: product.value.id,
    name: product.value.name,
    price: product.value.price,
    image: product.value.image,
    quantity: num.value
   });
}

// 將更新後的購物車資料儲存到 localStorage
localStorage.setItem("cart", JSON.stringify(cart));
ElMessage.success("已加入購物車!!!");
}

onMounted(() => {
  fetchProductDetail();
});
</script>
<template>
  <div class="product">
    <div class="back-shop">
      <el-button @click="$router.back()">返回商城</el-button>
    </div>

    <div v-if="!product.id">
      <el-empty v-if="!product.name" description="正在努力加載商品..." />
    </div>

    <el-row :gutter="40" style="margin-top: 20px">
      <el-col :md="12">
        <el-image :src="product.image" fit="contain" style="width: 100%" />
        <template #placeholder>
          <el-empty description="正在加載圖片..." />
        </template>
      </el-col>

        <el-col :md="12" class="good-des">
          <h1>{{ product.name }}</h1>
          <h2 style="color: #f56c6c">NT$ {{ product.price }}</h2>
          <p>商品描述：{{ product.description }}</p>
          <p>庫存數量：{{ product.stock }}</p>

          <div class="good-num">
            <el-input-number v-model="num" :min="1" :max="10" @change="handleChange" />
            <el-button type="primary" size="large" @click="addToCart">
              加入購物車
            </el-button>
          </div>

        </el-col>
      
    </el-row>
  </div>
</template>
<style >
  .back-shop {
    margin: 20px 0;
  }
  .good-des {
    border: 1px solid #eee;
    border-radius: 20px;
    display: flex;
    flex-direction: column;
    justify-content: center;
  }
  .good-num{
    display: flex;
    gap: 10px;
  }
</style>