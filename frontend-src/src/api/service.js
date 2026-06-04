import { apiClient } from '@/api/client'

export function fetchServiceTypes(bookId) {
  return apiClient.get(`/books/${bookId}/service/types`)
}

export function createServiceType(bookId, payload) {
  return apiClient.post(`/books/${bookId}/service/types`, payload)
}

export function updateServiceType(bookId, serviceTypeId, payload) {
  return apiClient.put(`/books/${bookId}/service/types/${serviceTypeId}`, payload)
}

export function deleteServiceType(bookId, serviceTypeId) {
  return apiClient.delete(`/books/${bookId}/service/types/${serviceTypeId}`)
}

export function fetchServiceOrders(bookId, params = {}) {
  return apiClient.get(`/books/${bookId}/service/orders`, { params })
}

export function fetchServiceOrder(bookId, orderId) {
  return apiClient.get(`/books/${bookId}/service/orders/${orderId}`)
}

export function createServiceOrder(bookId, payload) {
  return apiClient.post(`/books/${bookId}/service/orders`, payload)
}
