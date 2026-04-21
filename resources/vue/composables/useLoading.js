import { ref } from "vue";

// 模組層級的 ref：所有 import 這個檔案的地方共用同一個狀態
export const isLoading = ref(false);

// 追蹤目前有幾個請求還在進行中
export let pendingCount = 0;
