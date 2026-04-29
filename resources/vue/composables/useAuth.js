import { ref } from 'vue'
import api from '../api'

const user = ref(null)
const resolved = ref(false)

export function useAuth() {
  const fetchUser = async () => {
    if (resolved.value) return
    try {
      const res = await api.get('/me')
      user.value = res.data
    } catch {
      user.value = null
    } finally {
      resolved.value = true
    }
  }

  const isLoggedIn = () => !!user.value

  const clearUser = () => {
    user.value = null
    resolved.value = false
  }

  return { user, fetchUser, isLoggedIn, clearUser }
}
