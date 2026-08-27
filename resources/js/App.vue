<script setup>
import { computed, ref, watch } from 'vue'
import { useRoute } from 'vue-router'

const route = useRoute()
const menuOpen = ref(false)
const isAdmin = computed(() => route.path.startsWith('/admin'))

watch(() => route.fullPath, () => {
  menuOpen.value = false
})
</script>

<template>
  <template v-if="!isAdmin">
    <header class="site-header">
      <RouterLink class="brand" to="/" aria-label="বৌদ্ধ বিহার হোমপেজ">
        <span aria-hidden="true">☸</span>
        <div>
          <b>আপনার বৌদ্ধ বিহার</b>
          <small>Buddhist Bihar</small>
        </div>
      </RouterLink>

      <button
        class="menu"
        type="button"
        :aria-expanded="menuOpen"
        aria-controls="public-navigation"
        aria-label="নেভিগেশন মেনু"
        @click="menuOpen = !menuOpen"
      >
        {{ menuOpen ? '✕' : '☰' }}
      </button>

      <nav id="public-navigation" :class="{ open: menuOpen }">
        <RouterLink to="/">নীড়</RouterLink>
        <a href="/#about" @click="menuOpen = false">বিহার সম্পর্কে</a>
        <a href="/#activities" @click="menuOpen = false">কার্যক্রম</a>
        <a href="/#contact" @click="menuOpen = false">যোগাযোগ</a>
        <RouterLink class="status-link" to="/donation-status">দান অবস্থা</RouterLink>
        <RouterLink class="donate-link" to="/donate">🙏 দান করুন</RouterLink>
      </nav>
    </header>
  </template>

  <main><RouterView /></main>

  <footer v-if="!isAdmin">
    <span aria-hidden="true">☸</span>
    <p>সকলের মঙ্গল হোক।<br><small>© {{ new Date().getFullYear() }} আপনার বৌদ্ধ বিহার</small></p>
  </footer>
</template>
