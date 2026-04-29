<script setup>
import { computed } from "vue";
import { useRoute } from "vue-router";
import NavBar from "./components/NavBar.vue";
import ToolCircle from "./components/ToolCircle.vue";

const route = useRoute();
const isAdmin = computed(() => route.path.startsWith("/admin"));
</script>

<template>
    <v-app>
        <NavBar v-if="!isAdmin" />
        <div :class="{ 'with-navbar': !isAdmin }">
            <ToolCircle />
            <router-view />
        </div>
    </v-app>
</template>

<style>
/* 瀏覽列預留空間 */
.with-navbar {
    padding-top: 60px;
}
@media (max-width: 767px) {
    .with-navbar {
        padding-top: 56px;
    }
}
</style>
