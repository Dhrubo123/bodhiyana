<script setup>
import { computed, onMounted, ref } from 'vue'
import AdminLayout from '../../components/admin/AdminLayout.vue'
import { adminFetch } from '../../services/adminAuth'

const loading = ref(true)
const error = ref('')
const data = ref({stats:{donors:0,pending:0,confirmed:0,rejected:0,confirmed_amount:0},pending:[],recent_confirmed:[]})
const stats = computed(() => [
  ['◉','মোট দাতা',data.value.stats.donors,'sage'], ['◷','অপেক্ষমাণ',data.value.stats.pending,'amber'],
  ['✓','নিশ্চিত দান',data.value.stats.confirmed,'green'], ['×','প্রত্যাখ্যাত',data.value.stats.rejected,'red'],
  ['৳','নিশ্চিত দানের পরিমাণ',`৳ ${Number(data.value.stats.confirmed_amount).toLocaleString('bn-BD')}`,'blue'],
])
onMounted(async()=>{try{const response=await adminFetch('/api/admin/dashboard');if(!response.ok)throw new Error();data.value=await response.json()}catch{error.value='ড্যাশবোর্ডের তথ্য লোড করা যায়নি।'}finally{loading.value=false}})
</script>

<template><AdminLayout><div class="admin-title"><div><p>স্বাগতম</p><h1>ড্যাশবোর্ড</h1></div><RouterLink to="/admin/donations" class="admin-button">অনুদান পর্যালোচনা করুন</RouterLink></div><p v-if="error" class="login-error">{{error}}</p><div class="stat-grid"><article v-for="stat in stats" :key="stat[1]"><i :class="stat[3]">{{stat[0]}}</i><p>{{stat[1]}}</p><strong>{{loading?'—':stat[2]}}</strong></article></div><div class="admin-grid"><section class="panel wide"><div class="panel-head"><div><h2>অপেক্ষমাণ যাচাইকরণ</h2><p>পেমেন্ট যাচাইয়ের জন্য জমা থাকা দান</p></div><RouterLink to="/admin/donations">সব দেখুন →</RouterLink></div><div v-if="!data.pending.length" class="empty"><span>◷</span><b>কোনো অপেক্ষমাণ দান নেই</b><p>নতুন দানের তথ্য এখানে প্রদর্শিত হবে।</p></div><div v-else class="mini-list"><div v-for="item in data.pending" :key="item.id"><div><b>{{item.donor_name}}</b><small>{{item.receipt_number}} · {{item.purpose?.name_bn}}</small></div><strong>৳ {{Number(item.amount).toLocaleString('bn-BD')}}</strong></div></div></section><section class="panel"><div class="panel-head"><div><h2>দানের সারসংক্ষেপ</h2><p>নিশ্চিত দান অনুযায়ী</p></div></div><div class="empty compact"><span>▦</span><p>{{data.stats.confirmed ? `${data.stats.confirmed}টি নিশ্চিত দান` : 'এখনো কোনো নিশ্চিত দান নেই।'}}</p></div></section></div><section class="panel"><div class="panel-head"><div><h2>সাম্প্রতিক নিশ্চিত দান</h2><p>সর্বশেষ নিশ্চিত হওয়া অনুদান</p></div></div><div v-if="!data.recent_confirmed.length" class="empty compact"><span>✓</span><p>নিশ্চিত দানের তথ্য এখানে প্রদর্শিত হবে।</p></div><div v-else class="mini-list"><div v-for="item in data.recent_confirmed" :key="item.id"><div><b>{{item.donor_name}}</b><small>{{item.receipt_number}}</small></div><strong>৳ {{Number(item.amount).toLocaleString('bn-BD')}}</strong></div></div></section></AdminLayout></template>
