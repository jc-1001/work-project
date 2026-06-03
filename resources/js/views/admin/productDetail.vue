<script setup>
import { ref, onMounted } from "vue";
import api from "../../bootstrap.js";
import { getImageUrl } from "../../utils/image.js";
import AdminLayout from "../../layouts/AdminLayout.vue";

const window = globalThis;
const productId = document.getElementById("app").dataset.id;

const valid = ref(false);
const storing = ref(false);
const previewUrl = ref(null);
const fileInput = ref(null);
const categories = ref([]);
const loading = ref(true);
const loadFailed = ref(false);
const snackbar = ref({ show: false, text: "", color: "success" });

const notify = (text, color = "success") => {
    snackbar.value = { show: true, text, color };
};

const form = ref({
    name: "",
    price: null,
    stock: null,
    category_id: null,
    description: "",
    is_active: "1",
    image: null,
});

const rules = {
    required: (v) => !!v || "此欄位為必填",
    positiveNumber: (v) =>
        (v !== null && v !== "" && v >= 0) || "請輸入有效數字",
};

function handleImage(e) {
    const file = e.target.files[0];
    if (file) {
        form.value.image = file;
        previewUrl.value = URL.createObjectURL(file);
    }
}

function fetchCategories() {
    api.get("/categories")
        .then((res) => {
            categories.value = res.data.categories.map((c) => ({
                title: c.name,
                value: c.id,
            }));
        })
        .catch(() => {});
}

function fetchProduct() {
    loading.value = true;
    api.get(`/api/admin/products/${productId}`)
        .then((res) => {
            const p = res.data.product;
            form.value = {
                name: p.name,
                price: parseFloat(p.price),
                stock: parseInt(p.stock),
                category_id: Number(p.category_id),
                description: p.description ?? "",
                is_active: p.is_active ? "1" : "0",
                image: null,
            };

            if (p.image) {
                previewUrl.value = getImageUrl(p.image);
            }
        })
        .catch((err) => {
            loadFailed.value = true;
            const msg =
                err.response?.data?.message ?? "載入商品失敗，請稍後再試";
            notify(msg, "error");
        })
        .finally(() => {
            loading.value = false;
        });
}

function store() {
    if (!valid.value) return;
    storing.value = true;

    const data = new FormData();
    data.append("name", form.value.name);
    data.append("price", form.value.price);
    data.append("stock", form.value.stock);
    data.append("category_id", form.value.category_id);
    data.append("description", form.value.description || "");
    data.append("is_active", form.value.is_active === "1" ? 1 : 0);
    if (form.value.image) data.append("image", form.value.image);

    api.post(`/api/admin/products/${productId}`, data, {
        headers: { "Content-Type": "multipart/form-data" },
    })
        .then(() => {
            notify("儲存成功");
            setTimeout(() => {
                window.location.href = "/admin/products";
            }, 1000);
        })
        .catch((err) => {
            if (err.response?.status === 422) {
                const errors = err.response.data?.errors;
                const msg = errors
                    ? Object.values(errors).flat().join("、")
                    : (err.response.data?.message ?? "資料驗證失敗");
                notify(msg, "error");
            } else {
                notify("儲存失敗，請稍後再試", "error");
            }
        })
        .finally(() => {
            storing.value = false;
        });
}

onMounted(async () => {
    await fetchCategories();
    await fetchProduct();
});
</script>

<template>
    <AdminLayout>
        <div class="d-flex justify-space-between align-center mb-6">
            <h1>商品詳情</h1>
            <div class="d-flex ga-2">
                <v-btn
                    variant="tonal"
                    prepend-icon="mdi-arrow-left"
                    @click="window.location.href = '/admin/products'"
                    >返回</v-btn
                >
                <v-btn
                    variant="tonal"
                    color="primary"
                    prepend-icon="mdi-check"
                    :loading="storing"
                    :disabled="loading"
                    @click="store"
                    >儲存變更</v-btn
                >
            </div>
        </div>

        <div v-if="loading" class="text-center pa-12">
            <v-progress-circular
                :size="70"
                :width="7"
                color="primary"
                indeterminate
            />
        </div>

        <v-alert
            v-else-if="loadFailed"
            type="error"
            variant="tonal"
            class="mt-4"
            title="無法載入商品資料"
            text="請確認網路連線後重新整理，或返回商品列表。"
        />

        <v-form v-else v-model="valid">
            <v-row>
                <v-col cols="12" md="7">
                    <v-text-field
                        v-model="form.name"
                        label="商品名稱 *"
                        variant="outlined"
                        density="compact"
                        :rules="[rules.required]"
                        counter="20"
                        class="mb-3"
                    />

                    <v-row>
                        <v-col cols="12" sm="6">
                            <v-number-input
                                v-model="form.price"
                                label="售價 *"
                                variant="outlined"
                                density="compact"
                                :min="0"
                                controlVariant="hidden"
                                :rules="[rules.required, rules.positiveNumber]"
                                class="mb-3"
                            />
                        </v-col>
                        <v-col cols="12" sm="6">
                            <v-number-input
                                v-model="form.stock"
                                label="庫存數量 *"
                                variant="outlined"
                                density="compact"
                                :min="0"
                                :max="1000"
                                controlVariant="hidden"
                                :rules="[rules.required, rules.positiveNumber]"
                                class="mb-3"
                            />
                        </v-col>
                    </v-row>

                    <v-select
                        v-model="form.category_id"
                        :items="categories"
                        label="商品類別 *"
                        variant="outlined"
                        density="compact"
                        :rules="[rules.required]"
                        class="mb-3"
                    />

                    <v-textarea
                        v-model="form.description"
                        label="商品描述 *"
                        variant="outlined"
                        density="compact"
                        rows="4"
                        :rules="[rules.required]"
                        clearable
                    />
                </v-col>

                <v-col cols="12" md="5">
                    <div
                        class="w-100 d-flex justify-center align-center overflow-hidden rounded-lg cursor-pointer img-wrapper"
                        @click="fileInput.click()"
                    >
                        <v-img
                            v-if="previewUrl"
                            :src="previewUrl"
                            cover
                            class="w-100 h-100"
                        />
                        <div
                            v-else
                            class="d-flex flex-column align-center ga-2 text-grey"
                        >
                            <v-icon size="48" color="grey"
                                >mdi-image-plus</v-icon
                            >
                            <span>點擊上傳圖片</span>
                        </div>
                    </div>
                    <input
                        ref="fileInput"
                        type="file"
                        accept="image/*"
                        hidden
                        @change="handleImage"
                    />

                    <v-radio-group
                        v-model="form.is_active"
                        label="上下架"
                        inline
                        class="mt-4"
                    >
                        <v-radio label="上架" value="1" />
                        <v-radio label="下架" value="0" />
                    </v-radio-group>
                </v-col>
            </v-row>
        </v-form>

        <v-snackbar
            v-model="snackbar.show"
            :color="snackbar.color"
            location="top"
            timeout="3000"
        >
            {{ snackbar.text }}
        </v-snackbar>
    </AdminLayout>
</template>

<style scoped>
.img-wrapper {
    aspect-ratio: 1;
    border: 2px dashed #bbb;
    &:hover {
        border-color: #1976d2;
    }
}
</style>
