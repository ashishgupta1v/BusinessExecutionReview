<script setup>
import { computed } from 'vue'
import { Link, usePage, router } from '@inertiajs/vue3'

/**
 * Shared app shell — sidebar (desktop) + bottom nav (mobile), mirroring the prototype.
 * Use as an Inertia persistent layout from each page:
 *
 *   <script setup>
 *   import AppLayout from '@/Layouts/AppLayout.vue'
 *   defineOptions({ layout: AppLayout })
 *   </script>
 *
 * Share `streak` and `auth.user` from HandleInertiaRequests::share() so the header can read them.
 */
const page = usePage()
const user = computed(() => page.props.auth?.user ?? {})
const streak = computed(() => page.props.streak?.current ?? 0)

const nav = [
  { name: 'Day Close Report', route: 'dcr.show',       match: 'dcr',      icon: '◉', cadence: 'Daily',   short: 'DCR' },
  { name: 'Weekly Review',    route: 'weekly.show',     match: 'weekly',   icon: '✎', cadence: 'Weekly',  short: 'Review' },
  { name: 'KPI Tracker',      route: 'kpi.index',       match: 'kpi',      icon: '▦', cadence: 'Weekly',  short: 'KPIs' },
  { name: 'Feedback Log',     route: 'feedback.index',  match: 'feedback', icon: '✉', cadence: 'Monthly', short: 'Feedback' },
  { name: 'Kanban Board',     route: 'kanban.index',    match: 'kanban',   icon: '▤', cadence: 'Ongoing', short: 'Kanban' },
  { name: 'Overview',         route: 'overview.index',  match: 'overview', icon: '✦', cadence: 'Live',    short: 'Overview' },
]
const current = computed(() => nav.find(n => route().current(n.route)) ?? nav[0])
const isActive = n => route().current(n.route)
const logout = () => router.post(route('logout'))
</script>

<template>
  <div class="shell">
    <aside class="sidebar">
      <div class="brand"><div class="logo">EC</div><div><b>Execution</b><span>Cockpit</span></div></div>
      <div class="streakbox"><div class="lbl">Current streak</div><div class="num">{{ streak }} <small>days 🔥</small></div></div>
      <Link v-for="n in nav" :key="n.route" :href="route(n.route)" class="navbtn" :class="{ active: isActive(n) }">
        <span class="ic">{{ n.icon }}</span>
        <span class="nlbl"><b>{{ n.name }}</b><em>{{ n.cadence }}</em></span>
      </Link>
      <div class="foot">
        <div class="who">{{ user.name }}</div>
        <Link :href="route('settings.edit')" class="settings">⚙ Settings</Link>
        <button class="logout" @click="logout">Sign out</button>
      </div>
    </aside>

    <main class="main">
      <header class="topbar"><span>{{ current.name }}</span><span>{{ streak }}🔥</span></header>
      <div class="content"><slot /></div>
    </main>

    <nav class="bnav">
      <Link v-for="n in nav" :key="n.route" :href="route(n.route)" class="bbtn" :class="{ active: isActive(n) }">
        <span class="ic">{{ n.icon }}</span>{{ n.short }}
      </Link>
    </nav>
  </div>
</template>

<style scoped>
.shell{display:flex;min-height:100vh;background:#f1f5f9}
.sidebar{width:248px;background:linear-gradient(180deg,#111827,#0b1020);color:#cbd5e1;padding:18px 14px;position:sticky;top:0;height:100vh;display:flex;flex-direction:column;flex-shrink:0}
.brand{display:flex;align-items:center;gap:10px;margin-bottom:20px;padding:0 4px}
.brand .logo{width:40px;height:40px;border-radius:12px;background:linear-gradient(135deg,#6366f1,#4338ca);display:grid;place-items:center;color:#fff;font-weight:800}
.brand b{color:#fff;font-size:15px;display:block;line-height:1.1}.brand span{font-size:12px;color:#94a3b8}
.streakbox{margin:2px 4px 14px;padding:12px;border-radius:14px;background:rgba(99,102,241,.12);border:1px solid rgba(99,102,241,.25)}
.streakbox .lbl{font-size:11px;color:#94a3b8;text-transform:uppercase;letter-spacing:.04em}
.streakbox .num{font-size:26px;font-weight:800;color:#fff}.streakbox small{font-size:13px;font-weight:400;color:#94a3b8}
.navbtn{display:flex;align-items:center;gap:12px;padding:9px 12px;border-radius:12px;margin-bottom:3px;color:#cbd5e1;text-decoration:none;transition:.15s}
.navbtn .ic{width:24px;height:24px;display:grid;place-items:center;font-size:15px;border-radius:8px;background:rgba(255,255,255,.05)}
.navbtn .nlbl{display:flex;flex-direction:column;line-height:1.15}
.navbtn .nlbl b{font-weight:600;font-size:14px}.navbtn .nlbl em{font-style:normal;font-size:10px;color:#64748b;text-transform:uppercase}
.navbtn:hover{background:rgba(255,255,255,.06);color:#fff}
.navbtn.active{background:#4f46e5;color:#fff}.navbtn.active .ic{background:rgba(255,255,255,.18)}.navbtn.active .nlbl em{color:#c7d2fe}
.foot{margin-top:auto;padding:12px 4px 0;font-size:12px;color:#64748b}
.foot .who{margin-bottom:6px}
.logout{color:#94a3b8;background:none;border:none;cursor:pointer;font-size:12px;padding:0}
.logout:hover{color:#fff}
.main{flex:1;min-width:0;padding-bottom:80px}
.topbar{display:none}
.content{max-width:100%}
.bnav{display:none}
@media(max-width:719px){
  .sidebar{display:none}
  .topbar{display:flex;position:sticky;top:0;z-index:10;background:#0f172a;color:#fff;padding:12px 16px;justify-content:space-between;align-items:center;font-weight:700}
  .bnav{display:grid;grid-template-columns:repeat(6,1fr);position:fixed;bottom:0;left:0;right:0;background:#fff;border-top:1px solid #e2e8f0;z-index:20}
  .bbtn{padding:8px 0;display:flex;flex-direction:column;align-items:center;gap:2px;font-size:10px;color:#94a3b8;text-decoration:none}
  .bbtn .ic{font-size:17px}.bbtn.active{color:#4f46e5}
}
</style>
