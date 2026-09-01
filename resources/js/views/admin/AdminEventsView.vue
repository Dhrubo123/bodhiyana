<script setup>
import { onBeforeUnmount, onMounted, reactive, ref } from 'vue'
import AdminLayout from '../../components/admin/AdminLayout.vue'
import { adminFetch, adminJson } from '../../services/adminAuth'

const rows = ref([])
const editing = ref(null)
const error = ref('')
const saving = ref(false)
const imageFile = ref(null)
const imagePreview = ref(null)
const removeImage = ref(false)
const form = reactive({ title_bn: '', title_en: '', description: '', event_date: '', event_time: '', location: '', is_active: true })

async function load() { rows.value = await adminJson('/api/admin/events') }
function clearPreview() { if (imagePreview.value) URL.revokeObjectURL(imagePreview.value); imagePreview.value = null }
function reset() { clearPreview(); editing.value = null; imageFile.value = null; removeImage.value = false; error.value = ''; Object.assign(form, { title_bn: '', title_en: '', description: '', event_date: '', event_time: '', location: '', is_active: true }) }
function edit(item) { reset(); editing.value = item; Object.assign(form, { title_bn: item.title_bn, title_en: item.title_en || '', description: item.description || '', event_date: item.event_date?.slice(0, 10), event_time: item.event_time?.slice(0, 5) || '', location: item.location || '', is_active: item.is_active }); window.scrollTo({ top: 0, behavior: 'smooth' }) }
function chooseImage(event) { const file = event.target.files?.[0] ?? null; clearPreview(); imageFile.value = file; imagePreview.value = file ? URL.createObjectURL(file) : null; removeImage.value = false }
function deleteImage() { imageFile.value = null; clearPreview(); removeImage.value = true }
async function save() {
  error.value = ''; saving.value = true
  const data = new FormData()
  Object.entries(form).forEach(([key, value]) => data.append(key, typeof value === 'boolean' ? (value ? '1' : '0') : value ?? ''))
  if (editing.value) data.append('_method', 'PUT')
  if (imageFile.value) data.append('image', imageFile.value)
  if (removeImage.value) data.append('remove_image', '1')
  try {
    const response = await adminFetch(editing.value ? `/api/admin/events/${editing.value.id}` : '/api/admin/events', { method: 'POST', body: data })
    const json = await response.json()
    if (!response.ok) throw new Error(Object.values(json.errors ?? {}).flat()[0] ?? json.message)
    reset(); await load()
  } catch (exception) { error.value = exception.message } finally { saving.value = false }
}
async function remove(item) { if (confirm('ইভেন্টটি মুছে ফেলবেন?')) { await adminJson(`/api/admin/events/${item.id}`, { method: 'DELETE' }); await load() } }
onMounted(load)
onBeforeUnmount(clearPreview)
</script>

<template>
  <AdminLayout>
    <div class="admin-title"><div><p>বিহার ব্যবস্থাপনা</p><h1>ইভেন্ট</h1></div></div>
    <div class="management-grid">
      <section class="panel">
        <div class="panel-head"><h2>ইভেন্ট তালিকা</h2></div>
        <div class="record-list">
          <article v-for="item in rows" :key="item.id">
            <img v-if="item.image_url" :src="item.image_url" class="event-thumb" alt="ইভেন্ট পোস্টার">
            <div v-else class="date-badge">{{ new Date(item.event_date).toLocaleDateString('bn-BD', { day: '2-digit', month: 'short' }) }}</div>
            <div><b>{{ item.title_bn }}</b><small>{{ item.location || 'স্থান উল্লেখ নেই' }}</small></div>
            <span class="status-pill" :class="item.is_active ? 'confirmed' : 'rejected'">{{ item.is_active ? 'প্রকাশিত' : 'খসড়া' }}</span>
            <button @click="edit(item)">সম্পাদনা</button><button class="danger-text" @click="remove(item)">মুছুন</button>
          </article>
          <div v-if="!rows.length" class="empty compact">কোনো ইভেন্ট নেই।</div>
        </div>
      </section>
      <section class="panel sticky-panel">
        <div class="panel-head"><div><h2>{{ editing ? 'ইভেন্ট সম্পাদনা' : 'নতুন ইভেন্ট' }}</h2><p>পোস্টার ও বিস্তারিত যুক্ত করলে টিকারে ক্লিক করে দর্শনার্থীরা দেখতে পারবেন।</p></div></div>
        <form class="management-form" @submit.prevent="save">
          <label>বাংলা শিরোনাম<input v-model="form.title_bn" required></label><label>ইংরেজি শিরোনাম<input v-model="form.title_en"></label>
          <div class="two-inputs"><label>তারিখ<input v-model="form.event_date" type="date" required></label><label>সময়<input v-model="form.event_time" type="time"></label></div>
          <label>স্থান<input v-model="form.location"></label><label>বিস্তারিত বিবরণ<textarea v-model="form.description" rows="5" placeholder="যেমন: কঠিন চীবর দান অনুষ্ঠান, সময়সূচি ও অংশগ্রহণের তথ্য"></textarea></label>
          <label class="event-poster">পোস্টার / অনুষ্ঠান ছবি<input type="file" accept="image/png,image/jpeg,image/webp" @change="chooseImage"><img v-if="imagePreview || (!removeImage && editing?.image_url)" :src="imagePreview || editing.image_url" alt="পোস্টার প্রিভিউ"><small>JPG, PNG বা WebP · সর্বোচ্চ 5 MB</small><button v-if="imagePreview || editing?.image_url" type="button" class="danger-text" @click="deleteImage">পোস্টার সরান</button></label>
          <label class="toggle-label"><input v-model="form.is_active" type="checkbox"> প্রকাশ করুন</label><p v-if="error" class="login-error">{{ error }}</p><button class="admin-button" :disabled="saving">{{ saving ? 'সংরক্ষণ হচ্ছে…' : 'সংরক্ষণ করুন' }}</button><button v-if="editing" type="button" class="secondary-button" @click="reset">বাতিল</button>
        </form>
      </section>
    </div>
  </AdminLayout>
</template>

<style scoped>
.event-thumb{width:58px;height:48px;object-fit:cover;border-radius:7px}.event-poster{padding:13px;border:1px dashed #cbd7cf;border-radius:8px;background:#f8faf7}.event-poster>small{display:block;margin-top:7px;color:#849087;font-size:10px}.event-poster img{display:block;width:100%;height:180px;margin-top:10px;object-fit:contain;border-radius:6px;background:#fff}.event-poster button{display:block;margin-top:8px;padding:0;border:0;background:none;font:inherit;font-size:10px;cursor:pointer}
</style>
