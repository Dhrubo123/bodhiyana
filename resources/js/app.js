import { createApp } from 'vue'
import { createRouter, createWebHistory } from 'vue-router'
import App from './App.vue'
import HomeView from './views/HomeView.vue'
import DonateView from './views/DonateView.vue'
import StatusView from './views/StatusView.vue'
import AdminDashboardView from './views/admin/AdminDashboardView.vue'
import AdminDonationsView from './views/admin/AdminDonationsView.vue'
const router = createRouter({history:createWebHistory(),routes:[{path:'/',component:HomeView},{path:'/donate',component:DonateView},{path:'/donation-status',component:StatusView},{path:'/admin',component:AdminDashboardView},{path:'/admin/donations',component:AdminDonationsView}]})
createApp(App).use(router).mount('#app')
