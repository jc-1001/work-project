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

    const editDialog = ref(false)
    const editTarget = ref(null)
    const editForm = ref({ rating: 5, content: '' })
    const editSubmitting = ref(false)
    const keepIds = ref([])
    const newImages = ref([])
    const newImagePreviews = ref([])

    const openEdit = (review) => {
        editTarget.value = review
        editForm.value = { rating: review.rating, content: review.content ?? '' }
        keepIds.value = (review.images ?? []).map((img) => img.id)
        newImages.value = []
        newImagePreviews.value = []
        editDialog.value = true
    }

    const removeExistingImage = (id) => {
        keepIds.value = keepIds.value.filter((i) => i !== id)
    }

    const onNewImagesSelected = (files) => {
        if (!files) return
        const list = Array.isArray(files) ? files : Array.from(files)
        const remaining = 5 - keepIds.value.length - newImages.value.length
        list.slice(0, remaining).forEach((file) => {
            newImages.value.push(file)
            newImagePreviews.value.push(URL.createObjectURL(file))
        })
    }

    const removeNewImage = (index) => {
        URL.revokeObjectURL(newImagePreviews.value[index])
        newImages.value.splice(index, 1)
        newImagePreviews.value.splice(index, 1)
    }

    const submitEdit = () => {
        editSubmitting.value = true
        const fd = new FormData()
        fd.append('_method', 'PATCH')
        fd.append('rating', editForm.value.rating)
        fd.append('content', editForm.value.content ?? '')
        keepIds.value.forEach((id) => fd.append('keep_ids[]', id))
        newImages.value.forEach((file) => fd.append('images[]', file))

        api.post(`/reviews/${editTarget.value.id}`, fd, { headers: { 'Content-Type': undefined } })
            .then((res) => {
                editTarget.value.rating = editForm.value.rating
                editTarget.value.content = editForm.value.content
                editTarget.value.images = res.data.review.images
                editDialog.value = false
            })
            .catch(() => {})
            .finally(() => {
                editSubmitting.value = false
            })
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
                        <div class="d-flex ga-4 align-center">
                            <div class="text-caption text-medium-emphasis">{{ formatDate(review.created_at) }}</div>
                            <div v-if="review.updated_at !== review.created_at" class="text-medium-emphasis" style="font-size: 16px">
                                編輯於 {{ formatDate(review.updated_at) }}
                            </div>
                        </div>
                    </div>
                </div>
                <v-menu>
                    <template #activator="{ props: menuProps }">
                        <v-btn variant="text" class="ma-3" icon="mdi-dots-horizontal" v-bind="menuProps" />
                    </template>
                    <v-list density="compact">
                        <v-list-item
                            v-if="user?.id === review.user?.id"
                            class="text-body-2 cursor-pointer"
                            prepend-icon="mdi-comment-edit"
                            title="編輯評論"
                            @click="openEdit(review)"
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

    <v-dialog v-model="previewOpen" max-width="50vw">
        <v-img :src="previewSrc" max-height="50vh" contain @click="previewSrc = null" />
    </v-dialog>

    <v-dialog v-model="editDialog" max-width="480">
        <v-card rounded="xl">
            <v-card-title class="pt-5 px-5 text-h6 font-weight-bold">編輯評論</v-card-title>
            <v-card-text class="px-5">
                <v-sheet rounded="lg" color="grey-lighten-5" class="d-flex align-center px-4 py-3 mb-4">
                    <span class="text-body-2 text-medium-emphasis mr-4">評分</span>
                    <v-rating v-model="editForm.rating" active-color="primary" color="grey-lighten-2" density="default" size="30" />
                    <span class="text-caption text-medium-emphasis ml-3">
                        {{ ['', '非常差', '差', '普通', '不錯', '非常好'][editForm.rating] }}
                    </span>
                </v-sheet>
                <v-textarea v-model="editForm.content" label="評論內容" variant="outlined" density="compact" rows="5" clearable />

                <div v-if="editTarget?.images?.length || newImagePreviews.length" class="mb-2">
                    <div class="text-caption text-medium-emphasis mb-2">圖片</div>
                    <div class="d-flex flex-wrap ga-2">
                        <div
                            v-for="img in editTarget.images.filter((i) => keepIds.includes(i.id))"
                            :key="img.id"
                            class="position-relative"
                            :style="{ width: '80px', height: '80px' }"
                        >
                            <v-img :src="`/storage/${img.path}`" :width="80" :height="80" rounded="lg" cover />
                            <v-btn
                                icon="mdi-close"
                                size="x-small"
                                color="error"
                                variant="flat"
                                class="position-absolute"
                                :style="{ top: '-6px', right: '-6px' }"
                                @click="removeExistingImage(img.id)"
                            />
                        </div>
                        <div v-for="(preview, i) in newImagePreviews" :key="`new-${i}`" class="position-relative" :style="{ width: '80px', height: '80px' }">
                            <v-img :src="preview" :width="80" :height="80" rounded="lg" cover />
                            <v-btn
                                icon="mdi-close"
                                size="x-small"
                                color="error"
                                variant="flat"
                                class="position-absolute"
                                :style="{ top: '-6px', right: '-6px' }"
                                @click="removeNewImage(i)"
                            />
                        </div>
                    </div>
                </div>

                <v-file-input
                    label="新增圖片"
                    variant="filled"
                    density="compact"
                    accept="image/*"
                    multiple
                    hide-details
                    prepend-icon=""
                    prepend-inner-icon="mdi-image-plus"
                    @update:model-value="onNewImagesSelected"
                />
            </v-card-text>
            <v-card-actions class="px-5 pb-5">
                <v-spacer />
                <v-btn variant="text" @click="editDialog = false">取消</v-btn>
                <v-btn color="primary" variant="tonal" :loading="editSubmitting" @click="submitEdit">儲存</v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>
