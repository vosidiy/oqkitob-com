import { apiClient } from '@/api/client'

export function loginRequest(payload) {
  return apiClient.post('/auth/login', payload)
}

export function registerRequest(payload) {
  return apiClient.post('/auth/register', payload)
}

export function logoutRequest() {
  return apiClient.post('/auth/logout')
}

export function fetchCurrentUserRequest() {
  return apiClient.get('/auth/me')
}

export function updateProfileRequest(payload) {
  return apiClient.put('/auth/profile', payload)
}

export function updatePasswordRequest(payload) {
  return apiClient.put('/auth/password', payload)
}
