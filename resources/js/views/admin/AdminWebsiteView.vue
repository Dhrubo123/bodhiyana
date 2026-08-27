<script setup>
import { onBeforeUnmount, onMounted, reactive, ref } from 'vue'
import AdminLayout from '../../components/admin/AdminLayout.vue'
import { adminFetch, adminJson } from '../../services/adminAuth'

const saved = ref('')
const error = ref('')
const saving = ref(false)
const logoFile = ref(null)
const faviconFile = ref(null)
const logoPreview = ref(null)
const faviconPreview = ref(null)
const removeLogo = ref(false)
const removeFavicon = ref(false)
const assets = reactive({ logo_url: null, favicon_url: null })
const form = reactive({
    bihar_name: '', site_title: '', bihar_description: '', bihar_history: '', activities: '',
    address: '', contact_phone: '', email: '', facebook: '', youtube: '', google_maps: '',
})

onMounted(async () => applyResponse(await adminJson('/api/admin/website')))
onBeforeUnmount(() => {
    if (logoPreview.value) URL.revokeObjectURL(logoPreview.value)
    if (faviconPreview.value) URL.revokeObjectURL(faviconPreview.value)
})

function applyResponse(data) {
    Object.keys(form).forEach(key => { form[key] = data[key] ?? '' })
    assets.logo_url = data.logo_url ?? null
    assets.favicon_url = data.favicon_url ?? null
}

function selectAsset(event, type) {
    const file = event.target.files?.[0] ?? null
    const preview = type === 'logo' ? logoPreview : faviconPreview
    if (preview.value) URL.revokeObjectURL(preview.value)
    preview.value = file ? URL.createObjectURL(file) : null
    if (type === 'logo') {
        logoFile.value = file
        removeLogo.value = false
    } else {
        faviconFile.value = file
        removeFavicon.value = false
    }
}

function clearAsset(type) {
    if (type === 'logo') {
        logoFile.value = null
        removeLogo.value = true
        if (logoPreview.value) URL.revokeObjectURL(logoPreview.value)
        logoPreview.value = null
    } else {
        faviconFile.value = null
        removeFavicon.value = true
        if (faviconPreview.value) URL.revokeObjectURL(faviconPreview.value)
        faviconPreview.value = null
    }
}

async function save() {
    saved.value = ''
    error.value = ''
    saving.value = true

    const data = new FormData()
    Object.entries(form).forEach(([key, value]) => data.append(`settings[${key}]`, value ?? ''))
    if (logoFile.value) data.append('logo', logoFile.value)
    if (faviconFile.value) data.append('favicon', faviconFile.value)
    if (removeLogo.value) data.append('remove_logo', '1')
    if (removeFavicon.value) data.append('remove_favicon', '1')

    try {
        const response = await adminFetch('/api/admin/website', { method: 'POST', body: data })
        const json = await response.json()
        if (!response.ok) throw new Error(Object.values(json.errors ?? {}).flat()[0] ?? json.message)
        applyResponse(json)
        logoFile.value = null
        faviconFile.value = null
        removeLogo.value = false
        removeFavicon.value = false
        logoPreview.value = null
        faviconPreview.value = null
        saved.value = 'ওয়েবসাইটের তথ্য ও পরিচিতি সংরক্ষণ হয়েছে।'
    } catch (exception) {
        error.value = exception.message
    } finally {
        saving.value = false
    }
}
</script>

<template>
    <AdminLayout>
        <div class="admin-title"><div><p>কনটেন্ট ব্যবস্থাপনা</p><h1>ওয়েবসাইট তথ্য</h1></div></div>
        <section class="panel content-panel">
            <form class="management-form wide-form" @submit.prevent="save">
                <div class="form-section">
                    <h2>সাইট পরিচিতি</h2>
                    <div class="two-inputs">
                        <label>মন্দির/বিহারের নাম<input v-model="form.bihar_name" required></label>
                        <label>ব্রাউজার ট্যাবের নাম<input v-model="form.site_title" placeholder="বোধিনানা | দান ব্যবস্থাপনা" required></label>
                    </div>
                    <div class="site-assets-grid">
                        <div class="site-asset-card">
                            <div class="site-asset-preview logo"><img v-if="logoPreview || (!removeLogo && assets.logo_url)" :src="logoPreview || assets.logo_url" alt="Logo preview"><span v-else>LOGO</span></div>
                            <div><b>ওয়েবসাইট লোগো</b><p>PNG, JPG অথবা WEBP · সর্বোচ্চ 2 MB</p><label class="file-button">লোগো নির্বাচন<input type="file" accept="image/png,image/jpeg,image/webp" @change="selectAsset($event, 'logo')"></label><button v-if="logoPreview || assets.logo_url" type="button" class="remove-qr" @click="clearAsset('logo')">সরান</button></div>
                        </div>
                        <div class="site-asset-card">
                            <div class="site-asset-preview favicon"><img v-if="faviconPreview || (!removeFavicon && assets.favicon_url)" :src="faviconPreview || assets.favicon_url" alt="Favicon preview"><span v-else>ICON</span></div>
                            <div><b>ব্রাউজার ফেভিকন</b><p>ICO, PNG, JPG অথবা WEBP · সর্বোচ্চ 1 MB</p><label class="file-button">ফেভিকন নির্বাচন<input type="file" accept="image/x-icon,image/png,image/jpeg,image/webp,.ico" @change="selectAsset($event, 'favicon')"></label><button v-if="faviconPreview || assets.favicon_url" type="button" class="remove-qr" @click="clearAsset('favicon')">সরান</button></div>
                        </div>
                    </div>
                </div>
                <div class="form-section">
                    <h2>বিহারের পরিচিতি</h2>
                    <label>সংক্ষিপ্ত বিবরণ<textarea v-model="form.bihar_description" rows="4"></textarea></label>
                    <label>বিহারের ইতিহাস<textarea v-model="form.bihar_history" rows="5"></textarea></label>
                    <label>ধর্মীয় কার্যক্রম<textarea v-model="form.activities" rows="4"></textarea></label>
                </div>
                <div class="form-section">
                    <h2>যোগাযোগ</h2>
                    <label>ঠিকানা<textarea v-model="form.address" rows="3"></textarea></label>
                    <div class="two-inputs"><label>ফোন<input v-model="form.contact_phone"></label><label>ইমেইল<input v-model="form.email" type="email"></label></div>
                    <label>Facebook URL<input v-model="form.facebook" type="url"></label>
                    <label>YouTube URL<input v-model="form.youtube" type="url"></label>
                    <label>Google Maps লিংক/এম্বেড<input v-model="form.google_maps"></label>
                </div>
                <p v-if="saved" class="success-message">{{ saved }}</p>
                <p v-if="error" class="login-error">{{ error }}</p>
                <button class="admin-button" :disabled="saving">{{ saving ? 'সংরক্ষণ হচ্ছে…' : 'পরিবর্তন সংরক্ষণ করুন' }}</button>
            </form>
        </section>
    </AdminLayout>
</template>

<style scoped>
.site-assets-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-top: 4px; }
.site-asset-card { display: grid; grid-template-columns: 100px minmax(0, 1fr); gap: 15px; align-items: center; padding: 15px; border: 1px dashed #cbd7cf; border-radius: 9px; background: #f8faf7; }
.site-asset-preview { display: grid; width: 96px; height: 82px; place-items: center; overflow: hidden; border: 1px solid #dfe6e0; border-radius: 8px; background: #fff; color: #9aaa9f; font-size: 10px; font-weight: 700; }
.site-asset-preview.favicon { width: 72px; height: 72px; margin: auto; }
.site-asset-preview img { width: 100%; height: 100%; object-fit: contain; }
.site-asset-card b { display: block; color: #3a5145; font-size: 12px; }
.site-asset-card p { margin: 5px 0 10px; color: #89958e; font-size: 9px; }
@media (max-width: 760px) { .site-assets-grid { grid-template-columns: 1fr; } }
@media (max-width: 430px) { .site-asset-card { grid-template-columns: 76px minmax(0, 1fr); padding: 11px; } .site-asset-preview { width: 72px; height: 65px; } }
</style>
