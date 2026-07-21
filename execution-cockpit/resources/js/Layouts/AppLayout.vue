<script setup>
import { ref, computed } from 'vue'
import { Link, usePage, router } from '@inertiajs/vue3'

/**
 * Antigravity Dark Glassmorphic App Shell.
 * Provides a unified spatial background, glass sidebar, mobile profile drawer, and glowing navigation.
 */
const page = usePage()
const user = computed(() => page.props.auth?.user ?? {})
const streak = computed(() => page.props.streak?.current ?? 0)
const mobileMenuOpen = ref(false)

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
const toggleMobileMenu = () => { mobileMenuOpen.value = !mobileMenuOpen.value }
</script>

<template>
  <div class="shell">
    <!-- Ambient Glowing Orbs -->
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>

    <!-- Desktop Sidebar -->
    <aside class="sidebar">
      <Link href="/" class="brand">
        <div class="logo">BET</div>
        <div class="brand-text">
          <b>Business Execution</b>
          <span>Toolkit</span>
        </div>
      </Link>

      <div class="streakbox">
        <div class="lbl">Active Streak</div>
        <div class="num">
          {{ streak }} <span class="flame-anim">🔥</span> <small>days</small>
        </div>
      </div>

      <div class="nav-section">
        <Link 
          v-for="n in nav" 
          :key="n.route" 
          :href="route(n.route)" 
          class="navbtn" 
          :class="{ active: isActive(n) }"
        >
          <span class="ic">{{ n.icon }}</span>
          <span class="nlbl">
            <b>{{ n.name }}</b>
            <em>{{ n.cadence }}</em>
          </span>
        </Link>
      </div>

      <div class="foot">
        <div class="user-badge">
          <div class="avatar">{{ user.name ? user.name.charAt(0).toUpperCase() : 'U' }}</div>
          <div class="user-info">
            <span class="who">{{ user.name || 'User' }}</span>
            <span class="email">{{ user.email || '' }}</span>
          </div>
        </div>
        <div class="foot-actions">
          <Link :href="route('settings.edit')" class="foot-link">
            <span>⚙</span> Settings
          </Link>
          <button class="logout-btn" @click="logout">
            <span>↳</span> Sign out
          </button>
        </div>
      </div>
    </aside>

    <!-- Main Content Area -->
    <main class="main">
      <!-- Mobile Top Bar -->
      <header class="topbar">
        <div class="tb-left">
          <div class="logo-sm">BET</div>
          <span class="tb-title">Business Execution Toolkit</span>
        </div>
        <div class="tb-right">
          <div class="tb-streak">
            <span>{{ streak }}</span> <span class="flame-anim">🔥</span>
          </div>
          <button class="avatar-btn" @click="toggleMobileMenu" aria-label="Open profile menu">
            {{ user.name ? user.name.charAt(0).toUpperCase() : 'U' }}
          </button>
        </div>
      </header>

      <!-- Mobile Profile Popover Modal -->
      <div v-if="mobileMenuOpen" class="mobile-menu-backdrop" @click="mobileMenuOpen = false">
        <div class="mobile-menu-card" @click.stop>
          <div class="mm-header">
            <div class="avatar-lg">{{ user.name ? user.name.charAt(0).toUpperCase() : 'U' }}</div>
            <div class="mm-user">
              <b>{{ user.name || 'User' }}</b>
              <span>{{ user.email || '' }}</span>
            </div>
            <button class="mm-close" @click="mobileMenuOpen = false">&times;</button>
          </div>
          <div class="mm-links">
            <Link :href="route('settings.edit')" class="mm-item" @click="mobileMenuOpen = false">
              <span>⚙</span> Account & Reminders
            </Link>
            <button class="mm-item mm-logout" @click="logout">
              <span>↳</span> Sign out
            </button>
          </div>
        </div>
      </div>

      <div class="content">
        <slot />
      </div>
    </main>

    <!-- Mobile Bottom Navigation Bar -->
    <nav class="bnav">
      <Link 
        v-for="n in nav" 
        :key="n.route" 
        :href="route(n.route)" 
        class="bbtn" 
        :class="{ active: isActive(n) }"
      >
        <span class="ic">{{ n.icon }}</span>
        <span class="btext">{{ n.short }}</span>
      </Link>
    </nav>
  </div>
</template>

<style scoped>
.shell {
  display: flex;
  min-height: 100vh;
  background: #070b19;
  background-image: 
    radial-gradient(at 0% 0%, rgba(30, 27, 75, 0.6) 0px, transparent 50%),
    radial-gradient(at 100% 100%, rgba(15, 23, 42, 0.9) 0px, transparent 50%),
    radial-gradient(at 50% 50%, rgba(124, 58, 237, 0.05) 0px, transparent 50%);
  position: relative;
  overflow-x: hidden;
}

/* Background Ambient Orbs */
.orb {
  position: fixed;
  border-radius: 50%;
  filter: blur(100px);
  pointer-events: none;
  z-index: 0;
}
.orb-1 {
  width: 400px;
  height: 400px;
  background: rgba(99, 102, 241, 0.12);
  top: -100px;
  left: -100px;
}
.orb-2 {
  width: 450px;
  height: 450px;
  background: rgba(168, 85, 247, 0.1);
  bottom: -150px;
  right: -100px;
}

/* Sidebar */
.sidebar {
  width: 260px;
  background: rgba(11, 16, 32, 0.85);
  backdrop-filter: blur(20px);
  -webkit-backdrop-filter: blur(20px);
  border-right: 1px solid rgba(255, 255, 255, 0.08);
  color: #cbd5e1;
  padding: 24px 16px;
  position: sticky;
  top: 0;
  height: 100vh;
  display: flex;
  flex-direction: column;
  flex-shrink: 0;
  z-index: 10;
}

.brand {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 24px;
  padding: 0 4px;
  text-decoration: none;
}
.brand .logo {
  width: 42px;
  height: 42px;
  border-radius: 12px;
  background: linear-gradient(135deg, #6366f1 0%, #4338ca 100%);
  display: grid;
  place-items: center;
  color: #ffffff;
  font-weight: 800;
  font-size: 15px;
  box-shadow: 0 0 20px rgba(99, 102, 241, 0.4);
}
.brand-text b {
  color: #ffffff;
  font-size: 15px;
  display: block;
  line-height: 1.1;
}
.brand-text span {
  font-size: 12px;
  color: #818cf8;
  letter-spacing: 0.02em;
}

/* Streak Widget */
.streakbox {
  margin: 0 2px 20px;
  padding: 14px 16px;
  border-radius: 16px;
  background: linear-gradient(135deg, rgba(99, 102, 241, 0.15) 0%, rgba(139, 92, 246, 0.1) 100%);
  border: 1px solid rgba(99, 102, 241, 0.3);
  box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.1), 0 8px 20px -6px rgba(0,0,0,0.4);
}
.streakbox .lbl {
  font-size: 11px;
  color: #94a3b8;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  font-weight: 600;
}
.streakbox .num {
  font-size: 28px;
  font-weight: 900;
  color: #ffffff;
  margin-top: 2px;
  display: flex;
  align-items: center;
  gap: 6px;
}
.streakbox small {
  font-size: 13px;
  font-weight: 500;
  color: #94a3b8;
}

/* Navigation Links */
.nav-section {
  display: flex;
  flex-direction: column;
  gap: 4px;
}
.navbtn {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 10px 14px;
  border-radius: 14px;
  color: #94a3b8;
  text-decoration: none;
  transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
  border: 1px solid transparent;
}
.navbtn .ic {
  width: 26px;
  height: 26px;
  display: grid;
  place-items: center;
  font-size: 14px;
  border-radius: 8px;
  background: rgba(255, 255, 255, 0.05);
  transition: all 0.2s ease;
}
.navbtn .nlbl {
  display: flex;
  flex-direction: column;
  line-height: 1.2;
}
.navbtn .nlbl b {
  font-weight: 600;
  font-size: 14px;
  color: #e2e8f0;
}
.navbtn .nlbl em {
  font-style: normal;
  font-size: 10px;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: 0.04em;
  font-weight: 600;
}
.navbtn:hover {
  background: rgba(255, 255, 255, 0.06);
  color: #ffffff;
  border-color: rgba(255, 255, 255, 0.08);
  transform: translateX(3px);
}
.navbtn:hover .nlbl b {
  color: #ffffff;
}
.navbtn.active {
  background: linear-gradient(135deg, rgba(99, 102, 241, 0.9) 0%, rgba(79, 70, 229, 0.95) 100%);
  color: #ffffff;
  border-color: rgba(165, 180, 252, 0.4);
  box-shadow: 0 8px 20px -4px rgba(99, 102, 241, 0.5);
}
.navbtn.active .ic {
  background: rgba(255, 255, 255, 0.2);
  color: #ffffff;
}
.navbtn.active .nlbl b {
  color: #ffffff;
}
.navbtn.active .nlbl em {
  color: #c7d2fe;
}

/* Footer & Profile */
.foot {
  margin-top: auto;
  padding-top: 16px;
  border-top: 1px solid rgba(255, 255, 255, 0.08);
}
.user-badge {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 12px;
  padding: 4px;
}
.avatar {
  width: 34px;
  height: 34px;
  border-radius: 50%;
  background: linear-gradient(135deg, #4f46e5, #9333ea);
  color: #fff;
  font-weight: 700;
  display: grid;
  place-items: center;
  font-size: 14px;
  box-shadow: 0 0 12px rgba(147, 51, 234, 0.4);
}
.user-info {
  display: flex;
  flex-direction: column;
  overflow: hidden;
}
.user-info .who {
  font-size: 13px;
  font-weight: 600;
  color: #f1f5f9;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.user-info .email {
  font-size: 11px;
  color: #64748b;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.foot-actions {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 8px;
}
.foot-link {
  color: #94a3b8;
  font-size: 12px;
  text-decoration: none;
  padding: 6px 10px;
  border-radius: 8px;
  transition: all 0.15s ease;
  background: rgba(255, 255, 255, 0.04);
}
.foot-link:hover {
  color: #ffffff;
  background: rgba(255, 255, 255, 0.08);
}
.logout-btn {
  color: #f43f5e;
  background: rgba(244, 63, 94, 0.1);
  border: 1px solid rgba(244, 63, 94, 0.2);
  cursor: pointer;
  font-size: 12px;
  padding: 6px 10px;
  border-radius: 8px;
  transition: all 0.15s ease;
  font-weight: 500;
}
.logout-btn:hover {
  background: rgba(244, 63, 94, 0.2);
  color: #ff3355;
}

/* Main Content Wrapper */
.main {
  flex: 1;
  min-width: 0;
  position: relative;
  z-index: 1;
  padding-bottom: 90px;
}
.content {
  max-width: 100%;
}

/* Mobile Responsive Header & Nav */
.topbar {
  display: none;
}
.bnav {
  display: none;
}

@media (max-width: 768px) {
  .sidebar {
    display: none;
  }
  .main {
    padding-bottom: calc(72px + env(safe-area-inset-bottom, 0px));
  }
  .topbar {
    display: flex;
    position: sticky;
    top: 0;
    z-index: 50;
    background: rgba(11, 16, 32, 0.92);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    color: #fff;
    padding: 10px 14px;
    justify-content: space-between;
    align-items: center;
  }
  .tb-left {
    display: flex;
    align-items: center;
    gap: 8px;
    min-width: 0;
    flex: 1;
  }
  .logo-sm {
    width: 30px;
    height: 30px;
    border-radius: 8px;
    background: linear-gradient(135deg, #6366f1, #4338ca);
    display: grid;
    place-items: center;
    font-weight: 800;
    font-size: 11px;
    flex-shrink: 0;
  }
  .tb-title {
    font-weight: 700;
    font-size: 13px;
    color: #f8fafc;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }
  .tb-right {
    display: flex;
    align-items: center;
    gap: 10px;
  }
  .tb-streak {
    font-weight: 800;
    font-size: 13px;
    color: #f59e0b;
    display: flex;
    align-items: center;
    gap: 4px;
    background: rgba(245, 158, 11, 0.1);
    padding: 4px 8px;
    border-radius: 999px;
    border: 1px solid rgba(245, 158, 11, 0.2);
  }
  .avatar-btn {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: linear-gradient(135deg, #4f46e5, #9333ea);
    color: #ffffff;
    font-weight: 700;
    font-size: 13px;
    border: 1px solid rgba(255, 255, 255, 0.2);
    cursor: pointer;
    display: grid;
    place-items: center;
  }

  /* Mobile Profile Popover Modal */
  .mobile-menu-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.7);
    backdrop-filter: blur(8px);
    z-index: 100;
    display: flex;
    align-items: flex-end;
    padding: 16px;
  }
  .mobile-menu-card {
    width: 100%;
    background: #0f172a;
    border: 1px solid rgba(255, 255, 255, 0.15);
    border-radius: 20px;
    padding: 20px;
    box-shadow: 0 -10px 40px rgba(0, 0, 0, 0.8);
  }
  .mm-header {
    display: flex;
    align-items: center;
    gap: 12px;
    padding-bottom: 16px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    margin-bottom: 16px;
    position: relative;
  }
  .avatar-lg {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: linear-gradient(135deg, #4f46e5, #9333ea);
    color: #ffffff;
    font-weight: 800;
    font-size: 18px;
    display: grid;
    place-items: center;
  }
  .mm-user {
    display: flex;
    flex-direction: column;
  }
  .mm-user b {
    color: #ffffff;
    font-size: 15px;
  }
  .mm-user span {
    color: #94a3b8;
    font-size: 12px;
  }
  .mm-close {
    position: absolute;
    right: 0;
    top: 0;
    background: none;
    border: none;
    color: #94a3b8;
    font-size: 24px;
    cursor: pointer;
  }
  .mm-links {
    display: flex;
    flex-direction: column;
    gap: 8px;
  }
  .mm-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 14px;
    border-radius: 12px;
    background: rgba(255, 255, 255, 0.05);
    color: #f1f5f9;
    text-decoration: none;
    font-size: 14px;
    font-weight: 600;
    border: 1px solid rgba(255, 255, 255, 0.08);
  }
  .mm-logout {
    color: #f43f5e;
    background: rgba(244, 63, 94, 0.1);
    border-color: rgba(244, 63, 94, 0.2);
    cursor: pointer;
    width: 100%;
  }

  .bnav {
    display: grid;
    grid-template-columns: repeat(6, 1fr);
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    background: rgba(11, 16, 32, 0.95);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border-top: 1px solid rgba(255, 255, 255, 0.12);
    padding-bottom: env(safe-area-inset-bottom, 0px);
    z-index: 50;
  }
  .bbtn {
    padding: 8px 2px;
    min-height: 52px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 2px;
    font-size: 9px;
    color: #64748b;
    text-decoration: none;
    touch-action: manipulation;
  }
  .bbtn .ic {
    font-size: 16px;
    line-height: 1;
  }
  .bbtn.active {
    color: #818cf8;
    font-weight: 700;
  }
}
</style>
