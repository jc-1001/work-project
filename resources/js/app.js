import "./bootstrap";
import { createApp } from "vue";
import "vuetify/styles";
import "@mdi/font/css/materialdesignicons.css";
import { createVuetify } from "vuetify";
import "./style.css";

import Home                      from "./views/front/Home.vue";
import ShopIndex                 from "./views/front/shop/Shop.vue";
import ShopShow                  from "./views/front/shop/ProductDetail.vue";
import Login                     from "./views/front/Login.vue";
import Register                  from "./views/front/Register.vue";
import AdminLogin                from "./views/admin/adminLogin.vue";
import AdminProducts             from "./views/admin/products.vue";
import AdminProductShow          from "./views/admin/productDetail.vue";
import AdminProductNew           from "./views/admin/productNew.vue";
import AdminUsersIndex           from "./views/admin/user.vue";
import AdminUsersShow            from "./views/admin/userDetail.vue";
import AdminOrdersIndex          from "./views/admin/orders.vue";
import AdminOrdersShow           from "./views/admin/orderDetail.vue";
import Advertisements            from "./views/admin/Ad/AdList.vue";
import AdvertisementDetail       from "./views/admin/Ad/AdDetail.vue";
import AdminForbidden            from "./views/admin/AdminForbidden.vue";
import AdminAdministratorsIndex  from "./views/admin/admin.vue";
import AdminAdministratorsShow   from "./views/admin/adminDetail.vue";
import ShopOrder                 from "./views/front/shop/ShopOrder.vue";
import CouponList                from "./views/admin/coupon/CouponList.vue";
import CouponDetail              from "./views/admin/coupon/CouponDetail.vue";
import AdminDashboard            from "./views/admin/Dashboard.vue";
import CustomerServiceList       from "./views/admin/Reply/CustomerServiceList.vue";
import ReplyCustomer             from "./views/admin/Reply/ReplyCustomer.vue";
import AdminComplaintsIndex      from "./views/admin/ComplaintIndex.vue";
import AdminComplaintShow        from "./views/admin/ComplaintDetail.vue";
import Profile                   from "./views/front/profile/Profile.vue";
import ProfileOrder              from "./views/front/profile/ProfileOrder.vue";
import ShopCart                  from "./views/front/shop/ShopCart.vue";

// prettier-ignore
const pageMap = {
    'home':                        Home,
    'shop-index':                  ShopIndex,
    'shop-show':                   ShopShow,
    'login':                       Login,
    'register':                    Register,
    'admin-login':                 AdminLogin,
    'admin-products':              AdminProducts,
    'admin-products-show':         AdminProductShow,
    'admin-products-store':        AdminProductNew,
    'admin-user-index':            AdminUsersIndex,
    'admin-user-show':             AdminUsersShow,
    'admin-orders-index':          AdminOrdersIndex,
    'admin-orders-show':           AdminOrdersShow,
    'admin-advertisements':        Advertisements,
    'admin-advertisements-detail': AdvertisementDetail,
    'admin-forbidden':             AdminForbidden,
    'admin-administrators-index':  AdminAdministratorsIndex,
    'admin-administrators-show':   AdminAdministratorsShow,
    'shop-order':                  ShopOrder,
    'coupon-list':                 CouponList,
    'coupon-detail':               CouponDetail,
    'admin-dashboard':             AdminDashboard,
    'customer-service-index':      CustomerServiceList,
    'customer-service-reply':      ReplyCustomer,
    'admin-complaints-index':      AdminComplaintsIndex,
    'admin-complaints-show':       AdminComplaintShow,
    'profile':                     Profile,
    'profile-order':               ProfileOrder,
    'shop-cart':                   ShopCart,
}

const vuetify = createVuetify();

const el = document.getElementById("app");
const page = el?.dataset?.page;
const Component = pageMap[page];

if (Component) {
    createApp(Component).use(vuetify).mount("#app");
}
