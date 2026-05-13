import { apiClient } from '@/api/client'

export function loginRequest(payload) {
  return apiClient.post('/auth/login', payload)
}

export function logoutRequest() {
  return apiClient.post('/auth/logout')
}

export function fetchCurrentUserRequest() {
  return apiClient.get('/auth/me')
}
