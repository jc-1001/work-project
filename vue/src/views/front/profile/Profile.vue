<script setup>
import { onMounted, ref } from "vue";
import { ElInput, ElMessage } from "element-plus";
import api from "../../../api";

const profile = ref({
  name: "",
  email: "",
});

// 取得資料(姓名與EMAIL)
const fetchUsersProfile = async () => {
  try {
    const res = await api.get("/me");
    profile.value.name = res.data.name;
    profile.value.email = res.data.email;
  } catch (error) {
    console.log("抓會員失敗", err);
  }
};
// 更新資料(姓名)
const updateUser = async () => {
  try {
    const res = await api.put("/user/update", {
      name: profile.value.name,
    });
  } catch (error) {
    console.log("更新會員失敗", error);
    ElMessage.error("更新失敗請稍後再試!!")
  }
};

onMounted(() => {
  fetchUsersProfile();
});
</script>
<template>
  <h2>會員資料</h2>
  <div class="input-block">
    <h3>會員名稱</h3>
    <el-input
      v-model="profile.name"
      size="large"
      placeholder="請輸入您的姓名"
    ></el-input>
    <h3>E-MAIL</h3>
    <el-input v-model="profile.email" size="large" disabled=""></el-input>
  </div>
  <small style="color: red"
    >※ 請牢記您的EMAIL，這將是您登入帳號的重要資訊</small
  >
  <div class="button">
    <el-button size="large" @click="updateUser">確認更新資料</el-button>
  </div>
</template>
<style scoped>
.input-block {
  width: 100%;
  margin-bottom: 20px;
}
.button {
  margin-top: 30px;
}
</style>
