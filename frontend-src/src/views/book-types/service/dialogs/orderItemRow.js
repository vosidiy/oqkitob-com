let nextRowId = 1

export const SERVICE_UNIT_OPTIONS = Object.freeze([
  {
    value: 'qty',
    labelKey: 'service.units.qty',
  },
  {
    value: 'm2',
    labelKey: 'service.units.m2',
  },
  {
    value: 'kg',
    labelKey: 'service.units.kg',
  },
])

export function createServiceOrderItemRow(overrides = {}) {
  return {
    _key: `service-order-item-${nextRowId++}`,
    object_name: '',
    service_type_id: '',
    quantity: '1',
    unit_code: 'qty',
    unit_price: '0',
    note: '',
    ...overrides,
  }
}
