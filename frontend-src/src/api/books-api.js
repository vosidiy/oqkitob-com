import { apiClient } from '@/api/client'

export function fetchBooksList() {
  // Shared metadata list used by AppLayout and as the first lookup source for BookView.
  return apiClient.get('/books')
}

export function fetchBookTypes() {
  // The create-book dialog uses the active backend types so the UI stays in sync with the API.
  return apiClient.get('/books/types')
}

export function fetchBookById(bookId) {
  // Lightweight fallback used by BookView when a direct book URL is not found in the shared list.
  return apiClient.get(`/books/${bookId}`)
}

export function createBookRequest(payload) {
  // Create a new user-owned book and return the sidebar-ready metadata row.
  return apiClient.post('/books', payload)
}
