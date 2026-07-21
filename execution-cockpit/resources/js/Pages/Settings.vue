<script setup>
import AppLayout from '../Layouts/AppLayout.vue'
defineOptions({ layout: AppLayout })
import { reactive } from 'vue'
import { router } from '@inertiajs/vue3'
import PushToggle from '../Components/PushToggle.vue'

/** Settings — Antigravity Dark Glassmorphism UI. */
const props = defineProps({
  timezone: { type: String, default: 'Asia/Kolkata' },
  workspace: { type: Object, default: () => ({}) },
  reminders: { type: Object, required: true },
  timezones: { type: Array, default: () => [] },
})

const form = reactive({ timezone: props.timezone, ...props.reminders })
const days = [[1,'Mon'],[2,'Tue'],[3,'Wed'],[4,'Thu'],[5,'Fri'],[6,'Sat'],[7,'Sun']]

function toggleChannel(c) {
  const i = form.channels.indexOf(c)
  if (i >= 0) form.channels.splice(i, 1); else form.channels.push(c)
}
function save() {
  router.put(route('settings.update'), { ...form }, { preserveScroll: true })
}
</script>

<template>
  <div class="settings-page">
    <header class="settings-header">
      <div>
        <div class="badge-tag">WORKSPACE CONTROL</div>
        <h1 class="page-title">Settings</h1>
        <p class="page-sub">{{ workspace.name }} · {{ workspace.business_type }}</p>
      </div>
    </header>

    <div class="mt-4">
      <PushToggle />
    </div>

    <!-- Reminder Schedule Card -->
    <section class="glass-card section-card">
      <h3 class="card-subtitle">Reminder Schedule</h3>
      
      <div class="field-row">
        <span>Daily Day Close Report</span>
        <input type="time" v-model="form.dcr_reminder_time" class="glass-input time-input" />
      </div>

      <div class="field-row">
        <span>Weekly Review — Day of week</span>
        <select v-model.number="form.weekly_reminder_dow" class="glass-input select-input">
          <option v-for="d in days" :key="d[0]" :value="d[0]">{{ d[1] }}</option>
        </select>
      </div>

      <div class="field-row">
        <span>Weekly Review — Time</span>
        <input type="time" v-model="form.weekly_reminder_time" class="glass-input time-input" />
      </div>

      <div class="field-row">
        <span>Monthly Deep-dive — Day of month</span>
        <input type="number" min="1" max="28" v-model.number="form.monthly_reminder_dom" class="glass-input num-input" />
      </div>

      <div class="field-row">
        <span>Monthly Deep-dive — Time</span>
        <input type="time" v-model="form.monthly_reminder_time" class="glass-input time-input" />
      </div>
    </section>

    <!-- Timezone Card -->
    <section class="glass-card section-card">
      <h3 class="card-subtitle">
        Timezone 
        <small class="helper-text">reminders fire in your local time</small>
      </h3>
      <select v-model="form.timezone" class="glass-input full-select">
        <option v-for="tz in timezones" :key="tz" :value="tz">{{ tz }}</option>
      </select>
    </section>

    <!-- Notification Channels Card -->
    <section class="glass-card section-card">
      <h3 class="card-subtitle">Notification Channels</h3>
      
      <div class="chk-list">
        <label class="chk-item">
          <input type="checkbox" :checked="form.channels.includes('webpush')" @change="toggleChannel('webpush')" />
          <span class="chk-text">Push notifications (Desktop & Mobile Browser)</span>
        </label>

        <label class="chk-item">
          <input type="checkbox" :checked="form.channels.includes('mail')" @change="toggleChannel('mail')" />
          <span class="chk-text">Email fallback notifications</span>
        </label>

        <label class="chk-item">
          <input type="checkbox" v-model="form.enabled" />
          <span class="chk-text">All reminders enabled</span>
        </label>
      </div>
    </section>

    <!-- Save Button -->
    <div class="action-wrapper">
      <button class="btn-glow big-submit-btn" @click="save">
        <span>Save Settings ✓</span>
      </button>
    </div>
  </div>
</template>

<style scoped>
.settings-page {
  max-width: 680px;
  margin: 0 auto;
  padding: 32px 20px;
}

.settings-header {
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

.mt-4 { margin-top: 16px; }

.section-card {
  padding: 22px;
  margin-top: 20px;
}
.card-subtitle {
  font-size: 16px;
  font-weight: 800;
  color: #ffffff;
  margin-bottom: 16px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.helper-text {
  font-size: 12px;
  color: #64748b;
  font-weight: 400;
}

.field-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 12px;
  font-size: 14px;
  color: #e2e8f0;
}
.time-input, .select-input, .num-input {
  padding: 8px 12px !important;
  font-size: 13px;
}
.num-input { width: 80px; }
.full-select {
  width: 100%;
  padding: 10px 14px !important;
}

.chk-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}
.chk-item {
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 14px;
  color: #e2e8f0;
  cursor: pointer;
}
.chk-item input {
  width: 16px;
  height: 16px;
  accent-color: #6366f1;
}

.action-wrapper {
  margin-top: 24px;
}
.big-submit-btn {
  width: 100%;
  padding: 16px;
  font-size: 16px;
  border: none;
  cursor: pointer;
}
</style>
