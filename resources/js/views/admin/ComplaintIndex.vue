<script setup>
    import { ref, onMounted, computed } from 'vue'
    import api from '../../bootstrap.js'
    import AdminLayout from '../../layouts/AdminLayout.vue'

    const loading = ref(true)
    const search = ref('')
    const complaints = ref([])
    const selected = ref([])
    const snackbar = ref({ show: false, text: '', color: 'success' })
    const batchLoading = ref(false)
    const confirmDialog = ref({ show: false, action: null })

    const showMessage = (text, color = 'success') => {
        snackbar.value = { show: true, text, color }
    }

    // prettier-ignore
    const reasonsColorMap = {
        '偏離主題':           'indigo',
        '垃圾內容':           'orange',
        '利益衝突':           'purple',
        '不雅用語':           'green',
        '內容有害':           'teal',
        '霸凌或騷擾內容':     'error',
        '歧視內容或仇恨言論': 'cyan',
        '個人資訊':           'deep-orange',
        '沒有幫助':           'brown',
    }
    const getReasonColor = (reason) => reasonsColorMap[reason] ?? 'grey'

    // prettier-ignore
    const statusMap = {
        pending:   { label: '待處理', color: 'info' },
        dismissed: { label: '已駁回', color: 'default' },
    }
    const getStatusLabel = (s) => statusMap[s]?.label ?? s
    const getStatusColor = (s) => statusMap[s]?.color ?? 'grey'

    const selectedReason = ref(null)
    const selectedStatus = ref(null)
    const reasonOptions = ['偏離主題', '垃圾內容', '利益衝突', '不雅用語', '內容有害', '霸凌或騷擾內容', '歧視內容或仇恨言論', '個人資訊', '沒有幫助']
    const statusOptions = [
        { title: '待處理', value: 'pending' },
        { title: '已駁回', value: 'dismissed' },
    ]

    // prettier-ignore
    const confirmLabels = {
        delete_review: { title: '批次刪除評論', color: 'error',   btnText: '確認刪除' },
        dismiss:       { title: '批次駁回檢舉', color: 'warning',  btnText: '確認駁回' },
    }

    const confirmMsg = computed(() => {
        const action = confirmDialog.value.action
        if (!action) return ''
        const n = selected.value.length
        if (action === 'delete_review') return `確定要刪除這 ${n} 則評論？相關圖片與評論內容將一併移除，此操作無法復原。`
        return `確定要駁回這 ${n} 筆檢舉？`
    })

    const headers = [
        { title: '', key: 'data-table-select', sortable: false, width: '48px' },
        { title: '檢舉人', key: 'reporter', sortable: false, align: 'center', width: '100px' },
        { title: '檢舉時間', key: 'created_at', sortable: true, align: 'center', width: '200px' },
        { title: '被檢舉人', key: 'reported', sortable: false, align: 'center', width: '100px' },
        { title: '檢舉原因', key: 'reasons', sortable: false },
        { title: '狀態', key: 'status', sortable: false, align: 'center', width: '100px' },
        { title: '操作', key: 'actions', sortable: false, align: 'center', width: '80px' },
    ]

    const itemsPerPageOptions = [
        { value: 10, title: '10 筆' },
        { value: 15, title: '15 筆' },
        { value: 25, title: '25 筆' },
        { value: 50, title: '50 筆' },
    ]

    const filteredComplaints = computed(() => {
        return complaints.value.filter((complaint) => {
            const matchSearch = !search.value || complaint.user?.name?.includes(search.value) || complaint.review?.user?.name?.includes(search.value)
            const matchReason = !selectedReason.value || complaint.reasons?.includes(selectedReason.value)
            const matchStatus = !selectedStatus.value || complaint.status === selectedStatus.value
            return matchSearch && matchReason && matchStatus
        })
    })

    function fetchComplaints() {
        api.get('/api/admin/complaints')
            .then((res) => {
                complaints.value = res.data.data
            })
            .catch(() => {
                showMessage('載入檢舉列表失敗', 'error')
            })
            .finally(() => {
                loading.value = false
            })
    }

    function openConfirm(action) {
        confirmDialog.value = { show: true, action }
    }

    function executeBatch() {
        const action = confirmDialog.value.action
        confirmDialog.value.show = false
        batchLoading.value = true

        api.post('/api/admin/complaints/batch', { ids: selected.value, action })
            .then(() => {
                const n = selected.value.length
                const msg = action === 'delete_review' ? `已刪除 ${n} 則評論` : `已駁回 ${n} 筆檢舉`
                showMessage(msg)
                if (action === 'delete_review') {
                    complaints.value = complaints.value.filter((c) => !selected.value.includes(c.id))
                } else {
                    complaints.value = complaints.value.map((c) => (selected.value.includes(c.id) ? { ...c, status: 'dismissed' } : c))
                }
                selected.value = []
            })
            .catch(() => {
                showMessage('操作失敗，請稍後再試', 'error')
            })
            .finally(() => {
                batchLoading.value = false
            })
    }

    onMounted(() => fetchComplaints())
</script>

<template>
    <AdminLayout>
        <v-row align="center" class="mb-2">
            <v-col>
                <h1 class="text-h6 font-weight-bold">檢舉評論列表</h1>
            </v-col>
            <v-col cols="auto" class="d-flex align-center ga-2 flex-wrap justify-end">
                <template v-if="selected.length">
                    <v-chip color="primary" size="small">已選 {{ selected.length }} 項</v-chip>
                    <v-btn variant="tonal" color="warning" prepend-icon="mdi-close-circle-outline" :loading="batchLoading" @click="openConfirm('dismiss')">
                        批次駁回
                    </v-btn>
                    <v-btn variant="tonal" color="error" prepend-icon="mdi-delete" :loading="batchLoading" @click="openConfirm('delete_review')">批次刪除評論</v-btn>
                    <v-btn variant="text" color="grey" @click="selected = []">取消</v-btn>
                </template>
            </v-col>
        </v-row>

        <v-row density="comfortable" align="center" class="mb-2">
            <v-col cols="12" sm="5" md="4">
                <v-text-field
                    v-model="search"
                    density="compact"
                    label="搜尋檢舉人或被檢舉人"
                    prepend-inner-icon="mdi-magnify"
                    clearable
                    variant="outlined"
                    hide-details
                    :style="{ maxWidth: '280px' }"
                />
            </v-col>
            <v-col cols="6" sm="3" md="2">
                <v-select
                    v-model="selectedReason"
                    :items="reasonOptions"
                    label="篩選檢舉原因"
                    clearable
                    density="compact"
                    variant="outlined"
                    hide-details
                    :style="{ maxWidth: '300px' }"
                />
            </v-col>
            <v-col cols="6" sm="3" md="2">
                <v-select
                    v-model="selectedStatus"
                    :items="statusOptions"
                    item-title="title"
                    item-value="value"
                    label="篩選狀態"
                    clearable
                    density="compact"
                    variant="outlined"
                    hide-details
                    :style="{ maxWidth: '160px' }"
                />
            </v-col>
            <v-col class="text-right text-caption text-medium-emphasis">共 {{ filteredComplaints.length }} 筆</v-col>
        </v-row>

        <v-card>
            <v-data-table
                v-model="selected"
                :headers="headers"
                :items="filteredComplaints"
                :loading="loading"
                :items-per-page="15"
                :items-per-page-options="itemsPerPageOptions"
                item-value="id"
                show-select
                hover
                loading-text="載入中，請稍候..."
                no-data-text="目前無檢舉記錄"
                items-per-page-text="每頁顯示"
            >
                <template #item.reporter="{ item }">
                    <span class="text-body-2">{{ item.user?.name ?? '—' }}</span>
                </template>

                <template #item.created_at="{ item }">
                    <span class="text-caption text-medium-emphasis">{{ item.created_at?.slice(0, 10) }}</span>
                </template>

                <template #item.reported="{ item }">
                    <span class="text-body-2">{{ item.review?.user?.name ?? '（評論已刪除）' }}</span>
                </template>

                <template #item.reasons="{ item }">
                    <v-chip v-for="r in item.reasons" :key="r" :color="getReasonColor(r)" size="small" class="mr-1 my-1">{{ r }}</v-chip>
                </template>

                <template #item.status="{ item }">
                    <v-chip :color="getStatusColor(item.status)" size="small">{{ getStatusLabel(item.status) }}</v-chip>
                </template>

                <template #item.actions="{ item }">
                    <v-btn icon="mdi-eye" variant="text" size="small" :href="`/admin/complaints/${item.id}`" />
                </template>
            </v-data-table>
        </v-card>

        <v-dialog v-model="confirmDialog.show" max-width="420" persistent>
            <v-card v-if="confirmDialog.action">
                <v-card-title class="pt-5 px-5">
                    {{ confirmLabels[confirmDialog.action].title }}
                </v-card-title>
                <v-card-text class="px-5">
                    {{ confirmMsg }}
                </v-card-text>
                <v-card-actions class="px-5 pb-4">
                    <v-spacer />
                    <v-btn variant="text" @click="confirmDialog.show = false">取消</v-btn>
                    <v-btn :color="confirmLabels[confirmDialog.action].color" variant="flat" :loading="batchLoading" @click="executeBatch">
                        {{ confirmLabels[confirmDialog.action].btnText }}
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>

        <v-snackbar v-model="snackbar.show" :color="snackbar.color" location="top" timeout="3000">
            {{ snackbar.text }}
        </v-snackbar>
    </AdminLayout>
</template>
