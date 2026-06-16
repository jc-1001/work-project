<script setup>
    import { computed, ref, onMounted } from 'vue'
    import api from '../bootstrap'
    import { useAuth } from '../composables/useAuth'

    const { user, fetchUser, clearUser } = useAuth()

    onMounted(() => {
        fetchUser()
    })
    const isLoggedIn = computed(() => !!user.value)
    const currentPath = window.location.pathname
    const drawer = ref(false)
    const snackbar = ref({ show: false, text: '', color: 'success' })

    const notify = (text, color = 'success') => {
        snackbar.value = { show: true, text, color }
    }

    const menuItems = [
        { name: '首頁', icon: 'mdi-home', path: '/' },
        { name: '商城', icon: 'mdi-shopping', path: '/shop' },
        {
            name: '會員中心',
            icon: 'mdi-account',
            path: '/profile',
            requiresAuth: true,
        },
        { name: '購物車', icon: 'mdi-cart', path: '/cart', requiresAuth: true },
    ]

    const displayMenuItems = computed(() => (isLoggedIn.value ? menuItems : menuItems.filter((item) => !item.requiresAuth)))

    const navigate = (path) => {
        window.location.href = path
    }

    const handleLogout = async () => {
        drawer.value = false
        try {
            await api.post('/logout')
            notify('登出成功')
        } catch {
            notify('登出失敗，請稍後再試', 'error')
        } finally {
            clearUser()
            window.location.href = '/'
        }
    }
</script>

<template>
    <v-navigation-drawer v-model="drawer" temporary>
        <v-list-item title="購物網站" prepend-icon="mdi-storefront-outline" base-color="white" class="py-4 bg-primary" />
        <v-divider />
        <v-list nav>
            <v-list-item
                v-for="item in displayMenuItems"
                :key="item.path"
                :prepend-icon="item.icon"
                :title="item.name"
                :active="currentPath === item.path"
                rounded="lg"
                @click="navigate(item.path)"
            />
            <v-list-item v-if="isLoggedIn" prepend-icon="mdi-logout" title="登出" rounded="lg" @click="handleLogout" />
            <v-list-item v-else prepend-icon="mdi-login" title="登入" rounded="lg" @click="navigate('/login')" />
        </v-list>
    </v-navigation-drawer>

    <v-app-bar color="primary" elevation="2">
        <v-app-bar-nav-icon color="white" class="d-md-none" @click="drawer = !drawer" />

        <v-app-bar-title class="font-weight-bold">購物網站</v-app-bar-title>

        <template #append>
            <div class="d-none d-md-flex align-center">
                <v-btn
                    v-for="item in displayMenuItems"
                    :key="item.path"
                    :prepend-icon="item.icon"
                    variant="text"
                    color="white"
                    :active="currentPath === item.path"
                    @click="navigate(item.path)"
                >
                    {{ item.name }}
                </v-btn>

                <v-btn v-if="isLoggedIn" prepend-icon="mdi-logout" variant="text" color="white" @click="handleLogout">登出</v-btn>
                <v-btn v-else prepend-icon="mdi-login" variant="text" color="white" @click="navigate('/login')">登入</v-btn>
            </div>
        </template>
    </v-app-bar>

    <v-snackbar v-model="snackbar.show" :color="snackbar.color" location="top" timeout="3000">
        {{ snackbar.text }}
    </v-snackbar>
</template>
