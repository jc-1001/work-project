<script setup>
    import { ref } from 'vue'
    import ServiceCard from './ServiceCard.vue'

    const isOpen = ref(false)
    const serviceCardRef = ref(null)

    function scrollToTop() {
        window.scrollTo({ top: 0, behavior: 'smooth' })
        isOpen.value = false
    }

    function openService() {
        serviceCardRef.value.open()
        isOpen.value = false
    }

    function openLink() {
        window.open('https://www.ntnu.edu.tw/', '_blank')
    }
</script>

<template>
    <div class="position-fixed d-flex flex-column-reverse align-center ga-3" style="right: 30px; bottom: 30px; z-index: 999">
        <transition-group name="fab">
            <div v-if="isOpen" key="top">
                <v-tooltip text="回到頂部" location="left">
                    <template #activator="{ props }">
                        <div
                            class="fab-item d-flex align-center justify-center rounded-circle cursor-pointer bg-white elevation-1"
                            style="width: 50px; height: 50px"
                            v-bind="props"
                            @click="scrollToTop"
                        >
                            <v-icon icon="mdi-arrow-up" size="20" />
                        </div>
                    </template>
                </v-tooltip>
            </div>

            <div v-if="isOpen" key="service">
                <v-tooltip text="線上客服" location="left">
                    <template #activator="{ props }">
                        <div
                            class="fab-item d-flex align-center justify-center rounded-circle cursor-pointer bg-white elevation-1"
                            style="width: 50px; height: 50px"
                            v-bind="props"
                            @click="openService"
                        >
                            <v-icon icon="mdi-headset" size="20" />
                        </div>
                    </template>
                </v-tooltip>
            </div>

            <div v-if="isOpen" key="link">
                <v-tooltip text="聯絡我們" location="left">
                    <template #activator="{ props }">
                        <div
                            class="fab-item d-flex align-center justify-center rounded-circle cursor-pointer bg-white elevation-1"
                            style="width: 50px; height: 50px"
                            v-bind="props"
                            @click="openLink"
                        >
                            <v-icon icon="mdi-link" size="20" />
                        </div>
                    </template>
                </v-tooltip>
            </div>
        </transition-group>

        <div
            class="fab-main d-flex align-center justify-center rounded-circle cursor-pointer bg-primary elevation-3"
            style="width: 60px; height: 60px"
            @click="isOpen = !isOpen"
        >
            <v-icon :icon="isOpen ? 'mdi-close' : 'mdi-dots-vertical'" size="24" color="white" />
        </div>
    </div>
    <ServiceCard ref="serviceCardRef" />
</template>

<style scoped>
    .fab-main {
        transition: transform 0.3s;
    }
    .fab-main:hover {
        transform: scale(1.1);
    }

    .fab-item {
        transition: transform 0.2s;
    }
    .fab-item:hover {
        transform: scale(1.15);
        background: #ecf5ff;
    }

    .fab-enter-active {
        transition: all 0.3s ease;
    }
    .fab-leave-active {
        transition: all 0.2s ease;
    }
    .fab-enter-from,
    .fab-leave-to {
        opacity: 0;
        transform: translateY(20px) scale(0.5);
    }
</style>
