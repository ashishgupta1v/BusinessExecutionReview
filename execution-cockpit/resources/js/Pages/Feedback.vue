<script setup>
import AppLayout from '../Layouts/AppLayout.vue'
defineOptions({ layout: AppLayout })
import { ref, reactive, computed } from 'vue'
import { router } from '@inertiajs/vue3'

/** Feedback Log — Antigravity Dark Glassmorphism UI. */
const props = defineProps({ items: { type: Array, default: () => [] } })

const filter = ref('All')
const draft = reactive({ type: 'Suggestion', assignee: '', body: '', action: '' })

const rows = computed(() => props.items.filter(r => filter.value === 'All' || r.status === filter.value || r.type === filter.value))
const openCount = computed(() => props.items.filter(r => r.status === 'Pending').length)
const closedPct = computed(() => props.items.length ? Math.round((props.items.length - openCount.value) / props.items.length * 100) : 0)

function add() {
  if (!draft.body.trim()) return
  router.post(route('feedback.store'), { ...draft }, { preserveScroll: true, onSuccess: () => { draft.body=''; draft.action=''; draft.assignee='' } })
}
const toggle = r => router.put(route('feedback.toggle', r.id), {}, { preserveScroll: true })
const remove = r => router.delete(route('feedback.destroy', r.id), { preserveScroll: true })
const tone = t => t === 'Positive' ? 'green' : t === 'Negative' ? 'rose' : 'indigo'
</script>

<template>
  <div class="feedback-page">
    <header class="feedback-header">
      <div>
        <div class="badge-tag">MONTHLY CADENCE</div>
        <h1 class="page-title">Feedback Log</h1>
        <p class="page-sub">capture insight, assign an owner, close the loop</p>
      </div>
    </header>

    <!-- Ring Stats Card -->
    <section class="glass-card glance-card">
      <div class="ring-container">
        <div class="ring" :style="{ background: `conic-gradient(#10b981 0 ${closedPct}%, rgba(245, 158, 11, 0.3) ${closedPct}% 100%)` }">
          <div class="ring-hole">
            <b>{{ closedPct }}%</b>
            <span>closed</span>
          </div>
        </div>
      </div>

      <div class="ring-legend">
        <div class="legend-item">
          <span class="dot dot-green"></span>
          <span>Done · <b>{{ items.length - openCount }}</b></span>
        </div>
        <div class="legend-item">
          <span class="dot dot-amber"></span>
          <span>Pending · <b>{{ openCount }}</b></span>
        </div>
      </div>
    </section>

    <!-- Add Feedback Card -->
    <section class="glass-card add-card">
      <h3 class="card-subtitle">Log New Feedback</h3>
      
      <div class="form-row">
        <select v-model="draft.type" class="glass-input select-type">
          <option>Positive</option>
          <option>Negative</option>
          <option>Suggestion</option>
        </select>
        <input v-model="draft.assignee" placeholder="Assign to (e.g. Ashish)" class="glass-input flex-1" />
      </div>

      <input v-model="draft.body" placeholder="Feedback detail..." class="glass-input mt-3" />
      <input v-model="draft.action" placeholder="Action taken or plan..." class="glass-input mt-3" />

      <button class="btn-glow add-btn" @click="add">Log Feedback Item</button>
    </section>

    <!-- Filter Chips -->
    <div class="chips-row">
      <button 
        v-for="c in ['All','Pending','Done','Positive','Negative','Suggestion']" 
        :key="c"
        class="chip-btn" 
        :class="{ active: filter===c }" 
        @click="filter=c"
      >
        {{ c }}
      </button>
    </div>

    <!-- Feedback List Cards -->
    <div class="feedback-list">
      <section v-for="r in rows" :key="r.id" class="glass-card fb-row">
        <div class="fb-top">
          <div class="fb-meta">
            <span class="type-pill" :class="'pill-' + tone(r.type)">{{ r.type }}</span>
            <span class="date-text">{{ r.date }}<template v-if="r.assignee"> · {{ r.assignee }}</template></span>
          </div>
          
          <div class="fb-actions">
            <button @click="toggle(r)">
              <span class="status-pill" :class="r.status==='Done' ? 'pill-done' : 'pill-pending'">
                {{ r.status }}
              </span>
            </button>
            <button class="remove-btn" @click="remove(r)">×</button>
          </div>
        </div>

        <div class="fb-body">{{ r.body }}</div>
        <div v-if="r.action" class="fb-action">↳ Action: {{ r.action }}</div>
      </section>
    </div>
  </div>
</template>

<style scoped>
.feedback-page {
  max-width: 820px;
  margin: 0 auto;
  padding: 32px 20px;
}

.feedback-header {
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

/* Glance Ring */
.glance-card {
  padding: 22px;
  display: flex;
  align-items: center;
  gap: 24px;
  margin-bottom: 20px;
}
.ring-container {
  flex-shrink: 0;
}
.ring {
  width: 110px;
  height: 110px;
  border-radius: 50%;
  display: grid;
  place-items: center;
  box-shadow: 0 0 20px rgba(0,0,0,0.5);
}
.ring-hole {
  width: 80px;
  height: 80px;
  background: #0b1020;
  border-radius: 50%;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
}
.ring-hole b {
  font-size: 22px;
  color: #ffffff;
  font-weight: 900;
}
.ring-hole span {
  font-size: 10px;
  color: #94a3b8;
  text-transform: uppercase;
}

.ring-legend {
  display: flex;
  flex-direction: column;
  gap: 8px;
}
.legend-item {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 14px;
  color: #e2e8f0;
}
.dot {
  width: 10px;
  height: 10px;
  border-radius: 3px;
}
.dot-green { background: #10b981; }
.dot-amber { background: #f59e0b; }

/* Add Card */
.add-card {
  padding: 22px;
  margin-bottom: 24px;
}
.card-subtitle {
  font-size: 16px;
  font-weight: 700;
  color: #ffffff;
  margin-bottom: 14px;
}
.form-row {
  display: flex;
  gap: 10px;
}
.select-type {
  width: 140px;
}
.flex-1 { flex: 1; }
.mt-3 { margin-top: 10px; }

.add-btn {
  width: 100%;
  padding: 14px;
  margin-top: 14px;
  border: none;
  cursor: pointer;
  font-size: 15px;
  min-height: 48px;
}

/* Chips */
.chips-row {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
  margin-bottom: 20px;
}
.chip-btn {
  font-size: 13px;
  font-weight: 600;
  padding: 6px 14px;
  border-radius: 999px;
  background: rgba(15, 23, 42, 0.6);
  border: 1px solid rgba(255, 255, 255, 0.1);
  color: #94a3b8;
  cursor: pointer;
  transition: all 0.2s ease;
  touch-action: manipulation;
}
.chip-btn:hover {
  color: #ffffff;
  border-color: rgba(99, 102, 241, 0.4);
}
.chip-btn.active {
  background: linear-gradient(135deg, #6366f1, #4f46e5);
  color: #ffffff;
  border-color: #818cf8;
  box-shadow: 0 4px 14px rgba(99, 102, 241, 0.4);
}

/* List Items */
.feedback-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}
.fb-row {
  padding: 18px 20px;
}
.fb-top {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 8px;
  flex-wrap: wrap;
  gap: 8px;
}
.fb-meta {
  display: flex;
  align-items: center;
  gap: 10px;
}
.type-pill {
  font-size: 11px;
  font-weight: 700;
  padding: 3px 10px;
  border-radius: 999px;
}
.pill-green { background: rgba(16, 185, 129, 0.15); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.3); }
.pill-rose { background: rgba(244, 63, 94, 0.15); color: #fb7185; border: 1px solid rgba(244, 63, 94, 0.3); }
.pill-indigo { background: rgba(99, 102, 241, 0.15); color: #818cf8; border: 1px solid rgba(99, 102, 241, 0.3); }

.date-text {
  font-size: 12px;
  color: #94a3b8;
}

.fb-actions {
  display: flex;
  align-items: center;
  gap: 8px;
}
.status-pill {
  font-size: 11px;
  font-weight: 700;
  padding: 3px 10px;
  border-radius: 999px;
  cursor: pointer;
}
.pill-done { background: rgba(16, 185, 129, 0.2); color: #34d399; }
.pill-pending { background: rgba(245, 158, 11, 0.2); color: #fbbf24; }

.remove-btn {
  background: none;
  border: none;
  color: #64748b;
  font-size: 18px;
  cursor: pointer;
  padding: 4px;
}
.remove-btn:hover { color: #f43f5e; }

.fb-body {
  font-size: 15px;
  color: #f8fafc;
  line-height: 1.5;
}
.fb-action {
  font-size: 13px;
  color: #818cf8;
  margin-top: 6px;
}

@media (max-width: 640px) {
  .feedback-page {
    padding: 16px 12px;
  }
  .page-title {
    font-size: 22px;
  }
  .glance-card {
    flex-direction: column;
    text-align: center;
    padding: 18px 14px;
  }
  .form-row {
    flex-direction: column;
  }
  .select-type {
    width: 100%;
  }
  .add-card {
    padding: 16px 14px;
    border-radius: 16px;
  }
  .fb-row {
    padding: 14px 16px;
    border-radius: 16px;
  }
}
</style>
