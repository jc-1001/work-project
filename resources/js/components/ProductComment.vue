<script setup>
    import { ref, onMounted } from 'vue'
    import api from '../bootstrap.js'
    import { useAuth } from '../composables/useAuth'
    const window = globalThis
    const { fetchUser, isLoggedIn } = useAuth()

    const props = defineProps({ productId: [String, Number] })
    const emit = defineEmits(['submitted'])

    const MAX_IMAGES = 5
    const valid = ref(false)
    const submitting = ref(false)
    const previewUrls = ref([])
    const hoveredIndex = ref(null)
    const hoverAdd = ref(false)
    const fileInput = ref(null)
    const snackbar = ref({ show: false, text: '', color: 'success' })

    const rating = ref(5)
    const descriptionRef = ref(null)

    const notify = (text, color = 'success') => {
        snackbar.value = { show: true, text, color }
    }

    const form = ref({
        description: '',
        images: [],
    })

    const rules = {
        required: (v) => !!v || '此欄位為必填',
    }

    function handleImage(e) {
        const files = Array.from(e.target.files)
        files.forEach((file) => {
            if (previewUrls.value.length >= MAX_IMAGES) return
            form.value.images.push(file)
            previewUrls.value.push(URL.createObjectURL(file))
        })
        e.target.value = ''
    }

    function removeImage(i) {
        form.value.images.splice(i, 1)
        previewUrls.value.splice(i, 1)
    }

    function submit() {
        if (!valid.value) return
        submitting.value = true

        const data = new FormData()
        data.append('rating', rating.value)
        data.append('content', form.value.description || '')
        form.value.images.forEach((img, i) => data.append(`images[${i}]`, img))

        api.post(`/products/${props.productId}/reviews`, data, {
            headers: { 'Content-Type': 'multipart/form-data' },
        })
            .then(() => {
                notify('評論已送出！')
                form.value.description = ''
                form.value.images = []
                previewUrls.value = []
                rating.value = 5
                emit('submitted')
            })
            .catch((err) => {
                if (err.response?.status === 422) {
                    const errors = err.response.data?.errors
                    const msg = errors ? Object.values(errors).flat().join('、') : (err.response.data?.message ?? '資料驗證失敗')
                    notify(msg, 'error')
                } else {
                    notify(err.response?.data?.message ?? '送出失敗，請稍後再試', 'error')
                }
            })
            .finally(() => {
                submitting.value = false
            })
    }

    onMounted(() => {
        fetchUser()
    })
</script>

<template>
    <div class="position-relative">
        <div class="d-flex justify-space-between align-center mb-5">
            <div>
                <h2 class="text-h6 font-weight-bold ma-0">撰寫評論</h2>
                <p class="text-caption text-medium-emphasis ma-0 mt-2">分享你的使用心得，幫助其他買家</p>
            </div>
            <v-btn variant="tonal" color="primary" prepend-icon="mdi-send" :loading="submitting" @click="submit">確定送出</v-btn>
        </div>
        <v-divider class="mb-5" />

        <v-form v-model="valid">
            <v-row>
                <v-col cols="12" md="7">
                    <v-sheet rounded="lg" color="grey-lighten-5" class="d-flex align-center px-4 py-3 mb-3">
                        <span class="text-body-2 font-weight-medium text-medium-emphasis mr-4">商品評價</span>
                        <v-rating size="36" density="default" v-model="rating" active-color="primary" color="grey-lighten-2" />
                        <span v-if="rating" class="text-caption text-medium-emphasis ml-3">{{ ['', '非常差', '差', '普通', '不錯', '非常好'][rating] }}</span>
                    </v-sheet>

                    <v-textarea
                        ref="descriptionRef"
                        v-model="form.description"
                        label="評論 *"
                        variant="outlined"
                        density="compact"
                        rows="10"
                        :rules="[rules.required]"
                        clearable
                    />
                </v-col>

                <v-col cols="12" md="5">
                    <div class="d-flex align-center justify-space-between mb-3">
                        <span class="text-body-2 font-weight-medium">上傳圖片</span>
                        <v-chip size="x-small" variant="tonal" color="primary">{{ previewUrls.length }} / {{ MAX_IMAGES }} 張</v-chip>
                    </div>

                    <v-row density="comfortable">
                        <v-col v-for="(url, i) in previewUrls" :key="i" cols="4">
                            <v-sheet
                                rounded="lg"
                                border
                                class="overflow-hidden position-relative"
                                style="aspect-ratio: 1"
                                @mouseenter="hoveredIndex = i"
                                @mouseleave="hoveredIndex = null"
                            >
                                <v-img :src="url" cover class="h-100" />
                                <div
                                    v-if="i === 0"
                                    class="text-center text-caption text-white"
                                    style="position: absolute; bottom: 0; left: 0; right: 0; background: rgba(0, 0, 0, 0.45); padding: 2px 0"
                                ></div>
                                <v-btn
                                    v-show="hoveredIndex === i"
                                    icon="mdi-close"
                                    size="x-small"
                                    color="white"
                                    variant="flat"
                                    elevation="2"
                                    style="position: absolute; top: 4px; right: 4px"
                                    @click.stop="removeImage(i)"
                                />
                            </v-sheet>
                        </v-col>

                        <v-col v-if="previewUrls.length < MAX_IMAGES" cols="4">
                            <v-sheet
                                rounded="lg"
                                class="d-flex flex-column align-center justify-center cursor-pointer"
                                :style="{
                                    aspectRatio: '1',
                                    border: hoverAdd ? '2px solid #1976d2' : '2px dashed #bbb',
                                }"
                                @click="fileInput.click()"
                                @mouseenter="hoverAdd = true"
                                @mouseleave="hoverAdd = false"
                            >
                                <v-icon size="32" :color="hoverAdd ? 'primary' : 'grey-lighten-1'">mdi-plus</v-icon>
                                <span class="text-caption text-grey mt-1">
                                    {{ previewUrls.length === 0 ? '點擊上傳' : '新增圖片' }}
                                </span>
                            </v-sheet>
                        </v-col>
                    </v-row>
                    <input ref="fileInput" type="file" accept="image/*" multiple hidden @change="handleImage" />
                </v-col>
            </v-row>
        </v-form>

        <v-snackbar v-model="snackbar.show" :color="snackbar.color" location="top" timeout="3000">
            {{ snackbar.text }}
        </v-snackbar>

        <v-overlay :model-value="!isLoggedIn()" contained class="align-center justify-center" style="backdrop-filter: blur(1px)">
            <v-card rounded="xl" class="pa-5 text-center" max-width="320" elevation="4">
                <p class="text-body-2 text-medium-emphasis mb-5">請先登入才能撰寫評論</p>
                <v-btn color="primary" variant="tonal" @click="window.location.href = '/login'">前往登入</v-btn>
            </v-card>
        </v-overlay>
    </div>
</template>
