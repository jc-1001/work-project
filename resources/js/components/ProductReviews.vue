<script setup>
    import { ref, computed, watch } from 'vue'
    import api from '../bootstrap.js'

    const props = defineProps({ productId: [String, Number] })

    const reviews = ref([])
    const stats = ref(null)
    const loading = ref(false)
    const filterRating = ref(0)
    const currentPage = ref(1)
    const lastPage = ref(1)

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
    const avatarColors = ['blue-grey', 'blue-grey-darken-1', 'blue-grey-darken-2', 'grey-darken-1', 'grey-darken-2']
    const getAvatarColor = (id) => avatarColors[id % avatarColors.length]

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

    const vote = (review, type) => {
        api.post(`/reviews/${review.id}/vote`, { type })
            .then((res) => {
                review.up_count = res.data.up_count
                review.down_count = res.data.down_count
                review.my_vote = res.data.action === 'cancelled' ? null : type
            })
            .catch(() => {})
    }

    watch(
        () => props.productId,
        (id) => {
            if (id) fetchReviews(1)
        },
        { immediate: true }
    )
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

            <div class="ma-5 text-body-2 text-medium-emphasis text-right">
                <v-btn
                    variant="text"
                    size="small"
                    :color="review.my_vote === 'up' ? 'primary' : undefined"
                    :prepend-icon="review.my_vote === 'up' ? 'mdi-thumb-up' : 'mdi-thumb-up-outline'"
                    @click="vote(review, 'up')"
                >
                    有幫助 {{ review.up_count ? `(${review.up_count})` : '' }}
                </v-btn>
                <v-btn
                    variant="text"
                    size="small"
                    :color="review.my_vote === 'down' ? 'error' : undefined"
                    :icon="review.my_vote === 'down' ? 'mdi-thumb-down' : 'mdi-thumb-down-outline'"
                    @click="vote(review, 'down')"
                />
            </div>
        </v-card>
    </div>

    <div v-if="lastPage > 1" class="d-flex justify-center mt-4">
        <v-pagination v-model="currentPage" :length="lastPage" density="compact" @update:model-value="fetchReviews" />
    </div>

    <v-dialog v-model="previewOpen" max-width="50vw">
        <v-img :src="previewSrc" max-height="50vh" contain @click="previewSrc = null" />
    </v-dialog>
</template>
