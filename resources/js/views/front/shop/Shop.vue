<script setup>
import { ref, watch, onMounted } from "vue";

const window = globalThis;
import api from "../../../bootstrap";
import { getImageUrl } from "../../../utils/image";
import FrontLayout from "../../../layouts/FrontLayout.vue";
import { useAuth } from "../../../composables/useAuth";
import { useFavorites } from "../../../composables/useFavorites";
import { useHistories } from "../../../composables/useHistories";
import History from "../../../components/History.vue";

const { isFavorited, toggleFavorite } = useFavorites();
const { histories } = useHistories();

const showHistory = ref(false);

const loading = ref(false);
const products = ref([]);
const categories = ref([]);
const selectedCategoryId = ref(null);
const total = ref(0);
const currentPage = ref(1);
const pageSize = ref(12);
const { fetchUser, isLoggedIn } = useAuth();

const search = ref("");
const snackbar = ref({ show: false, text: "", color: "success" });
const loginDialog = ref(false);
const loadingProductId = ref(null);

let searchTimer = null;
watch(search, () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => {
        currentPage.value = 1;
        fetchProducts();
    }, 400);
});

const notify = (text, color = "success") => {
    snackbar.value = { show: true, text, color };
};

const fetchCategories = () => {
    api.get("/categories")
        .then((res) => {
            categories.value = res.data.categories;
        })
        .catch((error) => {
            console.error("無法抓取分類資料:", error);
        });
};

const fetchProducts = () => {
    loading.value = true;
    const params = { page: currentPage.value, per_page: pageSize.value, search: search.value || undefined };
    if (typeof selectedCategoryId.value === "number")
        params.category_id = selectedCategoryId.value;

    api.get("/products", { params })
        .then((response) => {
            products.value = response.data.data;
            total.value = response.data.total;
        })
        .catch((error) => {
            console.error("無法抓取商品資料:", error);
        })
        .finally(() => {
            loading.value = false;
        });
};

const onCategoryChange = () => {
    clearTimeout(searchTimer);
    currentPage.value = 1;
    fetchProducts();
};

const onPageChange = () => {
    fetchProducts();
    window.scrollTo({ top: 0, behavior: "smooth" });
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
        window.dispatchEvent(new Event("cart-updated"));
        notify("已加入購物車！");
    } finally {
        loadingProductId.value = null;
    }
};

onMounted(() => {
    fetchCategories();
    fetchProducts();
    fetchUser();
});

const goToProductDetail = (id) => {
    window.location.href = "/shop/" + id;
};
</script>

<template>
    <FrontLayout>
        <div
            class="text-white text-center pt-12 pb-10 px-6 mb-2"
            style="background-color: #0f3460"
        >
            <h1
                class="font-weight-bold mb-2"
                style="font-size: 2rem; letter-spacing: 2px"
            >
                商城
            </h1>
            <p class="mb-0" style="font-size: 0.95rem; opacity: 0.7">
                {{
                    showHistory
                        ? `共 ${histories.length} 筆 商品瀏覽紀錄`
                        : `共 ${total} 件 商品`
                }}
            </p>
        </div>

        <v-row class="px-4 pt-4 pb-2" align="center">
            <v-col cols="12" sm="4" md="3">
                <v-text-field
                    v-model="search"
                    density="compact"
                    label="搜尋商品名稱"
                    prepend-inner-icon="mdi-magnify"
                    clearable
                    variant="outlined"
                    hide-details
                />
            </v-col>
        </v-row>

        <div class="d-flex flex-wrap justify-center ga-2 py-4 px-4">
            <v-chip
                size="large"
                prepend-icon="mdi-history"
                :variant="showHistory ? 'flat' : 'outlined'"
                :color="showHistory ? 'primary' : 'default'"
                @click="showHistory = !showHistory"
                >最近瀏覽</v-chip
            >

            <v-chip
                size="large"
                :variant="
                    !showHistory && selectedCategoryId === null
                        ? 'flat'
                        : 'outlined'
                "
                :color="
                    !showHistory && selectedCategoryId === null
                        ? 'primary'
                        : undefined
                "
                @click="
                    showHistory = false;
                    selectedCategoryId = null;
                    onCategoryChange();
                "
                >全部</v-chip
            >
            <v-chip
                v-for="cat in categories"
                :key="cat.id"
                size="large"
                :variant="
                    !showHistory && selectedCategoryId === cat.id
                        ? 'flat'
                        : 'outlined'
                "
                :color="
                    !showHistory && selectedCategoryId === cat.id
                        ? 'primary'
                        : undefined
                "
                @click="
                    showHistory = false;
                    selectedCategoryId = cat.id;
                    onCategoryChange();
                "
                >{{ cat.name }}</v-chip
            >
        </div>

        <History v-if="showHistory" />

        <v-row v-else-if="loading" class="px-4 py-2">
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

        <template v-else>
            <div
                v-if="products.length === 0"
                class="d-flex flex-column align-center py-15"
            >
                <v-icon
                    :icon="search ? 'mdi-magnify-close' : 'mdi-package-variant-remove'"
                    size="64"
                    color="grey-lighten-1"
                    class="mb-3"
                />
                <p class="text-medium-emphasis">
                    <template v-if="search">找不到符合「{{ search }}」的商品</template>
                    <template v-else>此分類目前無商品</template>
                </p>
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
                    <v-card
                        class="product-card h-100 cursor-pointer"
                        @click="goToProductDetail(product.id)"
                    >
                        <div class="position-relative">
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
                                :icon="
                                    isFavorited(product.id)
                                        ? 'mdi-heart'
                                        : 'mdi-heart-outline'
                                "
                                size="medium"
                                :color="
                                    isFavorited(product.id) ? 'yellow' : 'white'
                                "
                                variant="text"
                                style="position: absolute; top: 8px; right: 8px"
                                @click.stop="
                                    toggleFavorite(product);
                                    notify(
                                        isFavorited(product.id)
                                            ? '已加入收藏'
                                            : '已移除收藏',
                                    );
                                "
                            />
                        </div>
                        <v-card-text class="pb-1">
                            <div
                                class="text-body-2 mb-1 text-truncate text-grey-darken-3"
                            >
                                {{ product.name }}
                            </div>
                            <div class="font-weight-bold text-primary">
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
        </template>

        <v-pagination
            v-if="!showHistory && total > pageSize"
            v-model="currentPage"
            :length="Math.ceil(total / pageSize)"
            density="comfortable"
            class="my-4"
            @update:model-value="onPageChange"
        />

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
    transition:
        transform 0.25s,
        box-shadow 0.25s;
}
.product-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 28px rgba(0, 0, 0, 0.12) !important;
}
</style>
