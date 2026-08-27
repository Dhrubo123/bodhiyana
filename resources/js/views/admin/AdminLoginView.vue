<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { adminLogin } from '../../services/adminAuth'

const router = useRouter()
const email = ref('')
const password = ref('')
const remember = ref(false)
const showPassword = ref(false)
const loading = ref(false)
const error = ref('')

async function submit() {
  error.value = ''
  loading.value = true
  try {
    await adminLogin({ email: email.value, password: password.value, remember: remember.value })
    await router.replace('/admin')
  } catch (exception) {
    error.value = exception.message
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="admin-login-page">
    <section class="login-identity">
      <div class="login-pattern" aria-hidden="true">☸</div>
      <div class="login-identity-content">
        <div class="login-mark">☸</div>
        <p class="login-kicker">বিহার প্রশাসন</p>
        <h1>সেবা ও স্বচ্ছতার<br>বিশ্বস্ত ব্যবস্থাপনা</h1>
        <p>দান যাচাই, রসিদ এবং বিহারের কার্যক্রম নিরাপদভাবে পরিচালনা করুন।</p>
      </div>
      <small>শুধুমাত্র অনুমোদিত প্রশাসকের জন্য</small>
    </section>

    <section class="login-form-side">
      <div class="login-card">
        <div class="login-mobile-mark">☸</div>
        <p class="login-kicker">স্বাগতম</p>
        <h2>অ্যাডমিন লগইন</h2>
        <p class="login-subtitle">আপনার নিরাপদ অ্যাডমিন অ্যাকাউন্টে প্রবেশ করুন।</p>

        <form @submit.prevent="submit">
          <label>
            ইমেইল ঠিকানা
            <span class="login-input"><i>✉</i><input v-model.trim="email" type="email" autocomplete="username" placeholder="admin@example.com" required autofocus></span>
          </label>
          <label>
            পাসওয়ার্ড
            <span class="login-input"><i>⌕</i><input v-model="password" :type="showPassword ? 'text' : 'password'" autocomplete="current-password" placeholder="আপনার পাসওয়ার্ড" required><button type="button" :aria-label="showPassword ? 'পাসওয়ার্ড লুকান' : 'পাসওয়ার্ড দেখুন'" @click="showPassword = !showPassword">{{ showPassword ? 'লুকান' : 'দেখুন' }}</button></span>
          </label>

          <div class="login-options">
            <label class="remember"><input v-model="remember" type="checkbox"> আমাকে মনে রাখুন</label>
            <span>পাসওয়ার্ড ভুলে গেছেন?</span>
          </div>

          <p v-if="error" class="login-error" role="alert">{{ error }}</p>
          <button class="login-submit" :disabled="loading">
            <span v-if="loading" class="spinner"></span>
            {{ loading ? 'প্রবেশ করা হচ্ছে…' : 'নিরাপদে প্রবেশ করুন' }}
          </button>
        </form>

        <p class="login-security">▣ আপনার সেশন নিরাপদ ও সুরক্ষিত</p>
      </div>
    </section>
  </div>
</template>
