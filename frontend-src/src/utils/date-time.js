import { FALLBACK_LOCALE, getCurrentLocale } from '@/i18n'
import { messages } from '@/i18n/messages'

const DATE_TIME_PATTERN = /^(\d{4})-(\d{2})-(\d{2})(?:[ T](\d{2}):(\d{2})(?::(\d{2})(?:\.\d+)?)?)?(?:Z|[+-]\d{2}:?\d{2})?$/

function resolveLocale(locale) {
  const normalizedLocale = String(locale ?? '').trim()

  if (normalizedLocale !== '') {
    return normalizedLocale
  }

  const currentLocale = String(getCurrentLocale() ?? '').trim()

  return currentLocale !== '' ? currentLocale : FALLBACK_LOCALE
}

function getLocaleMessages(locale) {
  const normalizedLocale = String(locale ?? '').trim()
  const languageCode = normalizedLocale.split('-')[0]

  return messages[normalizedLocale] ?? messages[languageCode] ?? messages[FALLBACK_LOCALE]
}

function getMonthNames(locale) {
  const localeMonthNames = getLocaleMessages(locale)?.common?.dates?.months

  if (Array.isArray(localeMonthNames) && localeMonthNames.length === 12) {
    return localeMonthNames
  }

  return messages.en.common.dates.months
}

function parseDateValue(value) {
  const normalizedValue = String(value ?? '').trim()

  if (normalizedValue === '') {
    return null
  }

  const matches = DATE_TIME_PATTERN.exec(normalizedValue)

  if (matches == null) {
    return null
  }

  const [, yearValue, monthValue, dayValue, hourValue = '00', minuteValue = '00', secondValue = '00'] = matches
  const parsedValue = {
    year: Number(yearValue),
    month: Number(monthValue),
    day: Number(dayValue),
    hour: Number(hourValue),
    minute: Number(minuteValue),
    second: Number(secondValue),
  }

  const validationDate = new Date(Date.UTC(
    parsedValue.year,
    parsedValue.month - 1,
    parsedValue.day,
    parsedValue.hour,
    parsedValue.minute,
    parsedValue.second
  ))

  const isValidDate = validationDate.getUTCFullYear() === parsedValue.year
    && validationDate.getUTCMonth() === parsedValue.month - 1
    && validationDate.getUTCDate() === parsedValue.day
    && validationDate.getUTCHours() === parsedValue.hour
    && validationDate.getUTCMinutes() === parsedValue.minute
    && validationDate.getUTCSeconds() === parsedValue.second

  return isValidDate ? parsedValue : null
}

function formatTime(parsedDate) {
  return [
    String(parsedDate.hour).padStart(2, '0'),
    String(parsedDate.minute).padStart(2, '0'),
  ].join(':')
}

function formatWithMonthNames(value, type, options = {}) {
  const rawValue = String(value ?? '').trim()

  if (rawValue === '') {
    return '-'
  }

  const parsedDate = parseDateValue(rawValue)

  if (parsedDate == null) {
    return rawValue
  }

  const monthNames = getMonthNames(resolveLocale(options.locale))
  const formattedDate = `${parsedDate.day}-${monthNames[parsedDate.month - 1]}, ${parsedDate.year}`

  return type === 'date' ? formattedDate : `${formattedDate}, ${formatTime(parsedDate)}`
}

export function formatDateTime(value, options = {}) {
  return formatWithMonthNames(value, 'dateTime', options)
}

export function formatDate(value, options = {}) {
  return formatWithMonthNames(value, 'date', options)
}
