<script setup>
  import { ref } from "vue";
  import NavBar from "./components/NavBar.vue";
  // import Loading from "./components/Loading.vue";
</script>

<template>
  <!-- <Loading /> -->
  <NavBar />
  <router-view />
</template>
