function getResponse(error) {
  if (!error || typeof error !== 'object' || !('response' in error)) {
    return null
  }

  return error.response && typeof error.response === 'object' ? error.response : null
}

export function getResponseStatus(error) {
  const response = getResponse(error)

  return typeof response?.status === 'number' ? response.status : null
}

export function isUnauthorizedError(error) {
  return getResponseStatus(error) === 401
}

export function isNotFoundError(error) {
  return getResponseStatus(error) === 404
}

export function getApiErrorMessage(error, fallbackMessage) {
  const response = getResponse(error)
  const message = response?.data?.message
  const nestedError = response?.data?.messages?.error

  if (typeof nestedError === 'string' && nestedError.trim() !== '') {
    return nestedError
  }

  return typeof message === 'string' && message.trim() !== '' ? message : fallbackMessage
}
