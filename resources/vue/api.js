import axios from 'axios'

const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL,
  withCredentials: true,              // 允許跨域請求時攜帶 cookie（session 和 XSRF-TOKEN 都需要）
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  }
})

// axios 會自動讀取瀏覽器的 XSRF-TOKEN cookie，並在每次請求加上 X-XSRF-TOKEN header
// Laravel 收到後會用它來驗證 CSRF，不需要手動處理

export default api
