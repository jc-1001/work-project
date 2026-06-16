<script setup>
    import { ref, onMounted } from 'vue'
    import api from '../../../bootstrap'
    import { getImageUrl } from '../../../utils/image'
    import FrontLayout from '../../../layouts/FrontLayout.vue'
    import ProfileLayout from '../../../components/ProfileLayout.vue'

    const orders = ref([])
    const total = ref(0)
    const currentPage = ref(1)
    const pageSize = ref(2)
    const loading = ref(false)
    const snackbar = ref({ show: false, text: '', color: 'success' })
    const SHIPPING_FEE = Number(import.meta.env.VITE_SHIPPING_FEE ?? 60)

    const STATUS_CONFIG = {
        pending: { label: '訂單已成立', color: 'warning' },
        shipping: { label: '出貨中', color: 'info' },
        completed: { label: '已完成', color: 'success' },
        cancelled: { label: '訂單已取消', color: 'error' },
        returned: { label: '已退貨', color: 'indigo' },
    }

    const notify = (text, color = 'success') => {
        snackbar.value = { show: true, text, color }
    }

    const fetchOrders = () => {
        loading.value = true
        api.get('/orders', { params: { page: currentPage.value } })
            .then((res) => {
                orders.value = res.data.data
                total.value = res.data.total
                currentPage.value = res.data.current_page
            })
            .catch(() => {
                notify('取得訂單失敗，請稍後再試', 'error')
            })
            .finally(() => {
                loading.value = false
            })
    }

    const paymentMethodLabel = {
        'Credit card': '信用卡',
        ATM: 'ATM 轉帳',
    }
    const invoiceTypeLabel = {
        個人電子發票: '個人電子發票（二聯式）',
        公司行號: '公司行號（三聯式）',
        手機載具: '手機載具',
    }

    const formatDate = (dateStr) => {
        if (!dateStr) return ''
        const d = new Date(dateStr)
        const pad = (n) => String(n).padStart(2, '0')
        return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())} ${pad(d.getHours())}:${pad(d.getMinutes())}`
    }

    const orderTotal = (order) => {
        const subtotal = Number(order.subtotal_amount ?? order.total_amount)
        const shipping = Number(order.shipping_fee ?? SHIPPING_FEE)
        const discount = Number(order.discount_amount ?? 0)
        return subtotal + shipping - discount
    }

    const buyOneTime = (order) => {
        try {
            const cart = JSON.parse(localStorage.getItem('cart')) || []
            order.items.forEach((item) => {
                const index = cart.findIndex((c) => c.id === item.product_id)
                if (index !== -1) {
                    cart[index].quantity += Number(item.quantity)
                } else {
                    cart.push({
                        id: item.product_id,
                        name: item.product_name,
                        price: item.price,
                        image: item.product?.image || null,
                        quantity: Number(item.quantity),
                    })
                }
            })
            localStorage.setItem('cart', JSON.stringify(cart))
            window.dispatchEvent(new Event('cart-updated'))
            notify('已加入購物車！')
        } catch {
            notify('加入購物車失敗，請稍後再試', 'error')
        }
    }

    onMounted(() => {
        fetchOrders()
    })
</script>

<template>
    <FrontLayout>
        <ProfileLayout>
            <div>
                <p class="text-h6 font-weight-bold mb-1">我的訂單</p>
                <p class="text-body-2 text-medium-emphasis mb-6">共 {{ total }} 筆訂單紀錄</p>

                <div v-if="loading" class="d-flex justify-center py-12">
                    <v-progress-circular indeterminate color="primary" />
                </div>

                <div v-else-if="orders.length === 0" class="text-center py-12">
                    <v-icon icon="mdi-clipboard-text-off-outline" size="72" color="grey-lighten-2" />
                    <p class="text-body-1 text-medium-emphasis mt-4">尚無訂單紀錄</p>
                </div>

                <template v-else>
                    <v-card v-for="order in orders" :key="order.id" class="mb-5" rounded="xl" elevation="1" style="border: 1px solid #eef0f4">
                        <div
                            class="bg-grey-lighten-5 d-flex flex-column flex-sm-row justify-space-between align-sm-start ga-3 px-4 px-sm-5 pt-3 pb-5 border-top-sm"
                            style="border-top-color: #eef0f4"
                        >
                            <div class="d-flex flex-column ga-1">
                                <span class="text-body-2 font-weight-bold">{{ order.order_number }}</span>
                                <span class="text-caption text-disabled">{{ formatDate(order.created_at) }}</span>
                            </div>
                            <div class="d-flex flex-column align-start align-sm-end ga-2">
                                <div class="d-flex align-center ga-2">
                                    <v-chip :color="STATUS_CONFIG[order.status]?.color" variant="tonal" size="small">
                                        {{ STATUS_CONFIG[order.status]?.label ?? order.status }}
                                    </v-chip>
                                    <v-btn size="small" variant="outlined" color="primary" rounded="pill" @click="buyOneTime(order)">再買一次</v-btn>
                                </div>
                                <div
                                    v-if="order.status === 'pending' || order.status === 'completed'"
                                    class="d-flex align-center ga-1 text-disabled"
                                    style="font-size: 0.7rem; line-height: 1.4"
                                >
                                    <v-icon size="12" color="grey-lighten-1" class="flex-shrink-0">mdi-alert-circle</v-icon>
                                    <span>{{ order.status === 'pending' ? '如需取消訂單' : '如需申請退貨' }}，請透過右下角客服中心聯繫我們，我們會盡快與您聯絡!</span>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex flex-column ga-2 py-3 px-5">
                            <div v-for="item in order.items" :key="item.id" class="d-flex align-center ga-3">
                                <div class="rounded-lg flex-shrink-0 overflow-hidden" style="width: 54px; height: 54px; border: 1px solid #eee">
                                    <v-img :src="getImageUrl(item.product?.image) || 'https://picsum.photos/60/60'" width="54" height="54" cover />
                                </div>
                                <div class="flex-grow-1" style="min-width: 0">
                                    <div class="d-flex align-center justify-space-between">
                                        <p class="font-weight-medium my-0 text-truncate" style="font-size: 16px">
                                            {{ item.product_name }}
                                        </p>
                                        <span class="text-medium-emphasis text-no-wrap ms-3" style="font-size: 16px">× {{ item.quantity }}</span>
                                    </div>
                                    <p class="text-medium-emphasis mt-1 mb-0" style="font-size: 16px">
                                        NT$
                                        {{ Number(item.price).toLocaleString() }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <v-row no-gutters align="start" class="ga-5 px-5 pt-4 pb-5 border-top-sm" style="border-top-color: #eef0f4">
                            <v-col cols="12" sm>
                                <v-row class="ga-3">
                                    <v-col cols="12" sm="6">
                                        <v-sheet color="grey-lighten-5" rounded="lg" class="info-block pa-3 h-100">
                                            <div class="text-caption font-weight-bold text-disabled text-uppercase mb-1" style="letter-spacing: 0.5px">購買人資訊</div>
                                            <div class="text-body-2 text-medium-emphasis my-1" style="font-size: 16px">
                                                {{ order.name }}
                                            </div>
                                            <div class="text-body-2 text-medium-emphasis my-1" style="font-size: 16px">
                                                {{ order.phone }}
                                            </div>
                                            <div class="text-body-2 text-medium-emphasis my-1" style="font-size: 16px">
                                                {{ order.address }}
                                            </div>
                                        </v-sheet>
                                    </v-col>
                                    <v-col cols="12" sm="6">
                                        <v-sheet color="grey-lighten-5" rounded="lg" class="info-block pa-3 h-100">
                                            <p class="text-caption font-weight-bold text-disabled text-uppercase mb-1" style="letter-spacing: 0.5px">付款資訊</p>
                                            <p class="text-body-2 text-medium-emphasis my-1" style="font-size: 16px">
                                                {{ paymentMethodLabel[order.payment_method] ?? order.payment_method }}
                                            </p>
                                            <p class="text-body-2 text-medium-emphasis my-1" style="font-size: 16px">
                                                {{ invoiceTypeLabel[order.invoice_type] ?? '—' }}
                                            </p>
                                            <p
                                                v-if="order.invoice_type === '公司行號' && order.tax_id"
                                                class="text-body-2 text-medium-emphasis my-1"
                                                style="font-size: 16px"
                                            >
                                                統編：{{ order.tax_id }}
                                            </p>
                                            <p
                                                v-if="order.invoice_type === '手機載具' && order.carrier"
                                                class="text-body-2 text-medium-emphasis my-1"
                                                style="font-size: 16px"
                                            >
                                                載具：{{ order.carrier }}
                                            </p>
                                        </v-sheet>
                                    </v-col>
                                </v-row>
                            </v-col>

                            <v-col cols="12" sm="auto" style="min-width: 180px">
                                <div class="d-flex flex-column ga-1">
                                    <div class="d-flex justify-space-between text-body-2 text-medium-emphasis">
                                        <span>小計</span>
                                        <span>NT$ {{ Number(order.subtotal_amount ?? order.total_amount).toLocaleString() }}</span>
                                    </div>
                                    <div v-if="Number(order.discount_amount) > 0" class="d-flex justify-space-between text-body-2 text-success">
                                        <span>代碼折扣</span>
                                        <span>- NT$ {{ Number(order.discount_amount).toLocaleString() }}</span>
                                    </div>
                                    <div class="d-flex justify-space-between text-body-2 text-medium-emphasis">
                                        <span>運費</span>
                                        <span>NT$ {{ Number(order.shipping_fee ?? SHIPPING_FEE).toLocaleString() }}</span>
                                    </div>
                                    <v-divider class="my-2" />
                                    <div class="d-flex justify-space-between text-body-1 font-weight-bold text-error">
                                        <span>總計</span>
                                        <span>NT$ {{ orderTotal(order).toLocaleString() }}</span>
                                    </div>
                                </div>
                            </v-col>
                        </v-row>
                    </v-card>

                    <v-pagination
                        v-if="total > pageSize"
                        v-model="currentPage"
                        :length="Math.ceil(total / pageSize)"
                        density="comfortable"
                        class="mt-4"
                        @update:model-value="fetchOrders"
                    />
                </template>
            </div>
        </ProfileLayout>

        <v-snackbar v-model="snackbar.show" :color="snackbar.color" location="top" timeout="3000">
            {{ snackbar.text }}
        </v-snackbar>
    </FrontLayout>
</template>
