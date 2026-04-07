<script setup>
import { ref, computed } from "vue";
import { useRoute } from "vue-router";
import { ElMenu, ElMenuItem, } from "element-plus";
import { House, ShoppingBag, User, ShoppingCart } from "@element-plus/icons-vue";
const route = useRoute();

const menuItems = ref([
  { name: "首頁", icon: "House", path: "/" },
  { name: "商城", icon: "ShoppingBag", path: "/shop" },
  { name: "會員中心", icon: "User", path: "/profile" },
  { name: "購物車", icon: "ShoppingCart", path: "/cart" },
]);

const activePath = computed(() => {
  return route.path;
});
const handleSelect = (index) => {
  // 這裡可以處理選單項目被選取的邏輯，例如導航到對應的路由
  console.log("導覽至", index);
};
</script>
<template>
  <el-menu
    :default-active="activePath"
    class="el-menu-demo"
    mode="horizontal"
    @select="handleSelect"
    router
  >
    <el-menu-item v-for="item in menuItems" :key="item.path" :index="item.path">
      <el-icon>
        <component :is="item.icon" />
      </el-icon>
      <span>{{ item.name }}</span>
    </el-menu-item>
  </el-menu>
</template>
