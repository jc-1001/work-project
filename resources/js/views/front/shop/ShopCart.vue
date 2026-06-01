<script setup>
import { ref, onMounted, onUnmounted, computed } from "vue";
import { getImageUrl } from "../../../utils/image";
import { useAuth } from "../../../composables/useAuth";
import FrontLayout from "../../../layouts/FrontLayout.vue";

const cartItem = ref([]);
const snackbar = ref({ show: false, text: "", color: "success" });

const notify = (text, color = "success") => {
    snackbar.value = { show: true, text, color };
};

const loadCart = () => {
    try {
        const savedCart = localStorage.getItem("cart");
        if (savedCart) {
            cartItem.value = JSON.parse(savedCart).map((item) => ({
                ...item,
                quantity: Number(item.quantity),
                price: Number(item.price),
            }));
        }
    } catch {
        localStorage.removeItem("cart");
    }
};

const totalPrice = computed(() =>
    cartItem.value.reduce((sum, item) => sum + item.price * item.quantity, 0),
);

const removeFromCart = (id) => {
    cartItem.value = cartItem.value.filter((item) => item.id !== id);
    updateLocalStorage();
    notify("已移除商品");
};

const updateLocalStorage = () => {
    localStorage.setItem("cart", JSON.stringify(cartItem.value));
};

const changeQuantity = (item, delta) => {
    const newQty = item.quantity + delta;
    if (newQty < 1 || newQty > 10) return;
    item.quantity = newQty;
    updateLocalStorage();
};

const onStorageChange = (e) => {
    if (e.key === "cart") loadCart();
};

const goTo = (url) => {
    window.location.href = url;
};

const { fetchUser } = useAuth();

onMounted(() => {
    fetchUser();
    loadCart();
    window.addEventListener("storage", onStorageChange);
});

onUnmounted(() => {
    window.removeEventListener("storage", onStorageChange);
});
</script>

<template>
    <FrontLayout>
        <v-container style="max-width: 1000px" class="py-6 px-4">
            <h1 class="text-h5 font-weight-bold mb-4">我的購物車</h1>

            <v-btn
                variant="outlined"
                color="primary"
                class="mb-6"
                prepend-icon="mdi-arrow-left"
                @click="goTo('/shop')"
            >
                返回商城繼續購物
            </v-btn>

            <template v-if="cartItem.length > 0">
                <v-table class="mb-4">
                    <thead>
                        <tr>
                            <th class="text-center">商品縮圖</th>
                            <th class="text-center">商品名稱</th>
                            <th class="text-center">單價</th>
                            <th class="text-center">數量</th>
                            <th class="text-center">小計</th>
                            <th class="text-center">操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="item in cartItem" :key="item.id">
                            <td class="text-center">
                                <v-img
                                    :src="getImageUrl(item.image)"
                                    class="mx-auto"
                                    width="80"
                                    height="60"
                                    cover
                                    rounded="sm"
                                />
                            </td>
                            <td class="text-center">{{ item.name }}</td>
                            <td class="text-center">NT$ {{ item.price }}</td>
                            <td class="text-center">
                                <div
                                    class="d-flex align-center justify-center ga-1"
                                >
                                    <v-btn
                                        icon="mdi-minus"
                                        size="x-small"
                                        variant="outlined"
                                        :disabled="item.quantity <= 1"
                                        @click="changeQuantity(item, -1)"
                                    />
                                    <span class="mx-2">{{
                                        item.quantity
                                    }}</span>
                                    <v-btn
                                        icon="mdi-plus"
                                        size="x-small"
                                        variant="outlined"
                                        :disabled="item.quantity >= 10"
                                        @click="changeQuantity(item, 1)"
                                    />
                                </div>
                            </td>
                            <td class="text-center">
                                NT$ {{ item.price * item.quantity }}
                            </td>
                            <td class="text-center">
                                <v-btn
                                    color="error"
                                    icon="mdi-trash-can"
                                    size="small"
                                    variant="tonal"
                                    @click="removeFromCart(item.id)"
                                />
                            </td>
                        </tr>
                    </tbody>
                </v-table>

                <div class="d-flex justify-end align-center ga-4">
                    <span class="text-h6">
                        總金額：<span class="text-error font-weight-bold"
                            >NT$ {{ totalPrice }}</span
                        >
                    </span>
                    <v-btn
                        color="success"
                        size="large"
                        @click="goTo('/order')"
                        >前往結帳</v-btn
                    >
                </div>
            </template>

            <div
                v-else
                class="d-flex flex-column align-center py-12 text-medium-emphasis"
            >
                <v-icon icon="mdi-cart-off" size="64" class="mb-4" />
                <p>購物車是空的，快去逛逛吧！</p>
            </div>
        </v-container>

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
