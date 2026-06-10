<script setup>
    import { ref, computed, watch, onMounted } from 'vue'
    import api from '../bootstrap.js'
    import { useAuth } from '../composables/useAuth'

    const props = defineProps({ productId: [String, Number] })
    const { user, fetchUser } = useAuth()

    const reviews = ref([])
    const stats = ref(null)
    const loading = ref(false)
    const filterRating = ref(0)
    const currentPage = ref(1)
    const lastPage = ref(1)
    const snackbar = ref({ show: false, text: '', color: 'success' })
    const notify = (text, color = 'success') => { snackbar.value = { show: true, text, color } }

    const complaintDialog = ref(false)
    const complaintTarget = ref(null)
    const complaintReasons = ref([])
    const complaintSubmitting = ref(false)

    const complaintOptions = [
        { label: '偏離主題', description: '評論內容與商家體驗或商家互動無關' },
        { label: '垃圾內容', description: '評論出現機器人或偽造帳戶、或包含廣告與宣傳內容' },
        { label: '利益衝突', description: '評論出自商家或競爭商家的相關人士' },
        { label: '不雅用語', description: '評論包含不雅字眼、色情或猥褻露骨字眼' },
        { label: '內容有害', description: '評論包含鼓吹自傷、濫用危險物品或鼓吹暴力對待他人或動物' },
        { label: '霸凌或騷擾內容', description: '評論對特定個人進行攻擊' },
        { label: '歧視內容或仇恨言論', description: '評論包含對個人或團體身分認同的有害語言' },
        { label: '個人資訊', description: '評論包含地址或電話號碼等個人資訊' },
        { label: '沒有幫助', description: '評論內容無益於使用者是否選擇商品' },
    ]

    const openComplaint = (review) => {
        complaintTarget.value = review
        complaintReasons.value = []
        complaintDialog.value = true
    }

    const submitComplaint = () => {
        complaintSubmitting.value = true
        api.post(`/reviews/${complaintTarget.value.id}/complaint`, { reasons: complaintReasons.value })
            .then(() => {
                complaintDialog.value = false
                notify('檢舉已送出，感謝您的回報')
            })
            .catch((err) => {
                const msg = err.response?.data?.message
                notify(msg === '您已檢舉過這則評論' ? msg : '送出失敗，請稍後再試', 'error')
            })
            .finally(() => {
                complaintSubmitting.value = false
            })
    }

    const filterChips = [
        { label: '全部', value: 0 },
        { label: '5★', value: 5 },
        { label: '4★', value: 4 },
        { label: '3★', value: 3 },
        { label: '2★', value: 2 },
        { label: '1★', value: 1 },
    ]

    const distribution = computed(() => [
        { star: 5, count: stats.value?.star5 ?? 0 },
        { star: 4, count: stats.value?.star4 ?? 0 },
        { star: 3, count: stats.value?.star3 ?? 0 },
        { star: 2, count: stats.value?.star2 ?? 0 },
        { star: 1, count: stats.value?.star1 ?? 0 },
    ])

    const totalReviews = computed(() => stats.value?.total ?? 0)
    const avgRating = computed(() => Number(stats.value?.avg_rating ?? 0).toFixed(1))

    const getPercent = (count) => {
        if (!totalReviews.value) return 0
        return Math.round((count / totalReviews.value) * 100)
    }

    const getInitial = (name) => name?.charAt(0) ?? '?'

    const formatDate = (dateStr) => dateStr?.slice(0, 10) ?? ''

    const previewSrc = ref(null)
    const previewOpen = computed({
        get: () => previewSrc.value !== null,
        set: (val) => {
            if (!val) previewSrc.value = null
        },
    })

    const fetchReviews = (page = 1) => {
        loading.value = true
        const params = { page }
        if (filterRating.value) params.rating = filterRating.value

        api.get(`/products/${props.productId}/reviews`, { params })
            .then((res) => {
                reviews.value = res.data.reviews.data
                stats.value = res.data.stats
                currentPage.value = res.data.reviews.current_page
                lastPage.value = res.data.reviews.last_page
            })
            .catch(() => {})
            .finally(() => {
                loading.value = false
            })
    }

    const applyFilter = (val) => {
        filterRating.value = val
        fetchReviews(1)
    }

    watch(
        () => props.productId,
        (id) => {
            if (id) fetchReviews(1)
        },
        { immediate: true }
    )

    onMounted(() => {
        fetchUser()
    })
</script>

<template>
    <v-row class="mb-4" align="center">
        <v-col cols="12" sm="auto" class="text-center">
            <div class="font-weight-black text-primary" :style="{ fontSize: '2.2rem', lineHeight: 1 }">{{ avgRating }}</div>
            <v-rating :model-value="Number(avgRating)" color="primary" half-increments readonly density="compact" size="36" class="mt-1" />
            <div class="text-caption text-medium-emphasis mt-1">共 {{ totalReviews }} 則評論</div>
        </v-col>
        <v-col cols="12" sm>
            <div v-for="item in distribution" :key="item.star" class="d-flex align-center ga-2 mb-1">
                <span class="text-caption text-medium-emphasis" :style="{ minWidth: '32px' }">
                    {{ item.star }}
                    <v-icon size="12" icon="mdi-star" />
                </span>
                <v-progress-linear :model-value="getPercent(item.count)" color="primary" bg-color="grey-lighten-3" rounded height="7" class="flex-grow-1" />
                <span class="text-caption text-medium-emphasis" :style="{ minWidth: '42px', textAlign: 'right' }">{{ item.count }}</span>
            </div>
        </v-col>
    </v-row>

    <v-divider class="mb-3" />

    <div class="d-flex ga-2 flex-wrap mb-4">
        <v-chip
            v-for="chip in filterChips"
            :key="chip.value"
            :color="filterRating === chip.value ? 'primary' : undefined"
            :variant="filterRating === chip.value ? 'flat' : 'outlined'"
            size="small"
            class="cursor-pointer"
            @click="applyFilter(chip.value)"
        >
            {{ chip.label }}
        </v-chip>
    </div>

    <div v-if="loading" class="text-center py-6">
        <v-progress-circular indeterminate color="primary" size="32" />
    </div>

    <div v-else-if="reviews.length === 0" class="text-center py-6 text-medium-emphasis text-body-2">目前尚無評論</div>

    <div v-else class="d-flex flex-column ga-3">
        <v-card v-for="review in reviews" :key="review.id" rounded="xl" elevation="1">
            <div class="d-flex align-center justify-space-between">
                <div class="d-flex align-center">
                    <div
                        class="d-flex align-center justify-center ma-5 rounded-circle text-white font-weight-bold"
                        :style="{
                            width: '52px',
                            height: '52px',
                            fontSize: '18px',
                            background: 'linear-gradient(135deg, #5c6bc0, #9575cd)',
                            boxShadow: '0 4px 14px rgba(102, 126, 234, 0.35)',
                            flexShrink: 0,
                        }"
                    >
                        {{ getInitial(review.user?.name) }}
                    </div>
                    <div class="d-flex flex-column">
                        <div class="font-weight-bold text-body-1">{{ review.user?.name }}</div>
                        <v-rating :model-value="review.rating" readonly density="comfortable" size="20" active-color="primary" color="primary" />
                        <div class="text-caption text-medium-emphasis">{{ formatDate(review.created_at) }}</div>
                    </div>
                </div>
                <v-menu v-if="user && user.id !== review.user?.id">
                    <template #activator="{ props: menuProps }">
                        <v-btn variant="text" class="ma-3" icon="mdi-dots-horizontal" v-bind="menuProps" />
                    </template>
                    <v-list density="compact">
                        <v-list-item
                            class="text-body-2 cursor-pointer"
                            prepend-icon="mdi-flag-variant"
                            title="檢舉評論"
                            @click="openComplaint(review)"
                        />
                    </v-list>
                </v-menu>
            </div>
            <v-divider class="mx-5" />

            <div class="ma-5 text-body-2 text-medium-emphasis" :style="{ lineHeight: '1.7' }">
                {{ review.content }}

                <div v-if="review.images?.length" class="d-flex ga-4 mt-2" style="width: fit-content">
                    <v-img
                        v-for="img in review.images"
                        :key="img.id"
                        :src="`/storage/${img.path}`"
                        :width="100"
                        :height="100"
                        rounded="lg"
                        class="cursor-pointer"
                        @click="previewSrc = `/storage/${img.path}`"
                    />
                </div>
            </div>
        </v-card>
    </div>

    <div v-if="lastPage > 1" class="d-flex justify-center mt-4">
        <v-pagination v-model="currentPage" :length="lastPage" density="compact" @update:model-value="fetchReviews" />
    </div>

    <v-snackbar v-model="snackbar.show" :color="snackbar.color" location="top" timeout="3000">
        {{ snackbar.text }}
    </v-snackbar>

    <v-dialog v-model="previewOpen" max-width="50vw">
        <v-img :src="previewSrc" max-height="50vh" contain @click="previewSrc = null" />
    </v-dialog>

    <v-dialog v-model="complaintDialog" max-width="500">
        <v-card rounded="xl">
            <v-card-title class="pt-5 px-5 text-h6 font-weight-bold">這則評論怎麼了?</v-card-title>
            <v-container>
                <div v-for="option in complaintOptions" :key="option.label">
                    <v-checkbox v-model="complaintReasons" :value="option.label" :label="option.label" density="compact" hide-details />
                    <div class="text-medium-emphasis ml-7 mb-5" style="font-size: medium">{{ option.description }}</div>
                </div>
            </v-container>
            <v-card-actions class="px-5 pb-5">
                <v-spacer />
                <v-btn variant="text" @click="complaintDialog = false">取消</v-btn>
                <v-btn color="primary" variant="tonal" :loading="complaintSubmitting" :disabled="complaintReasons.length === 0" @click="submitComplaint">提交檢舉</v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>
