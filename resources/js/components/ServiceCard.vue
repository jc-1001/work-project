<script setup>
import { ref, reactive } from "vue";
import api from "../bootstrap";

const sheet = ref(false);
const activeTab = ref("faq");
const snackbar = ref({ show: false, text: "", color: "success" });
const formRef = ref(null);
const submitting = ref(false);
const loading = ref(true);
const storing = ref(false);
const emit = defineEmits(["created"]);

const faqList = [
    {
        id: "1",
        question: "如何查詢訂單狀態？",
        answer: "登入後至「會員中心」頁面後，點選「我的訂單」即可查閱，並可點選再買一次。",
    },
    {
        id: "2",
        question: "可以按分類查找商品嗎？",
        answer: "至「商城」最頂部即可選擇商品種類。",
    },
    {
        id: "3",
        question: "如何更改自己的 姓名 / 暱稱 ？",
        answer: "登入後至「會員中心」頁面後，點選「個人資料」即可更改。",
    },
    {
        id: "4",
        question: "運費怎麼算？",
        answer: "只要於商城消費，均須收取 $60 運費。",
    },
];

const contactOptions = [
    { title: "商品問題", value: "product" },
    { title: "訂單問題", value: "order" },
    { title: "其他", value: "other" },
];

const form = reactive({
    name: "",
    email: "",
    category: "",
    description: "",
});

const rules = {
    required: (v) => !!v || "此欄位為必填",
    email: (v) => /.+@.+\..+/.test(v) || "Email 格式不正確",
};

async function submitForm() {
    if (storing.value) return;

    const { valid } = await formRef.value.validate();
    if (!valid) return;

    submitting.value = true;
    storing.value = true;
    const payload = {
        name: form.name || null,
        email: form.email || null,
        category: form.category,
        description: form.description || null,
    };
    api.post("/newMessage", payload)
        .then((res) => {
            snackbar.value = {
                show: true,
                text: "已收到您的問題，我們會盡快回覆！",
                color: "success",
            };
        })
        .catch((err) => {
            const errors = err.response?.data?.errors;
            if (errors?.code) alert(errors.code[0]);
            else alert(err.response?.data?.message ?? "新增失敗，請稍後再試");
        })
        .finally(() => {
            sheet.value = false;
            submitting.value = false;
            storing.value = false;
        });
}

defineExpose({ open: () => (sheet.value = true) });
</script>

<template>
    <Teleport to="body">
        <Transition name="service-sheet">
            <div
                v-if="sheet"
                style="
                    position: fixed;
                    inset: 0;
                    z-index: 1000;
                    display: flex;
                    flex-direction: column;
                    justify-content: flex-end;
                "
            >
                <div
                    style="
                        position: absolute;
                        inset: 0;
                        background: rgba(0, 0, 0, 0.5);
                    "
                    @click="sheet = false"
                />
                <div
                    style="
                        position: relative;
                        height: 80vh;
                        overflow-y: auto;
                        border-radius: 20px 20px 0 0;
                        background: white;
                    "
                >
                    <div
                        class="d-flex align-center justify-space-between px-5 pt-4 pb-2"
                    >
                        <span class="text-subtitle-1 font-weight-medium"
                            >客服中心</span
                        >
                        <v-btn
                            icon="mdi-close"
                            variant="text"
                            size="small"
                            @click="sheet = false"
                        />
                    </div>

                    <div class="px-4 pb-4">
                        <v-tabs
                            v-model="activeTab"
                            color="primary"
                            density="compact"
                            class="mb-3"
                        >
                            <v-tab value="faq">常見問題</v-tab>
                            <v-tab value="contact">聯繫我們</v-tab>
                        </v-tabs>

                        <v-tabs-window v-model="activeTab">
                            <v-tabs-window-item value="faq">
                                <v-expansion-panels variant="accordion">
                                    <v-expansion-panel
                                        v-for="item in faqList"
                                        :key="item.id"
                                        :value="item.id"
                                        :title="item.question"
                                    >
                                        <v-expansion-panel-text
                                            class="text-body-2"
                                        >
                                            {{ item.answer }}
                                        </v-expansion-panel-text>
                                    </v-expansion-panel>
                                </v-expansion-panels>
                            </v-tabs-window-item>

                            <v-tabs-window-item value="contact">
                                <v-form ref="formRef">
                                    <v-text-field
                                        v-model="form.name"
                                        label="您的姓名"
                                        :rules="[rules.required]"
                                        variant="outlined"
                                        density="compact"
                                        clearable
                                        class="my-3"
                                    />
                                    <v-select
                                        v-model="form.category"
                                        :rules="[rules.required]"
                                        :items="contactOptions"
                                        item-title="title"
                                        item-value="value"
                                        label="問題類型"
                                        variant="outlined"
                                        density="compact"
                                        placeholder="請選擇"
                                        class="my-3"
                                    />
                                    <v-text-field
                                        v-model="form.email"
                                        :rules="[rules.required]"
                                        label="您的 Email"
                                        variant="outlined"
                                        density="compact"
                                        clearable
                                        placeholder="example@mail.com"
                                        class="mb-3"
                                    />
                                    <v-textarea
                                        v-model="form.description"
                                        label="問題描述"
                                        variant="outlined"
                                        density="compact"
                                        clearable
                                        rows="4"
                                        placeholder="請描述您的問題..."
                                        class="mb-3"
                                    />
                                    <v-btn
                                        color="primary"
                                        variant="tonal"
                                        :loading="storing"
                                        @click="submitForm"
                                        >送出</v-btn
                                    >
                                </v-form>
                            </v-tabs-window-item>
                        </v-tabs-window>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>

    <v-snackbar
        v-model="snackbar.show"
        :color="snackbar.color"
        location="top"
        timeout="3000"
    >
        {{ snackbar.text }}
    </v-snackbar>
</template>

<style scoped>
.service-sheet-enter-active,
.service-sheet-leave-active {
    transition: opacity 0.25s ease;
}
.service-sheet-enter-active > div:last-child,
.service-sheet-leave-active > div:last-child {
    transition: transform 0.3s ease;
}
.service-sheet-enter-from,
.service-sheet-leave-to {
    opacity: 0;
}
.service-sheet-enter-from > div:last-child,
.service-sheet-leave-to > div:last-child {
    transform: translateY(100%);
}
</style>
