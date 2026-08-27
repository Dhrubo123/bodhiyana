<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { adminAuth, adminLogout } from '../../services/adminAuth'

const router = useRouter()
const open = ref(false)
const loggingOut = ref(false)

const navigation = [
  {
    label: 'প্রধান',
    links: [
      { icon: '▦', label: 'ড্যাশবোর্ড', to: '/admin' },
    ],
  },
  {
    label: 'দান ব্যবস্থাপনা',
    links: [
      { icon: '◷', label: 'অনুদানসমূহ', to: '/admin/donations' },
      { icon: '◉', label: 'দাতাগণ', to: '/admin/donors' },
      { icon: '◇', label: 'দানের উদ্দেশ্য', to: '/admin/purposes' },
      { icon: '▤', label: 'রসিদ', to: '/admin/receipts' },
    ],
  },
  {
    label: 'বিহার ব্যবস্থাপনা',
    links: [
      { icon: '◫', label: 'ইভেন্ট', to: '/admin/events' },
      { icon: '⚙', label: 'ওয়েবসাইট', to: '/admin/website' },
      { icon: '⌁', label: 'দান সেটিংস', to: '/admin/donation-settings' },
      { icon: '▱', label: 'রিপোর্ট', to: '/admin/reports' },
    ],
  },
]

async function logout() {
  loggingOut.value = true
  await adminLogout()
  await router.replace('/admin/login')
}
</script>

<template>
  <div class="admin-shell">
    <button v-if="open" class="admin-backdrop" aria-label="মেনু বন্ধ করুন" @click="open = false"></button>

    <aside class="admin-sidebar" :class="{ open }">
      <div class="admin-brand">
        <span class="admin-brand-mark" aria-hidden="true">☸</span>
        <div class="admin-brand-copy">
          <b>Bihar Admin</b>
          <small>Donation management</small>
        </div>
        <button class="sidebar-close" aria-label="মেনু বন্ধ করুন" @click="open = false">✕</button>
      </div>

      <nav class="admin-navigation" aria-label="অ্যাডমিন নেভিগেশন">
        <section v-for="group in navigation" :key="group.label" class="admin-nav-group">
          <p>{{ group.label }}</p>
          <template v-for="link in group.links" :key="link.label">
            <RouterLink v-if="link.to" :to="link.to" class="admin-nav-link" @click="open = false">
              <span class="admin-nav-icon" aria-hidden="true">{{ link.icon }}</span>
              <span>{{ link.label }}</span>
            </RouterLink>
            <button v-else class="admin-nav-link is-disabled" type="button" title="শীঘ্রই আসছে">
              <span class="admin-nav-icon" aria-hidden="true">{{ link.icon }}</span>
              <span>{{ link.label }}</span>
              <small>শীঘ্রই</small>
            </button>
          </template>
        </section>
      </nav>

      <div class="admin-user">
        <span class="admin-avatar">{{ adminAuth.user?.name?.charAt(0) || 'অ' }}</span>
        <div class="admin-user-copy">
          <b>{{ adminAuth.user?.name }}</b>
          <small>{{ adminAuth.user?.email }}</small>
        </div>
      </div>
    </aside>

    <div class="admin-main">
      <header>
        <button class="admin-menu" aria-label="সাইডবার খুলুন" @click="open = !open">☰</button>
        <div class="admin-header-meta"><small>আজকের তারিখ</small><b>{{ new Date().toLocaleDateString('bn-BD') }}</b></div>
        <button class="admin-logout" :disabled="loggingOut" @click="logout">{{ loggingOut ? 'অপেক্ষা…' : 'লগআউট' }}</button>
      </header>
      <section class="admin-content"><slot /></section>
    </div>
  </div>
</template>
