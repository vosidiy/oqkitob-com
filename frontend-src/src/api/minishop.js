import { apiClient } from '@/api/client'

export function fetchMinishopProducts(bookId) {
  return apiClient.get(`/books/${bookId}/minishop/products`)
}

export function createMinishopProduct(bookId, payload) {
  return apiClient.post(`/books/${bookId}/minishop/products`, payload)
}

export function updateMinishopProduct(bookId, productId, payload) {
  return apiClient.put(`/books/${bookId}/minishop/products/${productId}`, payload)
}

export function deactivateMinishopProduct(bookId, productId) {
  return apiClient.post(`/books/${bookId}/minishop/products/${productId}/deactivate`)
}

export function fetchMinishopCategories(bookId) {
  return apiClient.get(`/books/${bookId}/minishop/categories`)
}

export function createMinishopSale(bookId, payload) {
  return apiClient.post(`/books/${bookId}/minishop/sales`, payload)
}

export function fetchMinishopSales(bookId, params = {}) {
  return apiClient.get(`/books/${bookId}/minishop/sales`, { params })
}

export function fetchMinishopSale(bookId, saleId) {
  return apiClient.get(`/books/${bookId}/minishop/sales/${saleId}`)
}

export function updateMinishopSalePaymentSummary(bookId, saleId, payload) {
  return apiClient.put(`/books/${bookId}/minishop/sales/${saleId}/payment-summary`, payload)
}

export function deleteMinishopSale(bookId, saleId) {
  return apiClient.delete(`/books/${bookId}/minishop/sales/${saleId}`)
}
