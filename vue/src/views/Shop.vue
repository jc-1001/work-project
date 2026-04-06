<script setup>
import { ref } from 'vue';
import { ElRow, ElCol, ElCard, ElImage, ElButton } from 'element-plus';

const products = ref([
  { id: 1, name: '商品一', price: 999, image: 'https://via.placeholder.com/300x200' },
  { id: 2, name: '商品二', price: 1299, image: 'https://via.placeholder.com/300x200' },
  { id: 3, name: '商品三', price: 599, image: 'https://via.placeholder.com/300x200' },
  { id: 4, name: '商品四', price: 2499, image: 'https://via.placeholder.com/300x200' },
  { id: 5, name: '商品五', price: 1999, image: 'https://via.placeholder.com/300x200' },
        
])
</script>

<template>
    <h1>商城</h1>
    <el-row :gutter="20">
  <el-col :span="6" v-for="product in products" :key="product.id">
    <el-card shadow="hover">
      <template #header>
        <el-image :src="product.image" fit="cover" style="width: 100%; height: 200px" />
      </template>
      <h3>{{ product.name }}</h3>
      <p>NT$ {{ product.price }}</p>
      <el-button type="primary" size="small">加入購物車</el-button>
    </el-card>
  </el-col>
</el-row>
</template>