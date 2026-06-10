<script setup>
    import { ref, computed, onMounted } from 'vue'
    import api from '../../../bootstrap'
    import { getImageUrl } from '../../../utils/image'
    import { useAuth } from '../../../composables/useAuth'
    import FrontLayout from '../../../layouts/FrontLayout.vue'
    import ProductReviews from '../../../components/ProductReviews.vue'

    const window = globalThis

    const productId = document.getElementById('app')?.dataset?.id
    const product = ref({})
    const num = ref(1)
    const { fetchUser, isLoggedIn } = useAuth()
    const loginDialog = ref(false)
    const snackbar = ref({ show: false, text: '', color: 'success' })
    const loading = ref(true)

    const tab = ref('one')

    // 運送須知
    const shippingSections = [
        {
            title: '一、商品簽收與驗貨',
            items: [
                '收到商品時，請當場確認包裝完整、商品無損壞。',
                '若發現外箱有明顯擠壓或破損，請先拍照存證，並於 24 小時內聯繫客服。',
                '生鮮食品、冷凍商品請於簽收後立即檢查保存狀況。',
            ],
        },
        {
            title: '二、食品飲品類',
            items: [
                '請依包裝標示之保存方式與有效日期內食用。',
                '拆封後請儘速食用完畢，並避免陽光直射或高溫環境。',
                '個人過敏體質者，請於購買前詳閱成分標示。',
                '食品因個人衛生安全考量，除商品本身瑕疵外，恕無法辦理退換貨。',
            ],
        },
        {
            title: '三、服飾類',
            items: [
                '請參考商品頁面的尺寸表進行選擇，實際穿著可能因個人體型略有差異。',
                '商品顏色可能因螢幕顯示與拍攝光線而有色差，以實品為準。',
                '請依商品洗滌標籤指示清洗保養，避免造成損壞或褪色。',
                '試穿時請保持商品標籤完整，已拆封或留有使用痕跡者無法退換。',
            ],
        },
        {
            title: '四、數位商品類',
            items: [
                '數位商品（如序號、兌換碼、電子票券等）一經售出或已查看序號，恕無法退款。',
                '請妥善保管您的序號／兌換碼，因外洩或遭盜用之損失由買家自行承擔。',
                '部分數位商品有使用期限，請於期限內完成兌換或使用。',
            ],
        },
        {
            title: '五、退換貨須知',
            items: [
                '依《消費者保護法》規定，網購商品享有 7 天鑑賞期（特定商品除外）。',
                '退換貨商品須保持全新狀態，含原包裝、配件、贈品、保證書等均需完整退回。',
                '以下情形不適用鑑賞期退貨：食品、個人衛生用品、數位商品、已拆封耳機／耳塞、內衣褲等。',
                '退款將於商品檢查無誤後 5–10 個工作天內原路退回。',
            ],
        },
        {
            title: '六、其他注意事項',
            items: [
                '商品圖片僅供參考，實際商品以收到實品為準。',
                '促銷活動商品可能有額外限制條件，請以活動頁面說明為準。',
                '本平台保留隨時修改本須知之權利，更新後將公告於網站，不另行通知。',
            ],
        },
    ]
    // 免責聲明
    const disclaimerSections = [
        {
            title: '一、商品資訊',
            items: [
                '本平台商品資訊（含圖片、文字說明、規格、價格等）均盡力提供正確資訊，但不保證完全無誤。',
                '商品圖片可能因拍攝光線、螢幕顯示等因素而與實品略有差異，以實際收到之商品為準。',
                '本平台保留隨時調整商品價格、規格及上下架之權利，恕不另行通知。',
            ],
        },
        {
            title: '二、服務中斷與系統維護',
            items: [
                '本平台可能因系統維護、升級、不可抗力事件（如天災、網路攻擊、停電等）而暫時中斷服務。',
                '本平台將盡力提前通知，但對因服務中斷所造成之損失不承擔賠償責任。',
            ],
        },
        {
            title: '三、第三方連結與服務',
            items: [
                '本平台可能包含第三方網站連結，僅供使用者參考，本平台不對其內容或服務品質負責。',
                '使用第三方支付服務時，請遵守該服務之相關條款，本平台不介入且不承擔任何糾紛。',
            ],
        },
        {
            title: '四、智慧財產權',
            items: ['本平台上所有內容（含但不限於文字、圖片、商標、網頁設計）均受智慧財產權法保護。', '未經本平台書面同意，不得擅自複製、轉載、修改或用於商業用途。'],
        },
        {
            title: '五、個人資料與隱私',
            items: [
                '本平台將依據《個人資料保護法》及相關法令保護您的個人資料。',
                '您提供之個人資料僅用於訂單處理、客服聯繫及服務優化，不會任意提供給第三方。',
                '您有權隨時要求查詢、更正或刪除您的個人資料。',
            ],
        },
        {
            title: '六、責任限制',
            items: [
                '本平台對於因使用或無法使用本平台服務所產生之間接、附帶、特殊或懲罰性損害，不承擔賠償責任。',
                '本平台對商品之擔保僅限於商品本身缺陷，不包含因使用者個人使用不當所造成的損害。',
                '會員帳號之安全由用戶自行負責，因帳號外洩所產生之損失，本平台不承擔賠償責任。',
            ],
        },
        {
            title: '七、條款修改',
            items: ['本平台保留隨時修改本免責聲明之權利，修改後將於網站公告，不另行個別通知。', '您繼續使用本平台即視為同意修改後之條款。'],
        },
        {
            title: '八、準據法與管轄',
            items: ['本聲明之解釋與適用均以中華民國法律為準據法。', '因本聲明所生之糾紛，雙方同意以臺灣臺北地方法院為第一審管轄法院。'],
        },
    ]

    const notify = (text, color = 'success') => {
        snackbar.value = { show: true, text, color }
    }

    const stockStatus = computed(() => {
        const s = product.value.stock ?? 0
        if (s === 0) return { label: '已售完', color: 'error' }
        if (s <= 30) return { label: `僅剩 ${s} 件`, color: 'warning' }
        return { label: '庫存充足', color: 'success' }
    })

    const fetchProductDetail = async () => {
        loading.value = true
        try {
            const res = await api.get(`/products/${productId}`)
            product.value = res.data.product
        } catch {
            notify('無法載入商品資訊', 'error')
        } finally {
            loading.value = false
        }
    }

    const changeNum = (delta) => {
        const next = num.value + delta
        if (next < 1) return
        if (next > product.value.stock) {
            notify('已達最大庫存數量', 'warning')
            return
        }
        num.value = next
    }

    const addToCart = async () => {
        await fetchUser()
        if (!isLoggedIn()) {
            loginDialog.value = true
            return
        }

        const cart = JSON.parse(localStorage.getItem('cart')) || []
        const index = cart.findIndex((item) => item.id === product.value.id)

        if (index !== -1) {
            cart[index].quantity += num.value
        } else {
            cart.push({
                id: product.value.id,
                name: product.value.name,
                price: product.value.price,
                image: product.value.image,
                quantity: num.value,
            })
        }

        localStorage.setItem('cart', JSON.stringify(cart))
        notify('已加入購物車！')
    }

    onMounted(() => {
        fetchProductDetail()
    })
</script>

<template>
    <FrontLayout>
        <div class="detail-page">
            <!-- 麵包屑 -->
            <nav class="breadcrumb">
                <span class="breadcrumb-link" @click="window.location.href = '/'">首頁</span>
                <span class="breadcrumb-sep">›</span>
                <span class="breadcrumb-link" @click="window.location.href = '/shop'">商城</span>
                <span class="breadcrumb-sep">›</span>
                <span>{{ product.name ?? '載入中...' }}</span>
            </nav>

            <div v-if="loading" class="text-center pa-12">
                <v-progress-circular :size="70" :width="7" color="primary" indeterminate />
                <p>正在努力加載商品...</p>
            </div>

            <div v-else class="detail-layout">
                <!-- 左：圖片 -->
                <div class="img-wrap">
                    <v-img :src="getImageUrl(product.image)" cover class="product-img" />
                </div>

                <!-- 右：資訊 -->
                <div class="info-wrap">
                    <h1 class="product-name">{{ product.name }}</h1>

                    <div class="price-row">
                        <span class="price">NT$ {{ Number(product.price).toLocaleString() }}</span>
                    </div>

                    <div class="stock-row">
                        <v-chip :color="stockStatus.color" size="large" rounded="xl">
                            {{ stockStatus.label }}
                        </v-chip>
                    </div>

                    <div class="desc-block">
                        <p>{{ product.description }}</p>
                    </div>

                    <div class="action-row">
                        <!-- 數量選擇 -->
                        <div class="d-flex align-center ga-2">
                            <v-btn icon="mdi-minus" variant="outlined" size="small" :disabled="product.stock === 0 || num <= 1" @click="changeNum(-1)" />
                            <span class="text-h6 mx-1">{{ num }}</span>
                            <v-btn icon="mdi-plus" variant="outlined" size="small" :disabled="product.stock === 0 || num >= product.stock" @click="changeNum(1)" />
                        </div>

                        <v-btn class="cart-btn" size="large" :disabled="product.stock === 0" @click="addToCart">
                            <v-icon icon="mdi-cart" class="mr-2" />
                            加入購物車
                        </v-btn>
                    </div>

                    <v-btn variant="text" class="back-btn" @click="window.location.href = '/shop'">← 繼續逛逛</v-btn>
                </div>
            </div>

            <!-- 運送須知 & 免責聲明 -->
            <div class="tabs my-8">
                <v-sheet elevation="2" class="rounded-t-xl" color="blue-lighten-5">
                    <v-tabs v-model="tab" color="primary">
                        <v-tab value="one">運送須知</v-tab>
                        <v-tab value="two">免責聲明</v-tab>
                        <v-tab value="three">商品評論</v-tab>
                    </v-tabs>

                    <v-divider></v-divider>

                    <v-tabs-window v-model="tab">
                        <!-- 運送須知 -->
                        <v-tabs-window-item value="one">
                            <v-sheet class="pa-5">
                                <v-expansion-panels variant="accordion" class="pa-5">
                                    <v-expansion-panel v-for="section in shippingSections" :key="section.title" :title="section.title">
                                        <v-expansion-panel-text>
                                            <v-list density="compact">
                                                <v-list-item v-for="(item, idx) in section.items" :key="idx" prepend-icon="mdi-check-circle-outline">
                                                    <v-list-item-title class="text-wrap">{{ item }}</v-list-item-title>
                                                </v-list-item>
                                            </v-list>
                                        </v-expansion-panel-text>
                                    </v-expansion-panel>
                                </v-expansion-panels>

                                <v-alert type="info" variant="tonal" class="mt-4">如有任何疑問，請透過客服中心聯繫我們，我們將竭誠為您服務。</v-alert>
                            </v-sheet>
                        </v-tabs-window-item>

                        <!-- 免責聲明 -->
                        <v-tabs-window-item value="two">
                            <v-sheet class="pa-5">
                                <v-alert type="info" variant="tonal" class="mb-4">
                                    歡迎使用「我的商城」電商平台（以下簡稱「本平台」）。請您在使用前詳細閱讀以下免責條款，您一旦使用本平台，即表示您已瞭解並同意本聲明之全部內容。
                                </v-alert>
                                <v-expansion-panels variant="accordion" class="pa-5">
                                    <v-expansion-panel v-for="section in disclaimerSections" :key="section.title" :title="section.title">
                                        <v-expansion-panel-text>
                                            <v-list density="compact">
                                                <v-list-item v-for="(item, idx) in section.items" :key="idx" :prepend-icon="`mdi-numeric-${idx + 1}-circle`">
                                                    <v-list-item-title class="text-wrap">{{ item }}</v-list-item-title>
                                                </v-list-item>
                                            </v-list>
                                        </v-expansion-panel-text>
                                    </v-expansion-panel>
                                </v-expansion-panels>

                                <v-divider class="my-4" />
                                <p class="text-body-2 text-medium-emphasis text-center">本聲明最後更新日期：2026 年 5 月 4 日</p>
                            </v-sheet>
                        </v-tabs-window-item>
                        <v-tabs-window-item value="three">
                            <v-sheet class="pa-5">
                                <ProductReviews :product-id="productId" />
                            </v-sheet>
                        </v-tabs-window-item>
                    </v-tabs-window>
                </v-sheet>
            </div>
        </div>

        <!-- 登入確認 Dialog -->
        <v-dialog v-model="loginDialog" max-width="360">
            <v-card rounded="xl">
                <v-card-title class="pt-5 px-5">提示</v-card-title>
                <v-card-text class="px-5">您尚未登入，是否要前往登入頁面？</v-card-text>
                <v-card-actions class="px-4 pb-4">
                    <v-spacer />
                    <v-btn variant="text" @click="loginDialog = false">取消</v-btn>
                    <v-btn color="primary" variant="tonal" @click="window.location.href = '/login'">前往登入</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>

        <v-snackbar v-model="snackbar.show" :color="snackbar.color" location="top" timeout="3000">
            {{ snackbar.text }}
        </v-snackbar>
    </FrontLayout>
</template>

<style scoped>
    .detail-page {
        max-width: 1100px;
        margin: 0 auto;
        padding: 24px 24px 64px;
    }

    /* ── 麵包屑 ── */
    .breadcrumb {
        display: flex;
        align-items: center;
        gap: 6px;
        margin-bottom: 32px;
        font-size: 0.9rem;
        color: #888;
    }
    .breadcrumb-link {
        color: #409eff;
        cursor: pointer;
    }
    .breadcrumb-link:hover {
        text-decoration: underline;
    }
    .breadcrumb-sep {
        color: #ccc;
    }

    /* ── 主佈局 ── */
    .detail-layout {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 56px;
        align-items: start;
    }

    /* ── 圖片 ── */
    .img-wrap {
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 4px 24px rgba(0, 0, 0, 0.1);
        aspect-ratio: 1;
    }

    .product-img {
        width: 100%;
        height: 100%;
    }

    /* ── 資訊 ── */
    .info-wrap {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .product-name {
        font-size: 1.8rem;
        font-weight: 700;
        color: #1a1a2e;
        margin: 0;
        line-height: 1.3;
    }

    .price-row {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .price {
        font-size: 2rem;
        font-weight: 700;
        color: #f56c6c;
    }

    .stock-row {
        display: flex;
        align-items: center;
    }

    /* ── 描述區塊 ── */
    .desc-block {
        border-left: 4px solid #409eff;
        background: #f5f9ff;
        border-radius: 0 8px 8px 0;
        padding: 14px 18px;
    }

    .desc-block p {
        margin: 0;
        font-size: 0.95rem;
        color: #555;
        line-height: 1.8;
    }

    /* ── 操作列 ── */
    .action-row {
        display: flex;
        align-items: center;
        gap: 16px;
        flex-wrap: wrap;
    }

    .cart-btn {
        flex: 1;
        min-width: 160px;
        background: #409eff !important;
        color: #fff !important;
        font-size: 1rem;
        font-weight: 600;
        height: 44px;
        transition:
            background 0.25s,
            transform 0.2s,
            box-shadow 0.25s;
    }
    .cart-btn:hover:not(:disabled) {
        background: #337ecc !important;
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(64, 158, 255, 0.4);
    }

    /* ── 繼續逛逛 ── */
    .back-btn {
        align-self: flex-start;
        color: #888 !important;
        font-size: 0.9rem;
        padding: 0;
        transition: color 0.2s;
    }
    .back-btn:hover {
        color: #409eff !important;
    }

    @keyframes spin {
        from {
            transform: rotate(0deg);
        }
        to {
            transform: rotate(360deg);
        }
    }

    @media (max-width: 768px) {
        .detail-layout {
            grid-template-columns: 1fr;
            gap: 32px;
        }
    }
</style>
