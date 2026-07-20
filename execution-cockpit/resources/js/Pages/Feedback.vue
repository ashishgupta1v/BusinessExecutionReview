<script setup>
import AppLayout from '../Layouts/AppLayout.vue'
defineOptions({ layout: AppLayout })
import { ref, reactive, computed } from 'vue'
import { router } from '@inertiajs/vue3'

/** Feedback Log (Phase 1). Props from FeedbackController@index: items: [{id,date,type,body,action,assignee,status}] */
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
  <div class="fb">
    <header class="head">
      <div><h1>Feedback Log</h1><p class="sub">capture insight, assign an owner, close the loop</p><span class="cadence">Monthly cadence</span></div>
    </header>

    <section class="card glance">
      <div class="ring" :style="{ background: `conic-gradient(#059669 0 ${closedPct}%, #fef3c7 ${closedPct}% 100%)` }">
        <div class="hole"><b>{{ closedPct }}%</b><span>closed</span></div>
      </div>
      <div class="leg">
        <div><i style="background:#059669"></i> Done · {{ items.length - openCount }}</div>
        <div><i style="background:#d97706"></i> Pending · {{ openCount }}</div>
      </div>
    </section>

    <section class="card">
      <div class="addrow">
        <select v-model="draft.type"><option>Positive</option><option>Negative</option><option>Suggestion</option></select>
        <input v-model="draft.assignee" placeholder="Assign to" />
      </div>
      <input v-model="draft.body" placeholder="Feedback…" class="mt" />
      <input v-model="draft.action" placeholder="Action taken…" class="mt" />
      <button class="btn" @click="add">Log feedback</button>
    </section>

    <div class="chips">
      <button v-for="c in ['All','Pending','Done','Positive','Negative','Suggestion']" :key="c"
        class="chip" :class="{on:filter===c}" @click="filter=c">{{ c }}</button>
    </div>

    <section v-for="r in rows" :key="r.id" class="card fbrow">
      <div class="top">
        <div><span class="pill" :class="tone(r.type)">{{ r.type }}</span> <small>{{ r.date }}<template v-if="r.assignee"> · {{ r.assignee }}</template></small></div>
        <div>
          <button @click="toggle(r)"><span class="pill" :class="r.status==='Done'?'green':'amber'">{{ r.status }}</span></button>
          <button class="x" @click="remove(r)">×</button>
        </div>
      </div>
      <div class="body">{{ r.body }}</div>
      <div v-if="r.action" class="act">↳ {{ r.action }}</div>
    </section>
  </div>
</template>

<style scoped>
.fb{max-width:760px;margin:0 auto;padding:20px}
h1{font-size:23px;font-weight:800}.sub{color:#64748b;font-size:13px}
.cadence{font-size:10px;font-weight:700;text-transform:uppercase;color:#4f46e5;background:#eef2ff;padding:3px 9px;border-radius:999px;display:inline-block;margin-top:6px}
.card{background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:16px;margin-top:16px;box-shadow:0 1px 3px rgba(15,23,42,.08)}
.glance{display:flex;align-items:center;gap:20px}
.ring{width:118px;height:118px;border-radius:50%;display:grid;place-items:center}
.hole{width:84px;height:84px;background:#fff;border-radius:50%;display:grid;place-items:center;text-align:center}
.hole b{font-size:22px}.hole span{font-size:10px;color:#64748b}
.leg div{display:flex;align-items:center;gap:7px;font-size:13px;margin-bottom:6px}
.leg i{width:10px;height:10px;border-radius:3px}
.addrow{display:flex;gap:7px}.addrow select{width:auto}.addrow input{flex:1}
input,select{border:1px solid #e2e8f0;border-radius:10px;padding:9px 11px;width:100%;font:inherit}.mt{margin-top:7px}
.btn{background:#4f46e5;color:#fff;border:none;border-radius:10px;padding:10px;font-weight:600;width:100%;margin-top:9px;cursor:pointer}
.chips{display:flex;gap:7px;flex-wrap:wrap;margin:12px 0}
.chip{font-size:12px;padding:5px 12px;border-radius:999px;background:#fff;border:1px solid #e2e8f0;color:#64748b;cursor:pointer}
.chip.on{background:#4f46e5;color:#fff;border-color:#4f46e5}
.fbrow .top{display:flex;justify-content:space-between;align-items:center}
.fbrow small{color:#94a3b8}.fbrow .body{margin-top:7px}.fbrow .act{color:#94a3b8;font-size:12px;margin-top:3px}
.x{color:#cbd5e1;font-size:16px;background:none;border:none;cursor:pointer;margin-left:6px}
.pill{font-size:11px;font-weight:600;padding:3px 9px;border-radius:999px}
.pill.green{background:#d1fae5;color:#059669}.pill.amber{background:#fef3c7;color:#d97706}.pill.rose{background:#ffe4e6;color:#e11d48}.pill.indigo{background:#eef2ff;color:#4f46e5}
button{background:none;border:none;cursor:pointer}
</style>
