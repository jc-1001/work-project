<script setup>
import { ref, onMounted, onUnmounted } from "vue";

const window = globalThis;

const props = defineProps({
    ad: { type: Object, default: null },
});

const show = ref(false);
const countdown = ref(0);
let timer = null;

const TODAY = new Date().toISOString().slice(0, 10);
const STORAGE_KEY = "ad_closed_date";

const close = () => {
    show.value = false;
    sessionStorage.setItem(STORAGE_KEY, TODAY);
    clearInterval(timer);
    timer = null;
};

const goToShop = () => {
    window.location.href = props.ad?.link_url || "/shop";
};

onMounted(() => {
    if (!props.ad) return;
    if (sessionStorage.getItem(STORAGE_KEY) === TODAY) return;

    countdown.value = props.ad.countdown_seconds || 10;
    show.value = true;

    timer = setInterval(() => {
        countdown.value--;
        if (countdown.value <= 0) close();
    }, 1000);
});

onUnmounted(() => {
    if (timer) clearInterval(timer);
});
</script>

<template>
    <v-overlay
        v-model="show"
        persistent
    >
        <div class="d-flex align-center justify-center" style="width: 100vw; height: 100vh;">
        <div class="d-inline-block position-relative">
            <button
                class="close-btn position-absolute d-flex align-center ga-1 text-white rounded-pill cursor-pointer py-1 pl-2 pr-3 border-none"
                style="
                    top: -14px;
                    right: -14px;
                    background: rgba(0, 0, 0, 0.75);
                    line-height: 1;
                    z-index: 1;
                "
                @click="close"
            >
                <v-icon size="20">mdi-close</v-icon>
                {{ countdown }} 秒後關閉
            </button>
            <img
                :src="ad.image_url"
                :alt="ad.title"
                class="d-block rounded-lg cursor-pointer"
                style="max-width: min(600px, 90vw); max-height: 80vh"
                @click="goToShop"
            />
        </div>
        </div>
    </v-overlay>
</template>

<style scoped>
.close-btn {
    transition: background 0.2s;
}

.close-btn:hover {
    background: rgba(0, 0, 0, 0.95);
}
</style>
