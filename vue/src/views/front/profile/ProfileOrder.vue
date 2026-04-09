<script setup></script>
<template>
  <div class="orders-container" v-loading="loading">
    <h2>我的訂單紀錄</h2>

    <!-- <el-empty  description="尚無訂單紀錄" /> -->

    <!-- 模擬樣式方便設計，要串接資料庫 -->
    <div class="order-card">
      <div class="order-header">
        <span class="order-no">編號：ORD</span>
        <span class="order-date">111/4/1</span>
      </div>

      <el-divider />

      <div class="product-item">
        <div class="product-info">
          <img src="https://picsum.photos/seed/969/60/60" class="item-img" />
          <div class="item-detail">
            <p class="name">商品名</p>
            <p class="price">NT$ 123</p>
          </div>
        </div>
        <div class="item-qty">x 1</div>
      </div>

      <el-divider />

      <el-col :sm="24" class="text-right">
        <p class="total-label">總計金額</p>
        <p class="total-amount">NT$ 3000</p>
      </el-col>
      <el-divider />

      <el-row :gutter="20" class="order-footer">
        <el-col :sm="24">
          <div class="info-group">
            <h3>購買人資訊</h3>
            <p>購買人：</p>
            <p>電話：</p>
            <p>地址：桃園市楊梅區楊湖路三段197巷2號5樓</p>
          </div>
        </el-col>
        <el-col :sm="24">
          <div class="info-group">
            <h3>付款資訊</h3>
            <p>付款方式：</p>
            <p>發票類型：</p>
            <p>狀態：</p>
          </div>
        </el-col>

        <el-col :sm="24">
          <div class="onetime">
            <el-button type="success" size="large">再買一次</el-button>
          </div>
        </el-col>
      </el-row>
    </div>
  </div>
</template>
<style scoped>
.order-card {
  background: #fff;
  border: 1px solid #ddd;
  border-radius: 8px;
  padding: 20px;
  margin-bottom: 25px;
}

.order-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-weight: bold;
  color: #666;
}

.product-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 5px 0;
}

.product-info {
  display: flex;
  gap: 15px;
  align-items: center;
}

.item-img {
  width: 60px;
  height: 60px;
  border-radius: 4px;
  object-fit: cover;
}
.order-footer {
  display: flex;
  flex-direction: column;
}
.info-group {
  border: 1px solid #ddd;
  border-radius: 4px;
  margin: 10px 0;
  padding: 10px;
  display: flex;
  flex-direction: column;
  align-items: start;
  justify-content: center;
  p {
    margin: 5px 0;
    font-size: 0.9rem;
    color: #666;
  }
}
.text-right {
  text-align: right;
}

.total-amount {
  font-size: 1.4rem;
  color: #d51717;
  font-weight: bold;
}
.onetime {
  text-align: right;
}
</style>
