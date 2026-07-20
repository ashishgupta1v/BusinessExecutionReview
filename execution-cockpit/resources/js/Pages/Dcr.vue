<script setup>
import AppLayout from '../Layouts/AppLayout.vue'
defineOptions({ layout: AppLayout })
import { reactive, computed } from 'vue'
import { router, usePage } from '@inertiajs/vue3'

/**
 * Day Close Report — quick entry (Phase 1).
 * Expects props from DcrController@show:
 *   entry: { entry_date, completed[], pending[], priorities[], moved_needle, reflection_note } | null
 *   discipline: { rule_of_5: bool[5], two_minute: bool, time_blocked: bool }
 *   streak: { current, longest }
 */
const props = defineProps({
  entry: { type: Object, default: null },
  discipline: { type: Object, default: () => ({ rule_of_5: [false,false,false,false,false], two_minute:false, time_blocked:false }) },
  streak: { type: Object, default: () => ({ current:0, longest:0 }) },
  date: { type: String, required: true },
})

const form = reactive({
  completed:  props.entry?.completed?.map(t => ({ ...t })) ?? [],
  pending:    props.entry?.pending?.map(t => ({ ...t }))   ?? [],
  priorities: props.entry?.priorities?.length ? [...props.entry.priorities] : [''],
  moved_needle: props.entry?.moved_needle ?? false,
  reflection_note: props.entry?.reflection_note ?? '',
})
const disc = reactive({ ...props.discipline })
const draftDone = reactive({ done:'', pend:'' })
const filed = computed(() => !!props.entry)
const r5count = computed(() => disc.rule_of_5.filter(Boolean).length)

function addCompleted() { if (draftDone.done.trim()) { form.completed.push({ title: draftDone.done.trim() }); draftDone.done='' } }
function addPending()   { if (draftDone.pend.trim()) { form.pending.push({ title: draftDone.pend.trim(), new_deadline: props.date }); draftDone.pend='' } }
function addPriority()  { if (form.priorities.length < 5) form.priorities.push('') }
function toggleRule(i)  { disc.rule_of_5[i] = !disc.rule_of_5[i]; persistDiscipline() }

function persistDiscipline() {
  router.put(route('discipline.update'), { date: props.date, ...disc }, { preserveScroll: true, preserveState: true })
}
function fileDcr() {
  router.post(route('dcr.store'), {
    entry_date: props.date,
    completed: form.completed,
    pending: form.pending,
    priorities: form.priorities.map(p => p.trim()).filter(Boolean).slice(0, 5),
    moved_needle: form.moved_needle,
    reflection_note: form.reflection_note,
  }, { preserveScroll: true })
}
</script>

<template>
  <div class="dcr">
    <header class="head">
      <div>
        <h1>Day Close Report</h1>
        <p class="sub">{{ date }} · file in under 90 seconds</p>
        <span class="cadence">Daily cadence</span>
      </div>
      <span class="pill" :class="filed ? 'green' : 'amber'">{{ filed ? 'Filed ✓' : 'Open' }}</span>
    </header>

    <section class="card">
      <h3>✅ Tasks completed</h3>
      <div v-for="(c,i) in form.completed" :key="i" class="item green">
        <span>{{ c.title }}</span><button @click="form.completed.splice(i,1)">×</button>
      </div>
      <div class="addrow">
        <input v-model="draftDone.done" @keyup.enter="addCompleted" placeholder="e.g. 18 sales calls made" />
        <button class="btn green" @click="addCompleted">Add</button>
      </div>
    </section>

    <section class="card">
      <h3>⏳ Pending / carried over <small>→ auto-adds to Kanban</small></h3>
      <div v-for="(c,i) in form.pending" :key="i" class="item amber">
        <span>{{ c.title }}</span>
        <input type="date" v-model="c.new_deadline" />
        <button @click="form.pending.splice(i,1)">×</button>
      </div>
      <div class="addrow">
        <input v-model="draftDone.pend" @keyup.enter="addPending" placeholder="e.g. Send Distributor Z proposal" />
        <button class="btn amber" @click="addPending">Add</button>
      </div>
    </section>

    <section class="card">
      <h3>🎯 Tomorrow's top priorities <small>max 5</small></h3>
      <div v-for="(p,i) in form.priorities" :key="i" class="pri">
        <span>{{ i+1 }}.</span>
        <input v-model="form.priorities[i]" />
        <button v-if="form.priorities.length>1" @click="form.priorities.splice(i,1)">×</button>
      </div>
      <button v-if="form.priorities.length<5" class="ghost" @click="addPriority">+ add priority</button>
    </section>

    <section class="card">
      <label class="needle"><input type="checkbox" v-model="form.moved_needle" /> Did today move the needle?</label>
      <textarea v-model="form.reflection_note" rows="2" placeholder="One line of reflection…" />
    </section>

    <button class="btn big" @click="fileDcr">{{ filed ? 'Update Day Close Report' : 'File Day Close Report' }}</button>

    <section class="card">
      <h3>⚡ Discipline checklist <span class="pill" :class="r5count===5?'green':'slate'">{{ r5count }}/5 Rule of 5</span></h3>
      <div class="r5">
        <button v-for="(v,i) in disc.rule_of_5" :key="i" :class="{on:v}" @click="toggleRule(i)">{{ i+1 }}</button>
      </div>
      <div class="disc2">
        <label :class="{on:disc.two_minute}"><input type="checkbox" v-model="disc.two_minute" @change="persistDiscipline" /> 2-Minute rule</label>
        <label :class="{on:disc.time_blocked}"><input type="checkbox" v-model="disc.time_blocked" @change="persistDiscipline" /> Time-blocked</label>
      </div>
    </section>
  </div>
</template>

<style scoped>
.dcr{max-width:760px;margin:0 auto;padding:20px}
.head{display:flex;justify-content:space-between;align-items:flex-start}
h1{font-size:23px;font-weight:800;color:#0f172a}
.sub{color:#64748b;font-size:13px}
.cadence{font-size:10px;font-weight:700;text-transform:uppercase;color:#4f46e5;background:#eef2ff;padding:3px 9px;border-radius:999px;display:inline-block;margin-top:6px}
.card{background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:16px;margin-top:16px;box-shadow:0 1px 3px rgba(15,23,42,.08)}
h3{font-size:14px;margin-bottom:10px;display:flex;justify-content:space-between;align-items:center}
h3 small{color:#94a3b8;font-weight:400}
.item{display:flex;gap:8px;align-items:center;border-radius:10px;padding:9px 11px;margin-bottom:7px}
.item span{flex:1}.item.green{background:#ecfdf5}.item.amber{background:#fffbeb}
.item button,.pri button{color:#cbd5e1;font-size:16px}
.addrow{display:flex;gap:7px}.addrow input{flex:1}
input,textarea{border:1px solid #e2e8f0;border-radius:10px;padding:9px 11px;width:100%;font:inherit}
.pri{display:flex;gap:8px;align-items:center;margin-bottom:7px}.pri>span{color:#94a3b8;width:18px}
.needle{display:flex;gap:10px;align-items:center;font-weight:600;margin-bottom:10px}
.btn{background:#4f46e5;color:#fff;border:none;border-radius:10px;padding:9px 14px;font-weight:600;cursor:pointer}
.btn.green{background:#059669}.btn.amber{background:#d97706}
.btn.big{width:100%;padding:14px;border-radius:14px;font-size:16px;margin-top:16px}
.ghost{color:#4f46e5;font-weight:600;background:none;border:none;cursor:pointer}
.pill{font-size:11px;font-weight:600;padding:3px 9px;border-radius:999px}
.pill.green{background:#d1fae5;color:#059669}.pill.amber{background:#fef3c7;color:#d97706}.pill.slate{background:#f1f5f9;color:#64748b}
.r5{display:flex;gap:8px;margin-bottom:12px}
.r5 button{flex:1;height:46px;border-radius:12px;border:2px solid #e2e8f0;color:#cbd5e1;font-weight:800;cursor:pointer;background:none}
.r5 button.on{background:#4f46e5;border-color:#4f46e5;color:#fff}
.disc2{display:grid;grid-template-columns:1fr 1fr;gap:8px}
.disc2 label{display:flex;gap:8px;align-items:center;border:1px solid #e2e8f0;border-radius:12px;padding:11px;font-size:13px;cursor:pointer}
.disc2 label.on{background:#ecfdf5;border-color:#a7f3d0}
</style>
