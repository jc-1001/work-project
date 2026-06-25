# Work Shop — 全端電商購物平台

<p align="center">
  <img src="docs/cover.jpg" alt="Work Shop 封面" width="100%">
</p>

> 一套完整的電商購物系統，涵蓋前台購物流程與後台管理功能，整合 ECPay 綠界金流，以 Docker 容器化一鍵部署。

**開發期間：** 2026.4.2 — 2026.6.17

---

## 目錄

- [功能介紹](#功能介紹)
- [技術棧](#技術棧)
- [系統架構](#系統架構)
- [環境需求](#環境需求)
- [啟動方式](#啟動方式)
- [環境變數說明](#環境變數說明)
- [資料庫結構](#資料庫結構)
- [專案結構](#專案結構)

---

## 功能介紹

### 前台（購物者）

| 功能     | 說明                                          |
| -------- | --------------------------------------------- |
| 商品瀏覽 | 分類篩選、關鍵字搜尋、商品詳情頁              |
| 購物車   | 以 localStorage 儲存，跨頁保留                |
| 結帳流程 | 填寫收件資訊、套用優惠券、計算運費            |
| 金流支付 | 整合 ECPay 綠界第三方支付                     |
| 評論系統 | 購買後可留評、上傳圖片、點贊 / 點踩、投訴機制 |
| 會員中心 | 個人資料編輯、訂單歷史、收藏清單、瀏覽記錄    |
| 密碼重置 | Email 驗證流程，寄送重置連結                  |

### 後台（管理者）

| 功能       | 說明                                               |
| ---------- | -------------------------------------------------- |
| 儀表板     | 銷售統計圖表（折線 / 長條 / 甜甜圈）               |
| 商品管理   | 新增 / 編輯 / 下架，支援多張圖片上傳               |
| 訂單管理   | 訂單狀態流轉（待確認 → 出貨 → 完成 / 退貨 / 取消） |
| 用戶管理   | 查看用戶資訊、啟用 / 停用帳號                      |
| 優惠券管理 | 建立折扣碼、設定金額或百分比折扣、有效期限         |
| 廣告管理   | 首頁廣告橫幅上傳與排程顯示                         |
| 客服回覆   | 接收聯繫訊息、直接以 Email 回覆客戶                |
| 投訴管理   | 處理評論投訴，決定是否下架評論                     |
| 管理員管理 | 建立 / 停用管理員（限 Super Admin）                |

---

## 技術棧

### 後端

| 技術                   | 版本   | 用途                                                    |
| ---------------------- | ------ | ------------------------------------------------------- |
| **PHP**                | 8.2    | 執行環境                                                |
| **Laravel**            | 10.x   | 後端框架、MVC、ORM、郵件、隊列                          |
| **Laravel Sanctum**    | —      | API 身份驗證、Session Token 管理                        |
| **MySQL / SQL Server** | —      | 關聯式資料庫（本地用 MySQL，Docker 用 SQL Server 2022） |
| **Redis**              | Alpine | 快取與隊列驅動                                          |
| **Guzzle**             | —      | 串接 ECPay 金流的 HTTP 請求客戶端                       |

### 前端

| 技術                      | 版本   | 用途                                            |
| ------------------------- | ------ | ----------------------------------------------- |
| **Vue 3**                 | 3.5.30 | 前端框架（Composition API）                     |
| **Vue Router**            | 5.0.4  | SPA 路由管理                                    |
| **Vuetify**               | 4.0.1  | Material Design UI 元件庫                       |
| **Axios**                 | 1.14.0 | HTTP 請求，含攔截器（自動處理 401 / 422 / 500） |
| **Chart.js**              | 4.5.1  | 後台統計圖表                                    |
| **GSAP**                  | 3.15.0 | 動畫效果                                        |
| **Lenis**                 | 1.3.23 | 平滑滾動體驗                                    |
| **Material Design Icons** | —      | 圖示庫                                          |

### 建置 & 部署

| 技術                    | 版本  | 用途                                       |
| ----------------------- | ----- | ------------------------------------------ |
| **Vite**                | 6.0.0 | 前端打包器、HMR 熱更新開發體驗             |
| **Laravel Vite Plugin** | 1.0.0 | Vite 與 Laravel Blade 整合                 |
| **Docker**              | —     | 容器化應用部署                             |
| **Docker Compose**      | —     | 多服務編排（應用 / 資料庫 / Redis / 郵件） |
| **Nginx**               | —     | 反向代理 Web 伺服器                        |
| **PHP-FPM**             | 8.2   | PHP 進程管理                               |
| **Supervisor**          | —     | Queue Worker 背景進程守護                  |
| **Mailpit**             | —     | 本地開發郵件攔截測試介面                   |

### 第三方整合

| 服務                  | 說明                       |
| --------------------- | -------------------------- |
| **ECPay 綠界金流**    | 線上支付，使用測試沙盒環境 |
| **MSSQL ODBC Driver** | Docker 環境連接 SQL Server |

---

## 系統架構

```
瀏覽器
  │
  ▼
Nginx（port 8999）
  │
  ├── 靜態資源（Vite 打包後的 JS / CSS）
  │
  └── PHP-FPM（Laravel）
        │
        ├── Blade 模板渲染 + Vue 3 SPA
        ├── API 路由（Sanctum Token 認證）
        │
        ├── MySQL / SQL Server 2022
        ├── Redis（快取 / 隊列）
        └── Queue Worker（Supervisor 管理）
              └── 郵件發送（Mailpit / SMTP）
```

### Docker Compose 服務

| 服務      | Image                               | 對外 Port                |
| --------- | ----------------------------------- | ------------------------ |
| `shop`    | 自訂（PHP 8.2-FPM + Nginx）         | 8999                     |
| `mssql`   | mcr.microsoft.com/mssql/server:2022 | 1434                     |
| `redis`   | redis:alpine                        | —                        |
| `mailpit` | axllent/mailpit                     | 8025（UI）/ 1025（SMTP） |

---

## 環境需求

### Docker 方式（推薦）

- [Docker Desktop](https://www.docker.com/products/docker-desktop/) 4.x+
- Docker Compose V2

### 本地開發方式

- PHP 8.1+（含 `pdo_mysql` 或 `sqlsrv` / `pdo_sqlsrv` 擴充）
- Composer 2.x
- Node.js 18+ & npm 9+
- MySQL 8.x 或 SQL Server 2022

---

## 啟動方式

### 方法一：Docker（推薦）

```bash
# 1. 複製環境設定檔
cp .env.example .env

# 2. 依需求修改 .env 中的資料庫密碼與 APP_KEY

# 3. 啟動所有容器
docker compose up -d

# 4. 建置前端資源並同步至容器
npm run build:docker

# 5. 執行資料庫遷移與測試資料
docker compose exec shop php artisan migrate --seed

# 6. 建立 Storage 符號連結（容器啟動時會自動執行）
docker compose exec shop php artisan storage:link
```

開啟瀏覽器前往 **http://localhost:8999**

> **Mailpit 郵件測試介面：** http://localhost:8025

---

### 方法二：本地開發

```bash
# 1. 安裝 PHP 依賴
composer install

# 2. 安裝 Node.js 依賴
npm install

# 3. 複製並設定環境變數
cp .env.example .env
php artisan key:generate

# 4. 執行資料庫遷移與測試資料
php artisan migrate --seed

# 5. 建立 Storage 符號連結
php artisan storage:link

# 6. 同時啟動前後端開發伺服器（需開兩個終端）
php artisan serve       # 後端：http://localhost:8000
npm run dev             # 前端 HMR：http://localhost:5173
```

---

## 環境變數說明

複製 `.env.example` 為 `.env` 後，調整以下重要設定：

```env
# 應用程式
APP_URL=http://localhost:8999
VITE_API_URL=http://localhost:8999

# 資料庫（本地開發使用 MySQL）
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_db_name
DB_USERNAME=root
DB_PASSWORD=your_password

# 資料庫（Docker 使用 SQL Server，取消下方註解）
# DB_CONNECTION=sqlsrv
# MSSQL_SA_PASSWORD=your_password

# Redis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379

# 郵件（本地使用 Mailpit）
MAIL_MAILER=smtp
MAIL_HOST=localhost
MAIL_PORT=1025

# 運費設定
SHIPPING_FEE=60

# ECPay 綠界金流（測試環境）
ECPAY_MERCHANT_ID=2000132
ECPAY_HASH_KEY=your_hash_key
ECPAY_HASH_IV=your_hash_iv
```

---

## 資料庫結構

主要資料表共 **23 張**：

```
用戶與權限        users / roles / role_user
商品系統         products / categories / product_images
訂單系統         orders / order_items / carts
優惠系統         coupons / coupon_usages
評論系統         reviews / review_images / review_votes
客服與投訴       contact_messages / complaints
廣告             advertisements
其他             password_reset_tokens / failed_jobs / personal_access_tokens
```

**角色權限：**

- `user` — 一般會員
- `admin` — 後台管理員
- `super_admin` — 最高權限，可管理其他管理員

---

## 專案結構

```
work-project/
├── app/
│   ├── Http/
│   │   ├── Controllers/     # 12 個 Controller（前台 + 後台 + 金流）
│   │   └── Middleware/      # 認證 / 管理員 / 超級管理員
│   ├── Models/              # 16 個 Model
│   ├── Mail/                # 訂單通知 / 客服回覆 / 密碼重置
│   └── Services/
│       └── EcpayService.php # 綠界金流串接服務
├── resources/
│   ├── js/
│   │   ├── views/
│   │   │   ├── front/       # 前台 Vue 頁面
│   │   │   └── admin/       # 後台 Vue 頁面
│   │   ├── components/      # 共用元件
│   │   ├── composables/     # useAuth / useCart / useFavorites ...
│   │   └── router/          # Vue Router 設定
│   ├── views/               # Blade 模板（前台 / 後台 / 郵件）
│   └── css/
├── routes/
│   ├── web.php              # 頁面路由 + API 路由
│   └── api.php
├── database/
│   ├── migrations/          # 23 張資料表遷移
│   └── seeders/
├── .config/
│   ├── nginx/               # Nginx 設定
│   ├── php/                 # PHP 生產設定
│   └── supervisor/          # Queue Worker 守護進程設定
├── docs/
│   └── 我的商城封面.jpg       # README 封面圖
├── public/
├── docker-compose.yml
├── Dockerfile
└── vite.config.js
```

---

## 安全機制

- **CSRF 保護** — Laravel 內建 CSRF Token 防護
- **身份驗證** — Laravel Sanctum Session Token
- **角色權限控制** — 自訂 Middleware（`EnsureIsAdmin` / `EnsureIsSuperAdmin`）
- **API 限流** — Throttle 中介層（3–5 次 / 分鐘）
- **密碼加密** — Laravel Hashing（bcrypt）
- **未授權自動重導** — Axios 攔截器偵測 401 後跳轉登入頁

---

_Built with Laravel 10 + Vue 3 + Vuetify 4 + Docker_

---
