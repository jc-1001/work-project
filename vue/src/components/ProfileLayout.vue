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
  <main class="profile-container">
    <div class="profile-layout">
      <aside class="profile-sidebar">
        <h2 class="mb-4">會員中心</h2>
        
        <el-menu
          default-active="/profile"
          class="side-menu"
          router
        >
          <el-menu-item 
            v-for="item in profileItem" 
            :key="item.path" 
            :index="item.path"
          >
            <el-icon>
              <component :is="item.icon" />
            </el-icon>
            <span>{{ item.name }}</span>
          </el-menu-item>
        </el-menu>
      </aside>

      <section class="profile-content">
        <router-view />
      </section>
    </div>
  </main>
</template>
<style scoped>
.profile-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 40px 20px;
}

.profile-layout {
  display: flex;
  gap: 30px;
  align-items: flex-start;
}

.profile-sidebar {
  width: 280px; 
  background-color: #ffffff;
  border-radius: 12px;
  padding: 20px;
  border: 1px solid gray;
}

.profile-content {
  flex-grow: 1; 
  max-height: 60vh;
  background-color: #ffffff;
  border-radius: 12px;
  padding: 30px;
  border: 1px solid gray;
  overflow-y: auto;
}

/* 選單間距調整 */
.side-menu :deep(.el-menu-item) {
  margin-bottom: 10px; 
  border-radius: 8px;  
  border: 1px solid  gray; 
  height: 50px; 
  line-height: 50px;
}

/* 懸停的背景色 */
.side-menu :deep(.el-menu-item:hover) {
  background-color: #e6f7ff !important; 
  color: #2381df !important;            
}

/* 被選取時的顏色 */
.side-menu :deep(.el-menu-item.is-active) {
  background-color: #2381df !important; 
  color: #ffffff !important;       
  border: none;     
}
/* 移除預設的右邊框 */
.side-menu.el-menu {
  border-right: none;
}


@media screen and (max-width: 768px) {
  .profile-container {
    padding: 20px 10px; 
  }

  .profile-layout {
    flex-direction: column; 
    gap: 20px;
  }

  .profile-sidebar {
    width: 100%; 
    box-sizing: border-box; 
    display: block; 
  }

  .profile-content {
    width: 100%;
    box-sizing: border-box;
    padding: 20px; 
  }

  
  .side-menu {
    background: transparent;
    
  }
}
</style>
