const DEFAULT_MONEY_DISPLAY_SETTINGS = Object.freeze({
  thousand_separator: 'comma',
  show_cents: true,
})

const LOCALE_BY_SEPARATOR = Object.freeze({
  comma: 'en-US',
  dot: 'de-DE',
  space: 'fr-FR',
})

const formatterCache = new Map()

function normalizeMoneyDisplaySettings(settings) {
  const thousandSeparator = ['comma', 'dot', 'space'].includes(settings?.thousand_separator)
    ? settings.thousand_separator
    : DEFAULT_MONEY_DISPLAY_SETTINGS.thousand_separator

  return {
    thousand_separator: thousandSeparator,
    show_cents: settings?.show_cents === false ? false : DEFAULT_MONEY_DISPLAY_SETTINGS.show_cents,
  }
}

function getFormatter(settings) {
  const locale = LOCALE_BY_SEPARATOR[settings.thousand_separator] ?? LOCALE_BY_SEPARATOR.comma
  const fractionDigits = settings.show_cents ? 2 : 0
  const cacheKey = `${locale}:${fractionDigits}`

  if (!formatterCache.has(cacheKey)) {
    formatterCache.set(cacheKey, new Intl.NumberFormat(locale, {
      minimumFractionDigits: fractionDigits,
      maximumFractionDigits: fractionDigits,
    }))
  }

  return formatterCache.get(cacheKey)
}

function parseMoneyValue(value) {
  const parsedValue = typeof value === 'number'
    ? value
    : Number.parseFloat(String(value ?? '').trim())

  return Number.isFinite(parsedValue) ? parsedValue : 0
}

export function formatMoneyByBookSettings(value, book) {
  const settings = normalizeMoneyDisplaySettings(book?.settings?.money_display ?? null)
  let formattedValue = getFormatter(settings).format(parseMoneyValue(value))

  if (settings.thousand_separator === 'space') {
    formattedValue = formattedValue.replace(/\u00A0|\u202F/g, ' ')
  }

  return formattedValue
}
