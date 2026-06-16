<script setup>
import { ref, computed, onMounted } from "vue";
import api from "../bootstrap";
import { getImageUrl } from "../utils/image";
import FrontLayout from "../layouts/FrontLayout.vue";
import { useAuth } from "../composables/useAuth";
import { useFavorites } from "../composables/useFavorites";

const window = globalThis;

const { fetchUser, isLoggedIn } = useAuth();
const { favorites, removeFavorite } = useFavorites();

const categories = ref([]);
const selectedCategoryId = ref("all");
const snackbar = ref({ show: false, text: "", color: "success" });
const loginDialog = ref(false);
const loadingProductId = ref(null);
const addingAll = ref(false);

const notify = (text, color = "success") => {
    snackbar.value = { show: true, text, color };
};

const filteredProducts = computed(() => {
    if (selectedCategoryId.value === "all") return favorites.value;
    return favorites.value.filter(
        (p) => String(p.category_id) === String(selectedCategoryId.value),
    );
});

const total = computed(() => favorites.value.length);

const fetchCategories = () => {
    api.get("/categories")
        .then((res) => {
            categories.value = res.data.categories;
        })
        .catch((error) => {
            console.error("無法抓取分類資料:", error);
        });
};

const addToCart = async (product) => {
    if (loadingProductId.value !== null) return;
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
        window.dispatchEvent(new Event("cart-updated"));
        notify("已加入購物車！");
    } finally {
        loadingProductId.value = null;
    }
};

const addAllToCart = () => {
    if (addingAll.value) return;
    addingAll.value = true;
    fetchUser()
        .then(() => {
            if (!isLoggedIn()) {
                loginDialog.value = true;
                return;
            }

            const cart = JSON.parse(localStorage.getItem("cart")) || [];
            let addedCount = 0;

            filteredProducts.value.forEach((product) => {
                if (product.stock === 0) return;

                const index = cart.findIndex((item) => item.id === product.id);
                if (index !== -1) {
                    if (cart[index].quantity < product.stock) {
                        cart[index].quantity += 1;
                        addedCount++;
                    }
                } else {
                    cart.push({
                        id: product.id,
                        name: product.name,
                        price: product.price,
                        image: product.image,
                        quantity: 1,
                    });
                    addedCount++;
                }
            });

            localStorage.setItem("cart", JSON.stringify(cart));

            if (addedCount > 0) {
                window.dispatchEvent(new Event("cart-updated"));
                notify(`已將 ${addedCount} 件商品加入購物車！`);
            } else {
                notify("所有商品皆已售完或達庫存上限", "warning");
            }
        })
        .catch((error) => {
            console.error("一鍵加入購物車失敗:", error);
        })
        .finally(() => {
            addingAll.value = false;
        });
};

onMounted(() => {
    fetchCategories();
    fetchUser();
});

const goToProductDetail = (id) => {
    window.location.href = "/shop/" + id;
};
</script>

<template>
    <FrontLayout>
        <div
            class="text-center mb-2 py-12 px-6"
            style="background-color: #fb8c00; color: #fff"
        >
            <h1
                class="text-h5 font-weight-bold mb-2"
                style="letter-spacing: 2px"
            >
                我的商品收藏
            </h1>
            <p class="text-body-2 ma-0" style="opacity: 0.7">
                共 {{ total }} 件商品
            </p>
        </div>

        <div class="d-flex flex-column flex-sm-row align-sm-center justify-space-between px-3 py-3 ga-2">
            <div class="d-flex ga-2 flex-wrap">
                <v-btn
                    variant="elevated"
                    prepend-icon="mdi-arrow-left"
                    @click="window.location.href = '/shop'"
                >
                    返回商城
                </v-btn>
                <v-btn
                    variant="tonal"
                    color="warning"
                    :loading="addingAll"
                    prepend-icon="mdi-cart-heart"
                    @click="addAllToCart"
                >
                    一鍵加入購物車
                </v-btn>
            </div>
            <v-chip-group
                v-model="selectedCategoryId"
                color="primary"
                mandatory
                class="px-0"
            >
                <v-chip value="all">全部</v-chip>
                <v-chip
                    v-for="cat in categories"
                    :key="cat.id"
                    :value="String(cat.id)"
                >
                    {{ cat.name }}
                </v-chip>
            </v-chip-group>
        </div>

        <div
            v-if="filteredProducts.length === 0"
            class="d-flex flex-column align-center py-15 text-medium-emphasis"
        >
            <v-icon
                icon="mdi-heart-off-outline"
                size="64"
                color="grey-lighten-1"
                class="mb-3"
            />
            <p class="text-medium-emphasis">尚無收藏商品</p>
        </div>

        <v-row v-else class="px-4 py-2">
            <v-col
                v-for="product in filteredProducts"
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
                            icon="mdi-heart-remove"
                            size="medium"
                            color="yellow"
                            variant="text"
                            style="position: absolute; top: 8px; right: 8px"
                            @click.stop="
                                removeFavorite(product.id);
                                notify('已移除收藏', 'info');
                            "
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
    </FrontLayout>
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
