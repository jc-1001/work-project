<script setup>
  import { computed } from "vue";
  import { useRoute } from "vue-router";
  import NavBar from "./components/NavBar.vue";

  const route = useRoute();
  const isAdmin = computed(() => route.path.startsWith('/admin'));
</script>

<template>
  <v-app>
    <NavBar v-if="!isAdmin" />
    <v-main>
      <router-view />
    </v-main>
  </v-app>
</template>
