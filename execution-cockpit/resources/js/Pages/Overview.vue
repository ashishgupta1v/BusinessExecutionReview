<script setup>
import AppLayout from '../Layouts/AppLayout.vue'
defineOptions({ layout: AppLayout })
import { computed } from 'vue'

/** Overview dashboard — Antigravity Dark Glassmorphism UI. */
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
const dcrDonut = computed(() => `conic-gradient(#6366f1 0 ${comp30.value}%, rgba(255, 255, 255, 0.08) ${comp30.value}% 100%)`)
const fbDonut = computed(() => `conic-gradient(#10b981 0 ${closedPct.value}%, rgba(245, 158, 11, 0.3) ${closedPct.value}% 100%)`)
const barPct = k => k.target ? Math.min(100, Math.round(k.actual / k.target * 100)) : 0
</script>

<template>
  <div class="overview-page">
    <header class="overview-header">
      <div>
        <div class="badge-tag">LIVE COCKPIT</div>
        <h1 class="page-title">Executive Overview</h1>
        <p class="page-sub">your whole execution system at a glance</p>
      </div>
    </header>

    <!-- Top 4 Hero Stats -->
    <div class="hero-stats-grid">
      <div class="glass-card hero-stat-card">
        <div class="stat-value val-indigo">{{ streak.current }} <span class="flame-anim">🔥</span></div>
        <div class="stat-title">Current Streak</div>
      </div>
      <div class="glass-card hero-stat-card">
        <div class="stat-value val-violet">{{ streak.longest }}</div>
        <div class="stat-title">Longest Streak</div>
      </div>
      <div class="glass-card hero-stat-card">
        <div class="stat-value val-emerald">{{ dcrThisMonth }}</div>
        <div class="stat-title">DCRs Filed This Month</div>
      </div>
      <div class="glass-card hero-stat-card">
        <div class="stat-value val-amber">{{ openFeedback }}</div>
        <div class="stat-title">Open Feedback Items</div>
      </div>
    </div>

    <!-- 2 Donut Progress Section -->
    <div class="grid-2">
      <section class="glass-card section-card">
        <h3 class="card-subtitle">DCR Consistency <small>last 30 days</small></h3>
        <div class="donut-wrapper">
          <div class="donut-ring" :style="{ background: dcrDonut }">
            <div class="donut-hole">
              <b>{{ comp30 }}%</b>
              <span>days filed</span>
            </div>
          </div>
          <div class="donut-legend">
            <div class="legend-row"><span class="dot dot-indigo"></span>{{ filled30 }} of 30 filed</div>
            <div class="legend-row"><span class="dot dot-gray"></span>{{ 30 - filled30 }} missed</div>
            <div class="legend-row"><span class="dot dot-violet"></span>Rule-of-5 avg {{ rule5Avg }}/5</div>
          </div>
        </div>
      </section>

      <section class="glass-card section-card">
        <h3 class="card-subtitle">Feedback Status <small>{{ feedback.total }} total</small></h3>
        <div class="donut-wrapper">
          <div class="donut-ring" :style="{ background: fbDonut }">
            <div class="donut-hole">
              <b>{{ closedPct }}%</b>
              <span>closed</span>
            </div>
          </div>
          <div class="donut-legend">
            <div class="legend-row"><span class="dot dot-emerald"></span>Done · {{ feedback.done }}</div>
            <div class="legend-row"><span class="dot dot-amber"></span>Pending · {{ feedback.open }}</div>
          </div>
        </div>
      </section>
    </div>

    <!-- KPI Status Progress Horizontal Bars -->
    <section class="glass-card section-card">
      <div class="card-header-row">
        <h3 class="card-subtitle mb-0">KPI Status — Latest vs Target</h3>
        <span class="status-pill" :class="kpiOnTarget >= kpiStatus.length - 1 ? 'pill-good' : 'pill-amber'">
          {{ kpiOnTarget }}/{{ kpiStatus.length }} on target
        </span>
      </div>

      <div class="hbar-list">
        <div v-for="k in kpiStatus" :key="k.name" class="hbar-item">
          <div class="hbar-labels">
            <span class="hbar-name">{{ k.name }}</span>
            <span class="hbar-val">{{ k.unit }}{{ k.actual }} / {{ k.unit }}{{ k.target }}</span>
          </div>
          <div class="hbar-track">
            <div 
              class="hbar-fill" 
              :style="{ width: barPct(k) + '%', background: k.good ? 'linear-gradient(90deg, #10b981, #059669)' : 'linear-gradient(90deg, #f59e0b, #d97706)' }"
            ></div>
          </div>
        </div>
      </div>
    </section>

    <!-- Heatmap Section -->
    <section class="glass-card section-card">
      <h3 class="card-subtitle">Day Close Heatmap <small>18 weeks</small></h3>
      <div class="heatmap-scroll">
        <div class="heatmap-grid">
          <div v-for="(col,ci) in cols" :key="ci" class="heat-column">
            <div 
              v-for="c in col" 
              :key="c.date" 
              class="heat-cell" 
              :class="c.level ? 'level-' + c.level : ''" 
              :title="c.date"
            ></div>
          </div>
        </div>
      </div>
      <p class="heatmap-note">A broken streak is a reset, not a failure. File today to start again.</p>
    </section>
  </div>
</template>

<style scoped>
.overview-page {
  max-width: 900px;
  margin: 0 auto;
  padding: 32px 20px;
}

.overview-header {
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

/* Stats */
.hero-stats-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 16px;
  margin-bottom: 24px;
}
@media (min-width: 640px) {
  .hero-stats-grid {
    grid-template-columns: repeat(4, 1fr);
  }
}
.hero-stat-card {
  padding: 20px;
  text-align: center;
}
.stat-value {
  font-size: 32px;
  font-weight: 900;
  line-height: 1;
}
.val-indigo { color: #818cf8; }
.val-violet { color: #c084fc; }
.val-emerald { color: #34d399; }
.val-amber { color: #fbbf24; }

.stat-title {
  font-size: 12px;
  color: #94a3b8;
  margin-top: 8px;
  font-weight: 500;
}

/* Grid 2 */
.grid-2 {
  display: grid;
  grid-template-columns: 1fr;
  gap: 20px;
  margin-bottom: 24px;
}
@media (min-width: 640px) {
  .grid-2 {
    grid-template-columns: 1fr 1fr;
  }
}

.section-card {
  padding: 22px;
  margin-bottom: 24px;
}
.card-subtitle {
  font-size: 16px;
  font-weight: 800;
  color: #ffffff;
  margin-bottom: 16px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 4px;
}
.card-subtitle.mb-0 { margin-bottom: 0; }
.card-subtitle small {
  color: #64748b;
  font-size: 12px;
  font-weight: 400;
}

/* Donut */
.donut-wrapper {
  display: flex;
  align-items: center;
  gap: 20px;
}
.donut-ring {
  width: 110px;
  height: 110px;
  border-radius: 50%;
  display: grid;
  place-items: center;
  flex-shrink: 0;
  box-shadow: 0 0 20px rgba(0,0,0,0.5);
}
.donut-hole {
  width: 80px;
  height: 80px;
  background: #0b1020;
  border-radius: 50%;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
}
.donut-hole b {
  font-size: 22px;
  color: #ffffff;
  font-weight: 900;
}
.donut-hole span {
  font-size: 10px;
  color: #94a3b8;
  text-transform: uppercase;
}

.donut-legend {
  display: flex;
  flex-direction: column;
  gap: 8px;
}
.legend-row {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 13px;
  color: #e2e8f0;
}
.dot { width: 8px; height: 8px; border-radius: 50%; }
.dot-indigo { background: #6366f1; }
.dot-violet { background: #8b5cf6; }
.dot-emerald { background: #10b981; }
.dot-amber { background: #f59e0b; }
.dot-gray { background: rgba(255, 255, 255, 0.15); }

/* Horizontal Bars */
.card-header-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 16px;
  flex-wrap: wrap;
  gap: 8px;
}
.status-pill {
  font-size: 12px;
  font-weight: 700;
  padding: 4px 12px;
  border-radius: 999px;
}
.pill-good { background: rgba(16, 185, 129, 0.15); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.3); }
.pill-amber { background: rgba(245, 158, 11, 0.15); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.3); }

.hbar-list {
  display: flex;
  flex-direction: column;
  gap: 14px;
}
.hbar-labels {
  display: flex;
  justify-content: space-between;
  font-size: 14px;
  margin-bottom: 6px;
}
.hbar-name { color: #f8fafc; font-weight: 600; }
.hbar-val { color: #94a3b8; font-size: 13px; }

.hbar-track {
  height: 10px;
  background: rgba(2, 6, 23, 0.6);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 999px;
  overflow: hidden;
}
.hbar-fill {
  height: 100%;
  border-radius: 999px;
  transition: width 0.8s cubic-bezier(0.16, 1, 0.3, 1);
}

/* Heatmap */
.heatmap-scroll {
  overflow-x: auto;
  padding-bottom: 8px;
}
.heatmap-grid {
  display: flex;
  gap: 4px;
}
.heat-column {
  display: flex;
  flex-direction: column;
  gap: 4px;
}
.heat-cell {
  width: 16px;
  height: 16px;
  border-radius: 4px;
  background: rgba(255, 255, 255, 0.05);
}
.level-1 { background: rgba(99, 102, 241, 0.3); }
.level-2 { background: rgba(99, 102, 241, 0.5); }
.level-3 { background: rgba(99, 102, 241, 0.8); }
.level-4 { background: #6366f1; box-shadow: 0 0 8px rgba(99, 102, 241, 0.8); }

.heatmap-note {
  color: #64748b;
  font-size: 12px;
  margin-top: 10px;
}

@media (max-width: 640px) {
  .overview-page {
    padding: 16px 12px;
  }
  .page-title {
    font-size: 22px;
  }
  .hero-stat-card {
    padding: 14px;
  }
  .stat-value {
    font-size: 24px;
  }
  .section-card {
    padding: 16px 14px;
    border-radius: 16px;
  }
  .donut-wrapper {
    flex-direction: column;
    text-align: center;
  }
}
</style>
