import { apiClient } from '@/api/client'

export function fetchMinishopProducts(bookId) {
  return apiClient.get(`/books/${bookId}/minishop/products`)
}

export function createMinishopProduct(bookId, payload) {
  return apiClient.post(`/books/${bookId}/minishop/products`, payload)
}

export function fetchMinishopCategories(bookId) {
  return apiClient.get(`/books/${bookId}/minishop/categories`)
}

export function createMinishopSale(bookId, payload) {
  return apiClient.post(`/books/${bookId}/minishop/sales`, payload)
}

export function fetchMinishopSales(bookId) {
  return apiClient.get(`/books/${bookId}/minishop/sales`)
}

export function fetchMinishopSale(bookId, saleId) {
  return apiClient.get(`/books/${bookId}/minishop/sales/${saleId}`)
}
