import { apiClient } from '@/api/client'

export function fetchFinanceTransactions(bookId) {
  return apiClient.get(`/books/${bookId}/finance`)
}
