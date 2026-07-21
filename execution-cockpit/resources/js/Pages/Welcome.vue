<script setup>
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import GoogleLoginButton from '@/Components/GoogleLoginButton.vue';

defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
});

const mobileNavOpen = ref(false);

const faqs = ref([
    {
        q: 'What is Business Execution Toolkit?',
        a: 'Business Execution Toolkit is a multi-tenant business operating system designed for founders, agency owners, and high-performing teams to bridge the gap between high-level strategic goals and daily execution.',
        open: true,
    },
    {
        q: 'How does the Cadence Engine work?',
        a: 'The app structures your operational rhythm into 4 distinct cadences: Daily Close Reports (DCR), Weekly Reviews & KPIs, Monthly 360° Partner Feedback, and Ongoing Kanban execution.',
        open: false,
    },
    {
        q: 'How does Google Authentication work?',
        a: 'You can sign in with one click using your Google account. Your workspace, roles, and default agency KPIs are automatically provisioned in under 5 seconds.',
        open: false,
    },
    {
        q: 'Can I track streaks and set WebPush reminders?',
        a: 'Yes! Daily discipline items automatically track your active streak. WebPush notifications send daily and weekly nudges directly to your desktop or mobile browser.',
        open: false,
    },
]);

const toggleFaq = (index) => {
    faqs.value[index].open = !faqs.value[index].open;
};
</script>

<template>
    <Head title="Business Execution Toolkit — Turn Strategy Into Daily Execution" />

    <div class="landing-page">
        <!-- Ambient Glowing Background Spheres -->
        <div class="glow-orb orb-top-left"></div>
        <div class="glow-orb orb-top-right"></div>
        <div class="glow-orb orb-mid-left"></div>
        <div class="glow-orb orb-bottom-right"></div>

        <!-- Navigation Bar -->
        <header class="navbar">
            <div class="nav-container">
                <Link href="/" class="nav-brand">
                    <div class="logo-icon">BET</div>
                    <div class="brand-info">
                        <span class="brand-name"><b>Business Execution Toolkit</b></span>
                        <span class="brand-sub">Operating System</span>
                    </div>
                </Link>

                <!-- Desktop Links -->
                <nav class="nav-links">
                    <a href="#features">Features</a>
                    <a href="#cadence">Cadence Engine</a>
                    <a href="#preview">Cockpit Preview</a>
                    <a href="#faq">FAQ</a>
                </nav>

                <!-- Desktop Actions -->
                <div class="nav-actions">
                    <Link :href="route('login')" class="btn-ghost">Sign In</Link>
                    <a :href="route('google.redirect')" class="btn-primary">
                        <span>Google Auth</span>
                    </a>
                </div>

                <!-- Mobile Hamburger Toggle -->
                <button class="mobile-hamburger" @click="mobileNavOpen = !mobileNavOpen" aria-label="Toggle navigation">
                    <span class="bar"></span>
                    <span class="bar"></span>
                    <span class="bar"></span>
                </button>
            </div>

            <!-- Mobile Navigation Drawer -->
            <div v-if="mobileNavOpen" class="mobile-nav-drawer" @click="mobileNavOpen = false">
                <div class="drawer-content" @click.stop>
                    <a href="#features" @click="mobileNavOpen = false">Features</a>
                    <a href="#cadence" @click="mobileNavOpen = false">Cadence Engine</a>
                    <a href="#preview" @click="mobileNavOpen = false">Cockpit Preview</a>
                    <a href="#faq" @click="mobileNavOpen = false">FAQ</a>
                    <div class="drawer-divider"></div>
                    <a :href="route('google.redirect')" class="btn-primary mobile-btn">
                        <span>Continue with Google</span>
                    </a>
                    <Link :href="route('login')" class="btn-ghost mobile-btn" @click="mobileNavOpen = false">
                        Email Sign In
                    </Link>
                </div>
            </div>
        </header>

        <!-- Hero Section -->
        <section class="hero-section">
            <div class="hero-container">
                <div class="hero-badge">
                    <span class="badge-dot"></span>
                    <span>The Execution Operating System for Founders</span>
                </div>

                <h1 class="hero-title">
                    Turn Strategy Into<br />
                    <span class="gradient-text">Flawless Daily Execution.</span>
                </h1>

                <p class="hero-sub">
                    Stop losing momentum between quarterly goals. Track Daily Close Reports, 
                    Weekly KPIs, Monthly Partner Feedback, and Automated Streaks in one real-time cockpit.
                </p>

                <div class="hero-actions">
                    <div class="google-wrapper">
                        <GoogleLoginButton />
                    </div>
                    <Link :href="route('login')" class="btn-secondary">
                        Email Login &rarr;
                    </Link>
                </div>

                <div class="trust-bar">
                    <span class="trust-item"><span class="check">✓</span> Instant Workspace Provisioning</span>
                    <span class="dot">•</span>
                    <span class="trust-item"><span class="check">✓</span> 1-Click Google OAuth</span>
                    <span class="dot">•</span>
                    <span class="trust-item"><span class="check">✓</span> Real-Time WebPush Nudges</span>
                </div>
            </div>
        </section>

        <!-- 3D Spatial Mock Cockpit Preview -->
        <section id="preview" class="preview-section">
            <div class="preview-container">
                <div class="preview-card-3d">
                    <div class="card-topbar">
                        <div class="mac-dots">
                            <span class="dot-red"></span>
                            <span class="dot-yellow"></span>
                            <span class="dot-green"></span>
                        </div>
                        <div class="topbar-title">dhandadiary.cloud/overview</div>
                        <div class="streak-pill">14 Days Streak 🔥</div>
                    </div>

                    <div class="cockpit-grid">
                        <div class="stat-card">
                            <div class="stat-label">Daily Close Report (DCR)</div>
                            <div class="stat-value text-emerald">94% Done</div>
                            <div class="stat-sub">5 of 6 discipline checklist items completed</div>
                            <div class="mini-progress-bar"><div class="bar-fill bg-emerald" style="width: 94%"></div></div>
                        </div>

                        <div class="stat-card">
                            <div class="stat-label">Weekly KPI Scorecard</div>
                            <div class="stat-value text-indigo">Sales Calls: 24/20</div>
                            <div class="stat-sub">+20% over target default</div>
                            <div class="mini-progress-bar"><div class="bar-fill bg-indigo" style="width: 100%"></div></div>
                        </div>

                        <div class="stat-card">
                            <div class="stat-label">Monthly Review</div>
                            <div class="stat-value text-purple">4.8 / 5.0 Rating</div>
                            <div class="stat-sub">3 Partner accountability feedbacks</div>
                            <div class="mini-progress-bar"><div class="bar-fill bg-purple" style="width: 96%"></div></div>
                        </div>

                        <div class="stat-card">
                            <div class="stat-label">Kanban Board Status</div>
                            <div class="stat-value text-amber">12 Tasks Completed</div>
                            <div class="stat-sub">3 in progress, 0 overdue</div>
                            <div class="mini-progress-bar"><div class="bar-fill bg-amber" style="width: 80%"></div></div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Cadence Engine Grid Section -->
        <section id="cadence" class="cadence-section">
            <div class="section-container">
                <div class="section-header">
                    <div class="badge-tag">RHYTHMIC OPERATING SYSTEM</div>
                    <h2 class="section-title">The 4-Pillar Cadence Engine</h2>
                    <p class="section-sub">A structured operational rhythm designed to eliminate execution drift.</p>
                </div>

                <div class="cadence-grid">
                    <div class="cadence-card">
                        <div class="card-icon text-indigo">01</div>
                        <h3>Daily Close Report</h3>
                        <p>Lock in daily discipline, check off habits, track revenue metrics, and log daily wins before shutdown.</p>
                        <span class="cadence-badge">Daily Cadence</span>
                    </div>

                    <div class="cadence-card">
                        <div class="card-icon text-emerald">02</div>
                        <h3>Weekly KPI Scorecard</h3>
                        <p>Track core leading indicators, measure weekly velocity, and run async review retrospectives.</p>
                        <span class="cadence-badge">Weekly Cadence</span>
                    </div>

                    <div class="cadence-card">
                        <div class="card-icon text-purple">03</div>
                        <h3>Monthly 360° Feedback</h3>
                        <p>Gather structured feedback from accountability partners and evaluate long-term strategic alignment.</p>
                        <span class="cadence-badge">Monthly Cadence</span>
                    </div>

                    <div class="cadence-card">
                        <div class="card-icon text-amber">04</div>
                        <h3>Ongoing Kanban Board</h3>
                        <p>Manage high-impact execution tasks with drag-and-drop boards, automated stale detection, and priorities.</p>
                        <span class="cadence-badge">Continuous Cadence</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- Core Features Grid -->
        <section id="features" class="features-section">
            <div class="section-container">
                <div class="section-header">
                    <div class="badge-tag">POWERFUL CAPABILITIES</div>
                    <h2 class="section-title">Built for Modern High-Growth Teams</h2>
                    <p class="section-sub">Everything you need to maintain focus, velocity, and discipline.</p>
                </div>

                <div class="features-grid">
                    <div class="feature-card">
                        <div class="feature-icon">🔥</div>
                        <h4>Automated Streak Engine</h4>
                        <p>Habit tracking that calculates current and longest streaks to build psychological momentum.</p>
                    </div>

                    <div class="feature-card">
                        <div class="feature-icon">⚡</div>
                        <h4>Instant WebPush Reminders</h4>
                        <p>Receive scheduled nudges directly in your browser so daily close reports are never forgotten.</p>
                    </div>

                    <div class="feature-card">
                        <div class="feature-icon">🛡️</div>
                        <h4>Google OAuth & Workspace Isolation</h4>
                        <p>Enterprise multi-tenant architecture with auto-provisioned agency KPIs and roles.</p>
                    </div>

                    <div class="feature-card">
                        <div class="feature-icon">📊</div>
                        <h4>Visual Trend ApexCharts</h4>
                        <p>Interactive smooth area charts comparing weekly performance actuals against targets.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- FAQ Section -->
        <section id="faq" class="faq-section">
            <div class="section-container max-w-3xl">
                <div class="section-header">
                    <div class="badge-tag">CLEAR ANSWERS</div>
                    <h2 class="section-title">Frequently Asked Questions</h2>
                    <p class="section-sub">Everything you need to know about Business Execution Toolkit.</p>
                </div>

                <div class="faq-accordion">
                    <div 
                        v-for="(item, idx) in faqs" 
                        :key="idx" 
                        class="faq-item"
                        :class="{ active: item.open }"
                    >
                        <button class="faq-question" @click="toggleFaq(idx)">
                            <span>{{ item.q }}</span>
                            <span class="faq-icon">{{ item.open ? '−' : '+' }}</span>
                        </button>
                        <div v-if="item.open" class="faq-answer">
                            <p>{{ item.a }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Banner -->
        <section class="cta-section">
            <div class="cta-container">
                <div class="cta-badge">GET STARTED TODAY</div>
                <h2 class="cta-title">Ready to Elevate Your Execution?</h2>
                <p class="cta-sub">Join founders and operators using Business Execution Toolkit to build high-performing teams.</p>

                <div class="cta-action">
                    <a :href="route('google.redirect')" class="btn-primary-large">
                        <span>Continue with Google &rarr;</span>
                    </a>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="footer">
            <div class="footer-container">
                <div class="footer-left">
                    <div class="logo-icon sm">BET</div>
                    <span>© 2026 Business Execution Toolkit. All rights reserved.</span>
                </div>
                <div class="footer-right">
                    <Link :href="route('login')">Login</Link>
                    <Link :href="route('register')">Register</Link>
                </div>
            </div>
        </footer>
    </div>
</template>

<style scoped>
.landing-page {
  min-height: 100vh;
  background-color: #070b19;
  color: #f8fafc;
  font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  position: relative;
  overflow-x: hidden;
}

.glow-orb {
  position: absolute;
  border-radius: 50%;
  filter: blur(120px);
  pointer-events: none;
  opacity: 0.35;
}

.orb-top-left {
  width: 500px;
  height: 500px;
  background: rgba(99, 102, 241, 0.25);
  top: -150px;
  left: -100px;
}

.orb-top-right {
  width: 450px;
  height: 450px;
  background: rgba(168, 85, 247, 0.2);
  top: 100px;
  right: -100px;
}

.orb-mid-left {
  width: 400px;
  height: 400px;
  background: rgba(59, 130, 246, 0.15);
  top: 900px;
  left: -150px;
}

.orb-bottom-right {
  width: 450px;
  height: 450px;
  background: rgba(16, 185, 129, 0.12);
  bottom: 200px;
  right: -100px;
}

/* Navbar */
.navbar {
  position: sticky;
  top: 0;
  z-index: 100;
  background: rgba(7, 11, 25, 0.85);
  backdrop-filter: blur(20px);
  -webkit-backdrop-filter: blur(20px);
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);
}

.nav-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 16px 24px;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.nav-brand {
  display: flex;
  align-items: center;
  gap: 12px;
  text-decoration: none;
}

.logo-icon {
  width: 38px;
  height: 38px;
  border-radius: 10px;
  background: linear-gradient(135deg, #6366f1, #4338ca);
  display: grid;
  place-items: center;
  color: #fff;
  font-weight: 800;
  font-size: 15px;
  box-shadow: 0 4px 14px rgba(99, 102, 241, 0.4);
}

.logo-icon.sm {
  width: 28px;
  height: 28px;
  font-size: 12px;
  border-radius: 8px;
}

.brand-name {
  font-size: 17px;
  font-weight: 400;
  color: #cbd5e1;
}

.brand-name b {
  font-weight: 800;
  color: #ffffff;
}

.brand-sub {
  display: block;
  font-size: 10px;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.nav-links {
  display: flex;
  gap: 28px;
}

.nav-links a {
  color: #94a3b8;
  text-decoration: none;
  font-size: 14px;
  font-weight: 500;
  transition: color 0.15s ease;
}

.nav-links a:hover {
  color: #ffffff;
}

.nav-actions {
  display: flex;
  align-items: center;
  gap: 16px;
}

.btn-ghost {
  color: #cbd5e1;
  text-decoration: none;
  font-size: 14px;
  font-weight: 600;
  padding: 8px 16px;
  border-radius: 8px;
  transition: background 0.15s ease;
}

.btn-ghost:hover {
  background: rgba(255, 255, 255, 0.05);
  color: #fff;
}

.btn-primary {
  background: linear-gradient(135deg, #6366f1, #4f46e5);
  color: #ffffff;
  padding: 9px 18px;
  border-radius: 10px;
  font-size: 14px;
  font-weight: 600;
  text-decoration: none;
  box-shadow: 0 4px 14px rgba(99, 102, 241, 0.35);
  transition: all 0.2s ease;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

.btn-primary:hover {
  background: linear-gradient(135deg, #4f46e5, #4338ca);
  transform: translateY(-1px);
}

.mobile-hamburger {
  display: none;
  background: none;
  border: none;
  cursor: pointer;
  flex-direction: column;
  gap: 5px;
  padding: 4px;
}
.mobile-hamburger .bar {
  width: 22px;
  height: 2px;
  background: #f8fafc;
  border-radius: 2px;
}

/* Mobile Nav Drawer */
.mobile-nav-drawer {
  position: fixed;
  top: 65px;
  left: 0;
  right: 0;
  background: rgba(11, 16, 32, 0.95);
  backdrop-filter: blur(20px);
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  padding: 20px;
  z-index: 99;
}
.drawer-content {
  display: flex;
  flex-direction: column;
  gap: 16px;
}
.drawer-content a {
  color: #cbd5e1;
  text-decoration: none;
  font-size: 16px;
  font-weight: 600;
}
.drawer-divider {
  height: 1px;
  background: rgba(255, 255, 255, 0.08);
  margin: 4px 0;
}
.mobile-btn {
  width: 100%;
  text-align: center;
  padding: 12px;
  min-height: 48px;
}

/* Hero Section */
.hero-section {
  padding: 90px 24px 70px;
  text-align: center;
}

.hero-container {
  max-width: 880px;
  margin: 0 auto;
}

.hero-badge {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 6px 18px;
  border-radius: 100px;
  background: rgba(99, 102, 241, 0.12);
  border: 1px solid rgba(99, 102, 241, 0.3);
  color: #c7d2fe;
  font-size: 13px;
  font-weight: 600;
  margin-bottom: 24px;
}

.badge-dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: #818cf8;
  box-shadow: 0 0 8px #818cf8;
}

.hero-title {
  font-size: 58px;
  font-weight: 900;
  line-height: 1.1;
  letter-spacing: -0.03em;
  margin-bottom: 20px;
}

.gradient-text {
  background: linear-gradient(135deg, #818cf8 0%, #c084fc 50%, #38bdf8 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

.hero-sub {
  font-size: 19px;
  color: #94a3b8;
  max-width: 680px;
  margin: 0 auto 36px;
  line-height: 1.6;
}

.hero-actions {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 16px;
  max-width: 480px;
  margin: 0 auto 28px;
}

.google-wrapper {
  flex: 1;
  max-width: 260px;
}

.btn-secondary {
  padding: 11px 22px;
  border-radius: 10px;
  background: rgba(255, 255, 255, 0.06);
  border: 1px solid rgba(255, 255, 255, 0.12);
  color: #f8fafc;
  font-weight: 600;
  font-size: 14px;
  text-decoration: none;
  transition: all 0.2s ease;
  min-height: 44px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

.btn-secondary:hover {
  background: rgba(255, 255, 255, 0.12);
  border-color: rgba(255, 255, 255, 0.2);
}

.trust-bar {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 12px;
  font-size: 13px;
  color: #94a3b8;
}

.trust-item .check {
  color: #34d399;
  font-weight: 800;
}

.trust-bar .dot {
  color: #334155;
}

/* 3D Preview Card */
.preview-section {
  padding: 40px 24px 80px;
  perspective: 1200px;
}

.preview-container {
  max-width: 1020px;
  margin: 0 auto;
}

.preview-card-3d {
  background: rgba(15, 23, 42, 0.85);
  backdrop-filter: blur(20px);
  -webkit-backdrop-filter: blur(20px);
  border: 1px solid rgba(255, 255, 255, 0.15);
  border-radius: 20px;
  box-shadow: 
    0 30px 60px -12px rgba(0, 0, 0, 0.7),
    0 0 30px rgba(99, 102, 241, 0.25);
  transform: rotateX(6deg);
  transition: transform 0.5s cubic-bezier(0.16, 1, 0.3, 1);
}

.preview-card-3d:hover {
  transform: rotateX(0deg);
}

.card-topbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 14px 20px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);
}

.mac-dots {
  display: flex;
  gap: 6px;
}

.mac-dots span {
  width: 10px;
  height: 10px;
  border-radius: 50%;
}

.dot-red { background: #ef4444; }
.dot-yellow { background: #f59e0b; }
.dot-green { background: #10b981; }

.topbar-title {
  font-size: 12px;
  color: #64748b;
  font-family: monospace;
}

.streak-pill {
  padding: 4px 10px;
  border-radius: 100px;
  background: rgba(245, 158, 11, 0.15);
  border: 1px solid rgba(245, 158, 11, 0.3);
  color: #fbbf24;
  font-size: 12px;
  font-weight: 700;
}

.cockpit-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 16px;
  padding: 24px;
}

.stat-card {
  background: rgba(30, 41, 59, 0.5);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 14px;
  padding: 20px;
  text-align: left;
}

.stat-label {
  font-size: 12px;
  color: #94a3b8;
  text-transform: uppercase;
  letter-spacing: 0.04em;
  margin-bottom: 8px;
}

.stat-value {
  font-size: 24px;
  font-weight: 800;
  margin-bottom: 4px;
}

.text-emerald { color: #34d399; }
.text-indigo { color: #818cf8; }
.text-purple { color: #c084fc; }
.text-amber { color: #fbbf24; }

.stat-sub {
  font-size: 12px;
  color: #64748b;
  margin-bottom: 8px;
}

.mini-progress-bar {
  height: 4px;
  background: rgba(255, 255, 255, 0.08);
  border-radius: 999px;
  overflow: hidden;
}
.bar-fill { height: 100%; border-radius: 999px; }
.bg-emerald { background: #10b981; }
.bg-indigo { background: #6366f1; }
.bg-purple { background: #a855f7; }
.bg-amber { background: #f59e0b; }

/* Cadence Section */
.cadence-section {
  padding: 90px 24px;
  background: rgba(15, 23, 42, 0.4);
  border-top: 1px solid rgba(255, 255, 255, 0.05);
}

.section-container {
  max-width: 1100px;
  margin: 0 auto;
}

.section-header {
  text-align: center;
  margin-bottom: 56px;
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

.section-title {
  font-size: 38px;
  font-weight: 900;
  letter-spacing: -0.02em;
  margin-bottom: 12px;
}

.section-sub {
  font-size: 16px;
  color: #94a3b8;
}

.cadence-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 20px;
}

.cadence-card {
  background: rgba(15, 23, 42, 0.6);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 18px;
  padding: 28px 22px;
  display: flex;
  flex-direction: column;
  transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
}

.cadence-card:hover {
  transform: translateY(-4px);
  border-color: rgba(99, 102, 241, 0.4);
  box-shadow: 0 12px 30px rgba(0, 0, 0, 0.4);
}

.card-icon {
  font-size: 28px;
  font-weight: 900;
  margin-bottom: 16px;
}

.cadence-card h3 {
  font-size: 18px;
  font-weight: 700;
  margin-bottom: 10px;
}

.cadence-card p {
  font-size: 13px;
  color: #94a3b8;
  line-height: 1.6;
  margin-bottom: 20px;
  flex: 1;
}

.cadence-badge {
  align-self: flex-start;
  font-size: 11px;
  font-weight: 600;
  padding: 4px 10px;
  border-radius: 100px;
  background: rgba(255, 255, 255, 0.06);
  color: #cbd5e1;
}

/* Features Section */
.features-section {
  padding: 90px 24px;
}
.features-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 20px;
}
.feature-card {
  background: rgba(15, 23, 42, 0.5);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 18px;
  padding: 24px;
  transition: all 0.2s ease;
}
.feature-card:hover {
  border-color: rgba(99, 102, 241, 0.3);
  transform: translateY(-2px);
}
.feature-icon {
  font-size: 32px;
  margin-bottom: 14px;
}
.feature-card h4 {
  font-size: 16px;
  font-weight: 700;
  color: #ffffff;
  margin-bottom: 8px;
}
.feature-card p {
  font-size: 13px;
  color: #94a3b8;
  line-height: 1.5;
}

/* FAQ Section */
.faq-section {
  padding: 90px 24px;
  background: rgba(15, 23, 42, 0.3);
}

.max-w-3xl {
  max-width: 760px;
}

.faq-accordion {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.faq-item {
  background: rgba(15, 23, 42, 0.6);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 14px;
  overflow: hidden;
  transition: border-color 0.2s ease;
}

.faq-item.active {
  border-color: rgba(99, 102, 241, 0.4);
}

.faq-question {
  width: 100%;
  padding: 18px 20px;
  background: none;
  border: none;
  color: #ffffff;
  font-size: 16px;
  font-weight: 600;
  display: flex;
  align-items: center;
  justify-content: space-between;
  cursor: pointer;
  text-align: left;
}

.faq-icon {
  font-size: 20px;
  color: #818cf8;
}

.faq-answer {
  padding: 0 20px 18px;
  font-size: 14px;
  color: #94a3b8;
  line-height: 1.6;
}

/* CTA Section */
.cta-section {
  padding: 100px 24px;
  background: linear-gradient(180deg, rgba(15, 23, 42, 0.5), rgba(99, 102, 241, 0.12));
  border-top: 1px solid rgba(255, 255, 255, 0.08);
  text-align: center;
}

.cta-container {
  max-width: 660px;
  margin: 0 auto;
}

.cta-badge {
  font-size: 11px;
  font-weight: 800;
  letter-spacing: 0.1em;
  color: #818cf8;
  margin-bottom: 12px;
}

.cta-title {
  font-size: 40px;
  font-weight: 900;
  margin-bottom: 14px;
}

.cta-sub {
  font-size: 17px;
  color: #94a3b8;
  margin-bottom: 32px;
}

.btn-primary-large {
  display: inline-block;
  background: linear-gradient(135deg, #6366f1, #4f46e5);
  color: #ffffff;
  padding: 16px 36px;
  border-radius: 14px;
  font-size: 16px;
  font-weight: 700;
  text-decoration: none;
  box-shadow: 0 8px 24px rgba(99, 102, 241, 0.4);
  transition: all 0.2s ease;
}

.btn-primary-large:hover {
  background: linear-gradient(135deg, #4f46e5, #4338ca);
  transform: translateY(-2px);
}

/* Footer */
.footer {
  padding: 36px 24px;
  border-top: 1px solid rgba(255, 255, 255, 0.05);
  font-size: 13px;
  color: #64748b;
}

.footer-container {
  max-width: 1100px;
  margin: 0 auto;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.footer-left {
  display: flex;
  align-items: center;
  gap: 12px;
}

.footer-right {
  display: flex;
  gap: 20px;
}

.footer-right a {
  color: #94a3b8;
  text-decoration: none;
}

.footer-right a:hover {
  color: #ffffff;
}

@media (max-width: 990px) {
  .cadence-grid, .features-grid { grid-template-columns: repeat(2, 1fr); }
  .cockpit-grid { grid-template-columns: 1fr; }
  .nav-links { display: none; }
  .mobile-hamburger { display: flex; }
  .hero-title { font-size: 42px; }
}

@media (max-width: 640px) {
  .nav-container { padding: 12px 16px; }
  .brand-name { font-size: 14px; }
  .brand-sub { display: none; }
  .nav-actions { display: none; }
  .hero-section { padding: 48px 16px 40px; }
  .hero-title { font-size: 30px; }
  .hero-sub { font-size: 15px; margin-bottom: 24px; }
  .hero-actions { flex-direction: column; width: 100%; }
  .google-wrapper { max-width: 100%; width: 100%; }
  .btn-secondary { width: 100%; text-align: center; min-height: 48px; display: grid; place-items: center; }
  .trust-bar { flex-direction: column; gap: 6px; }
  .trust-bar .dot { display: none; }
  
  .preview-section { padding: 20px 12px 40px; }
  .preview-card-3d { transform: none !important; }
  .cockpit-grid { grid-template-columns: 1fr; padding: 14px; gap: 10px; }
  .stat-card { padding: 14px; }

  .cadence-section, .features-section, .faq-section, .cta-section { padding: 48px 16px; }
  .cadence-grid, .features-grid { grid-template-columns: 1fr; }
  .section-title, .cta-title { font-size: 26px; }

  .footer-container { flex-direction: column; gap: 14px; text-align: center; }
  .footer-left { flex-direction: column; }
}
</style>
