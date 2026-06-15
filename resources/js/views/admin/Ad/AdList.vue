<script setup>
import api from "../../../bootstrap";
import { ref, computed, onMounted } from "vue";
import AdminLayout from "../../../layouts/AdminLayout.vue";
import AdFormDialog from "../../../components/AdFormDialog.vue";

const window = globalThis;

const ads = ref([]);
const loading = ref(false);
const search = ref("");
const selectedIsActive = ref(null);
const dialog = ref(false);
const snackbar = ref({ show: false, text: "", color: "success" });

const statusOptions = [
    { title: "上架中", value: true },
    { title: "已下架", value: false },
];

const notify = (text, color = "success") => {
    snackbar.value = { show: true, text, color };
};

const filteredAds = computed(() => {
    return ads.value.filter((ad) => {
        const matchSearch = !search.value || ad.title.includes(search.value);
        const matchStatus =
            selectedIsActive.value === null ||
            selectedIsActive.value === undefined
                ? true
                : ad.is_active === selectedIsActive.value;
        return matchSearch && matchStatus;
    });
});

const fetchAds = () => {
    loading.value = true;
    api.get("/admin/api/advertisements")
        .then((res) => {
            ads.value = res.data.data;
        })
        .catch(() => {
            notify("載入失敗", "error");
        })
        .finally(() => {
            loading.value = false;
        });
};

const deleteAd = (id) => {
    if (!confirm("確定要刪除此廣告？")) return;
    api.delete(`/admin/api/advertisements/${id}`)
        .then(() => {
            notify("刪除成功");
            fetchAds();
        })
        .catch(() => {
            notify("刪除失敗", "error");
        });
};

const handleRowClick = (_, { item }) => {
    window.location.href = `/admin/advertisements/${item.id}`;
};

const toggleActive = (item) => {
    const newVal = !item.is_active;
    item.is_active = newVal;

    const fd = new FormData();
    fd.append("_method", "PUT");
    fd.append("is_active", newVal ? 1 : 0);
    api.post(`/admin/api/advertisements/${item.id}`, fd, {
        headers: { "Content-Type": "multipart/form-data" },
    })
        .then(() => {
            if (newVal) {
                notify("已上架，其他廣告已自動下架");
                fetchAds();
            } else {
                notify("已下架");
            }
        })
        .catch(() => {
            item.is_active = !newVal;
            notify("更新失敗", "error");
        });
};

const headers = [
    { title: "ID", key: "id", width: "70px" },
    { title: "標題", key: "title", sortable: false, width: "250px" },
    { title: "展示期間", key: "period", sortable: false, width: "250px" },
    {
        title: "倒數秒",
        key: "countdown_seconds",
        sortable: false,
        width: "100px",
    },
    { title: "狀態", key: "is_active", sortable: false, width: "90px" },
    { title: "操作", key: "actions", sortable: false, width: "120px" },
];

onMounted(fetchAds);
</script>

<template>
    <AdminLayout>
        <v-row align="center" class="mb-2">
            <v-col>
                <h1 class="text-h6 font-weight-bold">廣告列表</h1>
            </v-col>
            <v-col cols="auto">
                <v-btn
                    color="primary"
                    prepend-icon="mdi-plus"
                    @click="dialog = true"
                    >新增廣告</v-btn
                >
            </v-col>
        </v-row>

        <v-row density="comfortable" align="center" class="mb-2">
            <v-col cols="12" sm="5" md="4">
                <v-text-field
                    v-model="search"
                    density="compact"
                    label="搜尋廣告標題"
                    prepend-inner-icon="mdi-magnify"
                    clearable
                    variant="outlined"
                    hide-details
                />
            </v-col>
            <v-col cols="6" sm="3" md="2">
                <v-select
                    v-model="selectedIsActive"
                    :items="statusOptions"
                    item-title="title"
                    item-value="value"
                    label="篩選狀態"
                    clearable
                    density="compact"
                    variant="outlined"
                    hide-details
                />
            </v-col>
            <v-col class="text-right text-caption text-medium-emphasis">
                共 {{ filteredAds.length }} 筆
            </v-col>
        </v-row>

        <v-data-table
            :headers="headers"
            :items="filteredAds"
            :loading="loading"
            items-per-page="10"
            loading-text="載入中..."
            no-data-text="尚無廣告資料"
            items-per-page-text="每頁顯示"
            hover
            @click:row="handleRowClick"
        >
            <template #item.period="{ item }">
                {{ item.display_start_at?.slice(0, 10) }} ~
                {{ item.display_end_at?.slice(0, 10) }}
            </template>
            <template #item.is_active="{ item }">
                <v-chip
                    :color="item.is_active ? 'success' : 'default'"
                    size="small"
                    variant="tonal"
                    style="cursor: pointer"
                    @click.stop="toggleActive(item)"
                >
                    {{ item.is_active ? "上架中" : "已下架" }}
                </v-chip>
            </template>
            <template #item.actions="{ item }">
                <v-btn
                    icon
                    size="small"
                    variant="text"
                    @click.stop="
                        window.location.href = `/admin/advertisements/${item.id}`
                    "
                >
                    <v-icon>mdi-pencil</v-icon>
                </v-btn>
                <v-btn
                    icon
                    size="small"
                    variant="text"
                    color="error"
                    @click.stop="deleteAd(item.id)"
                >
                    <v-icon>mdi-delete</v-icon>
                </v-btn>
            </template>
        </v-data-table>

        <AdFormDialog v-model="dialog" @created="fetchAds" />

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
