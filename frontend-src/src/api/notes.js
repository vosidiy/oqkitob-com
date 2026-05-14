import { apiClient } from '@/api/client'

export function fetchNotes(bookId) {
  return apiClient.get(`/books/${bookId}/notes`)
}

export function createNoteRequest(bookId, payload) {
  return apiClient.post(`/books/${bookId}/notes`, payload)
}

export function updateNoteRequest(bookId, noteId, payload) {
  return apiClient.put(`/books/${bookId}/notes/${noteId}`, payload)
}

export function pinNoteRequest(bookId, noteId) {
  return apiClient.post(`/books/${bookId}/notes/${noteId}/pin`)
}

export function unpinNoteRequest(bookId, noteId) {
  return apiClient.post(`/books/${bookId}/notes/${noteId}/unpin`)
}

export function archiveNoteRequest(bookId, noteId) {
  return apiClient.post(`/books/${bookId}/notes/${noteId}/archive`)
}

export function deleteNoteRequest(bookId, noteId) {
  return apiClient.delete(`/books/${bookId}/notes/${noteId}`)
}
