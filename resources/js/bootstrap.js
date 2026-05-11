import axios from 'axios'

const api = axios.create({
    baseURL: import.meta.env.VITE_API_URL,
    withCredentials: true,              // 允許跨域請求時攜帶 cookie（session 和 XSRF-TOKEN 都需要）
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
    },
})

// axios 會自動讀取瀏覽器的 XSRF-TOKEN cookie，並在每次請求加上 X-XSRF-TOKEN header
// Laravel 收到後會用它來驗證 CSRF，不需要手動處理

/*
 * 全域 API 錯誤攔截器
 * 集中處理系統級錯誤（401、403、500），各 Vue 組件的 catch 只需處理業務邏輯錯誤（如 422 表單驗證）
 */
api.interceptors.response.use(
    response => response,
    error => {
        const status = error.response?.status
        const path   = window.location.pathname

        if (status === 401) {
            // /me 是用來探測登入狀態的，401 只代表「未登入」，不需要強制跳轉
            if (error.config?.url === '/me') {
                return Promise.reject(error)
            }
            // 其他 API 收到 401 表示 session 過期，依路徑導向對應登入頁
            const isAdmin = path.startsWith('/admin')
            window.location.href = isAdmin ? '/admin/login' : '/login'
        }

        if (status === 403) {
            // 有登入但無存取權限
            window.location.href = '/403'
        }

        if (status === 500) {
            // 伺服器錯誤，僅記錄 log，不強制跳頁（各組件可視情況顯示錯誤訊息）
            console.error('[API 500]', error.config?.url, error.response?.data)
        }

        // 其餘狀態碼（422 驗證錯誤等）交給各組件的 catch 自行處理
        return Promise.reject(error)
    }
)

export default api
