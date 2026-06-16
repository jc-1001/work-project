<script setup>
import { ref } from "vue";
import { getImageUrl } from "../utils/image";
import { useHistories } from "../composables/useHistories";
import { useAuth } from "../composables/useAuth";

const window = globalThis;

const { histories, removeHistory } = useHistories();
const { fetchUser, isLoggedIn } = useAuth();

const loadingProductId = ref(null);
const loginDialog = ref(false);
const snackbar = ref({ show: false, text: "", color: "success" });

const notify = (text, color = "success") => {
    snackbar.value = { show: true, text, color };
};

const addToCart = async (product) => {
    loadingProductId.value = product.id;
    try {
        await fetchUser();
        if (!isLoggedIn()) {
            loginDialog.value = true;
            return;
        }
        const cart = JSON.parse(localStorage.getItem("cart")) || [];
        const index = cart.findIndex((item) => item.id === product.id);
        if (index !== -1) {
            const newQty = cart[index].quantity + 1;
            if (newQty > product.stock) {
                notify("已達最大庫存數量", "warning");
                return;
            }
            cart[index].quantity = newQty;
        } else {
            cart.push({
                id: product.id,
                name: product.name,
                price: product.price,
                image: product.image,
                quantity: 1,
            });
        }
        localStorage.setItem("cart", JSON.stringify(cart));
        notify("已加入購物車！");
    } finally {
        loadingProductId.value = null;
    }
};

const goToProductDetail = (id) => {
    window.location.href = "/shop/" + id;
};
</script>

<template>
    <div>
        <div
            v-if="histories.length === 0"
            class="d-flex flex-column align-center py-15 text-medium-emphasis"
        >
            <v-icon
                icon="mdi-history"
                size="64"
                color="grey-lighten-1"
                class="mb-3"
            />
            <p>尚無瀏覽紀錄</p>
        </div>

        <v-row v-else class="px-4 py-2">
            <v-col
                v-for="product in histories"
                :key="product.id"
                cols="12"
                sm="6"
                md="4"
                lg="3"
                class="mb-1"
            >
                <v-card
                    class="product-card h-100"
                    @click="goToProductDetail(product.id)"
                >
                    <div style="position: relative">
                        <v-img
                            :src="getImageUrl(product.image)"
                            height="200"
                            cover
                        />
                        <v-chip
                            v-if="product.stock === 0"
                            color="error"
                            size="small"
                            style="position: absolute; top: 8px; left: 8px"
                            >已售完</v-chip
                        >
                        <v-btn
                            icon="mdi-close"
                            size="small"
                            color="grey"
                            variant="tonal"
                            style="position: absolute; top: 8px; right: 8px"
                            @click.stop="removeHistory(product.id)"
                        />
                    </div>
                    <v-card-text class="pb-1">
                        <div class="text-body-2 text-truncate mb-1">
                            {{ product.name }}
                        </div>
                        <div class="text-body-1 font-weight-bold text-primary">
                            NT$ {{ Number(product.price).toLocaleString() }}
                        </div>
                    </v-card-text>
                    <v-card-actions class="pt-0 px-4 pb-4">
                        <v-btn
                            block
                            color="primary"
                            variant="tonal"
                            size="large"
                            prepend-icon="mdi-cart-plus"
                            :disabled="product.stock === 0"
                            :loading="loadingProductId === product.id"
                            @click.stop="addToCart(product)"
                            >加入購物車
                        </v-btn>
                    </v-card-actions>
                </v-card>
            </v-col>
        </v-row>

        <v-dialog v-model="loginDialog" max-width="360">
            <v-card rounded="xl">
                <v-card-title class="pt-5 px-5">提示</v-card-title>
                <v-card-text class="px-5"
                    >您尚未登入，是否要前往登入頁面？</v-card-text
                >
                <v-card-actions class="px-4 pb-4">
                    <v-spacer />
                    <v-btn variant="text" @click="loginDialog = false"
                        >取消</v-btn
                    >
                    <v-btn
                        color="primary"
                        variant="tonal"
                        @click="window.location.href = '/login'"
                        >前往登入</v-btn
                    >
                </v-card-actions>
            </v-card>
        </v-dialog>

        <v-snackbar
            v-model="snackbar.show"
            :color="snackbar.color"
            location="top"
            timeout="3000"
        >
            {{ snackbar.text }}
        </v-snackbar>
    </div>
</template>

<style scoped>
.product-card {
    cursor: pointer;
    transition:
        transform 0.25s,
        box-shadow 0.25s;
}
.product-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 28px rgba(0, 0, 0, 0.12) !important;
}
</style>
