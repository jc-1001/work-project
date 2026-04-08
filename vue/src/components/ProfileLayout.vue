<script setup>
import { ref } from "vue";
import { RouterLink } from "vue-router";
import { User, List } from "@element-plus/icons-vue";
const profileItem = ref([
  { name: "個人資料", icon: "User", path: "/profile" },
  { name: "我的訂單", icon: "List", path: "/profile/orders" },
]);
</script>

<template>
  <main>
    <div class="profile-layout">
      <aside class="profile-sidebar">
        <ul>
          <li v-for="item in profileItem" :key="item.name">
            <RouterLink :to="item.path">
              <div class="icon-container">
                <el-icon>
                  <component :is="item.icon" />
                </el-icon>
                <h2>{{ item.name }}</h2>
              </div>
            </RouterLink>
          </li>
        </ul>
      </aside>
      <section class="profile-content">
        <router-view />
      </section>
    </div>
  </main>
</template>
<style scoped>
.profile-layout {
  display: flex;
  min-height: 100vh;
}
.profile-sidebar {
  width: 30%;
  height: 100%;
  background-color: #f5f5f5;
  padding: 20px;
  margin: 20px;
}
.profile-sidebar ul {
  list-style: none;
  padding: 0;
}
.profile-sidebar li {
  margin-bottom: 20px;
}
.profile-content {
  height: max-content;
  margin: 20px 0;
  width: 100%;
  padding: 20px;
  background-color: #f5f5f5;
}
.icon-container {
  background-color: white;
  border-radius: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 12px;
  padding: 20px 40px;
  border: 1px solid transparent;
}

/* 移除 h2 的預設邊距，避免它把外框撐開或造成不對齊 */
.icon-container h2 {
  margin: 0;
  font-size: 1.2rem;
  color: #333;
}
a {
  text-decoration: none;
}
@media screen and (max-width: 768px) {
  .profile-layout {
    display: flex;
    flex-direction: column;
  }
  
}
</style>
