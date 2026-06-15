<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from "vue";
import api from "../../../bootstrap";
import AdminLayout from "../../../layouts/AdminLayout.vue";

const window = globalThis;

const el = document.getElementById("app");
const adId = el?.dataset?.id;

const ad = ref(null);
const loading = ref(true);
const loadFailed = ref(false);
const saving = ref(false);
const fileInput = ref(null);
const snackbar = ref({ show: false, text: "", color: "success" });

const form = ref({
    title: "",
    image: null,
    link_url: "/shop",
    countdown_seconds: 10,
    display_start_at: "",
    display_end_at: "",
    is_active: false,
});

let activeBlobUrl = null;
const imageBlobUrl = ref(null);

watch(
    () => form.value.image,
    (newFile) => {
        if (activeBlobUrl) {
            URL.revokeObjectURL(activeBlobUrl);
            activeBlobUrl = null;
        }
        if (newFile) {
            activeBlobUrl = URL.createObjectURL(newFile);
            imageBlobUrl.value = activeBlobUrl;
        } else {
            imageBlobUrl.value = null;
        }
    },
);

onUnmounted(() => {
    if (activeBlobUrl) URL.revokeObjectURL(activeBlobUrl);
});

const previewUrl = computed(
    () => imageBlobUrl.value ?? ad.value?.image_url ?? null,
);

const notify = (text, color = "success") => {
    snackbar.value = { show: true, text, color };
};

const toInputFormat = (dt) => (dt ? dt.replace(" ", "T").slice(0, 16) : "");

const fetchAd = () => {
    loading.value = true;
    loadFailed.value = false;
    api.get(`/admin/api/advertisements/${adId}`)
        .then((res) => {
            ad.value = res.data.data;
            form.value = {
                title: ad.value.title,
                image: null,
                link_url: ad.value.link_url,
                countdown_seconds: ad.value.countdown_seconds,
                display_start_at: toInputFormat(ad.value.display_start_at),
                display_end_at: toInputFormat(ad.value.display_end_at),
                is_active: ad.value.is_active,
            };
        })
        .catch(() => {
            loadFailed.value = true;
            notify("載入失敗", "error");
        })
        .finally(() => {
            loading.value = false;
        });
};

const handleImage = (e) => {
    const file = e.target.files[0];
    if (file) form.value.image = file;
};

const save = () => {
    saving.value = true;
    // 必填欄位
    const fd = new FormData();
    fd.append("_method", "PUT");
    fd.append("title", form.value.title);
    fd.append("link_url", form.value.link_url);
    fd.append("countdown_seconds", form.value.countdown_seconds);
    fd.append("display_start_at", form.value.display_start_at);
    fd.append("display_end_at", form.value.display_end_at);
    fd.append("is_active", form.value.is_active ? 1 : 0);
    if (form.value.image) fd.append("image", form.value.image);

    api.post(`/admin/api/advertisements/${adId}`, fd, {
        headers: { "Content-Type": "multipart/form-data" },
    })
        .then(() => {
            notify(
                form.value.is_active
                    ? "儲存成功，其他廣告已自動下架"
                    : "儲存成功",
            );
            fetchAd();
        })
        .catch((err) => {
            notify(err.response?.data?.message || "儲存失敗", "error");
        })
        .finally(() => {
            saving.value = false;
        });
};

const deleteAd = () => {
    if (!confirm("確定要刪除此廣告？")) return;
    api.delete(`/admin/api/advertisements/${adId}`)
        .then(() => {
            window.location.href = "/admin/advertisements";
        })
        .catch(() => {
            notify("刪除失敗", "error");
        });
};

onMounted(fetchAd);
</script>

<template>
    <AdminLayout>
        <div class="d-flex justify-space-between align-center mb-6">
            <h1 class="text-h6 font-weight-bold">廣告詳情</h1>
            <div class="d-flex ga-2">
                <v-btn
                    variant="tonal"
                    prepend-icon="mdi-arrow-left"
                    @click="window.location.href = '/admin/advertisements'"
                    >返回</v-btn
                >
                <v-btn
                    variant="tonal"
                    color="error"
                    prepend-icon="mdi-delete"
                    @click="deleteAd"
                    >刪除廣告</v-btn
                >
                <v-btn
                    variant="tonal"
                    color="primary"
                    prepend-icon="mdi-check"
                    :loading="saving"
                    :disabled="loading"
                    @click="save"
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
            title="無法載入廣告資料"
            text="請確認網路連線後重新整理，或返回廣告列表。"
        />

        <v-row v-else>
            <v-col cols="12" md="7">
                <v-text-field
                    v-model="form.title"
                    label="廣告標題 *"
                    variant="outlined"
                    class="mb-3"
                />
                <v-text-field
                    v-model="form.link_url"
                    label="點擊跳轉 URL（選填）"
                    hint="留空預設跳轉至 /shop"
                    persistent-hint
                    variant="outlined"
                    class="mb-3"
                />
                <v-text-field
                    v-model="form.display_start_at"
                    label="展示開始時間 *"
                    type="datetime-local"
                    variant="outlined"
                    class="mb-3"
                />
                <v-text-field
                    v-model="form.display_end_at"
                    label="展示結束時間 *"
                    type="datetime-local"
                    variant="outlined"
                    class="mb-3"
                />
                <v-text-field
                    v-model.number="form.countdown_seconds"
                    label="倒數秒數 *"
                    type="number"
                    min="3"
                    max="120"
                    variant="outlined"
                    class="mb-3"
                />
                <v-switch
                    v-model="form.is_active"
                    label="立即上架"
                    color="primary"
                />
            </v-col>

            <v-col cols="12" md="5">
                <div
                    class="img-wrapper w-100 rounded-lg overflow-hidden cursor-pointer border-md border-dashed bg-grey-lighten-4"
                    style="aspect-ratio: 4 / 3"
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
                        class="d-flex flex-column align-center justify-center ga-2 text-grey h-100"
                    >
                        <v-icon size="48" color="grey">mdi-image-plus</v-icon>
                        <span>點擊上傳圖片</span>
                    </div>
                </div>
                <p class="text-caption text-grey mt-2 text-center">
                    點擊圖片更換
                </p>
                <p class="text-caption text-grey mt-2 text-center">
                    上傳圖片勿超過 2 MB
                </p>
                <input
                    ref="fileInput"
                    type="file"
                    accept="image/*"
                    hidden
                    @change="handleImage"
                />
            </v-col>
        </v-row>

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
    transition: border-color 0.2s;
}
.img-wrapper:hover {
    border-color: #409eff;
}
</style>
