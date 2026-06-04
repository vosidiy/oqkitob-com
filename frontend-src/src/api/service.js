import { apiClient } from '@/api/client'

export function fetchServiceOrders(bookId, params = {}) {
  return apiClient.get(`/books/${bookId}/service/orders`, { params })
}

export function fetchServiceClients(bookId, params = {}) {
  return apiClient.get(`/books/${bookId}/service/clients`, { params })
}

export function fetchServiceReports(bookId, params = {}) {
  return apiClient.get(`/books/${bookId}/service/reports`, { params })
}
