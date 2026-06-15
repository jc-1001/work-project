<script setup>
import { ref } from "vue";
import api from "../bootstrap";


const props = defineProps({
    modelValue: Boolean,
});
const emit = defineEmits(["update:modelValue", "created"]);

const showPwd = ref(false);
const showPwdConfirm = ref(false);
const formRef = ref(null);
const submitting = ref(false);
const form = ref({
    name: "",
    email: "",
    password: "",
    passwordConfirm: "",
    role: "admin",
});

const nameRef = ref(null);
const emailRef = ref(null);
const passwordRef = ref(null);
const passwordConfirmRef = ref(null);

const roleOptions = [
    { title: "一般管理員", value: "admin" },
    { title: "超級管理員", value: "super_admin" },
];

const rules = {
    required: (v) => !!v || "此欄位為必填",
    email: (v) => /.+@.+\..+/.test(v) || "請輸入有效的 Email",
    minLength: (v) => (v && v.length >= 8) || "密碼至少 8 個字元",
    passwordMatch: (v) => v === form.value.password || "兩次密碼不一致",
};

function close() {
    emit("update:modelValue", false);
}

async function submit() {
    const { valid } = await formRef.value.validate();
    if (!valid) return;
    submitting.value = true;

    api.post("/api/admin/administrators", {
            name: form.value.name,
            email: form.value.email,
            password: form.value.password,
            password_confirmation: form.value.passwordConfirm,
            role: form.value.role,
        })
    .then((res)=>{
        emit("created", res.data.admin);
        close();
    })
    .catch ((err)=> {
        emit("created", null, err.response?.data?.message || "新增失敗");
    })
    .finally(()=> {
        submitting.value = false;
    })
}

function onOpen() {
    form.value = {
        name: "",
        email: "",
        password: "",
        passwordConfirm: "",
        role: "admin",
    };
    formRef.value?.reset();
}
</script>

<template>
    <v-dialog
        :model-value="modelValue"
        max-width="480"
        persistent
        @after-enter="onOpen"
    >
        <v-card>
            <v-card-title class="pt-5 px-6">新增管理員</v-card-title>
            <v-card-text class="px-6">
                <v-form ref="formRef">
                    <v-text-field
                        ref="nameRef"
                        v-model="form.name"
                        label="管理員姓名 *"
                        variant="outlined"
                        density="compact"
                        :rules="[rules.required]"
                        counter="20"
                        class="mb-3"
                        @keydown.enter.prevent="emailRef.focus()"
                    />
                    <v-text-field
                        ref="emailRef"
                        v-model="form.email"
                        label="Email（登入帳號）*"
                        variant="outlined"
                        density="compact"
                        :rules="[rules.required, rules.email]"
                        class="mb-3"
                        @keydown.enter.prevent="passwordRef.focus()"
                    />
                    <v-text-field
                        ref="passwordRef"
                        v-model="form.password"
                        label="密碼 *"
                        :type="showPwd ? 'text' : 'password'"
                        :append-inner-icon="showPwd ? 'mdi-eye' : 'mdi-eye-off'"
                        @click:append-inner="showPwd = !showPwd"
                        variant="outlined"
                        density="compact"
                        :rules="[rules.required, rules.minLength]"
                        class="mb-3"
                        @keydown.enter.prevent="passwordConfirmRef.focus()"
                    />
                    <v-text-field
                        ref="passwordConfirmRef"
                        v-model="form.passwordConfirm"
                        label="再次確認密碼 *"
                        :type="showPwdConfirm ? 'text' : 'password'"
                        :append-inner-icon="
                            showPwdConfirm ? 'mdi-eye' : 'mdi-eye-off'
                        "
                        @click:append-inner="showPwdConfirm = !showPwdConfirm"
                        variant="outlined"
                        density="compact"
                        :rules="[rules.required, rules.passwordMatch]"
                        class="mb-3"
                    />
                    <v-select
                        v-model="form.role"
                        :items="roleOptions"
                        label="管理員身分 *"
                        variant="outlined"
                        density="compact"
                        :rules="[rules.required]"
                    />
                </v-form>
            </v-card-text>
            <v-card-actions class="px-6 pb-5">
                <v-spacer />
                <v-btn variant="text" @click="close">取消</v-btn>
                <v-btn
                    color="primary"
                    variant="tonal"
                    :loading="submitting"
                    @click="submit"
                >
                    確定新增
                </v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>
