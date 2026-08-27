<script setup>
import { onBeforeUnmount, onMounted, reactive, ref } from 'vue'
import AdminLayout from '../../components/admin/AdminLayout.vue'
import { adminFetch, adminJson } from '../../services/adminAuth'

const rows = ref([])
const editing = ref(null)
const error = ref('')
const notice = ref('')
const saving = ref(false)
const desktopFile = ref(null)
const mobileFile = ref(null)
const desktopPreview = ref(null)
const mobilePreview = ref(null)
const removeMobile = ref(false)
const form = reactive({ title_bn: '', title_en: '', subtitle_bn: '', subtitle_en: '', button_text: '', button_link: '', display_order: 0, is_active: true, start_date: '', end_date: '' })

async function load() { rows.value = await adminJson('/api/admin/banners') }

function releasePreviews() {
    if (desktopPreview.value) URL.revokeObjectURL(desktopPreview.value)
    if (mobilePreview.value) URL.revokeObjectURL(mobilePreview.value)
    desktopPreview.value = null
    mobilePreview.value = null
}

function reset() {
    releasePreviews()
    editing.value = null
    desktopFile.value = null
    mobileFile.value = null
    removeMobile.value = false
    error.value = ''
    Object.assign(form, { title_bn: '', title_en: '', subtitle_bn: '', subtitle_en: '', button_text: '', button_link: '', display_order: 0, is_active: true, start_date: '', end_date: '' })
}

function edit(item) {
    reset()
    editing.value = item
    Object.assign(form, {
        title_bn: item.title_bn, title_en: item.title_en ?? '', subtitle_bn: item.subtitle_bn ?? '', subtitle_en: item.subtitle_en ?? '',
        button_text: item.button_text ?? '', button_link: item.button_link ?? '', display_order: item.display_order,
        is_active: item.is_active, start_date: item.start_date?.slice(0, 10) ?? '', end_date: item.end_date?.slice(0, 10) ?? '',
    })
    window.scrollTo({ top: 0, behavior: 'smooth' })
}

function chooseImage(event, type) {
    const file = event.target.files?.[0] ?? null
    const preview = type === 'desktop' ? desktopPreview : mobilePreview
    if (preview.value) URL.revokeObjectURL(preview.value)
    preview.value = file ? URL.createObjectURL(file) : null
    if (type === 'desktop') desktopFile.value = file
    else { mobileFile.value = file; removeMobile.value = false }
}

async function save() {
    error.value = ''
    notice.value = ''
    saving.value = true
    const data = new FormData()
    Object.entries(form).forEach(([key, value]) => data.append(key, typeof value === 'boolean' ? (value ? '1' : '0') : value ?? ''))
    if (desktopFile.value) data.append('desktop_image', desktopFile.value)
    if (mobileFile.value) data.append('mobile_image', mobileFile.value)
    if (removeMobile.value) data.append('remove_mobile_image', '1')

    try {
        const response = await adminFetch(editing.value ? `/api/admin/banners/${editing.value.id}` : '/api/admin/banners', { method: 'POST', body: data })
        const json = await response.json()
        if (!response.ok) throw new Error(Object.values(json.errors ?? {}).flat()[0] ?? json.message)
        notice.value = editing.value ? 'ব্যানার আপডেট হয়েছে।' : 'নতুন ব্যানার যোগ হয়েছে।'
        reset()
        await load()
    } catch (exception) {
        error.value = exception.message
    } finally {
        saving.value = false
    }
}

async function remove(item) {
    if (!confirm('ব্যানারটি স্থায়ীভাবে মুছে ফেলবেন?')) return
    await adminJson(`/api/admin/banners/${item.id}`, { method: 'DELETE' })
    if (editing.value?.id === item.id) reset()
    await load()
}

onMounted(load)
onBeforeUnmount(releasePreviews)
</script>

<template>
    <AdminLayout>
        <div class="admin-title"><div><p>হোমপেজ ব্যবস্থাপনা</p><h1>ব্যানার</h1></div></div>
        <p v-if="notice" class="success-message">{{ notice }}</p>
        <div class="banner-management-grid">
            <section class="panel banner-editor">
                <div class="panel-head"><div><h2>{{ editing ? 'ব্যানার সম্পাদনা' : 'নতুন ব্যানার' }}</h2><p>ডেস্কটপ ও মোবাইলের জন্য আলাদা ছবি ব্যবহার করতে পারবেন</p></div></div>
                <form class="management-form" @submit.prevent="save">
                    <div class="two-inputs"><label>বাংলা শিরোনাম<input v-model="form.title_bn" required></label><label>ইংরেজি শিরোনাম<input v-model="form.title_en"></label></div>
                    <div class="two-inputs"><label>বাংলা বিবরণ<textarea v-model="form.subtitle_bn" rows="3"></textarea></label><label>ইংরেজি বিবরণ<textarea v-model="form.subtitle_en" rows="3"></textarea></label></div>
                    <div class="two-inputs"><label>বাটনের লেখা<input v-model="form.button_text" placeholder="এখন দান করুন"></label><label>বাটনের লিংক<input v-model="form.button_link" placeholder="/donate"></label></div>
                    <div class="banner-upload-grid">
                        <label class="banner-upload"><span>ডেস্কটপ ছবি {{ editing ? '(পরিবর্তন ঐচ্ছিক)' : '*' }}</span><input type="file" accept="image/png,image/jpeg,image/webp" :required="!editing" @change="chooseImage($event, 'desktop')"><img v-if="desktopPreview || editing?.desktop_image_url" :src="desktopPreview || editing.desktop_image_url" alt="Desktop banner preview"><small>প্রস্তাবিত: 1920 × 700 px · সর্বোচ্চ 5 MB</small></label>
                        <label class="banner-upload"><span>মোবাইল ছবি (ঐচ্ছিক)</span><input type="file" accept="image/png,image/jpeg,image/webp" @change="chooseImage($event, 'mobile')"><img v-if="mobilePreview || (!removeMobile && editing?.mobile_image_url)" :src="mobilePreview || editing.mobile_image_url" alt="Mobile banner preview"><small>প্রস্তাবিত: 750 × 1000 px · সর্বোচ্চ 5 MB</small><button v-if="mobilePreview || editing?.mobile_image_url" type="button" class="danger-text" @click="mobileFile=null;mobilePreview=null;removeMobile=true">মোবাইল ছবি সরান</button></label>
                    </div>
                    <div class="three-inputs"><label>ক্রম<input v-model.number="form.display_order" type="number" min="0" required></label><label>শুরুর তারিখ<input v-model="form.start_date" type="date"></label><label>শেষ তারিখ<input v-model="form.end_date" type="date"></label></div>
                    <label class="toggle-label"><input v-model="form.is_active" type="checkbox"> হোমপেজে প্রকাশ করুন</label>
                    <p v-if="error" class="login-error">{{ error }}</p>
                    <button class="admin-button" :disabled="saving">{{ saving ? 'সংরক্ষণ হচ্ছে…' : 'সংরক্ষণ করুন' }}</button>
                    <button v-if="editing" type="button" class="secondary-button" @click="reset">বাতিল</button>
                </form>
            </section>

            <section class="panel">
                <div class="panel-head"><div><h2>ব্যানার তালিকা</h2><p>{{ rows.length }}টি ব্যানার</p></div></div>
                <div class="banner-list">
                    <article v-for="item in rows" :key="item.id">
                        <img :src="item.desktop_image_url" :alt="item.title_bn">
                        <div><b>{{ item.title_bn }}</b><small>ক্রম: {{ item.display_order }} · {{ item.start_date ? item.start_date.slice(0,10) : 'সবসময়' }} — {{ item.end_date ? item.end_date.slice(0,10) : 'অনির্দিষ্ট' }}</small></div>
                        <span class="status-pill" :class="item.is_active ? 'confirmed' : 'rejected'">{{ item.is_active ? 'প্রকাশিত' : 'বন্ধ' }}</span>
                        <button @click="edit(item)">সম্পাদনা</button><button class="danger-text" @click="remove(item)">মুছুন</button>
                    </article>
                    <div v-if="!rows.length" class="empty compact">এখনো কোনো ব্যানার নেই।</div>
                </div>
            </section>
        </div>
    </AdminLayout>
</template>

<style scoped>
.banner-management-grid { display: grid; grid-template-columns: minmax(0, 1.1fr) minmax(380px, .9fr); gap: 20px; align-items: start; }
.banner-editor { position: sticky; top: 88px; }
.banner-upload-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
.banner-upload { padding: 13px; border: 1px dashed #cbd7cf; border-radius: 8px; background: #f8faf7; }
.banner-upload>span,.banner-upload small { display: block; }
.banner-upload input { font-size: 10px; }
.banner-upload img { width: 100%; height: 130px; margin-top: 9px; border-radius: 6px; object-fit: cover; }
.banner-upload small { margin-top: 7px; color: #89958e; font-size: 9px; }
.banner-upload button { margin-top: 7px; padding: 0; border: 0; background: none; font-size: 9px; cursor: pointer; }
.three-inputs { display: grid; grid-template-columns: .5fr 1fr 1fr; gap: 12px; }
.banner-list article { display: grid; grid-template-columns: 105px minmax(0,1fr) auto; gap: 10px; align-items: center; padding: 13px 0; border-bottom: 1px solid #edf0ec; }
.banner-list img { width: 105px; height: 62px; border-radius: 6px; object-fit: cover; }
.banner-list b,.banner-list small { display: block; }
.banner-list b { color: #30483c; font-size: 12px; }
.banner-list small { margin-top: 4px; color: #849087; font-size: 9px; }
.banner-list article>button { padding: 4px; border: 0; background: none; color: #43705b; font: inherit; font-size: 9px; cursor: pointer; }
.banner-list article>button:first-of-type { grid-column: 2; }
@media(max-width:1100px) { .banner-management-grid { grid-template-columns: 1fr; } .banner-editor { position: static; } }
@media(max-width:650px) { .banner-upload-grid,.three-inputs { grid-template-columns: 1fr; } .banner-list article { grid-template-columns: 84px minmax(0,1fr) auto; } .banner-list img { width: 84px; height: 54px; } }
</style>
