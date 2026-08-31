<script setup>
import { computed, onMounted, reactive, ref, watch } from 'vue'
import { useRoute } from 'vue-router'

const route = useRoute()
const menuOpen = ref(false)
const isAdmin = computed(() => route.path.startsWith('/admin'))
const site = reactive({
  bihar_name: 'আপনার বৌদ্ধ বিহার',
  site_title: 'বৌদ্ধ বিহার | দান ব্যবস্থাপনা',
  logo_url: null,
  favicon_url: null,
  whatsapp_number: null,
})
const whatsappUrl = computed(() => {
  const number = String(site.whatsapp_number ?? '').replace(/\D/g, '')
  return number ? `https://wa.me/${number}` : null
})

async function refreshSiteIdentity() {
  try {
    const response = await fetch(`/api/website-settings?updated=${Date.now()}`, { cache: 'no-store' })
    if (!response.ok) return
    Object.assign(site, await response.json())
    document.title = site.site_title || site.bihar_name

    if (site.favicon_url) {
      let favicon = document.querySelector("link[rel~='icon']")
      if (!favicon) {
        favicon = document.createElement('link')
        favicon.rel = 'icon'
        document.head.appendChild(favicon)
      }
      favicon.href = site.favicon_url
    }
  } catch {
    // Keep the built-in identity if public settings are temporarily unavailable.
  }
}

onMounted(() => {
  if (!isAdmin.value) refreshSiteIdentity()
})

watch(isAdmin, (adminArea, wasAdminArea) => {
  if (!adminArea && wasAdminArea) refreshSiteIdentity()
})

watch(() => route.fullPath, () => {
  menuOpen.value = false
})
</script>

<template>
  <template v-if="!isAdmin">
    <header class="site-header">
      <RouterLink class="brand" to="/" aria-label="বৌদ্ধ বিহার হোমপেজ">
        <img v-if="site.logo_url" :src="site.logo_url" :alt="`${site.bihar_name} logo`">
        <span v-else aria-hidden="true">☸</span>
        <div>
          <b>{{ site.bihar_name }}</b>
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

  <a v-if="!isAdmin && whatsappUrl" class="floating-whatsapp" :href="whatsappUrl" target="_blank" rel="noopener noreferrer" aria-label="WhatsApp এ যোগাযোগ করুন" title="WhatsApp এ যোগাযোগ করুন">☏<span>WhatsApp</span></a>

  <footer v-if="!isAdmin">
    <span aria-hidden="true">☸</span>
    <p>সকলের মঙ্গল হোক।<br><small>© {{ new Date().getFullYear() }} {{ site.bihar_name }}</small></p>
    <p class="developer-credit">Developed By Aparup Barua</p>
  </footer>
</template>
