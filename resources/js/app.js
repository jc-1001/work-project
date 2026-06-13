import './bootstrap'
import { createApp } from 'vue'
import 'vuetify/styles'
import '@mdi/font/css/materialdesignicons.css'
import { createVuetify } from 'vuetify'
import './style.css'

import Home from './views/front/Home.vue'
import ShopIndex from './views/front/shop/Shop.vue'
import ShopShow from './views/front/shop/ProductDetail.vue'
import Login from './views/front/Login.vue'
import Register from './views/front/Register.vue'
import AdminLogin from './views/admin/adminLogin.vue'
import AdminComplaintsIndex from './views/admin/ComplaintIndex.vue'
import AdminComplaintShow from './views/admin/ComplaintDetail.vue'

// prettier-ignore
const pageMap = {
    'home':        Home,
    'shop-index':  ShopIndex,
    'shop-show':   ShopShow,
    'login':       Login,
    'register':    Register,
    'admin-login': AdminLogin,
    'admin-complaints-index': AdminComplaintsIndex,
    'admin-complaints-show':  AdminComplaintShow,
}

const vuetify = createVuetify()

const el = document.getElementById('app')
const page = el?.dataset?.page
const Component = pageMap[page]

if (Component) {
    createApp(Component).use(vuetify).mount('#app')
}
