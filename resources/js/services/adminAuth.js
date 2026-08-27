import { reactive } from 'vue'

export const adminAuth = reactive({
  user: null,
  checked: false,
})

const csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.content ?? ''

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
  const response = await fetch('/api/admin/login', {
    method: 'POST',
    credentials: 'same-origin',
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': csrfToken(),
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
  await fetch('/api/admin/logout', {
    method: 'POST',
    credentials: 'same-origin',
    headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrfToken() },
  })
  adminAuth.user = null
  adminAuth.checked = true
}

export function adminFetch(url, options = {}) {
  const isFormData = options.body instanceof FormData
  return fetch(url, {
    ...options,
    credentials: 'same-origin',
    headers: {
      Accept: 'application/json',
      ...(options.method && options.method !== 'GET' ? { ...(!isFormData ? { 'Content-Type': 'application/json' } : {}), 'X-CSRF-TOKEN': csrfToken() } : {}),
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
