import { apiClient } from '@/api/client'

export function fetchBooksList() {
  // Shared metadata list used by AppLayout and as the first lookup source for BookView.
  return apiClient.get('/books')
}

export function fetchArchivedBooksList() {
  return apiClient.get('/books/archived')
}

export function fetchBookTypes() {
  // The create-book dialog uses the active backend types so the UI stays in sync with the API.
  return apiClient.get('/books/types')
}

export function fetchBookById(bookId) {
  // Fallback-only endpoint used after BookView has already tried the warmed
  // sidebar/store metadata path for direct URLs or stale local cache cases.
  return apiClient.get(`/books/${bookId}`)
}

export function createBookRequest(payload) {
  // Create a new user-owned book and return the sidebar-ready metadata row.
  return apiClient.post('/books', payload)
}

export function updateBookRequest(bookId, payload) {
  // Settings dialog updates mutable book metadata and top-level money settings.
  return apiClient.put(`/books/${bookId}`, payload)
}

export function archiveBookRequest(bookId) {
  return apiClient.post(`/books/${bookId}/archive`)
}

export function restoreBookRequest(bookId) {
  return apiClient.post(`/books/${bookId}/restore`)
}

export function deleteBookRequest(bookId) {
  return apiClient.delete(`/books/${bookId}`)
}
