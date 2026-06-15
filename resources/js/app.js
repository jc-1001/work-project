import "./bootstrap";
import { createApp } from "vue";
import "vuetify/styles";
import "@mdi/font/css/materialdesignicons.css";
import { createVuetify } from "vuetify";
import "./style.css";

import Home                from "./views/front/Home.vue";
import ShopIndex           from "./views/front/shop/Shop.vue";
import ShopShow            from "./views/front/shop/ProductDetail.vue";
import Login               from "./views/front/Login.vue";
import Register            from "./views/front/Register.vue";
import AdminLogin          from "./views/admin/adminLogin.vue";
import AdminProducts       from "./views/admin/products.vue";
import AdminProductShow    from "./views/admin/productDetail.vue";
import AdminProductNew     from "./views/admin/productNew.vue";
import AdminUsersIndex     from "./views/admin/user.vue";
import AdminUsersShow      from "./views/admin/userDetail.vue";
import AdminOrdersIndex    from "./views/admin/orders.vue";
import AdminOrdersShow     from "./views/admin/orderDetail.vue";
import Advertisements      from "./views/admin/Ad/AdList.vue";
import AdvertisementDetail from "./views/admin/Ad/AdDetail.vue";

// prettier-ignore
const pageMap = {
    'home':        Home,
    'shop-index':  ShopIndex,
    'shop-show':   ShopShow,
    'login':            Login,
    'register':         Register,
    'admin-login':      AdminLogin,
    'admin-products':   AdminProducts,
    'admin-products-show': AdminProductShow,
    'admin-products-store': AdminProductNew,
    'login':       Login,
    'register':    Register,
    'admin-login': AdminLogin,
    'admin-user-index':            AdminUsersIndex,
    'admin-user-show':             AdminUsersShow,
    'admin-orders-index':          AdminOrdersIndex,
    'admin-orders-show':           AdminOrdersShow,
    'admin-advertisements':        Advertisements,
    'admin-advertisements-detail': AdvertisementDetail,
}

const vuetify = createVuetify();

const el = document.getElementById("app");
const page = el?.dataset?.page;
const Component = pageMap[page];

if (Component) {
    createApp(Component).use(vuetify).mount("#app");
}
