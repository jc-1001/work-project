<script setup>
import AdminLayout from "../../layouts/AdminLayout.vue"
const window = globalThis
</script>

<template>
    <AdminLayout>
        <v-container
            class="d-flex align-center justify-center"
            style="min-height: 80vh"
        >
            <v-card max-width="480" class="text-center pa-8" flat>
                <v-icon size="72" color="warning" class="mb-4">mdi-emoticon-dead</v-icon>
                <v-card-title class="text-h5 mb-2">權限不足</v-card-title>
                <v-card-text class="text-body-1 text-medium-emphasis">
                    此頁面僅限超級管理員存取。
                    <br />
                    請聯繫超級管理員取得相應權限。
                </v-card-text>
                <v-card-actions class="justify-center mt-2">
                    <v-btn variant="tonal" @click="window.history.back()">返回上一頁</v-btn>
                </v-card-actions>
            </v-card>
        </v-container>
    </AdminLayout>
</template>
