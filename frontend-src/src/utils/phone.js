export function normalizeInternationalPhone(value) {
  const phone = String(value ?? '').trim()

  if (phone === '' || /\p{L}/u.test(phone)) {
    return null
  }

  const hasLeadingPlus = phone.startsWith('+')
  const digits = phone.replace(/\D+/g, '')

  if (digits === '') {
    return null
  }

  return hasLeadingPlus ? `+${digits}` : digits
}
