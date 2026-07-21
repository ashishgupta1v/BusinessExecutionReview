<script setup>
import AppLayout from '../Layouts/AppLayout.vue'
defineOptions({ layout: AppLayout })
import { reactive } from 'vue'
import { router } from '@inertiajs/vue3'

/**
 * Weekly Review — Antigravity Dark Glassmorphism UI.
 */
const props = defineProps({
  week: { type: String, required: true },
  review: { type: Object, default: null },
  prefill: { type: Object, default: () => ({ achieved:[], challenges:[], dcrCount:0, doneCount:0, carriedCount:0, needleDays:0 }) },
})

const form = reactive({
  planned_goals: props.review?.planned_goals ?? '',
  achieved: props.review?.achieved ?? props.prefill.achieved.join('\n'),
  major_wins: props.review?.major_wins ?? '',
  challenges: props.review?.challenges ?? props.prefill.challenges.join('\n'),
  moved_needle_answer: props.review?.moved_needle_answer ?? '',
  next_week_focus: props.review?.next_week_focus ?? '',
})
const done = props.review?.status === 'complete'

function save() {
  router.post(route('weekly.store'), { iso_week: props.week, ...form, status: 'complete' }, { preserveScroll: true })
}
const fields = [
  ['📋 Planned goals', 'planned_goals', 'What did you set out to do this week?', 3],
  ['✅ Achieved (rolled up from DCRs)', 'achieved', 'one per line', 3],
  ['🏆 Major wins', 'major_wins', '2 new distributors onboarded…', 3],
  ['⚠️ Challenges (from pending items)', 'challenges', 'one per line', 3],
  ["🎯 What moved the needle this week?", 'moved_needle_answer', 'The one thing that mattered most', 2],
  ['➡️ Next week focus (top 3)', 'next_week_focus', 'one per line', 3],
]
</script>

<template>
  <div class="weekly-page">
    <header class="weekly-header">
      <div>
        <div class="badge-tag">WEEKLY CADENCE</div>
        <h1 class="page-title">Weekly Review</h1>
        <p class="page-sub">{{ week }} · prefilled from {{ prefill.dcrCount }} DCRs</p>
      </div>
      <div class="status-badge" :class="done ? 'status-complete' : 'status-draft'">
        <span class="dot"></span>
        <span>{{ done ? 'Complete ✓' : 'Draft' }}</span>
      </div>
    </header>

    <!-- Stat Glance Banner -->
    <section class="glass-card glance-banner">
      <div class="stat-card">
        <b class="num-indigo">{{ prefill.dcrCount }}</b>
        <span class="stat-label">DCRs filed</span>
      </div>
      <div class="stat-card">
        <b class="num-emerald">{{ prefill.doneCount }}</b>
        <span class="stat-label">Tasks done</span>
      </div>
      <div class="stat-card">
        <b class="num-amber">{{ prefill.carriedCount }}</b>
        <span class="stat-label">Carried over</span>
      </div>
      <div class="stat-card">
        <b class="num-cyan">{{ prefill.needleDays }}</b>
        <span class="stat-label">Needle days</span>
      </div>
    </section>

    <!-- Field Cards -->
    <section v-for="f in fields" :key="f[1]" class="glass-card review-card">
      <h3 class="field-title">{{ f[0] }}</h3>
      <textarea 
        v-model="form[f[1]]" 
        :rows="f[3]" 
        :placeholder="f[2]" 
        class="glass-input review-textarea"
      />
    </section>

    <!-- Action Submit Button -->
    <div class="action-wrapper">
      <button class="btn-glow big-submit-btn" @click="save">
        <span>{{ done ? 'Update Weekly Review ✓' : 'Complete Weekly Review 🚀' }}</span>
      </button>
    </div>
  </div>
</template>

<style scoped>
.weekly-page {
  max-width: 820px;
  margin: 0 auto;
  padding: 32px 20px;
}

.weekly-header {
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
.status-complete {
  background: rgba(16, 185, 129, 0.15);
  color: #34d399;
  border: 1px solid rgba(16, 185, 129, 0.3);
}
.status-complete .dot {
  background: #34d399;
  box-shadow: 0 0 10px #34d399;
}
.status-draft {
  background: rgba(245, 158, 11, 0.15);
  color: #fbbf24;
  border: 1px solid rgba(245, 158, 11, 0.3);
}
.status-draft .dot {
  background: #fbbf24;
  box-shadow: 0 0 10px #fbbf24;
}

/* Stat Glance Banner */
.glance-banner {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 16px;
  padding: 20px;
  margin-bottom: 24px;
  text-align: center;
}
.stat-card b {
  font-size: 28px;
  font-weight: 900;
  display: block;
}
.num-indigo { color: #818cf8; }
.num-emerald { color: #34d399; }
.num-amber { color: #fbbf24; }
.num-cyan { color: #22d3ee; }

.stat-label {
  font-size: 12px;
  color: #94a3b8;
  font-weight: 500;
}

/* Review Cards */
.review-card {
  padding: 22px;
  margin-bottom: 20px;
}
.field-title {
  font-size: 16px;
  font-weight: 700;
  color: #f8fafc;
  margin-bottom: 12px;
}
.review-textarea {
  width: 100%;
  padding: 14px;
  font-size: 15px;
  line-height: 1.5;
}

/* Action Button */
.action-wrapper {
  margin-top: 28px;
}
.big-submit-btn {
  width: 100%;
  padding: 16px;
  font-size: 16px;
  letter-spacing: 0.02em;
  border: none;
  cursor: pointer;
  min-height: 52px;
}

@media (max-width: 640px) {
  .weekly-page {
    padding: 16px 12px;
  }
  .weekly-header {
    flex-direction: column;
    gap: 12px;
  }
  .status-badge {
    align-self: flex-start;
  }
  .page-title {
    font-size: 22px;
  }
  .glance-banner {
    grid-template-columns: repeat(2, 1fr);
    padding: 14px;
    gap: 12px;
  }
  .review-card {
    padding: 16px 14px;
    border-radius: 16px;
  }
}
</style>
