<script setup>
import { ref, onMounted } from "vue"
import { useRouter } from "vue-router"
import api from "../../../api"
import { getImageUrl } from "../../../utils/image"

const loading = ref(false)
const products = ref([])
const categories = ref([])
const selectedCategoryId = ref(null)
const total = ref(0)
const currentPage = ref(1)
const pageSize = ref(12)
const router = useRouter()

const fetchCategories = async () => {
  try {
    const res = await api.get("/categories")
    categories.value = res.data
  } catch (error) {
    console.error("無法抓取分類資料:", error)
  }
}

const fetchProducts = async () => {
  loading.value = true
  try {
    // 根據頁數回傳相應的類別id，當點擊到 '全部'(字串) 回傳 null
    const params = { page: currentPage.value, per_page: pageSize.value }
    if (typeof selectedCategoryId.value === "number")
      params.category_id = selectedCategoryId.value
    const response = await api.get("/products", { params })
    products.value = response.data.data
    total.value = response.data.total
  } catch (error) {
    console.error("無法抓取商品資料:", error)
  } finally {
    loading.value = false
  }
}

const onCategoryChange = () => {
  currentPage.value = 1
  fetchProducts()
}

// 換頁回頂部
const onPageChange = () => {
  fetchProducts()
  window.scrollTo({ top: 0, behavior: "smooth" })
}

onMounted(() => {
  fetchCategories()
  fetchProducts()
})

const goToProductDetail = (id) => {
  router.push({ name: "ProductDetail", params: { id } })
}
</script>

<template>
  <!-- 頁面 Banner -->
  <div class="shop-banner">
    <h1 class="shop-banner__title">商城</h1>
    <p class="shop-banner__subtitle">共 {{ total }} 件商品</p>
  </div>

  <!-- 藥丸形分類篩選 -->
  <div class="category-group">
    <button
      class="category-pill"
      :class="{ active: selectedCategoryId === null }"
      @click="selectedCategoryId = null; onCategoryChange()"
    >全部</button>
    <button
      v-for="cat in categories"
      :key="cat.id"
      class="category-pill"
      :class="{ active: selectedCategoryId === cat.id }"
      @click="selectedCategoryId = cat.id; onCategoryChange()"
    >{{ cat.name }}</button>
  </div>

  <!-- 骨架屏 loading -->
  <v-row v-if="loading" class="px-4 py-2">
    <v-col
      v-for="n in pageSize"
      :key="n"
      cols="12"
      sm="6"
      md="4"
      lg="3"
    >
      <v-skeleton-loader type="card" />
    </v-col>
  </v-row>

  <!-- 商品列表 -->
  <template v-else>
    <div v-if="products.length === 0" class="empty-state">
      <v-icon icon="mdi-package-variant-remove" size="64" color="grey-lighten-1" class="mb-3" />
      <p class="text-medium-emphasis">此分類目前無商品</p>
    </div>

    <v-row v-else class="px-4 py-2">
      <v-col
        v-for="product in products"
        :key="product.id"
        cols="12"
        sm="6"
        md="4"
        lg="3"
        class="mb-1"
      >
        <v-card class="product-card h-100" @click="goToProductDetail(product.id)">
          <div class="card-img-wrap">
            <v-img
              :src="getImageUrl(product.image)"
              height="200"
              cover
            />
            <v-chip
              v-if="product.stock === 0"
              color="error"
              size="small"
              class="sold-out-badge"
            >已售完</v-chip>
          </div>
          <v-card-text class="pb-1">
            <div class="product-name">{{ product.name }}</div>
            <div class="product-price">NT$ {{ Number(product.price).toLocaleString() }}</div>
          </v-card-text>
          <v-card-actions class="pt-0 px-4 pb-4">
            <v-btn
              block
              color="primary"
              variant="tonal"
              size="small"
              :disabled="product.stock === 0"
              @click.stop="goToProductDetail(product.id)"
            >查看詳細</v-btn>
          </v-card-actions>
        </v-card>
      </v-col>
    </v-row>
  </template>

  <v-pagination
    v-if="total > pageSize"
    v-model="currentPage"
    :length="Math.ceil(total / pageSize)"
    density="comfortable"
    class="my-4"
    @update:model-value="onPageChange"
  />
</template>

<style scoped>
/* ── Banner ── */
.shop-banner {
  background-color: #0f3460;
  color: #fff;
  text-align: center;
  padding: 48px 24px 40px;
  margin-bottom: 8px;
}
.shop-banner__title {
  font-size: 2rem;
  font-weight: 700;
  margin: 0 0 8px;
  letter-spacing: 2px;
}
.shop-banner__subtitle {
  font-size: 0.95rem;
  opacity: 0.7;
  margin: 0;
}

/* ── 藥丸分類 ── */
.category-group {
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
  gap: 10px;
  padding: 24px 16px;
}
.category-pill {
  padding: 6px 20px;
  border-radius: 999px;
  border: 1.5px solid #d0d7e3;
  background: #fff;
  color: #555;
  font-size: 0.875rem;
  cursor: pointer;
  transition: background 0.2s, color 0.2s, border-color 0.2s, box-shadow 0.2s;
}
.category-pill:hover {
  border-color: #409eff;
  color: #409eff;
}
.category-pill.active {
  background: #409eff;
  border-color: #409eff;
  color: #fff;
  box-shadow: 0 2px 10px rgba(64, 158, 255, 0.35);
}

/* ── 空狀態 ── */
.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 60px 0;
  color: #999;
}

/* ── 商品卡片 ── */
.product-card {
  cursor: pointer;
  transition: transform 0.25s, box-shadow 0.25s;
}
.product-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 28px rgba(0, 0, 0, 0.12) !important;
}
.card-img-wrap {
  position: relative;
}
.sold-out-badge {
  position: absolute;
  top: 8px;
  left: 8px;
}
.product-name {
  font-size: 0.9rem;
  color: #333;
  margin-bottom: 4px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.product-price {
  font-size: 1rem;
  font-weight: 700;
  color: #409eff;
}
</style>
