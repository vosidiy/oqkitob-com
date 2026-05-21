import { createI18n } from 'vue-i18n'
import { messages } from './messages'

export const DEFAULT_LOCALE = 'uz'
export const FALLBACK_LOCALE = 'en'
export const SUPPORTED_LOCALES = ['uz', 'en', 'ru']
export const LOCALE_STORAGE_KEY = 'oqkitob_locale'

function canUseStorage() {
  return typeof window !== 'undefined' && window.localStorage
}

export function isSupportedLocale(localeCode) {
  return SUPPORTED_LOCALES.includes(String(localeCode ?? '').trim())
}

export function resolveInitialLocale() {
  try {
    if (!canUseStorage()) {
      return DEFAULT_LOCALE
    }

    const storedLocale = window.localStorage.getItem(LOCALE_STORAGE_KEY)

    if (storedLocale == null || storedLocale.trim() === '') {
      return DEFAULT_LOCALE
    }

    return isSupportedLocale(storedLocale) ? storedLocale : FALLBACK_LOCALE
  } catch {
    return FALLBACK_LOCALE
  }
}

export function getCurrentLocale() {
  return i18n.global.locale.value
}

export function setLocale(localeCode) {
  const nextLocale = isSupportedLocale(localeCode) ? localeCode : FALLBACK_LOCALE
  i18n.global.locale.value = nextLocale

  try {
    if (canUseStorage()) {
      window.localStorage.setItem(LOCALE_STORAGE_KEY, nextLocale)
    }
  } catch {
    // Ignore persistence failures and keep the in-memory locale active.
  }

  return nextLocale
}

export const i18n = createI18n({
  legacy: false,
  globalInjection: true,
  locale: resolveInitialLocale(),
  fallbackLocale: FALLBACK_LOCALE,
  messages,
  missingWarn: false,
  fallbackWarn: false,
})
