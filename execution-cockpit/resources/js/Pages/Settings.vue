<script setup>
import AppLayout from '../Layouts/AppLayout.vue'
defineOptions({ layout: AppLayout })
import { reactive } from 'vue'
import { router } from '@inertiajs/vue3'
import PushToggle from '../Components/PushToggle.vue'

/** Settings / Reminders. Props from SettingsController@edit. */
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
  <div class="set">
    <header class="head"><div><h1>Settings</h1><p class="sub">{{ workspace.name }} · {{ workspace.business_type }}</p></div></header>

    <PushToggle class="mt" />

    <section class="card">
      <h3>Reminder schedule</h3>
      <label class="fld"><span>Daily Day Close Report</span><input type="time" v-model="form.dcr_reminder_time" /></label>
      <label class="fld"><span>Weekly Review — day</span>
        <select v-model.number="form.weekly_reminder_dow"><option v-for="d in days" :key="d[0]" :value="d[0]">{{ d[1] }}</option></select>
      </label>
      <label class="fld"><span>Weekly Review — time</span><input type="time" v-model="form.weekly_reminder_time" /></label>
      <label class="fld"><span>Monthly deep-dive — day of month</span><input type="number" min="1" max="28" v-model.number="form.monthly_reminder_dom" /></label>
      <label class="fld"><span>Monthly deep-dive — time</span><input type="time" v-model="form.monthly_reminder_time" /></label>
    </section>

    <section class="card">
      <h3>Timezone <small>reminders fire in your local time</small></h3>
      <select v-model="form.timezone" class="full">
        <option v-for="tz in timezones" :key="tz" :value="tz">{{ tz }}</option>
      </select>
    </section>

    <section class="card">
      <h3>Channels</h3>
      <label class="chk"><input type="checkbox" :checked="form.channels.includes('webpush')" @change="toggleChannel('webpush')" /> Push notifications</label>
      <label class="chk"><input type="checkbox" :checked="form.channels.includes('mail')" @change="toggleChannel('mail')" /> Email fallback</label>
      <label class="chk"><input type="checkbox" v-model="form.enabled" /> Reminders enabled</label>
    </section>

    <button class="btn big" @click="save">Save settings</button>
  </div>
</template>

<style scoped>
.set{max-width:620px;margin:0 auto;padding:20px}
h1{font-size:24px;font-weight:800}.sub{color:#64748b;font-size:13px}
.mt{margin-top:16px}
.card{background:#fff;border:1px solid #e2e8f0;border-radius:16px;box-shadow:0 1px 3px rgba(15,23,42,.08);padding:16px;margin-top:16px}
h3{font-size:14px;margin-bottom:12px;display:flex;justify-content:space-between}h3 small{color:#94a3b8;font-weight:400}
.fld{display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;font-size:14px;gap:12px}
.fld input,.fld select{width:auto;border:1px solid #e2e8f0;border-radius:10px;padding:8px 10px}
.full{width:100%;border:1px solid #e2e8f0;border-radius:10px;padding:9px 11px}
.chk{display:flex;gap:8px;align-items:center;margin-bottom:10px;font-size:14px}
.btn{background:#4f46e5;color:#fff;border:none;border-radius:14px;padding:14px;font-weight:700;width:100%;margin-top:16px;font-size:16px;cursor:pointer}
</style>
