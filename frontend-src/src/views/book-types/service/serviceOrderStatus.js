export const SERVICE_ORDER_STATUSES = ['received', 'working', 'ready', 'delivered']

const SERVICE_ORDER_STATUS_META = {
  received: {
    emoji: '🔴',
    labelKey: 'service.orderStatusLabels.received',
    textClass: 'text-red',
  },
  working: {
    emoji: '🟠',
    labelKey: 'service.orderStatusLabels.working',
    textClass: 'text-orange',
  },
  ready: {
    emoji: '🟢',
    labelKey: 'service.orderStatusLabels.ready',
    textClass: 'text-green',
  },
  delivered: {
    emoji: '✔️',
    labelKey: 'service.orderStatusLabels.delivered',
    textClass: 'text-green',
  },
}

export function getServiceOrderStatusMeta(status) {
  const normalizedStatus = String(status ?? '').trim()

  return SERVICE_ORDER_STATUS_META[normalizedStatus] ?? SERVICE_ORDER_STATUS_META.received
}

export function getServiceOrderStatusOptions(currentStatus) {
  return SERVICE_ORDER_STATUSES
    .filter((status) => status !== String(currentStatus ?? '').trim())
    .map((status) => ({
      value: status,
      ...getServiceOrderStatusMeta(status),
    }))
}
