import axios from 'axios'

const api = axios.create({
    baseURL: import.meta.env.VITE_API_URL,
    withCredentials: true,
    headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json',
    },
})

api.interceptors.response.use(
    (response) => response,
    (error) => {
        const status = error.response?.status
        const path = window.location.pathname

        if (status === 401) {
            if (error.config?.url === '/me') {
                return Promise.reject(error)
            }
            const isAdmin = path.startsWith('/admin')
            window.location.href = isAdmin ? '/admin/login' : '/login'
        }

        if (status === 422) {
            console.warn('[API 422]', error.config?.url, error.response?.data)
        }

        if (status === 500) {
            console.error('[API 500]', error.config?.url, error.response?.data)
        }

        return Promise.reject(error)
    }
)

export default api
