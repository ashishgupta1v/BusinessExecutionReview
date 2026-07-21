<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
  <div class="guest-container">
    <!-- Ambient glowing background spheres -->
    <div class="glow-orb orb-1"></div>
    <div class="glow-orb orb-2"></div>

    <div class="guest-wrapper">
      <div class="brand-header">
        <Link href="/" class="brand-logo-link">
          <img src="/images/logo.png" alt="Dhanda Diary Logo" class="guest-logo-img" />
          <div class="brand-text">
            <span class="title">Business Execution <b>Toolkit</b></span>
            <span class="subtitle">Operating System for Founders</span>
          </div>
        </Link>
      </div>

      <div class="glass-card">
        <slot />
      </div>

      <div class="footer-note">
        Protected with 256-bit SSL &amp; encrypted multi-tenant isolation
      </div>
    </div>
  </div>
</template>

<style scoped>
.guest-container {
  min-height: 100vh;
  width: 100%;
  background-color: #070b19;
  background-image: 
    radial-gradient(at 0% 0%, rgba(99, 102, 241, 0.15) 0px, transparent 50%),
    radial-gradient(at 100% 100%, rgba(168, 85, 247, 0.15) 0px, transparent 50%);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 40px 16px;
  position: relative;
  overflow: hidden;
  color: #f8fafc;
  font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

.glow-orb {
  position: absolute;
  border-radius: 50%;
  filter: blur(100px);
  pointer-events: none;
  opacity: 0.5;
}

.orb-1 {
  width: 450px;
  height: 450px;
  background: rgba(99, 102, 241, 0.3);
  top: -100px;
  left: 50%;
  transform: translateX(-50%);
}

.orb-2 {
  width: 350px;
  height: 350px;
  background: rgba(168, 85, 247, 0.25);
  bottom: -80px;
  right: 10%;
}

.guest-wrapper {
  width: 100%;
  max-width: 440px;
  position: relative;
  z-index: 10;
  display: flex;
  flex-direction: column;
  align-items: center;
}

.brand-header {
  margin-bottom: 28px;
  text-align: center;
}

.brand-logo-link {
  display: flex;
  align-items: center;
  gap: 14px;
  text-decoration: none;
  transition: transform 0.2s ease;
}

.brand-logo-link:hover {
  transform: translateY(-2px);
}

.guest-logo-img {
  width: 48px;
  height: 48px;
  object-fit: contain;
  filter: drop-shadow(0 4px 14px rgba(99, 102, 241, 0.45));
}

.brand-text {
  display: flex;
  flex-direction: column;
  text-align: left;
}

.brand-text .title {
  font-size: 20px;
  font-weight: 400;
  color: #94a3b8;
  line-height: 1.1;
}

.brand-text .title b {
  font-weight: 800;
  color: #ffffff;
}

.brand-text .subtitle {
  font-size: 11px;
  color: #64748b;
  margin-top: 3px;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.glass-card {
  width: 100%;
  background: rgba(15, 23, 42, 0.75);
  backdrop-filter: blur(20px);
  -webkit-backdrop-filter: blur(20px);
  border: 1px solid rgba(255, 255, 255, 0.12);
  border-radius: 20px;
  padding: 32px 28px;
  box-shadow: 
    0 25px 50px -12px rgba(0, 0, 0, 0.5),
    0 0 0 1px rgba(255, 255, 255, 0.05) inset;
  transition: box-shadow 0.3s ease, border-color 0.3s ease;
}

.glass-card:hover {
  border-color: rgba(99, 102, 241, 0.3);
  box-shadow: 
    0 30px 60px -12px rgba(0, 0, 0, 0.6),
    0 0 20px rgba(99, 102, 241, 0.15);
}

.footer-note {
  margin-top: 24px;
  font-size: 12px;
  color: #64748b;
  text-align: center;
}
</style>
