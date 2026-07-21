<script setup>
import AppLayout from '../Layouts/AppLayout.vue'
defineOptions({ layout: AppLayout })
import { reactive, computed } from 'vue'
import { router, usePage } from '@inertiajs/vue3'

/**
 * Day Close Report — Antigravity Dark Glassmorphism UI.
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
  <div class="dcr-page">
    <header class="dcr-header">
      <div>
        <div class="badge-tag">DAILY CADENCE</div>
        <h1 class="page-title">Day Close Report</h1>
        <p class="page-sub">{{ date }} · file in under 90 seconds</p>
      </div>
      <div class="status-badge" :class="filed ? 'status-filed' : 'status-open'">
        <span class="dot"></span>
        <span>{{ filed ? 'Filed ✓' : 'Open' }}</span>
      </div>
    </header>

    <!-- Card 1: Completed Tasks -->
    <section class="glass-card section-card">
      <div class="card-title">
        <h3><span class="icon">✅</span> Tasks completed</h3>
      </div>
      
      <div class="item-list">
        <div v-for="(c,i) in form.completed" :key="i" class="task-chip chip-emerald">
          <span class="chip-title">{{ c.title }}</span>
          <button class="btn-remove" @click="form.completed.splice(i,1)">×</button>
        </div>
      </div>

      <div class="add-row">
        <input 
          v-model="draftDone.done" 
          @keyup.enter="addCompleted" 
          placeholder="e.g. 18 sales calls made" 
          class="glass-input"
        />
        <button class="btn-add btn-emerald" @click="addCompleted">Add Task</button>
      </div>
    </section>

    <!-- Card 2: Pending Tasks -->
    <section class="glass-card section-card">
      <div class="card-title">
        <h3>
          <span class="icon">⏳</span> Pending / carried over 
          <small class="helper-text">→ auto-adds to Kanban</small>
        </h3>
      </div>

      <div class="item-list">
        <div v-for="(c,i) in form.pending" :key="i" class="task-chip chip-amber">
          <span class="chip-title">{{ c.title }}</span>
          <input type="date" v-model="c.new_deadline" class="deadline-input" />
          <button class="btn-remove" @click="form.pending.splice(i,1)">×</button>
        </div>
      </div>

      <div class="add-row">
        <input 
          v-model="draftDone.pend" 
          @keyup.enter="addPending" 
          placeholder="e.g. Send Distributor Z proposal" 
          class="glass-input"
        />
        <button class="btn-add btn-amber" @click="addPending">Add Pending</button>
      </div>
    </section>

    <!-- Card 3: Tomorrow's Priorities -->
    <section class="glass-card section-card">
      <div class="card-title">
        <h3>
          <span class="icon">🎯</span> Tomorrow's top priorities 
          <span class="counter-badge">{{ form.priorities.length }}/5 max</span>
        </h3>
      </div>

      <div class="priority-list">
        <div v-for="(p,i) in form.priorities" :key="i" class="priority-row">
          <span class="num-badge">{{ i+1 }}</span>
          <input v-model="form.priorities[i]" placeholder="Enter key priority..." class="glass-input" />
          <button v-if="form.priorities.length>1" class="btn-remove" @click="form.priorities.splice(i,1)">×</button>
        </div>
      </div>

      <button v-if="form.priorities.length<5" class="btn-ghost" @click="addPriority">
        <span>+</span> Add priority item
      </button>
    </section>

    <!-- Card 4: Reflection & Needle Mover Switch -->
    <section class="glass-card section-card needle-card">
      <div class="needle-toggle-wrapper">
        <div class="needle-info">
          <span class="needle-icon">🚀</span>
          <div>
            <h4 class="needle-title">Did today move the needle?</h4>
            <p class="needle-sub">Focus on high-impact strategic execution over busywork</p>
          </div>
        </div>
        <label class="switch">
          <input type="checkbox" v-model="form.moved_needle" />
          <span class="slider"></span>
        </label>
      </div>

      <div class="reflection-box">
        <label class="reflection-label">One line of reflection & takeaways</label>
        <textarea 
          v-model="form.reflection_note" 
          rows="3" 
          placeholder="What went well today? What will you sharpen tomorrow?" 
          class="glass-input reflection-textarea"
        />
      </div>
    </section>

    <!-- Action Button -->
    <div class="action-wrapper">
      <button class="btn-glow big-submit-btn" @click="fileDcr">
        <span>{{ filed ? 'Update Day Close Report ✓' : 'File Day Close Report 🚀' }}</span>
      </button>
    </div>

    <!-- Card 5: Discipline Checklist -->
    <section class="glass-card section-card discipline-card">
      <div class="card-title">
        <h3>
          <span class="icon">⚡</span> Daily Discipline Checklist
          <span class="pill-count" :class="r5count===5 ? 'pill-success' : 'pill-default'">
            {{ r5count }}/5 Rule of 5
          </span>
        </h3>
      </div>

      <div class="r5-grid">
        <button 
          v-for="(v,i) in disc.rule_of_5" 
          :key="i" 
          class="r5-button"
          :class="{ active: v }" 
          @click="toggleRule(i)"
        >
          <span class="r5-num">{{ i+1 }}</span>
          <span class="r5-check" v-if="v">✓</span>
        </button>
      </div>

      <div class="disc-toggles">
        <label class="toggle-card" :class="{ active: disc.two_minute }">
          <input type="checkbox" v-model="disc.two_minute" @change="persistDiscipline" />
          <span class="toggle-icon">⏱️</span>
          <div class="toggle-text">
            <b>2-Minute Rule</b>
            <small>Execute tasks under 2 mins immediately</small>
          </div>
        </label>

        <label class="toggle-card" :class="{ active: disc.time_blocked }">
          <input type="checkbox" v-model="disc.time_blocked" @change="persistDiscipline" />
          <span class="toggle-icon">🗓️</span>
          <div class="toggle-text">
            <b>Time-Blocked Calendar</b>
            <small>Deep work slots scheduled</small>
          </div>
        </label>
      </div>
    </section>
  </div>
</template>

<style scoped>
.dcr-page {
  max-width: 820px;
  margin: 0 auto;
  padding: 32px 20px;
}

/* Header */
.dcr-header {
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
.status-filed {
  background: rgba(16, 185, 129, 0.15);
  color: #34d399;
  border: 1px solid rgba(16, 185, 129, 0.3);
}
.status-filed .dot {
  background: #34d399;
  box-shadow: 0 0 10px #34d399;
}
.status-open {
  background: rgba(245, 158, 11, 0.15);
  color: #fbbf24;
  border: 1px solid rgba(245, 158, 11, 0.3);
}
.status-open .dot {
  background: #fbbf24;
  box-shadow: 0 0 10px #fbbf24;
}

/* Cards */
.section-card {
  padding: 22px;
  margin-bottom: 20px;
}
.card-title h3 {
  font-size: 16px;
  font-weight: 700;
  color: #f8fafc;
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 14px;
}
.card-title .icon {
  font-size: 18px;
}
.helper-text {
  color: #64748b;
  font-size: 12px;
  font-weight: 400;
}
.counter-badge {
  margin-left: auto;
  font-size: 11px;
  color: #94a3b8;
  background: rgba(255, 255, 255, 0.05);
  padding: 2px 8px;
  border-radius: 999px;
}

/* Items List */
.item-list {
  display: flex;
  flex-direction: column;
  gap: 8px;
  margin-bottom: 12px;
}
.task-chip {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  padding: 10px 14px;
  border-radius: 12px;
  font-size: 14px;
  transition: all 0.2s ease;
}
.chip-emerald {
  background: rgba(16, 185, 129, 0.12);
  border: 1px solid rgba(16, 185, 129, 0.25);
  color: #a7f3d0;
}
.chip-amber {
  background: rgba(245, 158, 11, 0.12);
  border: 1px solid rgba(245, 158, 11, 0.25);
  color: #fde68a;
}
.chip-title {
  flex: 1;
  font-weight: 500;
}
.deadline-input {
  background: rgba(2, 6, 23, 0.5);
  border: 1px solid rgba(255, 255, 255, 0.15);
  color: #fbbf24;
  border-radius: 8px;
  padding: 4px 8px;
  font-size: 12px;
}

.add-row {
  display: flex;
  gap: 10px;
}
.add-row input {
  flex: 1;
  padding: 11px 14px;
  font-size: 14px;
}
.btn-add {
  padding: 0 18px;
  border-radius: 12px;
  font-weight: 700;
  font-size: 13px;
  border: none;
  cursor: pointer;
  transition: all 0.2s ease;
}
.btn-emerald {
  background: #10b981;
  color: #ffffff;
  box-shadow: 0 4px 14px rgba(16, 185, 129, 0.4);
}
.btn-emerald:hover {
  background: #059669;
  transform: translateY(-1px);
}
.btn-amber {
  background: #f59e0b;
  color: #ffffff;
  box-shadow: 0 4px 14px rgba(245, 158, 11, 0.4);
}
.btn-amber:hover {
  background: #d97706;
  transform: translateY(-1px);
}

.btn-remove {
  background: none;
  border: none;
  color: #94a3b8;
  font-size: 18px;
  cursor: pointer;
  padding: 0 4px;
}
.btn-remove:hover {
  color: #f43f5e;
}

/* Priorities */
.priority-list {
  display: flex;
  flex-direction: column;
  gap: 10px;
  margin-bottom: 12px;
}
.priority-row {
  display: flex;
  align-items: center;
  gap: 12px;
}
.num-badge {
  width: 24px;
  height: 24px;
  border-radius: 50%;
  background: rgba(99, 102, 241, 0.2);
  color: #818cf8;
  font-weight: 800;
  font-size: 12px;
  display: grid;
  place-items: center;
  flex-shrink: 0;
}
.priority-row input {
  flex: 1;
  padding: 10px 14px;
  font-size: 14px;
}
.btn-ghost {
  background: rgba(99, 102, 241, 0.1);
  color: #818cf8;
  border: 1px dashed rgba(99, 102, 241, 0.3);
  border-radius: 12px;
  padding: 10px;
  width: 100%;
  font-weight: 600;
  font-size: 13px;
  cursor: pointer;
  transition: all 0.2s ease;
}
.btn-ghost:hover {
  background: rgba(99, 102, 241, 0.2);
  color: #ffffff;
}

/* Needle Card Switch */
.needle-card {
  background: linear-gradient(135deg, rgba(30, 27, 75, 0.5) 0%, rgba(15, 23, 42, 0.7) 100%);
  border: 1px solid rgba(129, 140, 248, 0.2);
}
.needle-toggle-wrapper {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding-bottom: 18px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);
  margin-bottom: 18px;
}
.needle-info {
  display: flex;
  align-items: center;
  gap: 12px;
}
.needle-icon {
  font-size: 24px;
}
.needle-title {
  font-size: 16px;
  font-weight: 700;
  color: #ffffff;
}
.needle-sub {
  font-size: 12px;
  color: #94a3b8;
}

/* Switch UI */
.switch {
  position: relative;
  display: inline-block;
  width: 50px;
  height: 26px;
  flex-shrink: 0;
}
.switch input {
  opacity: 0;
  width: 0;
  height: 0;
}
.slider {
  position: absolute;
  cursor: pointer;
  top: 0; left: 0; right: 0; bottom: 0;
  background-color: rgba(255, 255, 255, 0.1);
  transition: 0.3s;
  border-radius: 34px;
  border: 1px solid rgba(255, 255, 255, 0.15);
}
.slider:before {
  position: absolute;
  content: "";
  height: 18px;
  width: 18px;
  left: 3px;
  bottom: 3px;
  background-color: #cbd5e1;
  transition: 0.3s;
  border-radius: 50%;
}
input:checked + .slider {
  background: linear-gradient(135deg, #10b981, #059669);
  border-color: #34d399;
  box-shadow: 0 0 12px rgba(16, 185, 129, 0.5);
}
input:checked + .slider:before {
  transform: translateX(24px);
  background-color: #ffffff;
}

.reflection-box {
  display: flex;
  flex-direction: column;
  gap: 8px;
}
.reflection-label {
  font-size: 13px;
  font-weight: 600;
  color: #cbd5e1;
}
.reflection-textarea {
  width: 100%;
  padding: 12px 14px;
  font-size: 14px;
  resize: vertical;
}

/* Action Submit Button */
.action-wrapper {
  margin: 24px 0;
}
.big-submit-btn {
  width: 100%;
  padding: 16px;
  font-size: 17px;
  letter-spacing: 0.02em;
  border: none;
  cursor: pointer;
}

/* Discipline Checklist */
.discipline-card {
  border-color: rgba(16, 185, 129, 0.2);
}
.pill-count {
  font-size: 12px;
  font-weight: 700;
  padding: 4px 10px;
  border-radius: 999px;
  margin-left: auto;
}
.pill-success {
  background: rgba(16, 185, 129, 0.2);
  color: #34d399;
  border: 1px solid rgba(16, 185, 129, 0.4);
}
.pill-default {
  background: rgba(255, 255, 255, 0.05);
  color: #94a3b8;
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.r5-grid {
  display: grid;
  grid-template-columns: repeat(5, 1fr);
  gap: 10px;
  margin-bottom: 16px;
}
.r5-button {
  height: 48px;
  border-radius: 12px;
  background: rgba(2, 6, 23, 0.4);
  border: 1px solid rgba(255, 255, 255, 0.12);
  color: #64748b;
  font-weight: 800;
  font-size: 16px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 4px;
  transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
}
.r5-button:hover {
  border-color: #818cf8;
  color: #f8fafc;
}
.r5-button.active {
  background: linear-gradient(135deg, #6366f1 0%, #4338ca 100%);
  border-color: #a5b4fc;
  color: #ffffff;
  box-shadow: 0 4px 16px rgba(99, 102, 241, 0.4);
}

.disc-toggles {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 12px;
}
.toggle-card {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 14px 16px;
  border-radius: 14px;
  background: rgba(2, 6, 23, 0.4);
  border: 1px solid rgba(255, 255, 255, 0.1);
  cursor: pointer;
  transition: all 0.2s ease;
}
.toggle-card input {
  display: none;
}
.toggle-icon {
  font-size: 20px;
}
.toggle-text {
  display: flex;
  flex-direction: column;
}
.toggle-text b {
  font-size: 14px;
  color: #e2e8f0;
}
.toggle-text small {
  font-size: 11px;
  color: #64748b;
}
.toggle-card.active {
  background: rgba(16, 185, 129, 0.12);
  border-color: rgba(16, 185, 129, 0.4);
  box-shadow: 0 4px 16px rgba(16, 185, 129, 0.2);
}
.toggle-card.active .toggle-text b {
  color: #a7f3d0;
}

@media (max-width: 640px) {
  .disc-toggles {
    grid-template-columns: 1fr;
  }
}
</style>
