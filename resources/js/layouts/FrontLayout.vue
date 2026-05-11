<script setup>
import NavBar from '../components/NavBar.vue'
import ToolCircle from '../components/ToolCircle.vue'
</script>

<template>
  <v-app>
    <NavBar />
    <v-main>
      <slot />
    </v-main>
    <ToolCircle />
  </v-app>
</template>
