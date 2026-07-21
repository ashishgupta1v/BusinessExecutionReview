<script setup>
import AppLayout from '../Layouts/AppLayout.vue'
defineOptions({ layout: AppLayout })
import { ref, reactive, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import draggable from 'vuedraggable'

/**
 * Kanban Board — Antigravity Dark Glassmorphism UI.
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
const activeTab = ref('all') // 'all' | 'todo' | 'doing' | 'done'
const meta = [['todo', 'To Do', 'indigo'], ['doing', 'In Progress', 'amber'], ['done', 'Done', 'emerald']]

const visibleMeta = computed(() => {
  if (activeTab.value === 'all') return meta
  return meta.filter(m => m[0] === activeTab.value)
})

function isStale(card) {
  if (!card.entered_column_at) return false
  return (Date.now() - new Date(card.entered_column_at)) / 86400000 > props.staleDays
}
function daysStuck(card) { return Math.round((Date.now() - new Date(card.entered_column_at)) / 86400000) }

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
  <div class="kanban-page">
    <header class="kanban-header">
      <div>
        <div class="badge-tag">ONGOING EXECUTION</div>
        <h1 class="page-title">Kanban Board</h1>
        <p class="page-sub">drag cards between columns · stuck &gt; {{ staleDays }} days gets flagged</p>
      </div>

      <!-- Mobile Column Switcher -->
      <div class="mobile-tab-bar">
        <button 
          class="tab-btn" 
          :class="{ active: activeTab === 'all' }" 
          @click="activeTab = 'all'"
        >All</button>
        <button 
          v-for="m in meta" 
          :key="m[0]" 
          class="tab-btn" 
          :class="{ active: activeTab === m[0] }" 
          @click="activeTab = m[0]"
        >
          {{ m[1] }} ({{ cols[m[0]].length }})
        </button>
      </div>
    </header>

    <div class="board-grid">
      <div v-for="m in visibleMeta" :key="m[0]" class="glass-card column-card" :class="'col-' + m[2]">
        <div class="column-header">
          <span class="column-title">{{ m[1] }}</span>
          <span class="column-count">{{ cols[m[0]].length }}</span>
        </div>

        <draggable 
          :list="cols[m[0]]" 
          group="cards" 
          item-key="id" 
          @change="onChange(m[0])" 
          ghost-class="drag-ghost"
          class="card-list"
        >
          <template #item="{ element }">
            <div class="glass-card task-card" :class="{ 'card-stale': m[0]==='doing' && isStale(element) }">
              <div class="task-top">
                <span class="task-title">{{ element.title }}</span>
                <button class="remove-btn" @click="remove(element.id)">×</button>
              </div>

              <div class="task-tags" v-if="(m[0]==='doing' && isStale(element)) || element.due_date">
                <span v-if="m[0]==='doing' && isStale(element)" class="tag-pill tag-rose">
                  stuck {{ daysStuck(element) }}d
                </span>
                <span v-if="element.due_date" class="tag-pill tag-amber">
                  due {{ element.due_date }}
                </span>
              </div>
            </div>
          </template>
        </draggable>

        <div class="add-card-wrapper">
          <input 
            v-model="newCard[m[0]]" 
            @keyup.enter="add(m[0])" 
            placeholder="+ Add new card..." 
            class="glass-input add-card-input" 
          />
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.kanban-page {
  max-width: 1060px;
  margin: 0 auto;
  padding: 32px 20px;
}

.kanban-header {
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

/* Mobile Tab Switcher */
.mobile-tab-bar {
  display: none;
  gap: 6px;
  margin-top: 14px;
  overflow-x: auto;
  padding-bottom: 4px;
}
.tab-btn {
  padding: 6px 12px;
  border-radius: 999px;
  font-size: 12px;
  font-weight: 600;
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  color: #94a3b8;
  cursor: pointer;
  white-space: nowrap;
}
.tab-btn.active {
  background: #6366f1;
  color: #ffffff;
  border-color: #818cf8;
}

.board-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 20px;
}
@media (min-width: 768px) {
  .board-grid {
    grid-template-columns: repeat(3, 1fr);
  }
}

.column-card {
  padding: 16px;
  display: flex;
  flex-direction: column;
  min-height: 440px;
}
.col-indigo { border-top: 3px solid #6366f1; }
.col-amber { border-top: 3px solid #f59e0b; }
.col-emerald { border-top: 3px solid #10b981; }

.column-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 14px;
  padding: 0 4px;
}
.column-title {
  font-size: 15px;
  font-weight: 800;
  color: #ffffff;
}
.column-count {
  font-size: 12px;
  font-weight: 700;
  color: #94a3b8;
  background: rgba(255, 255, 255, 0.08);
  padding: 2px 10px;
  border-radius: 999px;
}

.card-list {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 10px;
  min-height: 160px;
}

.task-card {
  padding: 14px;
  background: rgba(15, 23, 42, 0.8);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 14px;
  cursor: grab;
  transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
  touch-action: manipulation;
}
.task-card:hover {
  transform: translateY(-2px);
  border-color: rgba(99, 102, 241, 0.4);
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.4);
}
.card-stale {
  border-color: rgba(244, 63, 94, 0.5) !important;
  background: rgba(244, 63, 94, 0.08) !important;
}

.task-top {
  display: flex;
  justify-content: space-between;
  gap: 8px;
}
.task-title {
  font-size: 14px;
  color: #f8fafc;
  font-weight: 500;
  line-height: 1.4;
}

.task-tags {
  display: flex;
  gap: 6px;
  flex-wrap: wrap;
  margin-top: 10px;
}
.tag-pill {
  font-size: 10px;
  font-weight: 700;
  padding: 2px 8px;
  border-radius: 999px;
}
.tag-rose { background: rgba(244, 63, 94, 0.2); color: #fb7185; }
.tag-amber { background: rgba(245, 158, 11, 0.2); color: #fbbf24; }

.remove-btn {
  background: none;
  border: none;
  color: #64748b;
  font-size: 18px;
  cursor: pointer;
  padding: 0 4px;
}
.remove-btn:hover { color: #f43f5e; }

.add-card-wrapper {
  margin-top: 14px;
}
.add-card-input {
  width: 100%;
  padding: 10px 12px;
  font-size: 14px;
}

.drag-ghost {
  opacity: 0.4;
  border: 2px dashed #818cf8;
}

@media (max-width: 767px) {
  .kanban-page {
    padding: 16px 12px;
  }
  .mobile-tab-bar {
    display: flex;
  }
  .page-title {
    font-size: 22px;
  }
}
</style>
