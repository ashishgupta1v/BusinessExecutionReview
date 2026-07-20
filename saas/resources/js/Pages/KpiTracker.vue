<script setup>
import { ref, reactive, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import VueApexCharts from 'vue3-apexcharts'

/**
 * KPI Tracker (Phase 1). Props from KpiController@index:
 *   kpis: [{ id, name, unit, direction, target, entries: [{ iso_week, actual }] }]
 *   currentWeek: "2026-W30"
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
    chart: { type: 'area', sparkline: { enabled: false }, toolbar: { show: false }, animations: { easing: 'easeinout' } },
    stroke: { curve: 'smooth', width: 2.5 },
    fill: { type: 'gradient', gradient: { opacityFrom: 0.25, opacityTo: 0 } },
    colors: ['#4f46e5'],
    dataLabels: { enabled: false },
    xaxis: { categories: k.entries.map(e => e.iso_week.slice(-3)), labels: { style: { fontSize: '10px' } } },
    yaxis: { labels: { formatter: v => k.unit + Math.round(v) } },
    annotations: { yaxis: [{ y: k.target, borderColor: '#cbd5e1', strokeDashArray: 4, label: { text: 'target', style: { fontSize: '9px' } } }] },
    grid: { borderColor: '#f1f5f9' },
    tooltip: { y: { formatter: v => k.unit + v } },
  }
}
const series = k => [{ name: k.name, data: k.entries.map(e => Number(e.actual)) }]
const onTarget = computed(() => props.kpis.filter(k => { const vr = variance(k); return vr?.good }).length)
</script>

<template>
  <div class="kpi">
    <header class="head">
      <div><h1>KPI Tracker</h1><p class="sub">{{ currentWeek }} · enter this week's actuals</p><span class="cadence">Weekly cadence</span></div>
      <span class="pill indigo">{{ onTarget }}/{{ kpis.length }} on target</span>
    </header>

    <section v-for="k in kpis" :key="k.id" class="card">
      <div class="row">
        <div><b>{{ k.name }}</b> <small>target {{ k.unit }}{{ k.target }} · {{ k.direction==='higher_better'?'higher better':'lower better' }}</small></div>
      </div>
      <div class="line">
        <span class="unit">{{ k.unit }}</span>
        <input class="act" type="number" v-model="actuals[k.id]" @change="saveActual(k)" placeholder="actual" />
        <span v-if="variance(k)" class="pill" :class="variance(k).good ? 'green':'rose'">
          {{ variance(k).v>=0?'+':'' }}{{ variance(k).v }} ({{ variance(k).pct>=0?'+':'' }}{{ variance(k).pct }}%)
        </span>
      </div>
      <VueApexCharts v-if="k.entries?.length" type="area" height="120" :options="chartOptions(k)" :series="series(k)" />
    </section>
  </div>
</template>

<style scoped>
.kpi{max-width:760px;margin:0 auto;padding:20px}
.head{display:flex;justify-content:space-between;align-items:flex-start}
h1{font-size:23px;font-weight:800}.sub{color:#64748b;font-size:13px}
.cadence{font-size:10px;font-weight:700;text-transform:uppercase;color:#4f46e5;background:#eef2ff;padding:3px 9px;border-radius:999px;display:inline-block;margin-top:6px}
.card{background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:16px;margin-top:16px;box-shadow:0 1px 3px rgba(15,23,42,.08)}
small{color:#94a3b8}
.line{display:flex;gap:10px;align-items:center;margin-top:10px;flex-wrap:wrap}
.unit{color:#94a3b8}.act{width:110px;border:1px solid #e2e8f0;border-radius:10px;padding:9px}
.pill{font-size:11px;font-weight:600;padding:3px 9px;border-radius:999px}
.pill.green{background:#d1fae5;color:#059669}.pill.rose{background:#ffe4e6;color:#e11d48}.pill.indigo{background:#eef2ff;color:#4f46e5}
</style>
