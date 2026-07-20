<script setup>
import { reactive } from 'vue'
import { router } from '@inertiajs/vue3'

/**
 * Weekly Review (Phase 1). Props from WeeklyReviewController@show:
 *   week: "2026-W30"
 *   review: existing weekly_review or null
 *   prefill: { achieved:[], challenges:[], dcrCount, doneCount, carriedCount, needleDays }
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
  ['📋 Planned goals','planned_goals','What did you set out to do this week?',3],
  ['✅ Achieved (rolled up from DCRs)','achieved','one per line',3],
  ['🏆 Major wins','major_wins','2 new distributors onboarded…',3],
  ['⚠️ Challenges (from pending items)','challenges','one per line',3],
  ["🎯 What moved the needle this week?",'moved_needle_answer','The one thing that mattered most',2],
  ['➡️ Next week focus (top 3)','next_week_focus','one per line',3],
]
</script>

<template>
  <div class="wk">
    <header class="head">
      <div><h1>Weekly Review</h1><p class="sub">{{ week }} · prefilled from {{ prefill.dcrCount }} DCRs</p><span class="cadence">Weekly cadence</span></div>
      <span class="pill" :class="done?'green':'amber'">{{ done ? 'Complete ✓' : 'Draft' }}</span>
    </header>

    <section class="card glance">
      <div class="stat"><b>{{ prefill.dcrCount }}</b><span>DCRs filed</span></div>
      <div class="stat"><b>{{ prefill.doneCount }}</b><span>tasks done</span></div>
      <div class="stat"><b>{{ prefill.carriedCount }}</b><span>carried over</span></div>
      <div class="stat"><b>{{ prefill.needleDays }}</b><span>needle days</span></div>
    </section>

    <section v-for="f in fields" :key="f[1]" class="card">
      <h3>{{ f[0] }}</h3>
      <textarea v-model="form[f[1]]" :rows="f[3]" :placeholder="f[2]" />
    </section>

    <button class="btn big" @click="save">{{ done ? 'Update review' : 'Complete weekly review' }}</button>
  </div>
</template>

<style scoped>
.wk{max-width:760px;margin:0 auto;padding:20px}
.head{display:flex;justify-content:space-between;align-items:flex-start}
h1{font-size:23px;font-weight:800}.sub{color:#64748b;font-size:13px}
.cadence{font-size:10px;font-weight:700;text-transform:uppercase;color:#4f46e5;background:#eef2ff;padding:3px 9px;border-radius:999px;display:inline-block;margin-top:6px}
.card{background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:16px;margin-top:16px;box-shadow:0 1px 3px rgba(15,23,42,.08)}
.glance{display:grid;grid-template-columns:repeat(4,1fr);gap:10px;text-align:center}
.stat b{font-size:24px;font-weight:800;display:block;color:#4f46e5}.stat span{font-size:11px;color:#64748b}
h3{font-size:14px;margin-bottom:10px}
textarea{border:1px solid #e2e8f0;border-radius:10px;padding:9px 11px;width:100%;font:inherit}
.btn{background:#4f46e5;color:#fff;border:none;border-radius:14px;padding:14px;font-weight:700;width:100%;margin-top:16px;font-size:16px;cursor:pointer}
.pill{font-size:11px;font-weight:600;padding:3px 9px;border-radius:999px;height:fit-content}
.pill.green{background:#d1fae5;color:#059669}.pill.amber{background:#fef3c7;color:#d97706}
</style>
