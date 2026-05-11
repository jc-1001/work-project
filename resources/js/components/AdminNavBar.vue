<script setup>
import { ref } from "vue"
import api from "../bootstrap"

const currentPath = window.location.pathname

const menuItems = ref([
  { name: "會員列表", icon: "mdi-account-group", path: "/admin/user" },
  { name: "商品管理", icon: "mdi-package-variant", path: "/admin/products" },
  { name: "訂單管理", icon: "mdi-clipboard-list", path: "/admin/orders" },
])

const navigate = (path) => {
  window.location.href = path
}

async function logout() {
  try {
    await api.post("/logout")
  } finally {
    window.location.href = "/admin/login"
  }
}
</script>

<template>
  <v-app-bar elevation="1">
    <v-btn
      v-for="item in menuItems"
      :key="item.path"
      variant="text"
      :prepend-icon="item.icon"
      :active="currentPath === item.path"
      @click="navigate(item.path)"
    >{{ item.name }}</v-btn>

    <v-spacer />

    <v-btn variant="text" prepend-icon="mdi-logout" @click="logout">登出</v-btn>
  </v-app-bar>
</template>
