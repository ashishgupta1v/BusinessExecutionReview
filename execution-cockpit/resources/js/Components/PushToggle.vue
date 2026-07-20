<script setup>
import { ref, onMounted } from 'vue'
import { enablePush, disablePush, isPushSupported, needsHomeScreenInstall } from '../push'

const supported = ref(true)
const iosInstall = ref(false)
const enabled = ref(false)
const busy = ref(false)
const error = ref('')

onMounted(async () => {
  supported.value = isPushSupported()
  iosInstall.value = needsHomeScreenInstall()
  if (supported.value && 'serviceWorker' in navigator) {
    const reg = await navigator.serviceWorker.ready.catch(() => null)
    const sub = reg && await reg.pushManager.getSubscription()
    enabled.value = !!sub
  }
})

async function toggle() {
  busy.value = true; error.value = ''
  try {
    if (enabled.value) { await disablePush(); enabled.value = false }
    else { await enablePush(); enabled.value = true }
  } catch (e) { error.value = e.message } finally { busy.value = false }
}
</script>

<template>
  <div class="push">
    <div class="row">
      <div>
        <b>Daily reminders</b>
        <p>Get a push at 6 PM to file your Day Close Report.</p>
      </div>
      <button class="sw" :class="{on:enabled}" :disabled="busy || !supported || iosInstall" @click="toggle">
        <span class="knob" />
      </button>
    </div>
    <p v-if="iosInstall" class="hint">On iPhone/iPad: tap <b>Share → Add to Home Screen</b>, open the app from your home screen, then enable reminders here.</p>
    <p v-else-if="!supported" class="hint">This browser doesn't support push notifications.</p>
    <p v-if="error" class="err">{{ error }}</p>
  </div>
</template>

<style scoped>
.push{background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:16px}
.row{display:flex;justify-content:space-between;align-items:center;gap:12px}
b{font-size:14px}p{color:#64748b;font-size:13px;margin-top:2px}
.sw{width:48px;height:28px;border-radius:999px;background:#e2e8f0;position:relative;transition:.2s;flex-shrink:0;border:none;cursor:pointer}
.sw.on{background:#4f46e5}.sw:disabled{opacity:.5;cursor:not-allowed}
.knob{position:absolute;top:3px;left:3px;width:22px;height:22px;background:#fff;border-radius:50%;transition:.2s}
.sw.on .knob{left:23px}
.hint{margin-top:10px;font-size:12px;color:#64748b;background:#f8fafc;padding:9px 11px;border-radius:10px}
.err{margin-top:8px;font-size:12px;color:#e11d48}
</style>
