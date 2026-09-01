<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRoute } from 'vue-router'

const route = useRoute()
const event = ref(null)
const loading = ref(true)
const missing = ref(false)
const donationLink = computed(() => event.value ? { path: '/donate', query: { event: event.value.id, event_title: event.value.title_bn } } : '/donate')
const dateText = computed(() => event.value ? new Intl.DateTimeFormat('bn-BD', { dateStyle: 'full' }).format(new Date(event.value.event_date)) : '')

onMounted(async () => {
  try {
    const response = await fetch(`/api/events/${route.params.id}`)
    if (!response.ok) throw new Error('not-found')
    event.value = await response.json()
  } catch { missing.value = true } finally { loading.value = false }
})
</script>

<template>
  <section v-if="loading" class="event-state">ইভেন্টের তথ্য লোড হচ্ছে…</section>
  <section v-else-if="missing" class="event-state"><h1>ইভেন্টটি পাওয়া যায়নি</h1><p>এই আয়োজনটি হয়তো আর প্রকাশিত নেই।</p><RouterLink to="/" class="button primary">হোমপেজে ফিরুন</RouterLink></section>
  <article v-else class="event-page">
    <div class="event-poster-wrap"><img v-if="event.image_url" :src="event.image_url" :alt="event.title_bn"><div v-else class="poster-placeholder">☸</div></div>
    <div class="event-details">
      <p class="eyebrow">ধর্মীয় আয়োজন</p>
      <h1>{{ event.title_bn }}</h1>
      <p v-if="event.title_en" class="event-english">{{ event.title_en }}</p>
      <dl><div><dt>তারিখ</dt><dd>{{ dateText }}</dd></div><div v-if="event.event_time"><dt>সময়</dt><dd>{{ event.event_time.slice(0, 5) }}</dd></div><div v-if="event.location"><dt>স্থান</dt><dd>{{ event.location }}</dd></div></dl>
      <div v-if="event.description" class="event-description">{{ event.description }}</div>
      <div class="donation-call"><div><b>এই পুণ্যময় আয়োজনে অংশ নিন</b><small>আপনার দান এই অনুষ্ঠান সফল করতে সহায়তা করবে।</small></div><RouterLink :to="donationLink" class="button primary">🙏 দান করুন</RouterLink></div>
    </div>
  </article>
</template>

<style scoped>
.event-page{display:grid;grid-template-columns:minmax(0,1fr) minmax(340px,.9fr);gap:clamp(26px,5vw,74px);max-width:1200px;margin:0 auto;padding:clamp(36px,7vw,92px) max(6vw,20px)}.event-poster-wrap{align-self:start;overflow:hidden;border-radius:18px;background:#eef2eb;box-shadow:0 18px 45px #183d2e1c}.event-poster-wrap img{display:block;width:100%;max-height:720px;object-fit:contain;background:#f5f3ec}.poster-placeholder{display:grid;min-height:360px;place-items:center;background:linear-gradient(135deg,#153d31,#326449);color:#ddb664;font-size:120px}.event-details h1{margin:0;color:#204536;font-family:'Noto Serif Bengali',serif;font-size:clamp(34px,4vw,52px);line-height:1.3}.event-english{margin:9px 0 25px;color:#728178}.event-details dl{display:flex;flex-wrap:wrap;gap:10px;margin:26px 0}.event-details dl div{min-width:150px;padding:12px 15px;border:1px solid #e3e9e1;border-radius:9px;background:#fbfcf9}.event-details dt{color:#88968d;font-size:11px}.event-details dd{margin:4px 0 0;color:#294d3d;font-family:'Noto Serif Bengali',serif;font-size:15px}.event-description{white-space:pre-line;color:#53665b;line-height:1.9}.donation-call{display:flex;align-items:center;justify-content:space-between;gap:18px;margin-top:30px;padding:20px;border:1px solid #edd7a4;border-radius:12px;background:#fff9eb}.donation-call b,.donation-call small{display:block}.donation-call b{color:#654913;font-family:'Noto Serif Bengali',serif;font-size:17px}.donation-call small{margin-top:5px;color:#81745a;font-size:12px;line-height:1.5}.event-state{max-width:700px;margin:80px auto;padding:30px;text-align:center}.event-state h1{font-family:'Noto Serif Bengali',serif;color:#244838}@media(max-width:760px){.event-page{grid-template-columns:1fr;gap:24px;padding:32px 20px 54px}.event-poster-wrap{border-radius:13px}.event-poster-wrap img{max-height:none}.poster-placeholder{min-height:230px;font-size:80px}.event-details h1{font-size:34px}.donation-call{align-items:stretch;flex-direction:column}.donation-call .button{display:flex;min-height:48px;align-items:center;justify-content:center}.event-details dl{display:grid;grid-template-columns:1fr 1fr}.event-details dl div:last-child{grid-column:1/-1}}
</style>
