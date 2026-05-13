import { apiClient } from '@/api/client'

export function fetchBooksList() {
  // Shared metadata list used by AppLayout and as the first lookup source for BookView.
  return apiClient.get('/books')
}

export function fetchBookById(bookId) {
  // Lightweight fallback used by BookView when a direct book URL is not found in the shared list.
  return apiClient.get(`/books/${bookId}`)
}
