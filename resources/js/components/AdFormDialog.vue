<script setup>
import { ref } from "vue";
import api from "../bootstrap";

defineProps({
    modelValue: { type: Boolean, default: false },
});

const emit = defineEmits(["update:modelValue", "created"]);

const creating = ref(false);
const snackbar = ref({ show: false, text: "", color: "success" });

const emptyForm = () => ({
    title: "",
    image: [],
    link_url: "/shop",
    countdown_seconds: 10,
    display_start_at: "",
    display_end_at: "",
    is_active: true,
});

const form = ref(emptyForm());

const notify = (text, color = "success") => {
    snackbar.value = { show: true, text, color };
};

const close = () => {
    emit("update:modelValue", false);
};

const submit = () => {
    if (
        !form.value.title ||
        !form.value.image ||
        !form.value.display_start_at ||
        !form.value.display_end_at
    ) {
        notify("請填寫所有必填欄位", "warning");
        return;
    }
    creating.value = true;
    const fd = new FormData();
    fd.append("title", form.value.title);
    fd.append("image", form.value.image);
    fd.append("link_url", form.value.link_url);
    fd.append("countdown_seconds", form.value.countdown_seconds);
    fd.append("display_start_at", form.value.display_start_at);
    fd.append("display_end_at", form.value.display_end_at);
    fd.append("is_active", form.value.is_active ? 1 : 0);

    api.post("/admin/api/advertisements", fd, {
        headers: { "Content-Type": "multipart/form-data" },
    })

        .then(() => {
            form.value = emptyForm();
            emit("created");
            close();
        })
        .catch((err) => {
            notify(err.response?.data?.message || "新增失敗", "error");
        })
        .finally(() => {
            creating.value = false;
        });
};
</script>

<template>
    <v-dialog
        :model-value="modelValue"
        max-width="560"
        persistent
        @update:model-value="$emit('update:modelValue', $event)"
    >
        <v-card>
            <v-card-title class="pa-6 pb-2">新增廣告</v-card-title>
            <v-card-text class="pa-6 pt-2">
                <v-text-field
                    v-model="form.title"
                    label="廣告標題 *"
                    variant="outlined"
                    class="mb-3"
                />
                <v-file-input
                    v-model="form.image"
                    label="廣告圖片 *"
                    accept="image/*"
                    variant="outlined"
                    prepend-icon=""
                    prepend-inner-icon="mdi-image"
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
                    v-model.number="form.countdown_seconds"
                    label="倒數秒數 *"
                    type="number"
                    min="3"
                    max="120"
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
                <v-switch
                    v-model="form.is_active"
                    label="立即上架"
                    color="primary"
                />
            </v-card-text>
            <v-card-actions class="pa-6 pt-0">
                <v-spacer />
                <v-btn variant="text" @click="close">取消</v-btn>
                <v-btn color="primary" :loading="creating" @click="submit"
                    >儲存</v-btn
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
</template>
