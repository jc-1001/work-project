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
            <el-button type="primary" size="large">加入購物車</el-button>
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