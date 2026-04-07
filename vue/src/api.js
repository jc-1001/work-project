import axios from 'axios'

const api = axios.create({
  baseURL: 'http://localhost:8080/api',  // Laravel API 位址
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  }
})

// 每次請求自動帶上 token
api.interceptors.request.use(config => {
  const token = localStorage.getItem('token')
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})

export default api