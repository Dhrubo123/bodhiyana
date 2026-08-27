<script setup>
import { reactive, ref } from 'vue'

const form = reactive({ receipt_number: '', mobile: '' })
const result = ref(null)
const error = ref('')

async function lookup() {
    error.value = ''
    result.value = null
    const response = await fetch('/api/donation-status', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
        body: JSON.stringify(form),
    })
    const json = await response.json()
    if (!response.ok) {
        error.value = 'রসিদ নম্বর ও মোবাইল নম্বর যাচাই করুন।'
        return
    }
    result.value = json.donation
}
</script>

<template>
    <section class="page-intro">
        <p class="eyebrow">দানের অবস্থা</p><h1>আপনার দানের তথ্য দেখুন</h1>
        <p>রসিদ নম্বর ও মোবাইল নম্বর দিয়ে দানের বর্তমান অবস্থা জানুন।</p>
    </section>
    <section class="form-wrap narrow">
        <form @submit.prevent="lookup">
            <label>রসিদ নম্বর<input v-model="form.receipt_number" placeholder="DON-2026-000001" required></label>
            <label>মোবাইল নম্বর<input v-model="form.mobile" placeholder="01XXXXXXXXX" required></label>
            <p v-if="error" class="error">{{ error }}</p>
            <button class="button primary submit">অবস্থা দেখুন</button>
        </form>
        <div v-if="result" class="status-result" :class="result.status">
            <b>{{ result.status === 'confirmed' ? 'দান নিশ্চিত হয়েছে' : result.status === 'rejected' ? 'দান প্রত্যাখ্যাত' : 'যাচাই করা হচ্ছে' }}</b>
            <p>{{ result.donor_name }} · ৳ {{ result.amount }}</p>
            <p>রসিদ: {{ result.receipt_number }}</p>
            <div v-if="result.status === 'confirmed'" class="public-receipt-actions">
                <a class="button secondary" :href="`/api/receipts/${result.public_token}/pdf`" target="_blank">রসিদ দেখুন</a>
                <a class="button primary" :href="`/api/receipts/${result.public_token}/pdf?download=1`">PDF ডাউনলোড</a>
            </div>
        </div>
    </section>
</template>

<style scoped>
.public-receipt-actions { display: flex; flex-wrap: wrap; justify-content: center; gap: 10px; margin-top: 18px; }
.public-receipt-actions a { text-decoration: none; }
</style>
