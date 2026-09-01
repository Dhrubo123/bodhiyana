import { createApp } from 'vue'
import { createRouter, createWebHistory } from 'vue-router'
import App from './App.vue'
import HomeView from './views/HomeView.vue'
import EventDetailView from './views/EventDetailView.vue'
import DonateView from './views/DonateView.vue'
import StatusView from './views/StatusView.vue'
import AdminDashboardView from './views/admin/AdminDashboardView.vue'
import AdminDonationsView from './views/admin/AdminDonationsView.vue'
import AdminLoginView from './views/admin/AdminLoginView.vue'
import AdminDonorsView from './views/admin/AdminDonorsView.vue'
import AdminPurposesView from './views/admin/AdminPurposesView.vue'
import AdminReceiptsView from './views/admin/AdminReceiptsView.vue'
import AdminEventsView from './views/admin/AdminEventsView.vue'
import AdminBannersView from './views/admin/AdminBannersView.vue'
import AdminGalleryView from './views/admin/AdminGalleryView.vue'
import AdminWebsiteView from './views/admin/AdminWebsiteView.vue'
import AdminDonationSettingsView from './views/admin/AdminDonationSettingsView.vue'
import AdminReportsView from './views/admin/AdminReportsView.vue'
import { ensureAdminAuth } from './services/adminAuth'

const router = createRouter({history:createWebHistory(),routes:[
  {path:'/',component:HomeView},
  {path:'/events/:id',component:EventDetailView},
  {path:'/donate',component:DonateView},
  {path:'/donation-status',component:StatusView},
  {path:'/admin/login',component:AdminLoginView,meta:{guestOnly:true}},
  {path:'/admin',component:AdminDashboardView,meta:{requiresAdmin:true}},
  {path:'/admin/donations',component:AdminDonationsView,meta:{requiresAdmin:true}},
  {path:'/admin/donors',component:AdminDonorsView,meta:{requiresAdmin:true}},
  {path:'/admin/purposes',component:AdminPurposesView,meta:{requiresAdmin:true}},
  {path:'/admin/receipts',component:AdminReceiptsView,meta:{requiresAdmin:true}},
  {path:'/admin/events',component:AdminEventsView,meta:{requiresAdmin:true}},
  {path:'/admin/banners',component:AdminBannersView,meta:{requiresAdmin:true}},
  {path:'/admin/gallery',component:AdminGalleryView,meta:{requiresAdmin:true}},
  {path:'/admin/website',component:AdminWebsiteView,meta:{requiresAdmin:true}},
  {path:'/admin/donation-settings',component:AdminDonationSettingsView,meta:{requiresAdmin:true}},
  {path:'/admin/reports',component:AdminReportsView,meta:{requiresAdmin:true}},
]})

router.beforeEach(async (to) => {
  if (!to.path.startsWith('/admin')) return true
  const authenticated = await ensureAdminAuth()
  if (to.meta.requiresAdmin && !authenticated) return {path:'/admin/login',query:{redirect:to.fullPath}}
  if (to.meta.guestOnly && authenticated) return '/admin'
  return true
})
createApp(App).use(router).mount('#app')
