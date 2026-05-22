const knownBookTypeKeys = new Set(['finance', 'minishop', 'notes', 'todo'])
const knownTodoPriorities = new Set(['high', 'low', 'medium'])
const knownPaymentStatuses = new Set(['paid', 'partial', 'unpaid'])

export function translateBookType(t, typeKey) {
  const normalizedKey = String(typeKey ?? '').trim().toLowerCase()
  return knownBookTypeKeys.has(normalizedKey) ? t(`bookTypes.${normalizedKey}`) : (typeKey || '-')
}

export function translateTodoPriority(t, priority) {
  const normalizedPriority = String(priority ?? '').trim().toLowerCase()
  return knownTodoPriorities.has(normalizedPriority)
    ? t(`todo.priorities.${normalizedPriority}`)
    : (priority || t('todo.priorities.medium'))
}

export function translatePaymentStatus(t, status) {
  const normalizedStatus = String(status ?? '').trim().toLowerCase()
  return knownPaymentStatuses.has(normalizedStatus)
    ? t(`minishop.paymentLabels.${normalizedStatus}`)
    : (status || '-')
}
