<script setup>
    import { onMounted, ref, watch } from 'vue'
    import api from '../../../bootstrap'
    import { useAuth } from '../../../composables/useAuth'
    import FrontLayout from '../../../layouts/FrontLayout.vue'
    import ProfileLayout from '../../../components/ProfileLayout.vue'

    const { user, fetchUser } = useAuth()
    const snackbar = ref({ show: false, text: '', color: 'success' })
    const loading = ref(true)
    const saving = ref(false)
    const editName = ref('')

    const notify = (text, color = 'success') => {
        snackbar.value = { show: true, text, color }
    }

    watch(
        user,
        (u) => {
            if (u) {
                editName.value = u.name
                loading.value = false
            }
        },
        { immediate: true }
    )

    onMounted(() => {
        fetchUser()
    })

    const updateUser = () => {
        saving.value = true
        api.put('/user/update', { name: editName.value })
            .then(() => {
                notify('更新成功！')
                user.value = { ...user.value, name: editName.value }
            })
            .catch(() => {
                notify('更新失敗，請稍後再試', 'error')
            })
            .finally(() => {
                saving.value = false
            })
    }
</script>

<template>
    <FrontLayout>
        <ProfileLayout>
            <p class="text-h6 font-weight-bold mb-1">個人資料</p>
            <p class="text-body-2 text-medium-emphasis mb-6">管理您的帳號資訊</p>

            <v-sheet color="grey-lighten-5" rounded="lg" class="pa-5 mb-6">
                <v-text-field v-model="editName" label="會員名稱" variant="outlined" prepend-inner-icon="mdi-account-outline" class="mb-3" :disabled="loading" />
                <v-text-field :model-value="user?.email" label="電子信箱" variant="outlined" prepend-inner-icon="mdi-email-outline" readonly>
                    <template #append-inner>
                        <v-tooltip text="Email 為登入帳號，無法修改" location="top">
                            <template #activator="{ props }">
                                <v-icon v-bind="props" icon="mdi-information-outline" color="grey-lighten-1" size="18" />
                            </template>
                        </v-tooltip>
                    </template>
                </v-text-field>
            </v-sheet>

            <div class="d-flex justify-end">
                <v-btn color="primary" rounded="lg" :loading="saving" :disabled="loading" @click="updateUser">儲存變更</v-btn>
            </div>
        </ProfileLayout>

        <v-snackbar v-model="snackbar.show" :color="snackbar.color" location="top" timeout="3000">
            {{ snackbar.text }}
        </v-snackbar>
    </FrontLayout>
</template>
