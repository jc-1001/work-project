<script setup>
import { ref, watch } from "vue";
import api from "../bootstrap";

const props = defineProps({
    modelValue: Boolean,
});
const emit = defineEmits(["update:modelValue", "created"]);

const formRef = ref(null);
const submitting = ref(false);
const form = ref(defaultForm());

function defaultForm() {
    return {
        code: "",
        discount_type: "fixed",
        discount_value: "",
        min_order_amount: "",
        max_discount_amount: "",
        max_uses: "",
        expires_at: "",
        is_active: true,
    };
}

watch(
    () => props.modelValue,
    (val) => {
        if (val) {
            form.value = defaultForm();
            formRef.value?.resetValidation();
        }
    },
);

const rules = {
    required:       (v) => !!v || "此欄位必填",
    positiveNumber: (v) => !v || Number(v) >= 0 || "請輸入正數",
    positiveInt:    (v) => !v || (Number.isInteger(Number(v)) && Number(v) >= 1) || "請輸入正整數",
};

async function submit() {
    const { valid } = await formRef.value.validate();
    if (!valid) return;

    submitting.value = true;
    
        const payload = {
            code:                form.value.code.trim().toUpperCase(),
            discount_type:       form.value.discount_type,
            discount_value:      Number(form.value.discount_value),
            min_order_amount:    form.value.min_order_amount    || null,
            max_discount_amount: form.value.max_discount_amount || null,
            max_uses:            form.value.max_uses            || null,
            expires_at:          form.value.expires_at          || null,
            is_active:           form.value.is_active,
        };
        api.post("/api/admin/coupons", payload)
        .then((res)=>{
            emit("created", res.data.coupon);
        })
    .catch ((err) =>{
        const errors = err.response?.data?.errors;
        if (errors?.code) alert(errors.code[0]);
        else alert(err.response?.data?.message ?? "新增失敗，請稍後再試");
    }) 
    .finally(()=> {
        submitting.value = false;
    })
}
</script>

<template>
    <v-dialog
        :model-value="modelValue"
        max-width="480"
        @update:model-value="emit('update:modelValue', $event)"
    >
        <v-card rounded="xl">
            <v-card-title class="pt-5 px-6 text-h6">新增優惠碼</v-card-title>
            <v-card-text class="px-6">
                <v-form ref="formRef">
                    <v-text-field
                        v-model="form.code"
                        label="折扣碼"
                        variant="outlined"
                        density="compact"
                        placeholder="如：SUMMER20"
                        :rules="[rules.required]"
                        class="mb-3"
                    />
                    <v-select
                        v-model="form.discount_type"
                        :items="[
                            { title: '固定金額 (NT$)', value: 'fixed' },
                            { title: '百分比 (%)', value: 'percent' },
                        ]"
                        label="折扣類型"
                        variant="outlined"
                        density="compact"
                        :rules="[rules.required]"
                        class="mb-3"
                    />
                    <v-text-field
                        v-model="form.discount_value"
                        :label="form.discount_type === 'percent' ? '折扣百分比 (%)' : '折扣金額 (NT$)'"
                        variant="outlined"
                        density="compact"
                        type="number"
                        :rules="[rules.required, rules.positiveNumber]"
                        class="mb-3"
                    />
                    <v-text-field
                        v-if="form.discount_type === 'percent'"
                        v-model="form.max_discount_amount"
                        label="最高折抵金額 (NT$)（選填）"
                        variant="outlined"
                        density="compact"
                        type="number"
                        :rules="[rules.positiveNumber]"
                        class="mb-3"
                    />
                    <v-text-field
                        v-model="form.min_order_amount"
                        label="最低消費金額 (NT$)（選填）"
                        variant="outlined"
                        density="compact"
                        type="number"
                        :rules="[rules.positiveNumber]"
                        class="mb-3"
                    />
                    <v-text-field
                        v-model="form.max_uses"
                        label="使用次數上限（選填，空白 = 無限）"
                        variant="outlined"
                        density="compact"
                        type="number"
                        :rules="[rules.positiveInt]"
                        class="mb-3"
                        hint="一組優惠碼可供多少人使用"
                        persistent-hint
                    />
                    <v-text-field
                        v-model="form.expires_at"
                        label="到期日（選填，空白 = 永不過期）"
                        variant="outlined"
                        density="compact"
                        type="date"
                        class="mt-8 mb-3"
                    />
                    <v-switch
                        v-model="form.is_active"
                        label="立即啟用"
                        color="success"
                        density="compact"
                        hide-details
                    />
                </v-form>
            </v-card-text>
            <v-card-actions class="px-6 pb-5 ga-2">
                <v-spacer />
                <v-btn variant="text" @click="emit('update:modelValue', false)">取消</v-btn>
                <v-btn color="primary" variant="tonal" :loading="submitting" @click="submit">新增</v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>
