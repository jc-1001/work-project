<script setup>
import AdminNavBar from "../components/AdminNavBar.vue"
</script>

<template>
  <v-app>
    <AdminNavBar />
    <v-main>
      <slot />
    </v-main>
  </v-app>
</template>
