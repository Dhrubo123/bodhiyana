<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'

const banners = ref([])
const activeIndex = ref(0)
const activeBanner = computed(() => banners.value[activeIndex.value] ?? null)
let rotation

onMounted(async () => {
    try {
        banners.value = await fetch('/api/banners').then(response => response.json())
        if (banners.value.length > 1) {
            rotation = window.setInterval(() => { activeIndex.value = (activeIndex.value + 1) % banners.value.length }, 6500)
        }
    } catch {
        banners.value = []
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
@media(max-width:760px) { .managed-banner { min-height: calc(100svh - 64px); } .banner-shade { background: linear-gradient(180deg, rgba(10,31,25,.45), rgba(10,33,26,.88)); } .banner-dots { right: 20px; bottom: 18px; } }
</style>
