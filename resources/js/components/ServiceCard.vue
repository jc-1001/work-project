<script setup>
import { ref, reactive } from 'vue'

const sheet = ref(false)
const activeTab = ref('faq')
const snackbar = ref({ show: false, text: '', color: 'success' })

const faqList = [
  { id: '1', question: '如何查詢訂單狀態？', answer: '登入後至「會員中心」頁面後，點選「我的訂單」即可查閱，並可點選再買一次。' },
  { id: '2', question: '可以按分類查找商品嗎？', answer: '至「商城」最頂部即可選擇商品種類。' },
  { id: '3', question: '如何更改自己的 姓名 / 暱稱 ？', answer: '登入後至「會員中心」頁面後，點選「個人資料」即可更改。' },
  { id: '4', question: '運費怎麼算？', answer: '只要於商城消費，均須收取 $60 運費。' },
]

const contactOptions = [
  { title: '商品問題', value: 'product' },
  { title: '訂單問題', value: 'order' },
  { title: '其他', value: 'other' },
]

const form = reactive({
  type: '',
  email: '',
  message: '',
})

async function submitForm() {
  snackbar.value = { show: true, text: '已收到您的問題，我們會盡快回覆！', color: 'success' }
  sheet.value = false
}

defineExpose({ open: () => (sheet.value = true) })
</script>

<template>
  <v-bottom-sheet v-model="sheet">
    <v-sheet rounded="t-xl" style="max-height: 65vh; overflow-y: auto">
      <div class="d-flex align-center justify-space-between px-5 pt-4 pb-2">
        <span class="text-subtitle-1 font-weight-medium">客服中心</span>
        <v-btn icon="mdi-close" variant="text" size="small" @click="sheet = false" />
      </div>

      <div class="px-4 pb-4">
        <v-tabs v-model="activeTab" color="primary" density="compact" class="mb-3">
          <v-tab value="faq">常見問題</v-tab>
          <v-tab value="contact">聯繫我們</v-tab>
        </v-tabs>

        <v-tabs-window v-model="activeTab">
          <!-- FAQ 分頁 -->
          <v-tabs-window-item value="faq">
            <v-expansion-panels variant="accordion">
              <v-expansion-panel
                v-for="item in faqList"
                :key="item.id"
                :value="item.id"
                :title="item.question"
              >
                <v-expansion-panel-text class="text-body-2">
                  {{ item.answer }}
                </v-expansion-panel-text>
              </v-expansion-panel>
            </v-expansion-panels>
          </v-tabs-window-item>

          <!-- 聯繫表單分頁 -->
          <v-tabs-window-item value="contact">
            <v-select
              v-model="form.type"
              :items="contactOptions"
              item-title="title"
              item-value="value"
              label="問題類型"
              variant="outlined"
              density="compact"
              placeholder="請選擇"
              class="my-3"
            />
            <v-text-field
              v-model="form.email"
              label="您的 Email"
              variant="outlined"
              density="compact"
              placeholder="example@mail.com"
              class="mb-3"
            />
            <v-textarea
              v-model="form.message"
              label="問題描述"
              variant="outlined"
              density="compact"
              rows="4"
              placeholder="請描述您的問題..."
              class="mb-3"
            />
            <v-btn color="primary" variant="tonal" @click="submitForm">送出</v-btn>
          </v-tabs-window-item>
        </v-tabs-window>
      </div>
    </v-sheet>
  </v-bottom-sheet>

  <v-snackbar v-model="snackbar.show" :color="snackbar.color" location="top" timeout="3000">
    {{ snackbar.text }}
  </v-snackbar>
</template>
