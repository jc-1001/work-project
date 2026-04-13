<script setup>
  import { ref } from "vue";
  import NavBar from "./components/NavBar.vue";
  // import AdminNavBar from "./components/AdminNavBar.vue";
  // import Loading from "./components/Loading.vue";
  
</script>

<template>
  <!-- <Loading /> -->
  <NavBar />
  <!-- <AdminNavBar/> -->
  <router-view />
</template>
