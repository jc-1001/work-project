import { reactive, computed } from 'vue'
import api from '../bootstrap'

const state = reactive({
  user: null
})

let fetchingPromise = null

export function useAdminAuth() {
  async function fetchUser() {
    if (fetchingPromise) return fetchingPromise

    fetchingPromise = api.get('/admin/me')
      .then(res => {
        state.user = res.data
        return true
      })
      .catch(() => {
        state.user = null
        return false
      })
      .finally(() => {
        fetchingPromise = null
      })

    return fetchingPromise
  }

  function clearUser() {
    state.user = null
  }

  const isLoggedIn   = computed(() => !!state.user)
  const isSuperAdmin = computed(() => state.user?.role === 'super_admin')

  return {
    user: computed(() => state.user),
    isLoggedIn,
    isSuperAdmin,
    fetchUser,
    clearUser,
  }
}
