<script setup>
import { ref, computed, onMounted } from "vue";
import api from "../../bootstrap";
import AdminLayout from "../../layouts/AdminLayout.vue";
import LineChart from "../../components/charts/LineChart.vue";
import DonutChart from "../../components/charts/DonutChart.vue";
import BarChart from "../../components/charts/BarChart.vue";

const window = globalThis;

const stats = ref(null);
const loading = ref(true);
const error = ref(false);

onMounted(() => {
    api.get("/admin/dashboard/stats")
        .then(({ data }) => {
            stats.value = data;
        })
        .catch(() => {
            error.value = true;
        })
        .finally(() => {
            loading.value = false;
        });
});

const userTrendSeries = computed(() =>
    stats.value
        ? [{ name: "新增使用者", data: stats.value.users.trend.data }]
        : [],
);

const orderTrendSeries = computed(() =>
    stats.value
        ? [{ name: "完成訂單", data: stats.value.orders.trend.data }]
        : [],
);

const userStatusData = computed(() =>
    stats.value
        ? [
              {
                  name: "啟用",
                  value: stats.value.users.total - stats.value.users.inactive,
              },
              { name: "停用", value: stats.value.users.inactive },
          ]
        : [],
);

const productStatusData = computed(() =>
    stats.value
        ? [
              { name: "上架", value: stats.value.products.active },
              { name: "下架", value: stats.value.products.inactive },
          ]
        : [],
);

const top3Categories = computed(
    () => stats.value?.products.top3.map((p) => p.product_name) ?? [],
);
const top3Data = computed(
    () => stats.value?.products.top3.map((p) => Number(p.total_sold)) ?? [],
);

function onTop3Click(index) {
    const product = stats.value?.products.top3[index];
    if (product?.product_id)
        window.location.href = `/admin/products/${product.product_id}`;
}

const orderStatusCategories = ["已成立", "已出貨"];
const orderStatusData = computed(() =>
    stats.value
        ? [stats.value.orders.pending, stats.value.orders.shipping]
        : [],
);

const activeAdsCount = computed(
    () => stats.value?.ads.filter((a) => a.is_active).length ?? 0,
);

const activeCouponsCount = computed(
    () => stats.value?.coupons.filter((c) => c.is_active).length ?? 0,
);

function formatDate(dt) {
    return dt ? dt.slice(0, 10) : "無限期";
}
</script>

<template>
    <AdminLayout>
        <v-container fluid class="pa-6">
            <h1>後台儀表板</h1>

            <template v-if="loading">
                <v-row>
                    <v-col v-for="i in 4" :key="i" cols="12" sm="6" md="3">
                        <v-skeleton-loader type="card" />
                    </v-col>
                </v-row>
            </template>

            <v-alert v-else-if="error" type="error" class="mb-4">
                資料載入失敗，請重新整理頁面
            </v-alert>

            <template v-else>
                <v-row>
                    <v-col cols="12" sm="6" md="3">
                        <v-card @click="window.location.href = '/admin/user'">
                            <v-card-text>
                                <div
                                    class="text-caption text-headline-small text-center"
                                >
                                    <v-icon size="20" class="mr-4"
                                        >mdi-account-group</v-icon
                                    >
                                    使用者總數
                                </div>
                                <div class="text-title-large mt-1 text-center">
                                    {{ stats.users.total }}
                                </div>
                                <div
                                    class="text-caption mt-2 text-success text-center"
                                >
                                    <v-icon size="20">mdi-menu-up</v-icon>
                                    本月新增 {{ stats.users.monthly_growth }} 人
                                </div>
                            </v-card-text>
                        </v-card>
                    </v-col>

                    <v-col cols="12" sm="6" md="3">
                        <v-card @click="window.location.href = '/admin/user'">
                            <v-card-text>
                                <div
                                    class="text-caption text-headline-small text-center"
                                >
                                    <v-icon size="20" class="mr-4"
                                        >mdi-account-off</v-icon
                                    >停用帳號
                                </div>
                                <div class="text-title-large mt-1 text-center">
                                    {{ stats.users.inactive }}
                                </div>
                                <div
                                    class="text-caption mt-2 text-medium-emphasis text-center"
                                >
                                    佔總數
                                    {{
                                        stats.users.total
                                            ? Math.round(
                                                  (stats.users.inactive /
                                                      stats.users.total) *
                                                      100,
                                              )
                                            : 0
                                    }}%
                                </div>
                            </v-card-text>
                        </v-card>
                    </v-col>

                    <v-col cols="12" sm="6" md="3">
                        <v-card
                            @click="window.location.href = '/admin/products'"
                        >
                            <v-card-text>
                                <div
                                    class="text-caption text-headline-small text-center"
                                >
                                    <v-icon size="20" class="mr-4"
                                        >mdi-package-variant</v-icon
                                    >
                                    商品總數
                                </div>
                                <div class="text-title-large mt-1 text-center">
                                    {{ stats.products.total }}
                                </div>
                                <div
                                    class="text-caption mt-2 text-error text-center"
                                >
                                    <v-icon size="20">mdi-alert</v-icon>
                                    低庫存 {{ stats.products.low_stock }} 件
                                </div>
                            </v-card-text>
                        </v-card>
                    </v-col>

                    <v-col cols="12" sm="6" md="3">
                        <v-card @click="window.location.href = '/admin/orders'">
                            <v-card-text>
                                <div
                                    class="text-caption text-headline-small text-center"
                                >
                                    <v-icon size="20" class="mr-4"
                                        >mdi-format-list-checks</v-icon
                                    >
                                    月完成訂單
                                </div>
                                <div class="text-title-large mt-1 text-center">
                                    {{ stats.orders.monthly_completed }}
                                </div>
                                <div
                                    class="text-caption mt-2 text-medium-emphasis text-center"
                                >
                                    待處理
                                    {{
                                        stats.orders.pending +
                                        stats.orders.shipping
                                    }}
                                    筆
                                </div>
                            </v-card-text>
                        </v-card>
                    </v-col>
                </v-row>

                <v-row class="mt-4">
                    <v-col cols="12" md="6">
                        <LineChart
                            title="月新增使用者趨勢"
                            :labels="stats.users.trend.labels"
                            :series="userTrendSeries"
                        />
                    </v-col>
                    <v-col cols="12" md="6">
                        <LineChart
                            title="月完成訂單趨勢"
                            :labels="stats.orders.trend.labels"
                            :series="orderTrendSeries"
                        />
                    </v-col>
                </v-row>

                <v-row class="mt-4">
                    <v-col cols="12" sm="6" md="3">
                        <DonutChart title="使用者狀態" :data="userStatusData" />
                    </v-col>
                    <v-col cols="12" sm="6" md="3">
                        <DonutChart
                            title="商品狀態"
                            :data="productStatusData"
                        />
                    </v-col>
                    <v-col cols="12" md="6">
                        <BarChart
                            title="訂單狀態分佈"
                            :categories="orderStatusCategories"
                            :data="orderStatusData"
                        />
                    </v-col>
                </v-row>

                <v-row class="mt-4">
                    <v-col cols="12" md="6">
                        <BarChart
                            title="商品 Top 3 銷售量"
                            :categories="top3Categories"
                            :data="top3Data"
                            icon="mdi-crown"
                            clickable
                            @bar-click="onTop3Click"
                        />
                    </v-col>
                    <v-col cols="12" md="6">
                        <v-card height="100%">
                            <v-card-title
                                class="text-body-1 font-weight-bold pt-4 px-4"
                            >
                                <v-icon size="20" class="mr-4"
                                    >mdi-package-variant-minus</v-icon
                                >
                                低庫存商品
                                <v-chip
                                    size="small"
                                    color="error"
                                    class="ml-2"
                                    >{{ stats.products.low_stock }}</v-chip
                                >
                            </v-card-title>
                            <v-card-text class="pa-0">
                                <v-table density="compact" hover>
                                    <thead>
                                        <tr>
                                            <th>商品名稱</th>
                                            <th>庫存</th>
                                            <th>狀態</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            v-for="p in stats.products
                                                .low_stock_list"
                                            :key="p.id"
                                            style="cursor: pointer"
                                            @click="
                                                window.location.href =
                                                    '/admin/products/' + p.id
                                            "
                                        >
                                            <td>{{ p.name }}</td>
                                            <td>
                                                <v-chip
                                                    size="small"
                                                    :color="
                                                        p.stock === 0
                                                            ? 'error'
                                                            : 'warning'
                                                    "
                                                >
                                                    {{
                                                        p.stock === 0
                                                            ? "無庫存"
                                                            : p.stock
                                                    }}
                                                </v-chip>
                                            </td>
                                            <td>
                                                <v-chip
                                                    size="small"
                                                    :color="
                                                        p.is_active
                                                            ? 'success'
                                                            : 'default'
                                                    "
                                                >
                                                    {{
                                                        p.is_active
                                                            ? "上架中"
                                                            : "下架中"
                                                    }}
                                                </v-chip>
                                            </td>
                                        </tr>
                                        <tr
                                            v-if="
                                                !stats.products.low_stock_list
                                                    .length
                                            "
                                        >
                                            <td
                                                colspan="3"
                                                class="text-center text-medium-emphasis py-4"
                                            >
                                                無低庫存商品
                                            </td>
                                        </tr>
                                    </tbody>
                                </v-table>
                            </v-card-text>
                        </v-card>
                    </v-col>
                </v-row>

                <v-row class="mt-4">
                    <v-col cols="12">
                        <v-card>
                            <v-card-title
                                class="text-body-1 font-weight-bold pt-4 px-4"
                            >
                                <v-icon size="20" class="mr-4"
                                    >mdi-ticket-percent</v-icon
                                >
                                優惠代碼一覽
                                <v-chip
                                    size="small"
                                    color="primary"
                                    class="ml-2"
                                    >啟用中 {{ activeCouponsCount }}</v-chip
                                >
                            </v-card-title>
                            <v-card-text class="pa-0">
                                <v-table density="compact" hover>
                                    <thead>
                                        <tr>
                                            <th>活動代碼</th>
                                            <th>結束日期</th>
                                            <th>狀態</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            v-for="(coupon, i) in stats.coupons"
                                            :key="i"
                                            style="cursor: pointer"
                                            @click="
                                                window.location.href =
                                                    '/admin/coupons/' +
                                                    coupon.id
                                            "
                                        >
                                            <td>{{ coupon.code }}</td>
                                            <td>
                                                {{
                                                    formatDate(
                                                        coupon.expires_at,
                                                    )
                                                }}
                                            </td>
                                            <td>
                                                <v-chip
                                                    size="small"
                                                    :color="
                                                        coupon.is_active
                                                            ? 'success'
                                                            : 'default'
                                                    "
                                                >
                                                    {{
                                                        coupon.is_active
                                                            ? "使用中"
                                                            : "已停用"
                                                    }}
                                                </v-chip>
                                            </td>
                                        </tr>
                                        <tr v-if="!stats.coupons.length">
                                            <td
                                                colspan="4"
                                                class="text-center text-medium-emphasis py-4"
                                            >
                                                尚無優惠代碼資料
                                            </td>
                                        </tr>
                                    </tbody>
                                </v-table>
                            </v-card-text>
                        </v-card>
                    </v-col>
                </v-row>

                <v-row class="mt-4">
                    <v-col cols="12">
                        <v-card>
                            <v-card-title
                                class="text-body-1 font-weight-bold pt-4 px-4"
                            >
                                <v-icon size="20" class="mr-4"
                                    >mdi-flower-poppy</v-icon
                                >
                                廣告投放檔期
                                <v-chip
                                    size="small"
                                    color="primary"
                                    class="ml-2"
                                    >投放中 {{ activeAdsCount }}</v-chip
                                >
                            </v-card-title>
                            <v-card-text class="pa-0">
                                <v-table density="compact" hover>
                                    <thead>
                                        <tr>
                                            <th>廣告名稱</th>
                                            <th>開始日期</th>
                                            <th>結束日期</th>
                                            <th>狀態</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            v-for="(ad, i) in stats.ads"
                                            :key="i"
                                            style="cursor: pointer"
                                            @click="
                                                window.location.href =
                                                    '/admin/advertisements/' +
                                                    ad.id
                                            "
                                        >
                                            <td>{{ ad.title }}</td>
                                            <td>
                                                {{
                                                    formatDate(
                                                        ad.display_start_at,
                                                    )
                                                }}
                                            </td>
                                            <td>
                                                {{
                                                    formatDate(
                                                        ad.display_end_at,
                                                    )
                                                }}
                                            </td>
                                            <td>
                                                <v-chip
                                                    size="small"
                                                    :color="
                                                        ad.is_active
                                                            ? 'success'
                                                            : 'default'
                                                    "
                                                >
                                                    {{
                                                        ad.is_active
                                                            ? "投放中"
                                                            : "已停用"
                                                    }}
                                                </v-chip>
                                            </td>
                                        </tr>
                                        <tr v-if="!stats.ads.length">
                                            <td
                                                colspan="4"
                                                class="text-center text-medium-emphasis py-4"
                                            >
                                                尚無廣告資料
                                            </td>
                                        </tr>
                                    </tbody>
                                </v-table>
                            </v-card-text>
                        </v-card>
                    </v-col>
                </v-row>
            </template>
        </v-container>
    </AdminLayout>
</template>
