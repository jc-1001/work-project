<script setup>
    import { ref, computed, onMounted, watch } from 'vue'
    import api from '../../../bootstrap.js'
    import adminLayout from '../../../layouts/AdminLayout.vue'

    const window = globalThis

    const loading = ref(false)
    const contactMessages = ref([])
    const selected = ref([])
    const search = ref('')
    const snackbar = ref(false)
    const snackbarText = ref('')
    const snackbarColor = ref('success')
    const selectedIsActive = ref(null)
    const currentPage = ref(1)

    const filteredMessages = computed(() => {
        if (!selectedIsActive.value) return contactMessages.value
        return contactMessages.value.filter((m) => m.status === selectedIsActive.value)
    })

    const total = computed(() => filteredMessages.value.length)

    const STATUS_CONFIG = {
        pending: { label: '未回覆', color: 'error' },
        replied: { label: '已回覆', color: 'success' },
    }

    const CATEGORY_LABEL = {
        product: '商品問題',
        order: '訂單問題',
        other: '其他',
    }

    const headers = [
        { title: '姓名', key: 'name', sortable: false, align: 'center' },
        { title: '電子郵箱', key: 'email', sortable: false },
        { title: '問題類別', key: 'category', sortable: false, align: 'center' },
        { title: '建立時間', key: 'created_at', sortable: true, align: 'center' },
        { title: '狀態', key: 'status', sortable: false, align: 'center' },
        {
            title: '操作',
            key: 'actions',
            sortable: false,
            align: 'center',
            width: '100px',
        },
    ]

    const statusOptions = [
        { title: '已回復', value: 'replied' },
        { title: '未回覆', value: 'pending' },
    ]

    watch(selectedIsActive, () => {
        currentPage.value = 1
    })

    const itemsPerPageOptions = [
        { value: 10, title: '10 筆' },
        { value: 15, title: '15 筆' },
        { value: 25, title: '25 筆' },
        { value: 50, title: '50 筆' },
    ]

    function fetchContactMessages() {
        loading.value = true
        api.get('/api/admin/contactMessages')
            .then((res) => {
                contactMessages.value = res.data.contact_messages
            })
            .catch(() => {
                showMessage('載入列表失敗', 'error')
            })
            .finally(() => {
                loading.value = false
            })
    }

    function showMessage(text, color = 'success') {
        snackbarText.value = text
        snackbarColor.value = color
        snackbar.value = true
    }

    function handleRowClick(_, { item }) {
        window.location.href = `/admin/reply/${item.id}`
    }

    onMounted(fetchContactMessages)
</script>

<template>
    <adminLayout>
        <v-row align="center" class="mb-2">
            <v-col>
                <h1 class="text-h6 font-weight-bold">客服問題清單</h1>
            </v-col>
        </v-row>

        <v-row align="center" class="mb-2">
            <v-col cols="6" sm="3" md="2">
                <v-select
                    v-model="selectedIsActive"
                    prepend-inner-icon="mdi-reply"
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
            <v-col class="text-right text-caption text-medium-emphasis">共 {{ total }} 筆 | 本頁 {{ contactMessages.length }} 筆</v-col>
        </v-row>
        <v-row>
            <v-col>
                <v-data-table
                    v-model="selected"
                    item-value="id"
                    :headers="headers"
                    :items="filteredMessages"
                    :search="search"
                    :loading="loading"
                    :items-per-page="15"
                    :items-per-page-options="itemsPerPageOptions"
                    loading-text="載入中，請稍候..."
                    no-data-text="查無資料"
                    items-per-page-text="每頁顯示"
                    class="clickable-rows"
                    hover
                    @click:row="handleRowClick"
                >
                    <template #item.category="{ item }">
                        {{ CATEGORY_LABEL[item.category] ?? item.category }}
                    </template>

                    <template #item.created_at="{ item }">
                        {{ item.created_at?.slice(0, 10) }}
                    </template>

                    <template #item.status="{ item }">
                        <v-chip :color="STATUS_CONFIG[item.status]?.color" size="small" label>
                            {{ STATUS_CONFIG[item.status]?.label }}
                        </v-chip>
                    </template>

                    <template #item.actions="{ item }">
                        <div class="d-flex align-center justify-center" @click.stop>
                            <v-btn icon="mdi-email-edit-outline" variant="text" size="small" @click="window.location.href = `/admin/reply/${item.id}`" />
                        </div>
                    </template>
                </v-data-table>
            </v-col>
        </v-row>

        <v-snackbar v-model="snackbar" :color="snackbarColor" timeout="3000">
            {{ snackbarText }}
        </v-snackbar>
    </adminLayout>
</template>
