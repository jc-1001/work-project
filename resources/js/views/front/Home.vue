<script setup>
import { ref, computed, onMounted, onUnmounted, watch, nextTick } from "vue";
import api from "../../bootstrap";
import { useAuth } from "../../composables/useAuth";
import { getImageUrl } from "../../utils/image";
import FrontLayout from "../../layouts/FrontLayout.vue";
import gsap from "gsap";
import { ScrollTrigger } from "gsap/ScrollTrigger";
import Lenis from "lenis";

gsap.registerPlugin(ScrollTrigger);

const window = globalThis;

let lenis = null;
let lenisTicker = null;

const { user, fetchUser } = useAuth();
const isLoggedIn = computed(() => !!user.value);
const hotProducts = ref([]);
const activeName = ref(undefined);
const snackbar = ref({ show: false, text: "", color: "success" });

const weather = ref(null);
const weatherLoading = ref(true);
const weatherError = ref(false);

const WMO_CODES = {
    0: { label: "晴天", icon: "mdi-weather-sunny" },
    1: { label: "大致晴朗", icon: "mdi-weather-partly-cloudy" },
    2: { label: "部分多雲", icon: "mdi-weather-partly-cloudy" },
    3: { label: "陰天", icon: "mdi-weather-cloudy" },
    45: { label: "霧", icon: "mdi-weather-fog" },
    48: { label: "結霜霧", icon: "mdi-weather-fog" },
    51: { label: "毛毛雨", icon: "mdi-weather-rainy" },
    53: { label: "毛毛雨", icon: "mdi-weather-rainy" },
    55: { label: "濃毛毛雨", icon: "mdi-weather-rainy" },
    61: { label: "小雨", icon: "mdi-weather-rainy" },
    63: { label: "中雨", icon: "mdi-weather-rainy" },
    65: { label: "大雨", icon: "mdi-weather-pouring" },
    71: { label: "小雪", icon: "mdi-weather-snowy" },
    73: { label: "中雪", icon: "mdi-weather-snowy" },
    75: { label: "大雪", icon: "mdi-weather-snowy-heavy" },
    80: { label: "陣雨", icon: "mdi-weather-partly-rainy" },
    81: { label: "陣雨", icon: "mdi-weather-partly-rainy" },
    82: { label: "強陣雨", icon: "mdi-weather-pouring" },
    95: { label: "雷陣雨", icon: "mdi-weather-lightning-rainy" },
    96: { label: "雷雨夾冰雹", icon: "mdi-weather-hail" },
    99: { label: "強雷雨夾冰雹", icon: "mdi-weather-hail" },
};

function getWeatherInfo(code) {
    return WMO_CODES[code] ?? { label: "未知", icon: "mdi-weather-cloudy" };
}

const fetchWeather = () => {
    weatherLoading.value = true;
    weatherError.value = false;
    fetch(
        "https://api.open-meteo.com/v1/forecast?latitude=25.05&longitude=121.53&current=temperature_2m,weathercode,windspeed_10m,relativehumidity_2m&timezone=Asia/Taipei",
    )
        .then((res) => res.json())
        .then((data) => {
            weather.value = data.current;
        })
        .catch(() => {
            weatherError.value = true;
        })
        .finally(() => {
            weatherLoading.value = false;
        });
};

const notify = (text, color = "success") => {
    snackbar.value = { show: true, text, color };
};

const banners = [
    { id: 1, image: "https://picsum.photos/800/500?random=1" },
    { id: 2, image: "https://picsum.photos/800/500?random=2" },
    { id: 3, image: "https://picsum.photos/800/500?random=3" },
    { id: 4, image: "https://picsum.photos/800/500?random=4" },
    { id: 5, image: "https://picsum.photos/800/500?random=5" },
];

const features = [
    {
        icon: "mdi-truck-delivery",
        title: "安全配送",
        desc: "防撞包裝，保證安全到您手中",
    },
    { icon: "mdi-lock", title: "安全付款", desc: "多種付款方式，全程加密保護" },
    {
        icon: "mdi-gift-outline",
        title: "多種折扣",
        desc: "不定期優惠特價，省您的荷包",
    },
];

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
];

function initStaticAnimations() {
    gsap.from(".features .section-title, .features .section-subtitle", {
        scrollTrigger: { trigger: ".features", start: "top 80%" },
        y: 40,
        opacity: 0,
        duration: 0.7,
        stagger: 0.15,
    });
    gsap.from(".feature-card", {
        scrollTrigger: { trigger: ".feature-grid", start: "top 80%" },
        y: 50,
        opacity: 0,
        duration: 0.6,
        stagger: 0.15,
    });
    gsap.from(".qa .section-title, .qa .section-subtitle", {
        scrollTrigger: { trigger: ".qa", start: "top 80%" },
        y: 40,
        opacity: 0,
        duration: 0.7,
        stagger: 0.15,
    });
    gsap.from(".qa-fold .v-expansion-panel", {
        scrollTrigger: { trigger: ".qa-fold", start: "top 85%" },
        x: -30,
        opacity: 0,
        duration: 0.5,
        stagger: 0.1,
    });
}

const fetchHotProducts = () => {
    api.get("/products", { params: { per_page: 5 } })
        .then((res) => {
            hotProducts.value = res.data.data;
        })
        .catch(() => {
            // 靜默失敗
        });
};

watch(
    hotProducts,
    async () => {
        await nextTick(); 
        gsap.from(
            ".hot-products .section-title, .hot-products .section-subtitle",
            {
                scrollTrigger: { trigger: ".hot-products", start: "top 80%" },
                y: 40,
                opacity: 0,
                duration: 0.7,
                stagger: 0.15,
            },
        );
        gsap.from(".product-card", {
            scrollTrigger: { trigger: ".product-grid", start: "top 85%" },
            y: 50,
            opacity: 0,
            duration: 0.5,
            stagger: 0.1,
        });
    },
    { once: true },
);

onMounted(async () => {
    fetchUser();
    fetchHotProducts();
    fetchWeather();

    lenis = new Lenis({
        duration: 1.4,
        easing: (t) => Math.min(1, 1.001 - Math.pow(2, -10 * t)),
    });

    lenis.on("scroll", ScrollTrigger.update);

    lenisTicker = (time) => lenis.raf(time * 1000);
    gsap.ticker.add(lenisTicker);

    gsap.ticker.lagSmoothing(0);

    await nextTick();
    initStaticAnimations();
});

onUnmounted(() => {
    ScrollTrigger.getAll().forEach((t) => t.kill());
    gsap.ticker.remove(lenisTicker);
    lenis?.destroy();
    lenis = null;
    lenisTicker = null;
});

const handleLogout = () => {
    api.post("/logout")
        .then(() => {
            window.location.href = "/login";
        })
        .catch(() => {
            notify("登出失敗，請稍後再試", "error");
        });
};

const goTo = (url) => {
    window.location.href = url;
};
</script>

<template>
    <FrontLayout>
        <main>
            <section class="position-relative w-100">
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

                <div
                    class="position-absolute d-flex align-center justify-center"
                    style="
                        inset: 0;
                        background: linear-gradient(
                            to bottom,
                            rgba(0, 0, 0, 0.25) 0%,
                            rgba(0, 0, 0, 0.55) 60%,
                            rgba(0, 0, 0, 0.7) 100%
                        );
                        z-index: 10;
                    "
                >
                    <div class="text-center text-white px-6">
                        <p
                            style="
                                font-size: clamp(1rem, 2vw, 1.25rem);
                                letter-spacing: 4px;
                                text-transform: uppercase;
                                opacity: 0.75;
                                margin: 40px 0;
                            "
                        >
                            Welcome to
                        </p>
                        <h1
                            class="font-weight-bold"
                            style="
                                font-size: clamp(2.5rem, 6vw, 5rem);
                                margin: 0 0 16px;
                                letter-spacing: 2px;
                                text-shadow: 0 2px 12px rgba(0, 0, 0, 0.4);
                            "
                        >
                            我的商城
                        </h1>
                        <p
                            style="
                                font-size: clamp(1rem, 2vw, 1.25rem);
                                opacity: 0.9;
                                margin: 40px 0;
                                letter-spacing: 1px;
                            "
                        >
                            精選好物，一站購齊，品質有保證
                        </p>
                        <div class="d-flex ga-4 justify-center flex-wrap">
                            <template v-if="!isLoggedIn">
                                <v-btn
                                    class="btn-primary"
                                    color="primary"
                                    variant="flat"
                                    size="large"
                                    rounded="xl"
                                    @click="goTo('/login')"
                                    >立即加入</v-btn
                                >
                                <v-btn
                                    class="btn-ghost"
                                    color="white"
                                    variant="outlined"
                                    size="large"
                                    rounded="xl"
                                    @click="goTo('/shop')"
                                    >瀏覽商品</v-btn
                                >
                            </template>
                            <template v-else>
                                <v-btn
                                    class="btn-primary"
                                    color="primary"
                                    variant="flat"
                                    size="large"
                                    rounded="xl"
                                    @click="goTo('/shop')"
                                    >前往商城</v-btn
                                >
                                <v-btn
                                    class="btn-ghost"
                                    color="white"
                                    variant="outlined"
                                    size="large"
                                    rounded="xl"
                                    @click="handleLogout"
                                    >登出</v-btn
                                >
                            </template>
                        </div>

                        <div
                            v-if="weather && !weatherLoading"
                            class="d-inline-flex align-center ga-2 mt-6 px-5 py-2"
                            style="
                                border-radius: 50px;
                                background: rgba(255, 255, 255, 0.15);
                                backdrop-filter: blur(8px);
                                border: 1px solid rgba(255, 255, 255, 0.25);
                                font-size: 0.88rem;
                                color: rgba(255, 255, 255, 0.9);
                            "
                        >
                            <v-icon
                                :icon="getWeatherInfo(weather.weathercode).icon"
                                size="24"
                            />
                            <span
                                >台北
                                {{ Math.round(weather.temperature_2m) }}°C</span
                            >
                            <span style="opacity: 0.45">|</span>
                            <span>{{
                                getWeatherInfo(weather.weathercode).label
                            }}</span>
                            <span style="opacity: 0.45">|</span>
                            <span>濕度 {{ weather.relativehumidity_2m }}%</span>
                        </div>
                    </div>
                </div>
            </section>

            <section class="features bg-grey-lighten-5" style="padding: 72px 0">
                <div class="mx-auto px-6" style="max-width: 1100px">
                    <h2
                        class="section-title font-weight-bold text-center ma-0 mb-2"
                        style="font-size: 2rem; color: #1a1a2e"
                    >
                        我們的保證
                    </h2>
                    <p
                        class="section-subtitle text-center ma-0 mb-12"
                        style="color: #888; font-size: 0.95rem"
                    >
                        我們的良好信譽，絕對值得您安心!
                    </p>
                    <v-row no-gutters class="feature-grid ga-8">
                        <v-col
                            v-for="f in features"
                            :key="f.title"
                            cols="12"
                            sm
                        >
                        <div class="feature-card rounded-xl pa-10 text-center bg-white elevation-2 h-100">
                            <div
                                class="d-inline-flex align-center justify-center rounded-circle mb-5"
                                style="
                                    width: 72px;
                                    height: 72px;
                                    background: #ecf5ff;
                                "
                            >
                                <v-icon
                                    :icon="f.icon"
                                    size="36"
                                    color="#409eff"
                                />
                            </div>
                            <h3
                                class="ma-0 mb-2"
                                style="
                                    font-size: 1.1rem;
                                    font-weight: 600;
                                    color: #1a1a2e;
                                "
                            >
                                {{ f.title }}
                            </h3>
                            <p
                                style="
                                    font-size: 0.9rem;
                                    color: #666;
                                    margin: 0;
                                    line-height: 1.6;
                                "
                            >
                                {{ f.desc }}
                            </p>
                        </div>
                        </v-col>
                    </v-row>
                </div>
            </section>

            <section
                v-if="hotProducts.length"
                class="hot-products bg-white"
                style="padding: 80px 0"
            >
                <div class="mx-auto px-6" style="max-width: 1100px">
                    <h2
                        class="section-title font-weight-bold text-center ma-0 mb-2"
                        style="font-size: 2rem; color: #1a1a2e"
                    >
                        熱門商品
                    </h2>
                    <p
                        class="section-subtitle text-center ma-0 mb-12"
                        style="color: #888; font-size: 0.95rem"
                    >
                        精選人氣好物，限時優惠中
                    </p>
                    <v-row class="product-grid mb-12">
                        <v-col
                            v-for="product in hotProducts"
                            :key="product.id"
                            cols="12"
                            sm="6"
                            md
                            style="min-width: 0"
                        >
                            <div
                                class="product-card rounded-lg overflow-hidden bg-white h-100"
                                style="border: 1px solid #eee; cursor: pointer"
                                @click="goTo('/shop/' + product.id)"
                            >
                                <div class="w-100 overflow-hidden" style="aspect-ratio: 1">
                                    <v-img
                                        :src="getImageUrl(product.image)"
                                        cover
                                        class="product-img w-100 h-100"
                                    />
                                </div>
                                <div style="padding: 12px 14px">
                                    <p
                                        class="ma-0 mb-1 text-truncate"
                                        style="font-size: 0.9rem; color: #333"
                                    >
                                        {{ product.name }}
                                    </p>
                                    <p
                                        class="font-weight-bold ma-0"
                                        style="font-size: 1rem; color: #409eff"
                                    >
                                        NT$ {{ Number(product.price).toLocaleString() }}
                                    </p>
                                </div>
                            </div>
                        </v-col>
                    </v-row>
                    <div class="text-center">
                        <v-btn
                            class="btn-outline mt-10 px-10"
                            color="primary"
                            variant="outlined"
                            size="large"
                            rounded="xl"
                            @click="goTo('/shop')"
                        >
                            查看全部商品
                        </v-btn>
                    </div>
                </div>
            </section>

            <section class="qa bg-grey-lighten-5" style="padding: 72px 0">
                <div class="mx-auto px-6" style="max-width: 1100px">
                    <h2
                        class="section-title font-weight-bold text-center ma-0 mb-2"
                        style="font-size: 2rem; color: #1a1a2e"
                    >
                        常見問題
                    </h2>
                    <p
                        class="section-subtitle text-center ma-0 mb-12"
                        style="color: #888; font-size: 0.95rem"
                    >
                        快速解答您的疑難雜症
                    </p>
                    <v-row justify="center" class="qa-fold ma-0">
                        <v-col cols="12" md="10" class="pa-0">
                            <v-expansion-panels v-model="activeName">
                                <v-expansion-panel
                                    v-for="faq in faqs"
                                    :key="faq.id"
                                    :value="faq.id"
                                >
                                    <v-expansion-panel-title>{{
                                        faq.q
                                    }}</v-expansion-panel-title>
                                    <v-expansion-panel-text>{{
                                        faq.a
                                    }}</v-expansion-panel-text>
                                </v-expansion-panel>
                            </v-expansion-panels>
                        </v-col>
                    </v-row>
                </div>
            </section>

            <footer style="background: #1a1a2e; color: #ccc; padding: 60px 0 0">
                <div class="mx-auto px-6" style="max-width: 1100px">
                    <v-row no-gutters class="ga-10 pb-12">
                        <v-col cols="12" md="6">
                            <h3
                                class="text-white ma-0 mb-3"
                                style="font-size: 1.4rem; font-weight: 700"
                            >
                                我的商城
                            </h3>
                            <p
                                style="
                                    font-size: 0.9rem;
                                    line-height: 1.7;
                                    color: #aaa;
                                    margin: 0;
                                "
                            >
                                精選好物，用心服務每一位顧客
                            </p>
                        </v-col>
                        <v-col cols="12" sm="6" md="3">
                            <h4
                                class="text-white ma-0 mb-4"
                                style="font-size: 1rem; font-weight: 600"
                            >
                                聯絡我們
                            </h4>
                            <ul
                                class="d-flex flex-column ga-2 pa-0 ma-0"
                                style="list-style: none"
                            >
                                <li
                                    class="d-flex align-center ga-2"
                                    style="font-size: 0.88rem; color: #aaa"
                                >
                                    <v-icon
                                        icon="mdi-map-marker"
                                        size="16"
                                    />台北市信義區信義路五段7號
                                </li>
                                <li
                                    class="d-flex align-center ga-2"
                                    style="font-size: 0.88rem; color: #aaa"
                                >
                                    <v-icon
                                        icon="mdi-phone"
                                        size="16"
                                    />02-1234-5678
                                </li>
                                <li
                                    class="d-flex align-center ga-2"
                                    style="font-size: 0.88rem; color: #aaa"
                                >
                                    <v-icon
                                        icon="mdi-email-outline"
                                        size="16"
                                    />service@myshop.com
                                </li>
                            </ul>
                        </v-col>
                        <v-col cols="12" sm="6" md="3">
                            <h4
                                class="text-white ma-0 mb-4"
                                style="font-size: 1rem; font-weight: 600"
                            >
                                快速連結
                            </h4>
                            <ul
                                class="d-flex flex-column ga-2 pa-0 ma-0"
                                style="list-style: none"
                            >
                                <li
                                    class="footer-link"
                                    style="
                                        font-size: 0.88rem;
                                        color: #aaa;
                                        cursor: pointer;
                                    "
                                    @click="goTo('/login')"
                                >
                                    會員登入
                                </li>
                                <li
                                    class="footer-link"
                                    style="
                                        font-size: 0.88rem;
                                        color: #aaa;
                                        cursor: pointer;
                                    "
                                    @click="goTo('/shop')"
                                >
                                    商城
                                </li>
                            </ul>
                        </v-col>
                    </v-row>
                </div>
                <div
                    class="text-center pa-5"
                    style="
                        border-top: 1px solid rgba(255, 255, 255, 0.08);
                        font-size: 0.8rem;
                        color: #666;
                    "
                >
                    © 2026 我的商城. All rights reserved.
                </div>
            </footer>
        </main>

        <v-snackbar
            v-model="snackbar.show"
            :color="snackbar.color"
            location="top"
            timeout="3000"
        >
            {{ snackbar.text }}
        </v-snackbar>
    </FrontLayout>
</template>

<style scoped>
.btn-primary {
    transition:
        transform 0.2s,
        box-shadow 0.25s;
}
.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(64, 158, 255, 0.45);
}

.btn-ghost {
    transition:
        background 0.25s,
        transform 0.2s;
}
.btn-ghost:hover {
    background: rgba(255, 255, 255, 0.15) !important;
    transform: translateY(-2px);
}

.btn-outline {
    transition:
        background 0.25s,
        color 0.25s,
        transform 0.2s;
}
.btn-outline:hover {
    background: #409eff !important;
    color: #fff !important;
    transform: translateY(-2px);
}

.feature-card {
    transition:
        transform 0.25s,
        box-shadow 0.25s;
}
.feature-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
}

.product-card {
    transition:
        transform 0.25s,
        box-shadow 0.25s;
}
.product-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 28px rgba(0, 0, 0, 0.1);
}

.product-img {
    transition: transform 0.35s;
}
.product-card:hover .product-img {
    transform: scale(1.05);
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
    transition:
        background 0.2s,
        color 0.2s;
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

.footer-link {
    transition: color 0.2s;
}
.footer-link:hover {
    color: #409eff;
}
</style>
