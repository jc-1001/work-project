<script setup>
import { ref, computed, onMounted, onUnmounted, watch, nextTick } from "vue"
import api from "../../bootstrap"
import { useAuth } from "../../composables/useAuth"
import { getImageUrl } from "../../utils/image"
import FrontLayout from "../../layouts/FrontLayout.vue"
import gsap from "gsap"
import { ScrollTrigger } from "gsap/ScrollTrigger"
import Lenis from "lenis"

gsap.registerPlugin(ScrollTrigger)

const window = globalThis

let lenis = null
let lenisTicker = null

const { fetchUser, user } = useAuth()
const isLoggedIn = computed(() => !!user.value)
const hotProducts = ref([])
const activeName = ref(undefined)
const snackbar = ref({ show: false, text: "", color: "success" })

const notify = (text, color = "success") => {
  snackbar.value = { show: true, text, color }
}

const banners = [
  { id: 1, image: "https://picsum.photos/800/500?random=1" },
  { id: 2, image: "https://picsum.photos/800/500?random=2" },
  { id: 3, image: "https://picsum.photos/800/500?random=3" },
  { id: 4, image: "https://picsum.photos/800/500?random=4" },
  { id: 5, image: "https://picsum.photos/800/500?random=5" },
]

const features = [
  { icon: "mdi-truck-delivery", title: "安全配送", desc: "防撞安全包裝，保證安全到您手中" },
  { icon: "mdi-lock",           title: "安全付款", desc: "多種付款方式，全程加密保護" },
  { icon: "mdi-gift-outline",   title: "多種折扣", desc: "不定期優惠特價，省您的荷包" },
]

const faqs = [
  {
    id: "1",
    q: "如何查看我的訂單狀態？",
    a: "登入會員後，點選右上角個人頭像進入「我的訂單」，即可查看所有訂單的最新狀態，包含待付款、處理中、已出貨及已完成。",
  },
  {
    id: "2",
    q: "可以修改或取消訂單嗎？",
    a: "訂單成立後 2 小時內可聯繫客服進行修改或取消。一旦進入出貨流程，將無法取消，請多加留意。",
  },
  {
    id: "3",
    q: "支援哪些付款方式？",
    a: "目前支援信用卡（Visa / MasterCard）、ATM 轉帳、超商代碼繳費及貨到付款，結帳時可依需求選擇。",
  },
  {
    id: "4",
    q: "商品可以退換貨嗎？",
    a: "收到商品後 7 天內，商品未拆封且保持原狀態，均可申請退換貨。請至「我的訂單」點選該筆訂單提出申請，客服將於 1 個工作天內回覆。",
  },
  {
    id: "5",
    q: "配送需要多久？會有物流追蹤嗎？",
    a: "一般訂單於付款確認後 1–3 個工作天出貨，台灣本島約 1–2 天到貨。出貨後系統將寄送物流追蹤連結至您的信箱，方便即時掌握配送進度。",
  },
]

// GSAP 捲動進場動畫（靜態區塊，DOM 掛載後立即註冊）
// scrollTrigger.trigger  ── 觸發動畫的目標元素
// scrollTrigger.start    ── "元素頂部 到達 視窗 80% 高度" 時觸發
// y / x                  ── 起始位移（px），動畫結束後回到原位
// opacity                ── 從透明淡入
// duration               ── 動畫秒數
// stagger                ── 多個子元素逐一延遲出現的間隔（秒）
function initStaticAnimations() {
  gsap.from(".features .section-title, .features .section-subtitle", {
    scrollTrigger: { trigger: ".features", start: "top 80%" },
    y: 40, opacity: 0, duration: 0.7, stagger: 0.15,
  })
  gsap.from(".feature-card", {
    scrollTrigger: { trigger: ".feature-grid", start: "top 80%" },
    y: 50, opacity: 0, duration: 0.6, stagger: 0.15,
  })
  gsap.from(".qa .section-title, .qa .section-subtitle", {
    scrollTrigger: { trigger: ".qa", start: "top 80%" },
    y: 40, opacity: 0, duration: 0.7, stagger: 0.15,
  })
  gsap.from(".qa-fold .v-expansion-panel", {
    scrollTrigger: { trigger: ".qa-fold", start: "top 85%" },
    x: -30, opacity: 0, duration: 0.5, stagger: 0.1,
  })
}


// 熱門商品(抓前五筆)
const fetchHotProducts = async () => {
  try {
    const res = await api.get("/products", { params: { per_page: 5 } })
    hotProducts.value = res.data.data.slice(0, 5)
  } catch {
    // 靜默失敗，熱門商品區塊不顯示
  }
}

// 熱門商品是非同步載入，等資料進 DOM 後再跑動畫
// once: true ── 只執行一次，避免重複觸發動畫
watch(hotProducts, async () => {
  await nextTick() // 等 Vue 將商品列表渲染進 DOM 才能找到 .product-card
  gsap.from(".hot-products .section-title, .hot-products .section-subtitle", {
    scrollTrigger: { trigger: ".hot-products", start: "top 80%" },
    y: 40, opacity: 0, duration: 0.7, stagger: 0.15,
  })
  gsap.from(".product-card", {
    scrollTrigger: { trigger: ".product-grid", start: "top 85%" },
    y: 50, opacity: 0, duration: 0.5, stagger: 0.1,
  })
}, { once: true })

onMounted(async () => {
  fetchUser()
  fetchHotProducts()

  // Lenis 捲動初始化
  // duration ── 慣性滑動時間（秒），越大越慢越絲滑
  // easing   ── 指數緩動曲線，模擬自然減速感
  lenis = new Lenis({ duration: 1.4, easing: (t) => Math.min(1, 1.001 - Math.pow(2, -10 * t)) })

  // 每次捲動時同步更新 ScrollTrigger 的捲動位置，確保進場動畫觸發點正確
  lenis.on("scroll", ScrollTrigger.update)

  // 將 Lenis 的 raf（requestAnimationFrame）掛進 GSAP ticker
  // gsap.ticker 已內建 rAF，統一驅動避免雙重 rAF 造成撕裂
  // time 單位是秒，lenis.raf 需要毫秒，所以 * 1000
  lenisTicker = (time) => lenis.raf(time * 1000)
  gsap.ticker.add(lenisTicker)

  // 關閉 GSAP 的延遲補償，讓捲動動畫在低幀率時不跳幀
  gsap.ticker.lagSmoothing(0)

  await nextTick()
  initStaticAnimations()
})

onUnmounted(() => {
  ScrollTrigger.getAll().forEach((t) => t.kill())
  gsap.ticker.remove(lenisTicker)
  lenis?.destroy()
  lenis = null
  lenisTicker = null
})

const handleLogout = async () => {
  try {
    await api.post("/logout")
    window.location.href = "/login"
  } catch {
    notify("登出失敗，請稍後再試", "error")
  }
}
</script>

<template>
  <FrontLayout>
  <main>
    <!-- Hero -->
    <section class="hero">
      <v-carousel
        height="100vh"
        cycle
        :interval="4000"
        hide-delimiters
        :show-arrows="false"
      >
        <v-carousel-item v-for="banner in banners" :key="banner.id">
          <v-img :src="banner.image" height="100%" cover />
        </v-carousel-item>
      </v-carousel>

      <div class="hero-overlay">
        <div class="hero-content">
          <p class="hero-eyebrow">Welcome to</p>
          <h1 class="hero-title">我的商城</h1>
          <p class="hero-slogan">精選好物，一站購齊，品質有保證</p>
          <div class="hero-actions">
            <template v-if="!isLoggedIn">
              <v-btn class="btn-primary" size="large" rounded="xl" @click="window.location.href = '/login'">
                立即加入
              </v-btn>
              <v-btn class="btn-ghost" size="large" rounded="xl" @click="window.location.href = '/shop'">
                瀏覽商品
              </v-btn>
            </template>
            <template v-else>
              <v-btn class="btn-primary" size="large" rounded="xl" @click="window.location.href = '/shop'">
                前往商城
              </v-btn>
              <v-btn class="btn-ghost" size="large" rounded="xl" @click="handleLogout">
                登出
              </v-btn>
            </template>
          </div>
        </div>
      </div>
    </section>

    <!-- Features -->
    <section class="features">
      <div class="section-inner">
        <h2 class="section-title">我們的保證</h2>
        <p class="section-subtitle">我們的良好信譽，絕對值得您安心!</p>
        <div class="feature-grid">
          <div v-for="f in features" :key="f.title" class="feature-card">
            <div class="feature-icon">
              <v-icon :icon="f.icon" size="36" color="#409eff" />
            </div>
            <h3>{{ f.title }}</h3>
            <p>{{ f.desc }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- 熱門商品 -->
    <section v-if="hotProducts.length" class="hot-products">
      <div class="section-inner">
        <h2 class="section-title">熱門商品</h2>
        <p class="section-subtitle">精選人氣好物，限時優惠中</p>
        <div class="product-grid">
          <div
            v-for="product in hotProducts"
            :key="product.id"
            class="product-card"
            @click="window.location.href = '/shop/' + product.id"
          >
            <div class="product-img-wrap">
              <v-img :src="getImageUrl(product.image)" cover class="product-img" />
            </div>
            <div class="product-info">
              <p class="product-name">{{ product.name }}</p>
              <p class="product-price">NT$ {{ Number(product.price).toLocaleString() }}</p>
            </div>
          </div>
        </div>
        <div class="section-footer">
          <v-btn class="btn-outline" size="large" rounded="xl" @click="window.location.href = '/shop'">
            查看全部商品
          </v-btn>
        </div>
      </div>
    </section>

    <!-- 常見問題 -->
    <section class="qa">
      <div class="section-inner">
        <h2 class="section-title">常見問題</h2>
        <p class="section-subtitle">快速解答您的疑難雜症</p>
        <div class="qa-fold">
          <v-expansion-panels v-model="activeName">
            <v-expansion-panel
              v-for="faq in faqs"
              :key="faq.id"
              :value="faq.id"
            >
              <v-expansion-panel-title>{{ faq.q }}</v-expansion-panel-title>
              <v-expansion-panel-text>{{ faq.a }}</v-expansion-panel-text>
            </v-expansion-panel>
          </v-expansion-panels>
        </div>
      </div>
    </section>

    <!-- Footer -->
    <footer class="site-footer">
      <div class="section-inner footer-inner">
        <div class="footer-brand">
          <h3>我的商城</h3>
          <p>精選好物，用心服務每一位顧客</p>
        </div>
        <div class="footer-contact">
          <h4>聯絡我們</h4>
          <ul>
            <li><v-icon icon="mdi-map-marker" size="16" />台北市信義區信義路五段7號</li>
            <li><v-icon icon="mdi-phone" size="16" />02-1234-5678</li>
            <li><v-icon icon="mdi-email-outline" size="16" />service@myshop.com</li>
          </ul>
        </div>
        <div class="footer-links">
          <h4>快速連結</h4>
          <ul>
            <li @click="window.location.href = '/login'">會員登入</li>
            <li @click="window.location.href = '/shop'">商城</li>
          </ul>
        </div>
      </div>
      <div class="footer-copy">© 2026 我的商城. All rights reserved.</div>
    </footer>
  </main>

  <v-snackbar v-model="snackbar.show" :color="snackbar.color" location="top" timeout="3000">
    {{ snackbar.text }}
  </v-snackbar>
  </FrontLayout>
</template>

<style scoped>
/* ── Hero ── */
.hero {
  position: relative;
  width: 100%;
}

.hero-overlay {
  position: absolute;
  inset: 0;
  background: linear-gradient(
    to bottom,
    rgba(0, 0, 0, 0.25) 0%,
    rgba(0, 0, 0, 0.55) 60%,
    rgba(0, 0, 0, 0.7) 100%
  );
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 10;
}

.hero-content {
  text-align: center;
  color: #fff;
  padding: 0 24px;
}

.hero-eyebrow {
  font-size: clamp(1rem, 2vw, 1.25rem);
  letter-spacing: 4px;
  text-transform: uppercase;
  opacity: 0.75;
  margin: 40px 0;
}

.hero-title {
  font-size: clamp(2.5rem, 6vw, 5rem);
  font-weight: 700;
  margin: 0 0 16px;
  letter-spacing: 2px;
  text-shadow: 0 2px 12px rgba(0, 0, 0, 0.4);
}

.hero-slogan {
  font-size: clamp(1rem, 2vw, 1.25rem);
  opacity: 0.9;
  margin: 40px 0;
  letter-spacing: 1px;
}

.hero-actions {
  display: flex;
  gap: 16px;
  justify-content: center;
  flex-wrap: wrap;
}

.btn-primary {
  background: #409eff !important;
  color: #fff !important;
  font-weight: 600;
  padding: 0 36px;
  transition: background 0.25s, transform 0.2s, box-shadow 0.25s;
}
.btn-primary:hover {
  background: #337ecc !important;
  transform: translateY(-2px);
  box-shadow: 0 8px 24px rgba(64, 158, 255, 0.45);
}

.btn-ghost {
  background: transparent !important;
  border: 2px solid rgba(255, 255, 255, 0.8) !important;
  color: #fff !important;
  font-weight: 600;
  padding: 0 36px;
  transition: background 0.25s, transform 0.2s;
}
.btn-ghost:hover {
  background: rgba(255, 255, 255, 0.15) !important;
  transform: translateY(-2px);
}

/* ── Shared ── */
.section-inner {
  max-width: 1100px;
  margin: 0 auto;
  padding: 0 24px;
}

/* ── Features ── */
.features, .qa {
  background: #f8f9fb;
  padding: 72px 0;
}

.feature-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
  gap: 32px;
}

.feature-card {
  background: #fff;
  border-radius: 16px;
  padding: 40px 32px;
  text-align: center;
  box-shadow: 0 2px 16px rgba(0, 0, 0, 0.06);
  transition: transform 0.25s, box-shadow 0.25s;
}
.feature-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
}

.feature-icon {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 72px;
  height: 72px;
  border-radius: 50%;
  background: #ecf5ff;
  color: #409eff;
  margin-bottom: 20px;
}

.feature-card h3 {
  font-size: 1.1rem;
  font-weight: 600;
  margin: 0 0 8px;
  color: #1a1a2e;
}

.feature-card p {
  font-size: 0.9rem;
  color: #666;
  margin: 0;
  line-height: 1.6;
}

/* ── Hot Products ── */
.hot-products {
  padding: 80px 0;
  background: #fff;
}

.section-title {
  font-size: 2rem;
  font-weight: 700;
  text-align: center;
  margin: 0 0 8px;
  color: #1a1a2e;
}

.section-subtitle {
  text-align: center;
  color: #888;
  margin: 0 0 48px;
  font-size: 0.95rem;
}

.product-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
  gap: 24px;
  margin-bottom: 48px;
}

.product-card {
  border-radius: 12px;
  overflow: hidden;
  border: 1px solid #eee;
  cursor: pointer;
  transition: transform 0.25s, box-shadow 0.25s;
  background: #fff;
}
.product-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 28px rgba(0, 0, 0, 0.1);
}

.product-img-wrap {
  width: 100%;
  aspect-ratio: 1;
  overflow: hidden;
}

.product-img {
  width: 100%;
  height: 100%;
  transition: transform 0.35s;
}
.product-card:hover .product-img {
  transform: scale(1.05);
}

.product-info {
  padding: 12px 14px;
}

.product-name {
  font-size: 0.9rem;
  color: #333;
  margin: 0 0 6px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.product-price {
  font-size: 1rem;
  font-weight: 700;
  color: #409eff;
  margin: 0;
}

.section-footer {
  text-align: center;
}

.btn-outline {
  border: 2px solid #409eff !important;
  color: #409eff !important;
  background: transparent !important;
  font-weight: 600;
  margin-top: 40px;
  padding: 0 40px;
  transition: background 0.25s, color 0.25s, transform 0.2s;
}
.btn-outline:hover {
  background: #409eff !important;
  color: #fff !important;
  transform: translateY(-2px);
}

/* ── QA ── */
.qa-fold {
  max-width: 80%;
  margin: 0 auto;
}

.qa-fold :deep(.v-expansion-panel) {
  margin-bottom: 12px;
  border-radius: 12px !important;
  overflow: hidden;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
  border: 1px solid #e8edf3;
  background: #fff;
}

.qa-fold :deep(.v-expansion-panel-title) {
  font-size: 1rem;
  font-weight: 600;
  color: #1a1a2e;
  padding: 20px 24px;
  line-height: 1.5;
  transition: background 0.2s, color 0.2s;
}

.qa-fold :deep(.v-expansion-panel-title:hover) {
  background: #f0f7ff;
  color: #409eff;
}

.qa-fold :deep(.v-expansion-panel-title--active) {
  color: #409eff;
  background: #f0f7ff;
  border-bottom: 1px solid #d9ecff;
}

.qa-fold :deep(.v-expansion-panel-text__wrapper) {
  padding: 16px 24px 20px;
  font-size: 0.95rem;
  color: #555;
  line-height: 1.8;
}

/* ── Footer ── */
.site-footer {
  background: #1a1a2e;
  color: #ccc;
  padding: 60px 0 0;
}

.footer-inner {
  display: grid;
  grid-template-columns: 2fr 1fr 1fr;
  gap: 40px;
  padding-bottom: 48px;
}

.footer-brand h3 {
  font-size: 1.4rem;
  font-weight: 700;
  color: #fff;
  margin: 0 0 12px;
}

.footer-brand p {
  font-size: 0.9rem;
  line-height: 1.7;
  color: #aaa;
  margin: 0;
}

.footer-contact h4,
.footer-links h4 {
  font-size: 1rem;
  font-weight: 600;
  color: #fff;
  margin: 0 0 16px;
}

.footer-contact ul,
.footer-links ul {
  list-style: none;
  padding: 0;
  margin: 0;
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.footer-contact li {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 0.88rem;
  color: #aaa;
}

.footer-links li {
  font-size: 0.88rem;
  color: #aaa;
  cursor: pointer;
  transition: color 0.2s;
}
.footer-links li:hover {
  color: #409eff;
}

.footer-copy {
  border-top: 1px solid rgba(255, 255, 255, 0.08);
  text-align: center;
  padding: 20px;
  font-size: 0.8rem;
  color: #666;
}

@media (max-width: 768px) {
  .footer-inner {
    grid-template-columns: 1fr;
    gap: 32px;
  }
  .qa-fold {
    max-width: 100%;
  }
}
</style>
