<script setup>
import { ref, onMounted } from "vue";
import { ElRow, ElCol, ElCard, ElImage, ElButton } from "element-plus";
import { useRouter } from "vue-router";
import api from "../../../api";

// 最推薦的方法是利用 Laravel 內建的 Seeder 和 Factory。這比手動在資料庫打 SQL 指令快得多，而且欄位（如 created_at）會自動幫你填好。
const products = ref([]);
const router = useRouter();

// 抓取商品資料
const fetchProducts = async () => {
  try {
    const response = await api.get("/products");
    products.value = response.data;
  } catch (error) {
    console.error("無法抓取商品資料:", error);
  }
};

// 在組件掛載時抓取商品資料
onMounted(() => {
  fetchProducts();
});

// 跳轉到詳細頁
const goToProductDetail = (id) => {
  router.push({
    name: "ProductDetail",
    params: { id: id },
  });
};
</script>

<template>
  <h1>商城</h1>
  <el-row :gutter="20">
    <el-col
      v-for="product in products"
      :key="product.id"
      :xs="24"
      :sm="12"
      :md="8"
      :lg="6"
      style="margin-bottom: 20px"
    >
      <el-card shadow="hover">
        <template #header>
          <el-image
            :src="product.image"
            fit="cover"
            style="width: 100%; height: 200px"
          />
        </template>
        <h3>{{ product.name }}</h3>
        <p>NT$ {{ product.price }}</p>
        <el-button
          type="primary"
          size="medium"
          class="goods-button"
          @click="goToProductDetail(product.id)"
          >查看詳細</el-button
        >
      </el-card>
    </el-col>
  </el-row>
</template>
<style scoped>
.goods-button {
  margin-top: 10px;
  width: 100%;
}
</style>
