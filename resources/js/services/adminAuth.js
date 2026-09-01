import { reactive } from 'vue'

export const adminAuth = reactive({
  user: null,
  checked: false,
})

const csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.content ?? ''

async function refreshCsrfToken() {
  const response = await fetch('/csrf-token', { credentials: 'same-origin', headers: { Accept: 'application/json' } })
  if (!response.ok) throw new Error('নিরাপত্তা টোকেন পাওয়া যায়নি। পেজটি রিফ্রেশ করে আবার চেষ্টা করুন।')
  const { token } = await response.json()
  const meta = document.querySelector('meta[name="csrf-token"]')
  if (meta && token) meta.content = token
  return token
}

export async function ensureAdminAuth(force = false) {
  if (adminAuth.checked && !force) return Boolean(adminAuth.user)

  const response = await fetch('/api/admin/me', {
    headers: { Accept: 'application/json' },
    credentials: 'same-origin',
  })

  adminAuth.checked = true
  adminAuth.user = response.ok ? (await response.json()).user : null
  return Boolean(adminAuth.user)
}

export async function adminLogin(credentials) {
  const token = await refreshCsrfToken()
  const response = await fetch('/api/admin/login', {
    method: 'POST',
    credentials: 'same-origin',
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': token,
    },
    body: JSON.stringify(credentials),
  })
  const data = await response.json()
  if (!response.ok) throw new Error(Object.values(data.errors ?? {}).flat()[0] ?? 'লগইন করা যায়নি।')
  adminAuth.user = data.user
  adminAuth.checked = true
  return data.user
}

export async function adminLogout() {
  const token = await refreshCsrfToken()
  await fetch('/api/admin/logout', {
    method: 'POST',
    credentials: 'same-origin',
    headers: { Accept: 'application/json', 'X-CSRF-TOKEN': token },
  })
  adminAuth.user = null
  adminAuth.checked = true
}

export async function adminFetch(url, options = {}) {
  const isFormData = options.body instanceof FormData
  const token = options.method && options.method !== 'GET' ? await refreshCsrfToken() : csrfToken()
  return fetch(url, {
    ...options,
    credentials: 'same-origin',
    headers: {
      Accept: 'application/json',
      ...(options.method && options.method !== 'GET' ? { ...(!isFormData ? { 'Content-Type': 'application/json' } : {}), 'X-CSRF-TOKEN': token } : {}),
      ...(options.headers ?? {}),
    },
  })
}

export async function adminJson(url, options = {}) {
  const response = await adminFetch(url, options)
  const data = await response.json()
  if (!response.ok) throw new Error(Object.values(data.errors ?? {}).flat()[0] ?? data.message ?? 'অনুরোধ সম্পন্ন করা যায়নি।')
  return data
}
