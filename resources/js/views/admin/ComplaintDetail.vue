<script setup>
import { ref, onMounted } from "vue";
import api from "../../bootstrap.js";
import AdminLayout from "../../layouts/AdminLayout.vue";
import { getImageUrl } from "../../utils/image.js";

const complaintId = window.location.pathname.split("/").filter(Boolean).pop();

const loading = ref(true);
const loadError = ref("");
const actionLoading = ref(false);
const complaint = ref(null);
const snackbar = ref({ show: false, text: "", color: "success" });
const confirmDialog = ref({ show: false, action: null });
const previewImg = ref({ show: false, src: "" });

const showMessage = (text, color = "success") => {
    snackbar.value = { show: true, text, color };
};

const reasonsColorMap = {
    偏離主題: "indigo",
    垃圾內容: "orange",
    利益衝突: "purple",
    不雅用語: "green",
    內容有害: "teal",
    霸凌或騷擾內容: "error",
    歧視內容或仇恨言論: "cyan",
    個人資訊: "deep-orange",
    沒有幫助: "brown",
};
const getReasonColor = (reason) => reasonsColorMap[reason] ?? "grey";

const statusMap = {
    pending: { label: "待處理", color: "info" },
    dismissed: { label: "已駁回", color: "default" },
};
const getStatusLabel = (s) => statusMap[s]?.label ?? s;
const getStatusColor = (s) => statusMap[s]?.color ?? "grey";

const confirmLabels = {
    delete_review: { title: "刪除評論", color: "error", btnText: "確認刪除" },
    dismiss: { title: "駁回檢舉", color: "warning", btnText: "確認駁回" },
};

function fetchComplaint() {
    loading.value = true;
    loadError.value = "";
    api.get(`/api/admin/complaints/${complaintId}`)
        .then((res) => {
            console.log("[ComplaintDetail] API response:", res.data);
            complaint.value = res.data.complaint ?? res.data;
        })
        .catch((err) => {
            console.error("[ComplaintDetail] API error:", err.response ?? err);
            loadError.value =
                err.response?.data?.message ?? "載入檢舉詳情失敗，請稍後再試";
        })
        .finally(() => {
            loading.value = false;
        });
}

function openConfirm(action) {
    confirmDialog.value = { show: true, action };
}

function executeAction() {
    const action = confirmDialog.value.action;
    confirmDialog.value.show = false;
    actionLoading.value = true;

    api.post("/api/admin/complaints/batch", {
        ids: [complaint.value.id],
        action,
    })
        .then(() => {
            if (action === "delete_review") {
                showMessage("已刪除評論");
                setTimeout(() => {
                    window.location.href = "/admin/complaints";
                }, 1200);
            } else {
                showMessage("已駁回檢舉");
                complaint.value = { ...complaint.value, status: "dismissed" };
            }
        })
        .catch(() => {
            showMessage("操作失敗，請稍後再試", "error");
        })
        .finally(() => {
            actionLoading.value = false;
        });
}

onMounted(() => fetchComplaint());
</script>

<template>
    <AdminLayout>
        <v-row align="center" class="mb-4">
            <v-col>
                <div class="d-flex align-center ga-2">
                    <v-btn
                        icon="mdi-arrow-left"
                        variant="text"
                        size="small"
                        href="/admin/complaints"
                    />
                    <h1 class="text-h6 font-weight-bold">檢舉評論詳情</h1>
                    <v-chip
                        v-if="complaint"
                        :color="getStatusColor(complaint.status)"
                        size="small"
                    >
                        {{ getStatusLabel(complaint.status) }}
                    </v-chip>
                </div>
            </v-col>
            <v-col
                cols="auto"
                class="d-flex align-center ga-2 flex-wrap justify-end"
            >
                <template v-if="complaint && complaint.status === 'pending'">
                    <v-btn
                        variant="tonal"
                        color="warning"
                        prepend-icon="mdi-close-circle-outline"
                        :loading="actionLoading"
                        @click="openConfirm('dismiss')"
                    >
                        駁回檢舉
                    </v-btn>
                    <v-btn
                        variant="tonal"
                        color="error"
                        prepend-icon="mdi-delete"
                        :loading="actionLoading"
                        @click="openConfirm('delete_review')"
                    >
                        刪除評論
                    </v-btn>
                </template>
            </v-col>
        </v-row>

        <template v-if="loading">
            <v-row class="mb-4">
                <v-col cols="12" md="3">
                    <v-skeleton-loader type="image, list-item" />
                </v-col>
                <v-col cols="12" md="9">
                    <v-skeleton-loader
                        type="list-item-avatar, divider, paragraph"
                    />
                </v-col>
            </v-row>
            <v-skeleton-loader type="list-item-three-line" />
        </template>

        <v-alert
            v-else-if="loadError"
            type="error"
            variant="tonal"
            class="mb-4"
        >
            {{ loadError }}
            <template #append>
                <v-btn variant="text" @click="fetchComplaint">重新載入</v-btn>
            </template>
        </v-alert>

        <template v-else-if="complaint">
            <v-row class="mb-4">
                <v-col cols="12" md="3">
                    <v-card height="100%">
                        <v-card-title
                            class="text-subtitle-2 text-medium-emphasis pt-4 pb-2 px-4"
                        >
                            <v-icon size="16" class="mr-1">mdi-shopping</v-icon
                            >評論商品
                        </v-card-title>
                        <v-divider />
                        <template v-if="complaint.review?.product">
                            <v-img
                                :src="
                                    getImageUrl(complaint.review.product.image)
                                "
                                height="160"
                                cover
                                class="bg-grey-lighten-3"
                            >
                                <template #error>
                                    <div
                                        class="d-flex align-center justify-center fill-height"
                                    >
                                        <v-icon size="48" color="grey-lighten-2"
                                            >mdi-image-off</v-icon
                                        >
                                    </div>
                                </template>
                            </v-img>
                            <v-card-text>
                                <div class="font-weight-medium text-body-2">
                                    {{ complaint.review.product.name }}
                                </div>
                            </v-card-text>
                        </template>
                        <v-card-text
                            v-else
                            class="text-medium-emphasis text-body-2"
                        >
                            （商品資訊不存在）
                        </v-card-text>
                    </v-card>
                </v-col>

                <v-col cols="12" md="9">
                    <v-card height="100%">
                        <v-card-title
                            class="text-subtitle-2 text-medium-emphasis pt-4 pb-2 px-4"
                        >
                            <v-icon size="16" class="mr-1"
                                >mdi-comment-text</v-icon
                            >被檢舉評論
                        </v-card-title>
                        <v-divider />
                        <v-card-text>
                            <template v-if="complaint.review">
                                <div class="d-flex align-center ga-3 mb-3">
                                    <v-avatar size="36" color="primary">
                                        <span
                                            class="text-caption font-weight-bold"
                                        >
                                            {{
                                                complaint.review.user?.name?.charAt(
                                                    0,
                                                ) ?? "?"
                                            }}
                                        </span>
                                    </v-avatar>
                                    <div class="flex-grow-1">
                                        <div
                                            class="font-weight-medium text-body-2"
                                        >
                                            {{
                                                complaint.review.user?.name ??
                                                "（未知用戶）"
                                            }}
                                        </div>
                                        <div
                                            class="text-caption text-medium-emphasis"
                                        >
                                            評論時間：{{
                                                complaint.review.created_at?.slice(
                                                    0,
                                                    10,
                                                ) ?? "—"
                                            }}
                                        </div>
                                    </div>
                                    <v-rating
                                        v-if="complaint.review.rating != null"
                                        :model-value="complaint.review.rating"
                                        color="amber"
                                        density="compact"
                                        size="small"
                                        readonly
                                        half-increments
                                    />
                                </div>
                                <v-divider class="mb-3" />
                                <p
                                    class="text-body-2"
                                    style="
                                        white-space: pre-line;
                                        line-height: 1.7;
                                    "
                                >
                                    {{
                                        complaint.review.content ??
                                        "（無評論內容）"
                                    }}
                                </p>
                                <div
                                    v-if="complaint.review.images?.length"
                                    class="d-flex flex-wrap ga-2 mt-3"
                                >
                                    <div
                                        v-for="img in complaint.review.images"
                                        :key="img.id"
                                        style="width: 100px; height: 100px; flex: 0 0 100px;"
                                    >
                                        <v-img
                                            :src="getImageUrl(img.path)"
                                            width="100"
                                            height="100"
                                            cover
                                            rounded="lg"
                                            class="bg-grey-lighten-3 cursor-pointer"
                                            @click="previewImg = { show: true, src: getImageUrl(img.path) }"
                                        />
                                    </div>
                                </div>
                            </template>
                            <span
                                v-else
                                class="text-medium-emphasis text-body-2"
                                >（評論已刪除）</span
                            >
                        </v-card-text>
                    </v-card>
                </v-col>
            </v-row>

            <v-card class="mb-4">
                <v-card-title
                    class="text-subtitle-2 text-medium-emphasis pt-4 pb-2 px-4"
                >
                    <v-icon size="16" class="mr-1">mdi-flag</v-icon>檢舉資訊
                </v-card-title>
                <v-divider />
                <v-card-text>
                    <v-row>
                        <v-col cols="12" sm="4">
                            <div class="text-caption text-medium-emphasis mb-2">
                                檢舉人
                            </div>
                            <div class="d-flex align-center ga-2">
                                <v-avatar size="28" color="secondary">
                                    <span class="text-caption">
                                        {{
                                            complaint.user?.name?.charAt(0) ??
                                            "?"
                                        }}
                                    </span>
                                </v-avatar>
                                <span class="text-body-2 font-weight-medium">
                                    {{ complaint.user?.name ?? "—" }}
                                </span>
                            </div>
                        </v-col>
                        <v-col cols="12" sm="4">
                            <div class="text-caption text-medium-emphasis mb-2">
                                檢舉時間
                            </div>
                            <div class="text-body-2">
                                {{ complaint.created_at?.slice(0, 10) ?? "—" }}
                            </div>
                        </v-col>
                        <v-col cols="12" sm="4">
                            <div class="text-caption text-medium-emphasis mb-2">
                                處理狀態
                            </div>
                            <v-chip
                                :color="getStatusColor(complaint.status)"
                                size="small"
                            >
                                {{ getStatusLabel(complaint.status) }}
                            </v-chip>
                        </v-col>
                    </v-row>
                    <v-row class="mt-1">
                        <v-col cols="12">
                            <div class="text-caption text-medium-emphasis mb-2">
                                檢舉原因
                            </div>
                            <div v-if="complaint.reasons?.length">
                                <v-chip
                                    v-for="r in complaint.reasons"
                                    :key="r"
                                    :color="getReasonColor(r)"
                                    size="small"
                                    class="mr-1 mb-1"
                                >
                                    {{ r }}
                                </v-chip>
                            </div>
                            <span
                                v-else
                                class="text-body-2 text-medium-emphasis"
                                >—</span
                            >
                        </v-col>
                    </v-row>
                </v-card-text>
            </v-card>
        </template>

        <v-dialog v-model="previewImg.show" max-width="720">
            <v-img
                :src="previewImg.src"
                cover
                @click="previewImg.show = false"
                style="cursor: zoom-out"
            />
        </v-dialog>

        <v-dialog v-model="confirmDialog.show" max-width="420" persistent>
            <v-card v-if="confirmDialog.action">
                <v-card-title class="pt-5 px-5">
                    {{ confirmLabels[confirmDialog.action].title }}
                </v-card-title>
                <v-card-text class="px-5">
                    <template v-if="confirmDialog.action === 'delete_review'">
                        確定要刪除這則評論？評論內容與相關圖片將一併移除，此操作無法復原。
                    </template>
                    <template v-else> 確定要駁回此筆檢舉？ </template>
                </v-card-text>
                <v-card-actions class="px-5 pb-4">
                    <v-spacer />
                    <v-btn variant="text" @click="confirmDialog.show = false"
                        >取消</v-btn
                    >
                    <v-btn
                        :color="confirmLabels[confirmDialog.action].color"
                        variant="flat"
                        :loading="actionLoading"
                        @click="executeAction"
                    >
                        {{ confirmLabels[confirmDialog.action].btnText }}
                    </v-btn>
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
    </AdminLayout>
</template>
