<script setup>
import AppLayout from '../Layouts/AppLayout.vue'
defineOptions({ layout: AppLayout })
import { ref, reactive } from 'vue'
import { router } from '@inertiajs/vue3'
import draggable from 'vuedraggable'

/**
 * Kanban Board (Phase 1). Props from KanbanController@index:
 *   columns: { todo:[card], doing:[card], done:[card] }
 *   staleDays: 3
 * card: { id, title, entered_column_at, due_date }
 */
const props = defineProps({
  columns: { type: Object, default: () => ({ todo: [], doing: [], done: [] }) },
  staleDays: { type: Number, default: 3 },
})

const cols = reactive({
  todo: [...props.columns.todo],
  doing: [...props.columns.doing],
  done: [...props.columns.done],
})
const newCard = reactive({ todo: '', doing: '', done: '' })
const meta = [['todo', 'To Do'], ['doing', 'In Progress'], ['done', 'Done']]

function isStale(card) {
  if (!card.entered_column_at) return false
  return (Date.now() - new Date(card.entered_column_at)) / 86400000 > props.staleDays
}
function daysStuck(card) { return Math.round((Date.now() - new Date(card.entered_column_at)) / 86400000) }

/** vuedraggable fires this on any list change; persist positions with a single upsert call. */
function onChange(colKey) {
  router.post(route('kanban.reorder'), {
    column: colKey,
    order: cols[colKey].map((c, i) => ({ id: c.id, position: i * 10 })),
  }, { preserveScroll: true, preserveState: true })
}
function add(colKey) {
  const t = newCard[colKey].trim(); if (!t) return
  router.post(route('kanban.cards.store'), { column: colKey, title: t }, {
    preserveScroll: true,
    onSuccess: () => { newCard[colKey] = '' },
  })
}
const remove = id => router.delete(route('kanban.cards.destroy', id), { preserveScroll: true })
</script>

<template>
  <div class="kb">
    <header class="head">
      <div><h1>Kanban Board</h1><p class="sub">drag cards between columns · stuck &gt; {{ staleDays }} days gets flagged</p><span class="cadence">Ongoing</span></div>
    </header>

    <div class="board">
      <div v-for="m in meta" :key="m[0]" class="col" :class="m[0]">
        <div class="ch"><span>{{ m[1] }}</span><span class="cnt">{{ cols[m[0]].length }}</span></div>
        <draggable :list="cols[m[0]]" group="cards" item-key="id" @change="onChange(m[0])" ghost-class="ghost">
          <template #item="{ element }">
            <div class="kcard" :class="{ stale: m[0]==='doing' && isStale(element) }">
              <div class="top"><span>{{ element.title }}</span><button class="x" @click="remove(element.id)">×</button></div>
              <div class="tags">
                <span v-if="m[0]==='doing' && isStale(element)" class="pill rose">stuck {{ daysStuck(element) }}d</span>
                <span v-if="element.due_date" class="pill amber">due {{ element.due_date }}</span>
              </div>
            </div>
          </template>
        </draggable>
        <input v-model="newCard[m[0]]" @keyup.enter="add(m[0])" placeholder="+ add card" class="addcard" />
      </div>
    </div>
  </div>
</template>

<style scoped>
.kb{max-width:900px;margin:0 auto;padding:20px}
h1{font-size:23px;font-weight:800}.sub{color:#64748b;font-size:13px}
.cadence{font-size:10px;font-weight:700;text-transform:uppercase;color:#4f46e5;background:#eef2ff;padding:3px 9px;border-radius:999px;display:inline-block;margin-top:6px}
.board{display:grid;grid-template-columns:1fr;gap:14px;margin-top:16px}
@media(min-width:720px){.board{grid-template-columns:1fr 1fr 1fr}}
.col{border-radius:16px;padding:12px;min-height:120px}
.col.todo{background:#f1f5f9}.col.doing{background:#fffbeb}.col.done{background:#ecfdf5}
.ch{display:flex;justify-content:space-between;font-weight:700;font-size:13px;padding:2px 4px 8px}.ch .cnt{color:#94a3b8;font-weight:500}
.kcard{background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:11px;margin-bottom:8px;box-shadow:0 1px 3px rgba(15,23,42,.08);cursor:grab}
.kcard.stale{border-color:#fda4af}
.kcard .top{display:flex;justify-content:space-between;gap:6px}.kcard .top span{flex:1}
.tags{display:flex;gap:5px;flex-wrap:wrap;margin-top:7px}
.x{color:#cbd5e1;font-size:15px;background:none;border:none;cursor:pointer}
.addcard{border:1px solid #e2e8f0;border-radius:10px;padding:8px 10px;width:100%;font:inherit;font-size:13px;margin-top:6px}
.ghost{opacity:.4}
.pill{font-size:11px;font-weight:600;padding:3px 9px;border-radius:999px}
.pill.rose{background:#ffe4e6;color:#e11d48}.pill.amber{background:#fef3c7;color:#d97706}
</style>
