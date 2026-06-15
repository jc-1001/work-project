<script setup>
    import { ref, onMounted } from 'vue'
    import api from '../../../bootstrap.js'
    import adminLayout from '../../../layouts/AdminLayout.vue'

    const window = globalThis
    const replyId = document.getElementById('app')?.dataset?.id

    const loading = ref(true)
    const storing = ref(false)
    const loadFailed = ref(false)
    const message = ref(null)
    const replyContent = ref('')
    const snackbar = ref({ show: false, text: '', color: 'success' })

    const STATUS_CONFIG = {
        pending: { label: '未回覆', color: 'error' },
        replied: { label: '已回覆', color: 'success' },
    }

    const CATEGORY_LABEL = {
        product: '商品問題',
        order: '訂單問題',
        other: '其他',
    }

    const notify = (text, color = 'success') => {
        snackbar.value = { show: true, text, color }
    }

    function fetchContactMessage() {
        loading.value = true
        api.get(`/api/admin/contactMessages/${replyId}`)
            .then((res) => {
                message.value = res.data.contact_message
                replyContent.value = res.data.contact_message.reply_content ?? ''
            })
            .catch((err) => {
                loadFailed.value = true
                const msg = err.response?.data?.message ?? '載入資料失敗，請稍後再試'
                notify(msg, 'error')
            })
            .finally(() => {
                loading.value = false
            })
    }

    function store() {
        storing.value = true
        api.patch(`/api/admin/contactMessages/${replyId}/reply`, {
            reply_content: replyContent.value,
        })
            .then((res) => {
                message.value = res.data.contact_message
                notify('回覆成功')
            })
            .catch((err) => {
                if (err.response?.status === 422) {
                    const errors = err.response.data?.errors
                    const msg = errors ? Object.values(errors).flat().join('、') : (err.response.data?.message ?? '資料驗證失敗')
                    notify(msg, 'error')
                } else {
                    notify('儲存失敗，請稍後再試', 'error')
                }
            })
            .finally(() => {
                storing.value = false
            })
    }

    onMounted(fetchContactMessage)
</script>

<template>
    <adminLayout>
        <v-row align="center" class="mb-5">
            <v-col>
                <h1 class="text-h6 font-weight-bold">回覆使用者</h1>
            </v-col>
            <v-col cols="auto" class="d-flex flex-row ga-2 align-center">
                <v-chip v-if="message" :color="STATUS_CONFIG[message.status]?.color" variant="tonal" label>
                    {{ STATUS_CONFIG[message.status]?.label }}
                </v-chip>
                <v-col cols="auto">
                    <v-btn variant="tonal" color="primary" prepend-icon="mdi-arrow-left" @click="window.location.href = '/admin/reply'">返回列表</v-btn>
                </v-col>
            </v-col>
        </v-row>

        <v-row v-if="loading">
            <v-col class="text-center py-16">
                <v-progress-circular indeterminate size="48" />
            </v-col>
        </v-row>

        <v-row v-else-if="loadFailed">
            <v-col>
                <v-alert type="error" variant="tonal" rounded="lg">載入失敗，請重新整理頁面</v-alert>
            </v-col>
        </v-row>

        <v-row v-else-if="message" align="stretch">
            <v-col cols="12" md="5">
                <v-card rounded="lg" elevation="1" height="100%">
                    <v-card-item class="pa-4">
                        <v-card-title class="text-body-1">
                            <v-icon icon="mdi-account-circle" class="mr-2" />
                            客戶問題資訊
                        </v-card-title>
                    </v-card-item>

                    <v-card-text class="pa-4">
                        <v-list density="comfortable" lines="two" bg-color="transparent">
                            <v-list-item prepend-icon="mdi-account-outline" title="姓名" :subtitle="message.name" />
                            <v-list-item prepend-icon="mdi-email-outline" title="電子郵箱" :subtitle="message.email" />
                            <v-list-item prepend-icon="mdi-calendar-outline" title="建立時間" :subtitle="message.created_at?.slice(0, 10)" />
                            <v-list-item prepend-icon="mdi-tag-outline" title="問題類別">
                                <template #subtitle>
                                    <v-chip size="small" color="orange" variant="tonal" label class="mt-1">
                                        {{ CATEGORY_LABEL[message.category] ?? message.category }}
                                    </v-chip>
                                </template>
                            </v-list-item>
                        </v-list>

                        <v-divider class="my-3" />

                        <p style="font-size: 24px" class="text-medium-emphasis mb-2">問題描述</p>

                        <v-sheet color="blue-lighten-5" rounded="md" class="pa-3" style="font-size: large">
                            {{ message.description }}
                        </v-sheet>
                    </v-card-text>
                </v-card>
            </v-col>

            <v-col cols="12" md="7">
                <v-card rounded="lg" elevation="1" height="100%">
                    <v-card-item class="pa-4">
                        <v-card-title class="text-body-1">
                            <v-icon icon="mdi-reply" class="mr-2" />
                            {{ message.status === 'replied' ? '重新編輯回覆' : '填寫回覆內容' }}
                        </v-card-title>
                    </v-card-item>

                    <v-card-text class="pa-4">
                        <v-alert v-if="message.replied_at" type="success" variant="tonal" density="compact" rounded="md" class="mb-4" icon="mdi-check-circle-outline">
                            已於 {{ message.replied_at?.slice(0, 10) }} 回覆過
                        </v-alert>

                        <v-textarea v-model="replyContent" label="回覆內容" variant="outlined" rows="9" counter="2000" maxlength="2000" no-resize />

                        <v-btn color="primary" block size="large" prepend-icon="mdi-send" :loading="storing" :disabled="!replyContent.trim()" class="mt-2" @click="store">
                            送出回覆
                        </v-btn>
                    </v-card-text>
                </v-card>
            </v-col>
        </v-row>

        <v-snackbar v-model="snackbar.show" :color="snackbar.color" timeout="3000">
            {{ snackbar.text }}
        </v-snackbar>
    </adminLayout>
</template>
