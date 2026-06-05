<script setup>
    import { ref } from 'vue'
    import api from '../../bootstrap'
    const account = ref('')
    const password = ref('')
    const showPwd = ref(false)
    const loading = ref(false)

    const snackbar = ref(false)
    const snackbarText = ref('')
    const snackbarColor = ref('success')

    function showMessage(text, color = 'success') {
        snackbarText.value = text
        snackbarColor.value = color
        snackbar.value = true
    }

    async function login() {
        if (!account.value || !password.value) {
            showMessage('請輸入帳號與密碼', 'warning')
            return
        }
        loading.value = true
        try {
            const res = await api.post('/admin/login', {
                email: account.value,
                password: password.value,
            })
            window.location.href = res.data.redirect ?? '/admin/products'
        } catch (err) {
            showMessage(err.response?.data?.message || '帳號或密碼錯誤', 'error')
            loading.value = false
        }
    }
</script>

<template>
    <v-container class="d-flex align-center" fluid style="min-height: 100vh">
        <v-row justify="center" align="center">
            <v-col cols="12" sm="6" md="4">
                <v-card class="pa-6" elevation="4">
                    <v-card-title class="text-center">後台登入</v-card-title>
                    <v-card-text>
                        <v-text-field v-model="account" label="帳號" prepend-inner-icon="mdi-account" variant="outlined" />
                        <v-text-field
                            v-model="password"
                            label="密碼"
                            prepend-inner-icon="mdi-lock"
                            :type="showPwd ? 'text' : 'password'"
                            :append-inner-icon="showPwd ? 'mdi-eye-off' : 'mdi-eye'"
                            @click:append-inner="showPwd = !showPwd"
                            variant="outlined"
                        />
                    </v-card-text>
                    <v-card-actions>
                        <v-btn block color="primary" :loading="loading" @click="login">登入</v-btn>
                    </v-card-actions>
                </v-card>
            </v-col>
        </v-row>
    </v-container>

    <v-snackbar v-model="snackbar" :color="snackbarColor" timeout="3000" location="top">
        {{ snackbarText }}
    </v-snackbar>
</template>
