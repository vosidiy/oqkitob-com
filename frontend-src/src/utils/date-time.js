import { FALLBACK_LOCALE, getCurrentLocale } from '@/i18n'

const formatterCache = new Map()

function resolveLocale(locale) {
  const normalizedLocale = String(locale ?? '').trim()

  if (normalizedLocale !== '') {
    return normalizedLocale
  }

  const currentLocale = String(getCurrentLocale() ?? '').trim()

  return currentLocale !== '' ? currentLocale : FALLBACK_LOCALE
}

function normalizeDateInput(value) {
  const normalizedValue = String(value ?? '').trim()

  if (normalizedValue === '') {
    return ''
  }

  return normalizedValue.includes('T')
    ? normalizedValue
    : normalizedValue.replace(' ', 'T')
}

function parseDateValue(value) {
  const normalizedValue = normalizeDateInput(value)

  if (normalizedValue === '') {
    return null
  }

  const parsedDate = new Date(normalizedValue)

  return Number.isNaN(parsedDate.getTime()) ? null : parsedDate
}

function getFormatter(locale, type) {
  const cacheKey = `${locale}:${type}`

  if (!formatterCache.has(cacheKey)) {
    const formatterOptions = type === 'date'
      ? {
          year: 'numeric',
          month: 'long',
          day: 'numeric',
        }
      : {
          year: 'numeric',
          month: 'long',
          day: 'numeric',
          hour: '2-digit',
          minute: '2-digit',
          hour12: false,
        }

    formatterCache.set(cacheKey, new Intl.DateTimeFormat(locale, formatterOptions))
  }

  return formatterCache.get(cacheKey)
}

function formatWithFormatter(value, type, options = {}) {
  const rawValue = String(value ?? '').trim()

  if (rawValue === '') {
    return '-'
  }

  const parsedDate = parseDateValue(rawValue)

  if (parsedDate == null) {
    return rawValue
  }

  try {
    return getFormatter(resolveLocale(options.locale), type).format(parsedDate)
  } catch {
    return rawValue
  }
}

export function formatDateTime(value, options = {}) {
  return formatWithFormatter(value, 'dateTime', options)
}

export function formatDate(value, options = {}) {
  return formatWithFormatter(value, 'date', options)
}
