<script setup>
import { ref, computed, onMounted } from "vue";
import { useDisplay } from "vuetify";
import { useAdminAuth } from "../composables/useAdminAuth";
import api from "../bootstrap";

const { user, isSuperAdmin, clearUser, fetchUser } = useAdminAuth();

onMounted(() => {
    fetchUser();
});
const { mobile } = useDisplay();
const drawer = ref(false);
const snackbar = ref({ show: false, text: "", color: "success" });

const showMessage = (text, color = "success") => {
    snackbar.value = { show: true, text, color };
};

const menuItems = computed(() => [
    ...(isSuperAdmin.value
        ? [{ name: "管理員管理", icon: "mdi-shield-account", path: "/admin/administrators" }]
        : []),
]);

const navigate = (path) => {
    window.location.href = path;
};

function logout() {
    api.post("/admin/logout")
        .then(() => {
            showMessage("登出成功");
        })
        .catch(() => {
            showMessage("登出失敗，請稍後再試", "error");
        })
        .finally(() => {
            clearUser();
            setTimeout(() => {
                window.location.href = "/admin/login";
            }, 1000);
        });
}
</script>

<template>
    <v-navigation-drawer v-if="mobile" v-model="drawer" temporary>
        <v-list-item prepend-icon="mdi-shield-crown" title="後台管理" nav />
        <v-divider />
        <v-list nav>
            <v-list-item
                v-for="item in menuItems"
                :key="item.path"
                :prepend-icon="item.icon"
                :title="item.name"
                @click="
                    navigate(item.path);
                    drawer = false;
                "
            />
            <v-divider class="my-2" />
            <v-list-item
                prepend-icon="mdi-logout"
                title="登出"
                @click="logout"
            />
        </v-list>
    </v-navigation-drawer>

    <v-app-bar elevation="1">
        <v-app-bar-nav-icon v-if="mobile" @click="drawer = !drawer" />
        <v-app-bar-title v-if="mobile">後台管理</v-app-bar-title>
        <template v-if="!mobile">
            <v-btn
                v-for="item in menuItems"
                :key="item.path"
                variant="text"
                :prepend-icon="item.icon"
                @click="navigate(item.path)"
                >{{ item.name }}</v-btn
            >
        </template>

        <v-spacer />
        <span v-if="user" class="text-body-2 text-medium-emphasis mr-2">{{
            user.name
        }}</span>
        <v-btn variant="text" prepend-icon="mdi-logout" @click="logout"
            >登出</v-btn
        >
    </v-app-bar>

    <v-snackbar
        v-model="snackbar.show"
        :color="snackbar.color"
        timeout="1000"
        location="top"
    >
        {{ snackbar.text }}
    </v-snackbar>
</template>
