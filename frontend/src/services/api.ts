import axios from 'axios'

const envApiBase = (import.meta.env.VITE_API_URL || '').replace(/\/$/, '')

// Em produção usamos proxy do próprio frontend (/api) para evitar CORS no browser.
const API_BASE = import.meta.env.PROD ? '' : envApiBase

const api = axios.create({
  baseURL: API_BASE ? `${API_BASE}/v1` : '/api/v1',
  withCredentials: false,
  headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
})

api.interceptors.request.use((config) => {
  const authFreeRoutes = ['/auth/login', '/auth/register']
  const url = config.url ?? ''
  if (authFreeRoutes.some((route) => url.includes(route))) {
    delete config.headers.Authorization
    return config
  }

  const token = localStorage.getItem('auth_token')
  if (token) config.headers.Authorization = `Bearer ${token}`
  return config
})

api.interceptors.response.use(
  (res) => res,
  (error) => {
    if (error.response?.status === 401) {
      localStorage.removeItem('auth_token')
      window.location.href = '/login'
    }
    return Promise.reject(error)
  }
)

export default api
