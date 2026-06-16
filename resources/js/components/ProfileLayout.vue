<script setup>
    import { computed, onMounted } from 'vue'
    import { useAuth } from '../composables/useAuth'

    const profileItems = [
        { name: '個人資料', icon: 'mdi-account-outline', path: '/profile' },
        { name: '我的訂單', icon: 'mdi-format-list-bulleted', path: '/profile/orders' },
    ]

    const { user, fetchUser } = useAuth()
    const currentPath = window.location.pathname

    onMounted(() => {
        if (!user.value) fetchUser()
    })

    const avatarLetter = computed(() => (user.value?.name ? user.value.name.charAt(0).toUpperCase() : 'U'))

    const navigate = (path) => {
        window.location.href = path
    }
</script>

<template>
    <v-container style="max-width: 1200px" class="py-8 px-4">
        <v-row align="start">
            <v-col cols="12" md="3">
                <v-card rounded="xl" elevation="2" class="pa-4">
                    <div class="d-flex align-center ga-4 pb-4">
                        <v-avatar color="primary" size="52">
                            <span class="text-h6 font-weight-bold text-white">{{ avatarLetter }}</span>
                        </v-avatar>
                        <div>
                            <p class="text-caption text-medium-emphasis mb-0">歡迎回來</p>
                            <p class="text-body-1 font-weight-semibold mb-0">{{ user?.name || '載入中...' }}</p>
                        </div>
                    </div>
                    <v-divider class="mb-2" />
                    <v-list nav density="compact">
                        <v-list-item
                            v-for="item in profileItems"
                            :key="item.path"
                            :prepend-icon="item.icon"
                            :title="item.name"
                            :active="currentPath === item.path"
                            color="primary"
                            rounded="lg"
                            @click="navigate(item.path)"
                        />
                    </v-list>
                </v-card>
            </v-col>

            <v-col cols="12" md="9">
                <v-card rounded="xl" elevation="2" class="pa-6" style="min-height: 400px">
                    <slot />
                </v-card>
            </v-col>
        </v-row>
    </v-container>
</template>
