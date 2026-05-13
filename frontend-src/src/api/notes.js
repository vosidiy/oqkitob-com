import { apiClient } from '@/api/client'

export function fetchNotes(bookId) {
  return apiClient.get(`/books/${bookId}/notes`)
}
