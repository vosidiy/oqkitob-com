import { apiClient } from '@/api/client'

export function fetchTodos(bookId) {
  return apiClient.get(`/books/${bookId}/todos`)
}
