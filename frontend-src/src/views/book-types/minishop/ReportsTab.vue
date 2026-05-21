<template>
  <section class="reports-tab flex-1 overflow-y-scroll p-4 bg-lower">
    <div class="reports-hero card mb-4">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-start gap-3 mobile:flex-col">
          <div>
            <p class="reports-eyebrow mb-2">{{ $t('minishop.reports.preview') }}</p>
            <h2 class="reports-title mb-2">{{ $t('minishop.reports.title') }}</h2>
            <p class="text-secondary mb-0">
              {{ $t('minishop.reports.subtitle') }}
            </p>
          </div>

          <div class="reports-badge">
            {{ $t('minishop.reports.demoData') }}
          </div>
        </div>
      </div>
    </div>

    <div class="reports-kpi-grid mb-4">
      <article
        v-for="kpi in reportsOverviewMockData.kpis"
        :key="kpi.id"
        class="reports-kpi-card card"
        :class="`reports-kpi-card--${kpi.tone}`"
      >
        <div class="card-body">
          <p class="text-secondary mb-2">{{ $t(`minishop.reports.kpis.${kpi.id}`) }}</p>
          <h3 class="reports-kpi-value mb-2">{{ formatMetricValue(kpi) }}</h3>
          <div class="d-flex justify-content-between gap-3 align-items-center">
            <strong :class="toneClassByKey[kpi.tone]">{{ kpi.change }}</strong>
            <span class="text-secondary text-sm text-right">{{ $t(`minishop.reports.notes.${kpi.noteKey}`) }}</span>
          </div>
        </div>
      </article>
    </div>

    <div class="reports-chart-layout">
      <article class="card reports-chart-card reports-chart-card--wide">
        <div class="card-body">
          <div class="d-flex justify-content-between gap-3 align-items-start mb-4 mobile:flex-col">
            <div>
              <h3 class="h5 mb-1">{{ $t('minishop.reports.salesTrend') }}</h3>
              <p class="text-secondary mb-0">
                {{ $t('minishop.reports.salesTrendSubtitle') }}
              </p>
            </div>
            <div class="reports-inline-metric">
              <span class="reports-inline-metric__label">{{ $t('minishop.reports.peakDay') }}</span>
              <strong>{{ formatMoneyValue(1845000) }}</strong>
            </div>
          </div>
          <div class="reports-canvas-shell reports-canvas-shell--tall">
            <canvas ref="salesTrendCanvas"></canvas>
          </div>
        </div>
      </article>

      <article class="card reports-chart-card">
        <div class="card-body">
          <div class="mb-4">
            <h3 class="h5 mb-1">{{ $t('minishop.reports.paymentStatus') }}</h3>
            <p class="text-secondary mb-0">
              {{ $t('minishop.reports.paymentStatusSubtitle') }}
            </p>
          </div>
          <div class="reports-canvas-shell reports-canvas-shell--compact">
            <canvas ref="paymentStatusCanvas"></canvas>
          </div>
        </div>
      </article>

      <article class="card reports-chart-card">
        <div class="card-body">
          <div class="mb-4">
            <h3 class="h5 mb-1">{{ $t('minishop.reports.topProducts') }}</h3>
            <p class="text-secondary mb-0">
              {{ $t('minishop.reports.topProductsSubtitle') }}
            </p>
          </div>
          <div class="reports-canvas-shell reports-canvas-shell--compact">
            <canvas ref="topProductsCanvas"></canvas>
          </div>
        </div>
      </article>
    </div>
  </section>
</template>

<script setup>
import { nextTick, onBeforeUnmount, onMounted, ref } from 'vue'
import { Chart, registerables } from 'chart.js'
import { useI18n } from 'vue-i18n'
import { reportsOverviewMockData } from '@/views/book-types/minishop/reportsMockData'

Chart.register(...registerables)
const { t } = useI18n()

const salesTrendCanvas = ref(null)
const paymentStatusCanvas = ref(null)
const topProductsCanvas = ref(null)

const toneClassByKey = {
  blue: 'reports-kpi-tone reports-kpi-tone--blue',
  green: 'reports-kpi-tone reports-kpi-tone--green',
  orange: 'reports-kpi-tone reports-kpi-tone--orange',
  red: 'reports-kpi-tone reports-kpi-tone--red',
}

let salesTrendChart = null
let paymentStatusChart = null
let topProductsChart = null

onMounted(async () => {
  await nextTick()
  createCharts()
})

onBeforeUnmount(() => {
  destroyCharts()
})

function createCharts() {
  destroyCharts()

  if (salesTrendCanvas.value) {
    salesTrendChart = new Chart(salesTrendCanvas.value, {
      type: 'line',
      data: {
        labels: reportsOverviewMockData.salesTrend.labels,
        datasets: [
          {
            label: t('minishop.reports.chartSalesLabel'),
            data: reportsOverviewMockData.salesTrend.totals,
            borderColor: '#0f766e',
            backgroundColor: 'rgba(15, 118, 110, 0.14)',
            fill: true,
            tension: 0.35,
            pointRadius: 3,
            pointHoverRadius: 5,
            pointBackgroundColor: '#0f766e',
          },
        ],
      },
      options: makeBaseChartOptions({
        plugins: {
          legend: { display: false },
          tooltip: {
            callbacks: {
              label(context) {
                return ` ${t('minishop.reports.chartTooltipSales', { amount: formatMoneyValue(context.parsed.y) })}`
              },
            },
          },
        },
        scales: {
          x: {
            grid: { display: false },
            ticks: {
              color: '#6b7280',
              maxRotation: 0,
            },
          },
          y: {
            beginAtZero: true,
            ticks: {
              color: '#6b7280',
              callback(value) {
                return formatCompactMoneyValue(value)
              },
            },
            grid: {
              color: 'rgba(148, 163, 184, 0.18)',
            },
          },
        },
      }),
    })
  }

  if (paymentStatusCanvas.value) {
    paymentStatusChart = new Chart(paymentStatusCanvas.value, {
      type: 'doughnut',
      data: {
        labels: [
          t('minishop.reports.paymentLabels.paid'),
          t('minishop.reports.paymentLabels.partial'),
          t('minishop.reports.paymentLabels.unpaid'),
        ],
        datasets: [
          {
            data: reportsOverviewMockData.paymentStatus.values,
            backgroundColor: ['#15803d', '#ea580c', '#dc2626'],
            borderColor: '#ffffff',
            borderWidth: 4,
            hoverOffset: 8,
          },
        ],
      },
      options: makeBaseChartOptions({
        cutout: '62%',
        plugins: {
          legend: {
            position: 'bottom',
            labels: {
              boxWidth: 14,
              color: '#374151',
              usePointStyle: true,
              pointStyle: 'circle',
            },
          },
          tooltip: {
            callbacks: {
              label(context) {
                return ` ${t('minishop.reports.chartTooltipPayment', { label: context.label, value: context.parsed })}`
              },
            },
          },
        },
      }),
    })
  }

  if (topProductsCanvas.value) {
    topProductsChart = new Chart(topProductsCanvas.value, {
      type: 'bar',
      data: {
        labels: reportsOverviewMockData.topProducts.labels,
        datasets: [
          {
            label: 'Revenue',
            data: reportsOverviewMockData.topProducts.totals,
            backgroundColor: ['#0f766e', '#14b8a6', '#f59e0b', '#fb7185', '#6366f1'],
            borderRadius: 10,
            borderSkipped: false,
          },
        ],
      },
      options: makeBaseChartOptions({
        indexAxis: 'y',
        plugins: {
          legend: { display: false },
          tooltip: {
            callbacks: {
              label(context) {
                return ` Revenue: ${formatMoneyValue(context.parsed.x)}`
              },
            },
          },
        },
        scales: {
          x: {
            beginAtZero: true,
            ticks: {
              color: '#6b7280',
              callback(value) {
                return formatCompactMoneyValue(value)
              },
            },
            grid: {
              color: 'rgba(148, 163, 184, 0.18)',
            },
          },
          y: {
            ticks: {
              color: '#374151',
            },
            grid: { display: false },
          },
        },
      }),
    })
  }
}

function destroyCharts() {
  salesTrendChart?.destroy()
  paymentStatusChart?.destroy()
  topProductsChart?.destroy()
  salesTrendChart = null
  paymentStatusChart = null
  topProductsChart = null
}

function makeBaseChartOptions(overrides = {}) {
  return {
    responsive: true,
    maintainAspectRatio: false,
    animation: {
      duration: 650,
      easing: 'easeOutQuart',
    },
    plugins: {
      legend: {
        labels: {
          color: '#374151',
        },
      },
    },
    ...overrides,
  }
}

function formatMetricValue(kpi) {
  if (kpi.type === 'currency') {
    return formatMoneyValue(kpi.value)
  }

  return formatIntegerValue(kpi.value)
}

function formatMoneyValue(value) {
  const amount = Number(value ?? 0)

  return new Intl.NumberFormat('en-US', {
    maximumFractionDigits: 0,
  }).format(amount)
}

function formatCompactMoneyValue(value) {
  const amount = Number(value ?? 0)

  return new Intl.NumberFormat('en-US', {
    notation: 'compact',
    maximumFractionDigits: 1,
  }).format(amount)
}

function formatIntegerValue(value) {
  const amount = Number(value ?? 0)

  return new Intl.NumberFormat('en-US', {
    maximumFractionDigits: 0,
  }).format(amount)
}
</script>

<style scoped>
.reports-tab {
  background:
    radial-gradient(circle at top left, rgba(20, 184, 166, 0.08), transparent 26rem),
    radial-gradient(circle at top right, rgba(245, 158, 11, 0.08), transparent 24rem);
}

.reports-hero {
  border: 1px solid rgba(15, 118, 110, 0.12);
  background:
    linear-gradient(145deg, rgba(255, 255, 255, 0.98), rgba(248, 250, 252, 0.96)),
    linear-gradient(135deg, rgba(20, 184, 166, 0.08), rgba(245, 158, 11, 0.04));
}

.reports-eyebrow {
  color: #0f766e;
  font-size: 0.76rem;
  font-weight: 700;
  letter-spacing: 0.08em;
  text-transform: uppercase;
}

.reports-title {
  font-size: 1.75rem;
  line-height: 1.1;
}

.reports-badge {
  border: 1px solid rgba(15, 118, 110, 0.12);
  border-radius: 999px;
  background: rgba(255, 255, 255, 0.9);
  color: #0f766e;
  font-size: 0.82rem;
  font-weight: 600;
  padding: 0.65rem 0.9rem;
  white-space: nowrap;
}

.reports-kpi-grid {
  display: grid;
  gap: 1rem;
  grid-template-columns: repeat(4, minmax(0, 1fr));
}

.reports-kpi-card {
  overflow: hidden;
  position: relative;
}

.reports-kpi-card::before {
  content: '';
  position: absolute;
  inset: 0 auto 0 0;
  width: 0.35rem;
}

.reports-kpi-card--green::before {
  background: #16a34a;
}

.reports-kpi-card--blue::before {
  background: #2563eb;
}

.reports-kpi-card--orange::before {
  background: #ea580c;
}

.reports-kpi-card--red::before {
  background: #dc2626;
}

.reports-kpi-value {
  font-size: 1.8rem;
  line-height: 1.1;
}

.reports-kpi-tone {
  font-size: 0.95rem;
}

.reports-kpi-tone--green {
  color: #15803d;
}

.reports-kpi-tone--blue {
  color: #1d4ed8;
}

.reports-kpi-tone--orange {
  color: #c2410c;
}

.reports-kpi-tone--red {
  color: #b91c1c;
}

.reports-chart-layout {
  display: grid;
  gap: 1rem;
  grid-template-columns: repeat(2, minmax(0, 1fr));
}

.reports-chart-card {
  min-width: 0;
}

.reports-chart-card--wide {
  grid-column: 1 / -1;
}

.reports-inline-metric {
  border: 1px solid rgba(148, 163, 184, 0.18);
  border-radius: 1rem;
  background: rgba(248, 250, 252, 0.88);
  min-width: 8.5rem;
  padding: 0.85rem 1rem;
  text-align: right;
}

.reports-inline-metric__label {
  color: #6b7280;
  display: block;
  font-size: 0.78rem;
  margin-bottom: 0.25rem;
  text-transform: uppercase;
  letter-spacing: 0.06em;
}

.reports-canvas-shell {
  position: relative;
  width: 100%;
}

.reports-canvas-shell--tall {
  height: 20rem;
}

.reports-canvas-shell--compact {
  height: 18rem;
}

@media (max-width: 960px) {
  .reports-kpi-grid,
  .reports-chart-layout {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}

@media (max-width: 640px) {
  .reports-kpi-grid,
  .reports-chart-layout {
    grid-template-columns: minmax(0, 1fr);
  }

  .reports-title {
    font-size: 1.4rem;
  }

  .reports-badge,
  .reports-inline-metric {
    white-space: normal;
    width: 100%;
  }

  .reports-canvas-shell--tall,
  .reports-canvas-shell--compact {
    height: 16rem;
  }
}
</style>
