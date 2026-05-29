function trimTrailingQuantityZeros(value) {
  return value.replace(/\.?0+$/, '')
}

export function formatQuantityDisplay(value) {
  const quantity = Number.parseFloat(String(value ?? 0))

  if (!Number.isFinite(quantity)) {
    return '0'
  }

  return trimTrailingQuantityZeros(quantity.toFixed(3))
}

export function normalizeQuantityInput(value, fallback = 0) {
  const parsedValue = Number.parseFloat(String(value ?? '').trim())
  const normalizedQuantity = Number.isFinite(parsedValue) && parsedValue >= 0
    ? parsedValue
    : fallback

  return trimTrailingQuantityZeros(normalizedQuantity.toFixed(3))
}
