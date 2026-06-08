<script setup>
    import { ref, computed, onMounted } from 'vue'
    import api from '../../bootstrap.js'
    import { getImageUrl } from '../../utils/image.js'
    import AdminLayout from '../../layouts/AdminLayout.vue'

    const window = globalThis
    const productId = document.getElementById('app').dataset.id

    const MAX_IMAGES = 5

    const valid = ref(false)
    const storing = ref(false)
    const loading = ref(true)
    const loadFailed = ref(false)
    const categories = ref([])
    const snackbar = ref({ show: false, text: '', color: 'success' })

    const fileInputCover = ref(null)
    const fileInputExtra = ref(null)
    const coverHovered = ref(false)
    const hoverExtraIndex = ref(null)
    const hoverAdd = ref(false)

    const coverUrl = ref(null)
    const newCoverFile = ref(null)

    const extraItems = ref([])

    const form = ref({
        name: '',
        price: null,
        stock: null,
        category_id: null,
        description: '',
        is_active: '1',
    })

    const rules = {
        required: (v) => !!v || '此欄位為必填',
        positiveNumber: (v) => (v !== null && v !== '' && v >= 0) || '請輸入有效數字',
    }

    const canAddMore = computed(() => extraItems.value.length < MAX_IMAGES - 1)

    const notify = (text, color = 'success') => {
        snackbar.value = { show: true, text, color }
    }

    function handleCover(e) {
        const file = e.target.files[0]
        if (!file) return
        newCoverFile.value = file
        coverUrl.value = URL.createObjectURL(file)
        e.target.value = ''
    }

    function handleExtra(e) {
        const files = Array.from(e.target.files)
        files.forEach((file) => {
            if (!canAddMore.value) return
            extraItems.value.push({ id: null, url: URL.createObjectURL(file), file })
        })
        e.target.value = ''
    }

    function removeExtra(i) {
        const item = extraItems.value[i]
        if (item.id !== null) {
            api.delete(`/api/admin/products/${productId}/images/${item.id}`)
                .then(() => {
                    extraItems.value.splice(i, 1)
                })
                .catch(() => notify('刪除圖片失敗', 'error'))
        } else {
            extraItems.value.splice(i, 1)
        }
    }

    function fetchCategories() {
        api.get('/categories')
            .then((res) => {
                categories.value = res.data.categories.map((c) => ({
                    title: c.name,
                    value: c.id,
                }))
            })
            .catch(() => {})
    }

    function fetchProduct() {
        loading.value = true
        api.get(`/api/admin/products/${productId}`)
            .then((res) => {
                const p = res.data.product
                form.value = {
                    name: p.name,
                    price: parseFloat(p.price),
                    stock: parseInt(p.stock),
                    category_id: Number(p.category_id),
                    description: p.description ?? '',
                    is_active: p.is_active ? '1' : '0',
                }
                coverUrl.value = p.image ? getImageUrl(p.image) : null
                extraItems.value = (p.product_images ?? []).map((img) => ({
                    id: img.id,
                    url: getImageUrl(img.image_path),
                    file: null,
                }))
            })
            .catch((err) => {
                loadFailed.value = true
                notify(err.response?.data?.message ?? '載入商品失敗，請稍後再試', 'error')
            })
            .finally(() => {
                loading.value = false
            })
    }

    function store() {
        if (!valid.value) return
        storing.value = true

        const data = new FormData()
        data.append('name', form.value.name)
        data.append('price', form.value.price)
        data.append('stock', form.value.stock)
        data.append('category_id', form.value.category_id)
        data.append('description', form.value.description || '')
        data.append('is_active', form.value.is_active === '1' ? 1 : 0)
        if (newCoverFile.value) data.append('image', newCoverFile.value)

        const pendingExtras = extraItems.value.filter((item) => item.id === null && item.file)
        pendingExtras.forEach((item, i) => data.append(`new_images[${i}]`, item.file))

        api.post(`/api/admin/products/${productId}`, data, {
            headers: { 'Content-Type': 'multipart/form-data' },
        })
            .then((res) => {
                notify('儲存成功')
                newCoverFile.value = null
                extraItems.value = (res.data.product.product_images ?? []).map((img) => ({
                    id: img.id,
                    url: getImageUrl(img.image_path),
                    file: null,
                }))
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

    onMounted(() => {
        fetchCategories()
        fetchProduct()
    })
</script>

<template>
    <AdminLayout>
        <div class="d-flex justify-space-between align-center mb-6">
            <h1>商品詳情</h1>
            <div class="d-flex ga-2">
                <v-btn variant="tonal" prepend-icon="mdi-arrow-left" @click="window.location.href = '/admin/products'">返回</v-btn>
                <v-btn variant="tonal" color="primary" prepend-icon="mdi-check" :loading="storing" :disabled="loading" @click="store">儲存變更</v-btn>
            </div>
        </div>

        <div v-if="loading" class="text-center pa-12">
            <v-progress-circular :size="70" :width="7" color="primary" indeterminate />
        </div>

        <v-alert v-else-if="loadFailed" type="error" variant="tonal" class="mt-4" title="無法載入商品資料" text="請確認網路連線後重新整理，或返回商品列表。" />

        <v-form v-else v-model="valid">
            <v-row>
                <v-col cols="12" md="7">
                    <v-text-field v-model="form.name" label="商品名稱 *" variant="outlined" density="compact" :rules="[rules.required]" counter="20" class="mb-3" />

                    <v-row>
                        <v-col cols="12" sm="6">
                            <v-number-input
                                v-model="form.price"
                                label="售價 *"
                                variant="outlined"
                                density="compact"
                                :min="0"
                                controlVariant="hidden"
                                :rules="[rules.required, rules.positiveNumber]"
                                class="mb-3"
                            />
                        </v-col>
                        <v-col cols="12" sm="6">
                            <v-number-input
                                v-model="form.stock"
                                label="庫存數量 *"
                                variant="outlined"
                                density="compact"
                                :min="0"
                                :max="1000"
                                controlVariant="hidden"
                                :rules="[rules.required, rules.positiveNumber]"
                                class="mb-3"
                            />
                        </v-col>
                    </v-row>

                    <v-select
                        v-model="form.category_id"
                        :items="categories"
                        label="商品類別 *"
                        variant="outlined"
                        density="compact"
                        :rules="[rules.required]"
                        class="mb-3"
                    />

                    <v-textarea v-model="form.description" label="商品描述 *" variant="outlined" density="compact" rows="4" :rules="[rules.required]" clearable />
                </v-col>

                <v-col cols="12" md="5">
                    <div class="text-caption text-grey my-1">{{ (coverUrl ? 1 : 0) + extraItems.length }} / {{ MAX_IMAGES }} 張</div>

                    <v-row dense>
                        <!-- 封面 -->
                        <v-col cols="4">
                            <v-sheet
                                rounded="lg"
                                border
                                class="overflow-hidden position-relative cursor-pointer"
                                style="aspect-ratio: 1"
                                @click="fileInputCover.click()"
                                @mouseenter="coverHovered = true"
                                @mouseleave="coverHovered = false"
                            >
                                <v-img v-if="coverUrl" :src="coverUrl" cover class="h-100" />
                                <div v-else class="d-flex flex-column align-center justify-center h-100 text-grey">
                                    <v-icon size="28" color="grey-lighten-1">mdi-image-plus</v-icon>
                                    <span class="text-caption mt-1">點擊上傳</span>
                                </div>

                                <div
                                    class="text-center text-caption text-white"
                                    style="position: absolute; bottom: 0; left: 0; right: 0; background: rgba(0, 0, 0, 0.45); padding: 2px 0"
                                >
                                    封面
                                </div>

                                <div
                                    v-if="coverHovered && coverUrl"
                                    class="d-flex align-center justify-center"
                                    style="position: absolute; inset: 0; background: rgba(0, 0, 0, 0.2)"
                                >
                                    <v-icon color="white" size="22">mdi-pencil</v-icon>
                                </div>
                            </v-sheet>
                        </v-col>

                        <v-col v-for="(item, i) in extraItems" :key="i" cols="4">
                            <v-sheet
                                rounded="lg"
                                border
                                class="overflow-hidden position-relative"
                                style="aspect-ratio: 1"
                                @mouseenter="hoverExtraIndex = i"
                                @mouseleave="hoverExtraIndex = null"
                            >
                                <v-img :src="item.url" cover class="h-100" />
                                <v-btn
                                    v-show="hoverExtraIndex === i"
                                    icon="mdi-close"
                                    size="x-small"
                                    color="white"
                                    variant="flat"
                                    elevation="2"
                                    style="position: absolute; top: 4px; right: 4px"
                                    @click.stop="removeExtra(i)"
                                />
                            </v-sheet>
                        </v-col>

                        <!-- 新增按鈕 -->
                        <v-col v-if="canAddMore" cols="4">
                            <v-sheet
                                rounded="lg"
                                class="d-flex flex-column align-center justify-center cursor-pointer"
                                :style="{
                                    aspectRatio: '1',
                                    border: hoverAdd ? '2px solid #1976d2' : '2px dashed #bbb',
                                }"
                                @click="fileInputExtra.click()"
                                @mouseenter="hoverAdd = true"
                                @mouseleave="hoverAdd = false"
                            >
                                <v-icon size="32" :color="hoverAdd ? 'primary' : 'grey-lighten-1'">mdi-plus</v-icon>
                                <span class="text-caption text-grey mt-1">新增圖片</span>
                            </v-sheet>
                        </v-col>
                    </v-row>

                    <input ref="fileInputCover" type="file" accept="image/*" hidden @change="handleCover" />
                    <input ref="fileInputExtra" type="file" accept="image/*" multiple hidden @change="handleExtra" />

                    <v-radio-group v-model="form.is_active" label="上下架" inline class="mt-4">
                        <v-radio label="上架" value="1" />
                        <v-radio label="下架" value="0" />
                    </v-radio-group>
                </v-col>
            </v-row>
        </v-form>

        <v-snackbar v-model="snackbar.show" :color="snackbar.color" location="top" timeout="3000">
            {{ snackbar.text }}
        </v-snackbar>
    </AdminLayout>
</template>
