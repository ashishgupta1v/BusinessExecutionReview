<script setup>
import { computed } from 'vue'

/** Overview dashboard. Props from OverviewController@index. */
const props = defineProps({
  streak: { type: Object, default: () => ({ current: 0, longest: 0 }) },
  dcrThisMonth: { type: Number, default: 0 },
  openFeedback: { type: Number, default: 0 },
  reviewsDone: { type: Number, default: 0 },
  filled30: { type: Number, default: 0 },
  rule5Avg: { type: Number, default: 0 },
  kpiStatus: { type: Array, default: () => [] },
  kpiOnTarget: { type: Number, default: 0 },
  feedback: { type: Object, default: () => ({ total: 0, open: 0, done: 0 }) },
  heatmap: { type: Array, default: () => [] },
})

const comp30 = computed(() => Math.round(props.filled30 / 30 * 100))
const closedPct = computed(() => props.feedback.total ? Math.round(props.feedback.done / props.feedback.total * 100) : 0)
const cols = computed(() => { const o = []; for (let w = 0; w < Math.ceil(props.heatmap.length / 7); w++) o.push(props.heatmap.slice(w * 7, w * 7 + 7)); return o })
const dcrDonut = computed(() => `conic-gradient(#4f46e5 0 ${comp30.value}%, #e2e8f0 ${comp30.value}% 100%)`)
const fbDonut = computed(() => `conic-gradient(#059669 0 ${closedPct.value}%, #d97706 ${closedPct.value}% 100%)`)
const barPct = k => k.target ? Math.min(100, Math.round(k.actual / k.target * 100)) : 0
</script>

<template>
  <div class="ov">
    <header class="head"><div><h1>Overview</h1><p class="sub">your whole execution system at a glance</p><span class="cadence">Live</span></div></header>

    <div class="stats">
      <div class="stat"><div class="v i">{{ streak.current }}</div><div class="l">current streak 🔥</div></div>
      <div class="stat"><div class="v">{{ streak.longest }}</div><div class="l">longest streak</div></div>
      <div class="stat"><div class="v g">{{ dcrThisMonth }}</div><div class="l">DCRs this month</div></div>
      <div class="stat"><div class="v a">{{ openFeedback }}</div><div class="l">open feedback</div></div>
    </div>

    <div class="grid2">
      <section class="card">
        <h3>DCR consistency <small>last 30 days</small></h3>
        <div class="donutwrap">
          <div class="donut" :style="{ background: dcrDonut }"><div class="hole"><b>{{ comp30 }}%</b><span>days filed</span></div></div>
          <div class="leg">
            <div><i style="background:#4f46e5"></i>{{ filled30 }} of 30 filed</div>
            <div><i style="background:#e2e8f0"></i>{{ 30 - filled30 }} missed</div>
            <div><i style="background:#6366f1"></i>Rule-of-5 avg {{ rule5Avg }}/5</div>
          </div>
        </div>
      </section>
      <section class="card">
        <h3>Feedback status <small>{{ feedback.total }} total</small></h3>
        <div class="donutwrap">
          <div class="donut" :style="{ background: fbDonut }"><div class="hole"><b>{{ closedPct }}%</b><span>closed</span></div></div>
          <div class="leg">
            <div><i style="background:#059669"></i>Done · {{ feedback.done }}</div>
            <div><i style="background:#d97706"></i>Pending · {{ feedback.open }}</div>
          </div>
        </div>
      </section>
    </div>

    <section class="card">
      <h3>KPI status — latest vs target <span class="pill" :class="kpiOnTarget >= kpiStatus.length - 1 ? 'green' : 'amber'">{{ kpiOnTarget }}/{{ kpiStatus.length }} on target</span></h3>
      <div v-for="k in kpiStatus" :key="k.name" class="hbar">
        <div class="hbar-l"><span>{{ k.name }}</span><span>{{ k.unit }}{{ k.actual }} / {{ k.unit }}{{ k.target }}</span></div>
        <div class="hbar-t"><div class="hbar-f" :style="{ width: barPct(k) + '%', background: k.good ? '#059669' : '#f59e0b' }"></div></div>
      </div>
    </section>

    <section class="card">
      <h3>Day Close heatmap <small>18 weeks</small></h3>
      <div class="heat">
        <div v-for="(col,ci) in cols" :key="ci" class="wk">
          <div v-for="c in col" :key="c.date" class="d" :class="c.level ? 'l'+c.level : ''" :title="c.date"></div>
        </div>
      </div>
      <p class="note">A broken streak is a reset, not a failure. File today to start again.</p>
    </section>
  </div>
</template>

<style scoped>
.ov{max-width:820px;margin:0 auto;padding:20px}
h1{font-size:24px;font-weight:800}.sub{color:#64748b;font-size:13px}
.cadence{font-size:10px;font-weight:700;text-transform:uppercase;color:#4f46e5;background:#eef2ff;padding:3px 9px;border-radius:999px;display:inline-block;margin-top:6px}
.stats{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-top:16px}
@media(min-width:560px){.stats{grid-template-columns:repeat(4,1fr)}}
.stat{background:#fff;border:1px solid #e2e8f0;border-radius:16px;box-shadow:0 1px 3px rgba(15,23,42,.08);padding:15px;text-align:center}
.stat .v{font-size:26px;font-weight:800}.stat .v.i{color:#4f46e5}.stat .v.g{color:#059669}.stat .v.a{color:#d97706}
.stat .l{font-size:11px;color:#64748b;margin-top:2px}
.grid2{display:grid;grid-template-columns:1fr;gap:16px;margin-top:16px}
@media(min-width:640px){.grid2{grid-template-columns:1fr 1fr}}
.card{background:#fff;border:1px solid #e2e8f0;border-radius:16px;box-shadow:0 1px 3px rgba(15,23,42,.08);padding:16px;margin-top:16px}
h3{font-size:14px;margin-bottom:10px;display:flex;justify-content:space-between;align-items:center}h3 small{color:#94a3b8;font-weight:400}
.donutwrap{display:flex;align-items:center;gap:16px}
.donut{width:118px;height:118px;border-radius:50%;display:grid;place-items:center;flex-shrink:0}
.hole{width:84px;height:84px;background:#fff;border-radius:50%;display:grid;place-items:center;text-align:center}
.hole b{font-size:22px}.hole span{font-size:10px;color:#64748b}
.leg div{display:flex;align-items:center;gap:7px;font-size:13px;margin-bottom:6px}.leg i{width:10px;height:10px;border-radius:3px}
.hbar{margin-bottom:12px}.hbar-l{display:flex;justify-content:space-between;font-size:13px;margin-bottom:4px}.hbar-l span:last-child{color:#94a3b8}
.hbar-t{height:10px;background:#f1f5f9;border-radius:99px;overflow:hidden}.hbar-f{height:100%;border-radius:99px;transition:width .8s cubic-bezier(.2,.8,.2,1)}
.heat{display:flex;gap:3px;overflow-x:auto;padding-bottom:4px}.heat .wk{display:flex;flex-direction:column;gap:3px}
.heat .d{width:15px;height:15px;border-radius:3px;background:#e2e8f0}
.heat .d.l1{background:#c7d2fe}.heat .d.l2{background:#a5b4fc}.heat .d.l3{background:#6366f1}.heat .d.l4{background:#4338ca}
.note{color:#94a3b8;font-size:12px;margin-top:8px}
.pill{font-size:11px;font-weight:600;padding:3px 9px;border-radius:999px}.pill.green{background:#d1fae5;color:#059669}.pill.amber{background:#fef3c7;color:#d97706}
</style>
