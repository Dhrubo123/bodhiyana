<script setup>
import { computed, onMounted, reactive, ref, watch } from 'vue'
import { useRoute } from 'vue-router'

const route = useRoute()
const menuOpen = ref(false)
const isAdmin = computed(() => route.path.startsWith('/admin'))
const events = ref([])
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
    const [response, eventsResponse] = await Promise.all([
      fetch(`/api/website-settings?updated=${Date.now()}`, { cache: 'no-store' }),
      fetch('/api/events', { cache: 'no-store' }),
    ])
    if (!response.ok) return
    Object.assign(site, await response.json())
    events.value = eventsResponse.ok ? await eventsResponse.json() : []
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

      <RouterLink class="mobile-donate" to="/donate" aria-label="এখন দান করুন">দান করুন</RouterLink>

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
    <section v-if="events.length" class="event-ticker" aria-label="চলমান অনুষ্ঠান">
      <div class="ticker-label"><span aria-hidden="true">☸</span><b>ধর্মীয় আয়োজন</b><small>Temple Events</small></div>
      <div class="ticker-window"><div class="ticker-track"><RouterLink v-for="(event, index) in [...events, ...events]" :key="`${event.id}-${index}`" :to="`/events/${event.id}`" :aria-label="`${event.title_bn} এর বিস্তারিত দেখুন`"><i aria-hidden="true">✦</i>{{ event.title_bn }} <small>{{ event.event_date }}{{ event.event_time ? ` · ${event.event_time.slice(0, 5)}` : '' }}{{ event.location ? ` · ${event.location}` : '' }}</small><em>বিস্তারিত →</em></RouterLink></div></div>
    </section>
  </template>

  <main><RouterView /></main>

  <a v-if="!isAdmin && whatsappUrl" class="floating-whatsapp" :href="whatsappUrl" target="_blank" rel="noopener noreferrer" aria-label="WhatsApp এ যোগাযোগ করুন" title="WhatsApp এ যোগাযোগ করুন">☏<span>WhatsApp</span></a>

  <footer v-if="!isAdmin">
    <span aria-hidden="true">☸</span>
    <p>সকলের মঙ্গল হোক।<br><small>© {{ new Date().getFullYear() }} {{ site.bihar_name }}</small></p>
    <p class="developer-credit">Developed By Aparup Barua</p>
  </footer>
</template>

<style scoped>
.event-ticker{display:flex;min-height:52px;align-items:stretch;overflow:hidden;border-bottom:1px solid #c38a3266;background:linear-gradient(90deg,#102f27,#1b4a3b 50%,#12382e);color:#fff8e9}.ticker-label{position:relative;z-index:1;display:grid;grid-template-columns:28px auto;grid-template-rows:1fr 1fr;align-items:center;column-gap:7px;min-width:182px;padding:7px max(4vw,18px);background:linear-gradient(135deg,#b46f1e,#d3973b);white-space:nowrap}.ticker-label::after{position:absolute;top:0;right:-15px;width:30px;height:100%;background:#d3973b;clip-path:polygon(0 0,50% 50%,0 100%);content:''}.ticker-label span{grid-row:1/3;font-size:25px}.ticker-label b{font-family:'Noto Serif Bengali',serif;font-size:13px}.ticker-label small{font-size:8px}.ticker-window{display:flex;min-width:0;flex:1;align-items:center;overflow:hidden;padding-left:20px}.ticker-track{display:flex;width:max-content;animation:ticker-scroll 34s linear infinite;white-space:nowrap}.ticker-track a{display:inline-flex;align-items:center;gap:8px;padding-right:74px;font-family:'Noto Serif Bengali',serif;font-size:14px;cursor:pointer}.ticker-track a:hover{color:#ffe0a0;text-decoration:underline}.ticker-track i{color:#e8b455;font-style:normal}.ticker-track small{color:#cfe0d5;font-family:'Noto Sans Bengali',sans-serif;font-size:11px}.ticker-track em{padding:3px 7px;border:1px solid #dba84f99;border-radius:999px;color:#ffd785;font-family:'Noto Sans Bengali',sans-serif;font-size:9px;font-style:normal}@keyframes ticker-scroll{to{transform:translateX(-50%)}}@media(max-width:760px){.event-ticker{min-height:48px}.ticker-label{min-width:135px;padding:6px 12px;grid-template-columns:23px auto}.ticker-label span{font-size:21px}.ticker-label b{font-size:11px}.ticker-label small{font-size:7px}.ticker-track{animation-duration:26s}.ticker-track a{padding-right:44px;font-size:12px}.ticker-track small{font-size:9px}.ticker-track em{font-size:8px}}
</style>
