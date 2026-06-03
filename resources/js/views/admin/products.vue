<script setup>
import { ref, watch, onMounted } from "vue";
import api from "../../bootstrap.js";
import { getImageUrl } from "../../utils/image.js";
import AdminLayout from "../../layouts/AdminLayout.vue";

const window = globalThis;

const search = ref("");
const products = ref([]);
const snackbar = ref(false);
const snackbarText = ref("");
const snackbarColor = ref("success");
const loading = ref(true);

const total = ref(0);
const currentPage = ref(1);
const lastPage = ref(1);

const categories = ref([]);
const selectedCategoryId = ref(null);
const selectedIsActive = ref(null);
const statusOptions = [
    { title: "上架中", value: 1 },
    { title: "已下架", value: 0 },
];

const headers = [
    { title: "商品名稱", key: "name", align: "start" },
    { title: "類別", key: "category", align: "center", sortable: false },
    { title: "單價", key: "price", align: "center" },
    { title: "庫存", key: "stock", align: "center", sortable: false },
    { title: "狀態", key: "is_active", align: "center", sortable: false },
    {
        title: "",
        key: "actions",
        align: "center",
        sortable: false,
        width: "80px",
    },
];

const selected = ref([]);

function showMessage(text, color = "success") {
    snackbarText.value = text;
    snackbarColor.value = color;
    snackbar.value = true;
}

watch([search, selectedCategoryId, selectedIsActive], () => {
    currentPage.value = 1;
    fetchProducts();
});

function getStockColor(stock) {
    if (stock <= 0) return "error";
    if (stock < 30) return "warning";
    return "success";
}

function getStockText(stock) {
    if (stock <= 0) return "無庫存";
    if (stock < 30) return "庫存偏低";
    return "庫存充足";
}

function fetchCategory() {
    api.get("/categories")
        .then((res) => {
            categories.value = res.data.categories;
        })
        .catch((error) => {
            console.error("無法抓取分類資料:", error);
        });
}

function fetchProducts() {
    loading.value = true;
    const params = {
        page: currentPage.value,
        search: search.value || undefined,
        category_id: selectedCategoryId.value || undefined,
        is_active:
            selectedIsActive.value !== null
                ? selectedIsActive.value
                : undefined,
    };
    api.get("/api/admin/products", { params })
        .then((res) => {
            products.value = res.data.data;
            total.value = res.data.total;
            currentPage.value = res.data.current_page;
            lastPage.value = res.data.last_page;
        })
        .catch(() => {
            showMessage("載入商品失敗", "error");
        })
        .finally(() => {
            loading.value = false;
        });
}

const batchUpdating = ref(false);

function batchSetActive(isActive) {
    if (!selected.value.length) return;
    batchUpdating.value = true;
    const count = selected.value.length;
    api.patch("/api/admin/products/batch-status", {
        ids: selected.value,
        is_active: isActive ? 1 : 0,
    })
        .then(() => {
            selected.value = [];
            fetchProducts();
            showMessage(`已${isActive ? "上架" : "下架"} ${count} 項商品`);
        })
        .catch(() => {
            showMessage("操作失敗，請稍後再試", "error");
        })
        .finally(() => {
            batchUpdating.value = false;
        });
}

function handleRowClick(_, { item }) {
    window.location.href = `/admin/products/${item.id}`;
}

onMounted(() => {
    fetchCategory();
    fetchProducts();
});
</script>

<template>
    <AdminLayout>
        <v-row align="center" class="mb-2">
            <v-col>
                <h1 class="text-h6 font-weight-bold">商品列表</h1>
            </v-col>
            <v-col
                cols="auto"
                class="d-flex align-center ga-2 flex-wrap justify-end"
            >
                <v-chip v-if="selected.length" color="primary" size="small">
                    已選 {{ selected.length }} 項
                </v-chip>
                <v-tooltip text="請先勾選商品" :disabled="!!selected.length">
                    <template #activator="{ props }">
                        <span v-bind="props">
                            <v-btn
                                variant="tonal"
                                color="error"
                                prepend-icon="mdi-arrow-down-box"
                                :disabled="!selected.length"
                                :loading="batchUpdating"
                                @click="batchSetActive(false)"
                                >下架</v-btn
                            >
                        </span>
                    </template>
                </v-tooltip>
                <v-tooltip text="請先勾選商品" :disabled="!!selected.length">
                    <template #activator="{ props }">
                        <span v-bind="props">
                            <v-btn
                                variant="tonal"
                                color="success"
                                prepend-icon="mdi-arrow-up-box"
                                :disabled="!selected.length"
                                :loading="batchUpdating"
                                @click="batchSetActive(true)"
                                >上架</v-btn
                            >
                        </span>
                    </template>
                </v-tooltip>
                <v-btn
                    color="primary"
                    prepend-icon="mdi-plus"
                    @click="window.location.href = '/admin/products/create'"
                >
                    新增商品
                </v-btn>
            </v-col>
        </v-row>

        <v-row density="comfortable" align="center" class="mb-2">
            <v-col cols="12" sm="5" md="4">
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
            <v-col cols="6" sm="4" md="3">
                <v-select
                    v-model="selectedCategoryId"
                    prepend-inner-icon="mdi-shape"
                    :items="categories"
                    item-title="name"
                    item-value="id"
                    :menu-props="{ scrim: true, scrollStrategy: 'close' }"
                    label="篩選類別"
                    clearable
                    density="compact"
                    variant="outlined"
                    hide-details
                />
            </v-col>
            <v-col cols="6" sm="3" md="2">
                <v-select
                    v-model="selectedIsActive"
                    prepend-inner-icon="mdi-store"
                    :items="statusOptions"
                    item-title="title"
                    item-value="value"
                    :menu-props="{ scrim: true, scrollStrategy: 'close' }"
                    label="篩選狀態"
                    clearable
                    density="compact"
                    variant="outlined"
                    hide-details
                />
            </v-col>
            <v-col class="text-right text-caption text-medium-emphasis">
                共 {{ total }} 筆 | 本頁 {{ products.length }} 筆
            </v-col>
        </v-row>

        <v-data-table
            v-model="selected"
            item-value="id"
            show-select
            :headers="headers"
            :items="products"
            :loading="loading"
            :items-per-page="-1"
            loading-text="載入中..."
            no-data-text="尚無商品資料"
            @click:row="handleRowClick"
            hover
        >
            <template #item.name="{ item }">
                <div class="d-flex align-center ga-3 py-2">
                    <div
                        class="flex-shrink-0 rounded-sm overflow-hidden bg-grey-lighten-5"
                        style="
                            width: 52px;
                            height: 52px;
                            min-width: 52px;
                            border: 1px solid #eee;
                        "
                    >
                        <v-img
                            :src="getImageUrl(item.image)"
                            cover
                            rounded="sm"
                            height="100%"
                            width="100%"
                        >
                            <template #placeholder>
                                <v-icon color="grey-lighten-1"
                                    >mdi-image</v-icon
                                >
                            </template>
                        </v-img>
                    </div>
                    <span class="font-weight-medium">{{ item.name }}</span>
                </div>
            </template>

            <template #item.category="{ item }">
                {{ item.category?.name ?? "—" }}
            </template>

            <template #item.price="{ item }"> NT$ {{ item.price }} </template>

            <template #item.stock="{ item }">
                <v-chip
                    :color="getStockColor(item.stock)"
                    size="small"
                    variant="tonal"
                >
                    {{ getStockText(item.stock) }}（{{ item.stock }}）
                </v-chip>
            </template>

            <template #item.is_active="{ item }">
                <v-chip
                    :color="item.is_active ? 'success' : 'default'"
                    size="small"
                    variant="tonal"
                >
                    {{ item.is_active ? "上架中" : "已下架" }}
                </v-chip>
            </template>

            <template #item.actions="{ item }">
                <v-btn
                    icon="mdi-square-edit-outline"
                    variant="text"
                    size="small"
                    @click.stop="window.location.href = `/admin/products/${item.id}`"
                />
            </template>

            <template #bottom />
        </v-data-table>

        <v-snackbar
            v-model="snackbar"
            :color="snackbarColor"
            timeout="3000"
            location="top"
        >
            {{ snackbarText }}
        </v-snackbar>

        <div class="pagination mt-4">
            <v-pagination
                v-model="currentPage"
                :length="lastPage"
                :total-visible="7"
                @update:model-value="fetchProducts"
            />
        </div>
    </AdminLayout>
</template>
