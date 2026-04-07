<script setup>
import { ref } from "vue";
import { ElInput } from "element-plus";
</script>
<template>
  <h1>會員中心資料異動</h1>
  <div class="input-block">
    <h3>會員名稱</h3>
    <el-input size="large" placeholder="請輸入您的姓名"></el-input>
    <h3>EMAIL</h3>
    <el-input size="large" placeholder="請輸入您的EMAIL"></el-input>
  </div>
  <div class="button"><el-button size="large">確認更新資料</el-button></div>
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