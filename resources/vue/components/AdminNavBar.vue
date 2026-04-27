<script setup>
import { ref } from "vue";
import { useRouter } from "vue-router";

const router = useRouter();

const menuItems = ref([
  { name: "會員列表", icon: "mdi-account-group", path: "/admin/user" },
  { name: "商品管理", icon: "mdi-package-variant", path: "/admin/products" },
  { name: "訂單管理", icon: "mdi-clipboard-list", path: "/admin/orders" },
]);

function logout() {
  router.push("/admin");
}
</script>

<template>
  <v-app-bar elevation="1">
    <v-btn
      v-for="item in menuItems"
      :key="item.path"
      variant="text"
      :prepend-icon="item.icon"
      :to="item.path"
    >{{ item.name }}</v-btn>

    <v-spacer />

    <v-btn variant="text" prepend-icon="mdi-logout" @click="logout">登出</v-btn>
  </v-app-bar>
</template>
