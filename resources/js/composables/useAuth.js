import { ref } from 'vue'
import api from '../bootstrap'

const user = ref(null)
let pendingFetch = null

export function useAuth() {
  const fetchUser = async () => {
    if (pendingFetch) return pendingFetch
    pendingFetch = api.get('/me')
      .then(res => {
        const u = res.data.user
        user.value = u.is_admin ? null : u
      })
      .catch(() => { user.value = null })
      .finally(() => { pendingFetch = null })
    return pendingFetch
  }

  const isLoggedIn = () => !!user.value

  const clearUser = () => {
    user.value = null
    pendingFetch = null
  }

  return { user, fetchUser, isLoggedIn, clearUser }
}
