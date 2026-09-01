<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'

const banners = ref([])
const gallery = ref([])
const activeIndex = ref(0)
const activeBanner = computed(() => banners.value[activeIndex.value] ?? null)
let rotation

onMounted(async () => {
    try {
        const [bannerData, galleryData] = await Promise.all([
            fetch('/api/banners').then(response => response.json()),
            fetch('/api/gallery').then(response => response.json()),
        ])
        banners.value = bannerData
        gallery.value = galleryData
        if (banners.value.length > 1) {
            rotation = window.setInterval(() => { activeIndex.value = (activeIndex.value + 1) % banners.value.length }, 6500)
        }
    } catch {
        banners.value = []; gallery.value = []
    }
})

onBeforeUnmount(() => window.clearInterval(rotation))
</script>

<template>
    <section class="hero" :class="{ 'managed-banner': activeBanner }">
        <picture v-if="activeBanner" class="banner-picture">
            <source v-if="activeBanner.mobile_image_url" media="(max-width: 760px)" :srcset="activeBanner.mobile_image_url">
            <img :src="activeBanner.desktop_image_url" :alt="activeBanner.title_bn">
        </picture>
        <div class="banner-shade"></div>
        <div class="hero-content">
            <template v-if="activeBanner">
                <p v-if="activeBanner.title_en" class="eyebrow">{{ activeBanner.title_en }}</p>
                <h1>{{ activeBanner.title_bn }}</h1>
                <p v-if="activeBanner.subtitle_bn">{{ activeBanner.subtitle_bn }}</p>
                <div v-if="activeBanner.button_text && activeBanner.button_link" class="hero-actions">
                    <a :href="activeBanner.button_link" class="button primary">{{ activeBanner.button_text }}</a>
                </div>
            </template>
            <template v-else>
                <p class="eyebrow">বুদ্ধং শরণং গচ্ছামি</p>
                <h1>ধর্ম, সেবা ও সম্প্রীতির<br>এক পবিত্র আশ্রয়</h1>
                <p>বিহারের ধর্মীয়, সামাজিক, শিক্ষামূলক ও সেবামূলক কার্যক্রমে আপনার সহমর্মী অংশগ্রহণ কাম্য।</p>
                <div class="hero-actions"><RouterLink to="/donate" class="button primary">🙏 দান করুন</RouterLink><a href="#about" class="button ghost">বিহার সম্পর্কে জানুন</a></div>
                <div class="lotus">☸</div>
            </template>
        </div>
        <div v-if="banners.length > 1" class="banner-dots" aria-label="ব্যানার নির্বাচন">
            <button v-for="(_, index) in banners" :key="index" :class="{ active: activeIndex === index }" :aria-label="`ব্যানার ${index + 1}`" @click="activeIndex = index"></button>
        </div>
    </section>

    <section id="about" class="section two-col"><div><p class="eyebrow">আমাদের বিহার</p><h2>শান্তি, প্রজ্ঞা ও করুণার পথে</h2></div><p>এই বৌদ্ধ বিহার ধর্মচর্চা, ধ্যান, নৈতিক শিক্ষা ও মানবসেবার একটি আন্তরিক কেন্দ্র। সকল ধর্ম ও সম্প্রদায়ের মানুষের জন্য আমাদের দ্বার উন্মুক্ত।</p></section>
    <section id="activities" class="section soft"><p class="eyebrow center">ধর্মীয় কার্যক্রম</p><h2 class="center">প্রতিদিনের কল্যাণময় আয়োজন</h2><div class="cards"><article><i>✦</i><h3>ধর্মদেশনা</h3><p>বুদ্ধের শিক্ষা ও নৈতিক জীবনবোধ নিয়ে আলোচনা।</p></article><article><i>◌</i><h3>ধ্যান অনুশীলন</h3><p>প্রশান্ত মন ও সচেতন জীবনের জন্য নিয়মিত ধ্যান।</p></article><article><i>♥</i><h3>সেবামূলক কাজ</h3><p>সমাজের মানুষের পাশে দাঁড়ানোর আন্তরিক প্রয়াস।</p></article></div></section>
    <section v-if="gallery.length" class="section gallery-section"><p class="eyebrow center">ছবির গ্যালারি</p><h2 class="center">বিহারের স্মরণীয় মুহূর্ত</h2><div class="public-gallery"><figure v-for="image in gallery" :key="image.id"><img :src="image.image_url" :alt="image.title_bn || 'বিহারের ছবি'"><figcaption v-if="image.title_bn || image.title_en">{{ image.title_bn || image.title_en }}</figcaption></figure></div></section>
    <section class="section donation-cta"><div><p class="eyebrow">দান ও সহযোগিতা</p><h2>আপনার দান সেবার আলো ছড়িয়ে দিক</h2><p>আপনার মূল্যবান দান বিহারের ধর্মীয়, সামাজিক, শিক্ষামূলক ও সেবামূলক কার্যক্রমে সহায়তা করবে।</p></div><RouterLink to="/donate" class="button primary">এখন দান করুন →</RouterLink></section>
    <section id="contact" class="section contact"><div><p class="eyebrow">যোগাযোগ</p><h2>আমাদের সাথে যুক্ত থাকুন</h2><p>ঠিকানা ও যোগাযোগের তথ্য শীঘ্রই এখানে যুক্ত হবে।</p></div><div class="contact-card"><b>বিহার কর্তৃপক্ষ</b><p>বাংলাদেশ</p><a href="mailto:info@example.test">info@example.test</a></div></section>
</template>

<style scoped>
.managed-banner { isolation: isolate; min-height: 560px; background: #173c32; }
.banner-picture,.banner-picture img,.banner-shade { position: absolute; inset: 0; width: 100%; height: 100%; }
.banner-picture { z-index: -2; }
.banner-picture img { object-fit: cover; }
.banner-shade { z-index: -1; background: linear-gradient(90deg, rgba(11,36,29,.88) 0%, rgba(15,42,33,.62) 48%, rgba(13,34,28,.22) 100%); }
.managed-banner .hero-content { max-width: 760px; }
.managed-banner h1 { white-space: pre-line; }
.banner-dots { position: absolute; z-index: 2; right: max(5vw,24px); bottom: 25px; display: flex; gap: 7px; }
.banner-dots button { width: 9px; height: 9px; padding: 0; border: 1px solid #fff; border-radius: 50%; background: #ffffff66; cursor: pointer; }
.banner-dots button.active { width: 25px; border-radius: 8px; background: #fff; }
.event-ticker { display: flex; min-height: 52px; align-items: stretch; overflow: hidden; border-bottom: 1px solid #c38a3266; background: linear-gradient(90deg, #102f27, #1b4a3b 50%, #12382e); box-shadow: inset 0 1px #e3b45a2a, 0 5px 15px #16382a1a; color: #fff8e9; font-size: 13px; }
.ticker-label { position: relative; z-index: 1; display: grid; grid-template-columns: 28px auto; grid-template-rows: 1fr 1fr; align-items: center; column-gap: 7px; min-width: 182px; padding: 7px max(4vw,18px); background: linear-gradient(135deg, #b46f1e, #d3973b); box-shadow: 7px 0 18px #071e1733; white-space: nowrap; }
.ticker-label::after { position: absolute; top: 0; right: -15px; width: 30px; height: 100%; background: #d3973b; clip-path: polygon(0 0, 50% 50%, 0 100%); content: ''; }
.ticker-label span { grid-row: 1 / 3; color: #fff3cf; font-size: 25px; line-height: 1; }.ticker-label b { font-family: 'Noto Serif Bengali', serif; font-size: 13px; line-height: 1; }.ticker-label small { color: #fff3d9cc; font-family: system-ui, sans-serif; font-size: 8px; letter-spacing: .08em; text-transform: uppercase; }
.ticker-window { display: flex; min-width: 0; flex: 1; align-items: center; overflow: hidden; padding-left: 20px; }
.ticker-track { display: flex; width: max-content; animation: ticker-scroll 34s linear infinite; white-space: nowrap; }.event-ticker:hover .ticker-track { animation-play-state: paused; }
.ticker-track span { display: inline-flex; align-items: center; gap: 8px; padding-right: 74px; color: #fff7e7; font-family: 'Noto Serif Bengali', serif; font-size: 14px; }.ticker-track i { color: #e8b455; font-style: normal; font-size: 10px; }.ticker-track small { color: #cfe0d5; font-family: 'Noto Sans Bengali', sans-serif; font-size: 11px; }
.public-gallery { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-top: 30px; }
.public-gallery figure { min-height: 210px; margin: 0; overflow: hidden; border-radius: 10px; background: #edf1eb; }
.public-gallery img { display: block; width: 100%; height: 210px; object-fit: cover; transition: transform .3s ease; }.public-gallery figure:hover img { transform: scale(1.04); }
.public-gallery figcaption { padding:10px 12px; background:#fff; color:#506158; font-size:13px; }
@keyframes ticker-scroll { from { transform: translateX(0); } to { transform: translateX(-50%); } }
@media(max-width:760px) { .managed-banner { min-height: calc(100svh - 64px); } .banner-shade { background: linear-gradient(180deg, rgba(10,31,25,.45), rgba(10,33,26,.88)); } .banner-dots { right: 20px; bottom: 18px; } .event-ticker { min-height: 48px; }.ticker-label { min-width: 135px; padding: 6px 12px; grid-template-columns: 23px auto; column-gap: 5px; }.ticker-label span { font-size: 21px; }.ticker-label b { font-size: 11px; }.ticker-label small { font-size: 7px; }.ticker-window { padding-left: 13px; }.ticker-track { animation-duration: 26s; }.ticker-track span { gap: 6px; padding-right: 44px; font-size: 12px; }.ticker-track small { font-size: 9px; } .public-gallery { grid-template-columns: 1fr 1fr; gap: 10px; } .public-gallery figure,.public-gallery img { min-height: 145px; height: 145px; } }
</style>
