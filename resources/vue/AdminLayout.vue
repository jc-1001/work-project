<script setup>
import AdminNavBar from "./components/AdminNavBar.vue";
</script>

<template>
  <v-app>
    <AdminNavBar />
    <v-main>
      <router-view />
    </v-main>
  </v-app>
</template>
