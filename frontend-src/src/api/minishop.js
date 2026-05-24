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

export function fetchMinishopCustomersList(bookId) {
  return apiClient.get(`/books/${bookId}/minishop/customers/list`)
}

export function fetchMinishopCustomersListData(bookId, params = {}) {
  return apiClient.get(`/books/${bookId}/minishop/customers`, { params })
}

export function fetchMinishopCustomer(bookId, customerId) {
  return apiClient.get(`/books/${bookId}/minishop/customers/${customerId}`)
}

export function createMinishopCustomer(bookId, payload) {
  return apiClient.post(`/books/${bookId}/minishop/customers`, payload)
}

export function updateMinishopCustomer(bookId, customerId, payload) {
  return apiClient.put(`/books/${bookId}/minishop/customers/${customerId}`, payload)
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

export function createMinishopSalePayment(bookId, saleId, payload) {
  return apiClient.post(`/books/${bookId}/minishop/sales/${saleId}/payments`, payload)
}

export function deleteMinishopSalePayment(bookId, saleId, paymentId) {
  return apiClient.delete(`/books/${bookId}/minishop/sales/${saleId}/payments/${paymentId}`)
}

export function deleteMinishopSale(bookId, saleId) {
  return apiClient.delete(`/books/${bookId}/minishop/sales/${saleId}`)
}
