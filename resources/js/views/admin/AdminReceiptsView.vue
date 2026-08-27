<script setup>
import { onMounted, ref } from 'vue'
import AdminLayout from '../../components/admin/AdminLayout.vue'
import { adminJson } from '../../services/adminAuth'

const rows = ref([])
const search = ref('')

async function load() {
    rows.value = (await adminJson(`/api/admin/receipts?search=${encodeURIComponent(search.value)}`)).data
}

onMounted(load)
</script>

<template>
    <AdminLayout>
        <div class="admin-title">
            <div><p>নিশ্চিত দান</p><h1>দানের রসিদ</h1></div>
        </div>
        <section class="panel">
            <form class="management-toolbar" @submit.prevent="load">
                <input v-model="search" placeholder="রসিদ, নাম অথবা মোবাইল">
                <button class="admin-button">খুঁজুন</button>
            </form>
            <div class="table-wrap">
                <table>
                    <thead><tr><th>রসিদ</th><th>দাতা</th><th>পরিমাণ</th><th>উদ্দেশ্য</th><th>নিশ্চিত হওয়ার তারিখ</th><th>পিডিএফ</th></tr></thead>
                    <tbody>
                        <tr v-if="!rows.length"><td colspan="6"><div class="empty compact">এখনো কোনো নিশ্চিত রসিদ নেই।</div></td></tr>
                        <tr v-for="item in rows" :key="item.id">
                            <td><b>{{ item.receipt_number }}</b></td>
                            <td>{{ item.donor_name }}<small class="table-sub">{{ item.mobile }}</small></td>
                            <td>৳ {{ Number(item.amount).toLocaleString('bn-BD') }}</td>
                            <td>{{ item.purpose?.name_bn }}</td>
                            <td>{{ new Date(item.confirmed_at).toLocaleDateString('bn-BD') }}</td>
                            <td class="receipt-actions">
                                <a class="review-button" :href="`/api/admin/receipts/${item.id}/pdf`" target="_blank">দেখুন</a>
                                <a class="review-button" :href="`/api/admin/receipts/${item.id}/pdf?download=1`">ডাউনলোড</a>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    </AdminLayout>
</template>

<style scoped>
.receipt-actions { display: flex; gap: 8px; align-items: center; white-space: nowrap; }
.receipt-actions a { display: inline-flex; text-decoration: none; }
</style>
