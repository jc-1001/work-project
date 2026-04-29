<script setup>
import { ref, computed, onMounted } from "vue"
import { useRoute, useRouter } from "vue-router"
import api from "../../../api"
import { getImageUrl } from "../../../utils/image"
import { useAuth } from "../../../composables/useAuth"

const product = ref({})
const route = useRoute()
const router = useRouter()
const num = ref(1)
const { fetchUser, isLoggedIn } = useAuth()
const loginDialog = ref(false)
const snackbar = ref({ show: false, text: "", color: "success" })
const loading = ref(true);

const notify = (text, color = "success") => {
  snackbar.value = { show: true, text, color }
}

const stockStatus = computed(() => {
  const s = product.value.stock ?? 0
  if (s === 0)  return { label: "已售完", color: "error" }
  if (s <= 5)   return { label: `僅剩 ${s} 件`, color: "warning" }
  return { label: "庫存充足", color: "success" }
})

const fetchProductDetail = async () => {
  loading.value = true
  try {
    const res = await api.get(`/products/${route.params.id}`)
    product.value = res.data
  } catch {
    notify("無法載入商品資訊", "error")
  } finally {
    loading.value = false;
  }
}

const changeNum = (delta) => {
  const next = num.value + delta
  if (next < 1) return
  if (next > product.value.stock) {
    notify("已達最大庫存數量", "warning")
    return
  }
  num.value = next
}

const addToCart = async () => {
  await fetchUser()
  if (!isLoggedIn()) {
    loginDialog.value = true
    return
  }

  const cart = JSON.parse(localStorage.getItem("cart")) || []
  const index = cart.findIndex((item) => item.id === product.value.id)

  if (index !== -1) {
    cart[index].quantity += num.value
  } else {
    cart.push({
      id: product.value.id,
      name: product.value.name,
      price: product.value.price,
      image: product.value.image,
      quantity: num.value,
    })
  }

  localStorage.setItem("cart", JSON.stringify(cart))
  notify("已加入購物車！")
}

onMounted(() => {
  fetchProductDetail()
})
</script>

<template>
  <div class="detail-page">
    <!-- 麵包屑 -->
    <nav class="breadcrumb">
      <span class="breadcrumb-link" @click="router.push('/')">首頁</span>
      <span class="breadcrumb-sep">›</span>
      <span class="breadcrumb-link" @click="router.push('/shop')">商城</span>
      <span class="breadcrumb-sep">›</span>
      <span>{{ product.name ?? '載入中...' }}</span>
    </nav>

    <div v-if="loading" class="text-center pa-12">
    <v-progress-circular
      :size="70"
      :width="7"
      color="primary"
      indeterminate />
      <p>正在努力加載商品...</p>
  </div>

    <div v-else class="detail-layout">
      <!-- 左：圖片 -->
      <div class="img-wrap">
        <v-img
          :src="getImageUrl(product.image)"
          cover
          class="product-img"
        />
      </div>

      <!-- 右：資訊 -->
      <div class="info-wrap">
        <h1 class="product-name">{{ product.name }}</h1>

        <div class="price-row">
          <span class="price">NT$ {{ Number(product.price).toLocaleString() }}</span>
        </div>

        <div class="stock-row">
          <v-chip :color="stockStatus.color" size="large" rounded="xl">
            {{ stockStatus.label }}
          </v-chip>
        </div>

        <div class="desc-block">
          <p>{{ product.description }}</p>
        </div>

        <div class="action-row">
          <!-- 數量選擇 -->
          <div class="d-flex align-center ga-2">
            <v-btn
              icon="mdi-minus"
              variant="outlined"
              size="small"
              :disabled="product.stock === 0 || num <= 1"
              @click="changeNum(-1)"
            />
            <span class="text-h6 mx-1">{{ num }}</span>
            <v-btn
              icon="mdi-plus"
              variant="outlined"
              size="small"
              :disabled="product.stock === 0 || num >= product.stock"
              @click="changeNum(1)"
            />
          </div>

          <v-btn
            class="cart-btn"
            size="large"
            :disabled="product.stock === 0"
            @click="addToCart"
          >
            <v-icon icon="mdi-cart" class="mr-2" />
            加入購物車
          </v-btn>
        </div>

        <v-btn variant="text" class="back-btn" @click="router.push('/shop')">
          ← 繼續逛逛
        </v-btn>
      </div>
    </div>
  </div>

  <!-- 登入確認 Dialog -->
  <v-dialog v-model="loginDialog" max-width="360">
    <v-card rounded="xl">
      <v-card-title class="pt-5 px-5">提示</v-card-title>
      <v-card-text class="px-5">您尚未登入，是否要前往登入頁面？</v-card-text>
      <v-card-actions class="px-4 pb-4">
        <v-spacer />
        <v-btn variant="text" @click="loginDialog = false">取消</v-btn>
        <v-btn color="primary" variant="tonal" @click="router.push('/login')">前往登入</v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>

  <v-snackbar v-model="snackbar.show" :color="snackbar.color" location="top" timeout="3000">
    {{ snackbar.text }}
  </v-snackbar>
</template>

<style scoped>
.detail-page {
  max-width: 1100px;
  margin: 0 auto;
  padding: 24px 24px 64px;
}

/* ── 麵包屑 ── */
.breadcrumb {
  display: flex;
  align-items: center;
  gap: 6px;
  margin-bottom: 32px;
  font-size: 0.9rem;
  color: #888;
}
.breadcrumb-link {
  color: #409eff;
  cursor: pointer;
}
.breadcrumb-link:hover {
  text-decoration: underline;
}
.breadcrumb-sep {
  color: #ccc;
}

/* ── 主佈局 ── */
.detail-layout {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 56px;
  align-items: start;
}

/* ── 圖片 ── */
.img-wrap {
  border-radius: 20px;
  overflow: hidden;
  box-shadow: 0 4px 24px rgba(0, 0, 0, 0.1);
  aspect-ratio: 1;
}

.product-img {
  width: 100%;
  height: 100%;
}

/* ── 資訊 ── */
.info-wrap {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.product-name {
  font-size: 1.8rem;
  font-weight: 700;
  color: #1a1a2e;
  margin: 0;
  line-height: 1.3;
}

.price-row {
  display: flex;
  align-items: center;
  gap: 12px;
}

.price {
  font-size: 2rem;
  font-weight: 700;
  color: #f56c6c;
}

.stock-row {
  display: flex;
  align-items: center;
}

/* ── 描述區塊 ── */
.desc-block {
  border-left: 4px solid #409eff;
  background: #f5f9ff;
  border-radius: 0 8px 8px 0;
  padding: 14px 18px;
}

.desc-block p {
  margin: 0;
  font-size: 0.95rem;
  color: #555;
  line-height: 1.8;
}

/* ── 操作列 ── */
.action-row {
  display: flex;
  align-items: center;
  gap: 16px;
  flex-wrap: wrap;
}

.cart-btn {
  flex: 1;
  min-width: 160px;
  background: #409eff !important;
  color: #fff !important;
  font-size: 1rem;
  font-weight: 600;
  height: 44px;
  transition: background 0.25s, transform 0.2s, box-shadow 0.25s;
}
.cart-btn:hover:not(:disabled) {
  background: #337ecc !important;
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(64, 158, 255, 0.4);
}

/* ── 繼續逛逛 ── */
.back-btn {
  align-self: flex-start;
  color: #888 !important;
  font-size: 0.9rem;
  padding: 0;
  transition: color 0.2s;
}
.back-btn:hover {
  color: #409eff !important;
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to   { transform: rotate(360deg); }
}

@media (max-width: 768px) {
  .detail-layout {
    grid-template-columns: 1fr;
    gap: 32px;
  }
}
</style>
