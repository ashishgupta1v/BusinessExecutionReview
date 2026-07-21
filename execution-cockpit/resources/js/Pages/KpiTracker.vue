<script setup>
import AppLayout from '../Layouts/AppLayout.vue'
defineOptions({ layout: AppLayout })
import { ref, reactive, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import VueApexCharts from 'vue3-apexcharts'

/**
 * KPI Tracker — Antigravity Dark Glassmorphism UI.
 */
const props = defineProps({
  kpis: { type: Array, default: () => [] },
  currentWeek: { type: String, required: true },
})

const actuals = reactive({})
props.kpis.forEach(k => {
  const cur = k.entries?.find(e => e.iso_week === props.currentWeek)
  actuals[k.id] = cur?.actual ?? ''
})

function variance(k) {
  const a = Number(actuals[k.id]); if (actuals[k.id] === '' || isNaN(a)) return null
  const v = a - k.target
  return { v, pct: k.target ? Math.round(v / k.target * 100) : 0, good: k.direction === 'higher_better' ? a >= k.target : a <= k.target }
}
function saveActual(k) {
  router.post(route('kpi.entry.store'), { kpi_id: k.id, iso_week: props.currentWeek, actual: actuals[k.id] }, { preserveScroll: true, preserveState: true })
}
function chartOptions(k) {
  return {
    chart: { 
      type: 'area', 
      sparkline: { enabled: false }, 
      toolbar: { show: false }, 
      animations: { easing: 'easeinout' },
      background: 'transparent',
      foreColor: '#94a3b8'
    },
    theme: { mode: 'dark' },
    stroke: { curve: 'smooth', width: 2.5 },
    fill: { 
      type: 'gradient', 
      gradient: { 
        shadeIntensity: 1, 
        opacityFrom: 0.45, 
        opacityTo: 0.05, 
        stops: [0, 90, 100] 
      } 
    },
    colors: ['#6366f1'],
    dataLabels: { enabled: false },
    xaxis: { 
      categories: k.entries.map(e => e.iso_week.slice(-3)), 
      labels: { style: { colors: '#94a3b8', fontSize: '11px' } },
      axisBorder: { show: false },
      axisTicks: { show: false }
    },
    yaxis: { 
      labels: { style: { colors: '#94a3b8' }, formatter: v => k.unit + Math.round(v) } 
    },
    annotations: { 
      yaxis: [{ 
        y: k.target, 
        borderColor: '#818cf8', 
        strokeDashArray: 4, 
        label: { text: 'Target: ' + k.unit + k.target, style: { color: '#ffffff', background: '#4338ca', fontSize: '10px' } } 
      }] 
    },
    grid: { borderColor: 'rgba(255, 255, 255, 0.06)' },
    tooltip: { 
      theme: 'dark',
      y: { formatter: v => k.unit + v } 
    },
  }
}
const series = k => [{ name: k.name, data: k.entries.map(e => Number(e.actual)) }]
const onTarget = computed(() => props.kpis.filter(k => { const vr = variance(k); return vr?.good }).length)
</script>

<template>
  <div class="kpi-page">
    <header class="kpi-header">
      <div>
        <div class="badge-tag">WEEKLY CADENCE</div>
        <h1 class="page-title">KPI Tracker</h1>
        <p class="page-sub">{{ currentWeek }} · enter this week's actuals</p>
      </div>
      <div class="status-badge" :class="onTarget >= kpis.length ? 'status-good' : 'status-amber'">
        <span class="dot"></span>
        <span>{{ onTarget }}/{{ kpis.length }} on target</span>
      </div>
    </header>

    <!-- KPI Cards -->
    <div class="kpi-grid">
      <section v-for="k in kpis" :key="k.id" class="glass-card kpi-card">
        <div class="kpi-card-header">
          <div class="kpi-info">
            <h3 class="kpi-name">{{ k.name }}</h3>
            <span class="kpi-meta">
              target: <b>{{ k.unit }}{{ k.target }}</b> · {{ k.direction==='higher_better'?'higher better':'lower better' }}
            </span>
          </div>
        </div>

        <div class="kpi-input-row">
          <div class="input-wrapper">
            <span class="unit-symbol">{{ k.unit }}</span>
            <input 
              class="glass-input actual-input" 
              type="number" 
              v-model="actuals[k.id]" 
              @change="saveActual(k)" 
              placeholder="Enter actual..." 
            />
          </div>

          <span v-if="variance(k)" class="variance-pill" :class="variance(k).good ? 'pill-green' : 'pill-rose'">
            {{ variance(k).v>=0 ? '+' : '' }}{{ variance(k).v }} ({{ variance(k).pct>=0 ? '+' : '' }}{{ variance(k).pct }}%)
          </span>
        </div>

        <div class="chart-wrapper" v-if="k.entries?.length">
          <VueApexCharts type="area" height="150" :options="chartOptions(k)" :series="series(k)" />
        </div>
      </section>
    </div>
  </div>
</template>

<style scoped>
.kpi-page {
  max-width: 860px;
  margin: 0 auto;
  padding: 32px 20px;
}

.kpi-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 24px;
}
.badge-tag {
  font-size: 10px;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: #818cf8;
  background: rgba(99, 102, 241, 0.15);
  border: 1px solid rgba(99, 102, 241, 0.3);
  padding: 4px 10px;
  border-radius: 999px;
  display: inline-block;
  margin-bottom: 8px;
}
.page-title {
  font-size: 28px;
  font-weight: 900;
  color: #ffffff;
  letter-spacing: -0.02em;
}
.page-sub {
  color: #94a3b8;
  font-size: 14px;
  margin-top: 2px;
}

.status-badge {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 6px 14px;
  border-radius: 999px;
  font-size: 13px;
  font-weight: 700;
  backdrop-filter: blur(10px);
}
.status-badge .dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
}
.status-good {
  background: rgba(16, 185, 129, 0.15);
  color: #34d399;
  border: 1px solid rgba(16, 185, 129, 0.3);
}
.status-good .dot {
  background: #34d399;
  box-shadow: 0 0 10px #34d399;
}
.status-amber {
  background: rgba(245, 158, 11, 0.15);
  color: #fbbf24;
  border: 1px solid rgba(245, 158, 11, 0.3);
}
.status-amber .dot {
  background: #fbbf24;
  box-shadow: 0 0 10px #fbbf24;
}

.kpi-grid {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.kpi-card {
  padding: 22px;
}

.kpi-card-header {
  margin-bottom: 16px;
}
.kpi-name {
  font-size: 18px;
  font-weight: 800;
  color: #ffffff;
}
.kpi-meta {
  font-size: 12px;
  color: #94a3b8;
}
.kpi-meta b {
  color: #818cf8;
}

.kpi-input-row {
  display: flex;
  align-items: center;
  gap: 14px;
  margin-bottom: 16px;
  flex-wrap: wrap;
}
.input-wrapper {
  display: flex;
  align-items: center;
  position: relative;
}
.unit-symbol {
  position: absolute;
  left: 12px;
  color: #94a3b8;
  font-weight: 600;
}
.actual-input {
  padding-left: 28px !important;
  width: 140px;
  font-weight: 700;
  font-size: 15px;
}

.variance-pill {
  font-size: 12px;
  font-weight: 700;
  padding: 5px 12px;
  border-radius: 999px;
}
.pill-green {
  background: rgba(16, 185, 129, 0.15);
  color: #34d399;
  border: 1px solid rgba(16, 185, 129, 0.3);
}
.pill-rose {
  background: rgba(244, 63, 94, 0.15);
  color: #fb7185;
  border: 1px solid rgba(244, 63, 94, 0.3);
}

.chart-wrapper {
  margin-top: 10px;
  padding-top: 10px;
  border-top: 1px solid rgba(255, 255, 255, 0.06);
}
</style>
