<template>
  <aside
    class="sb"
    :class="[
      isSidebarOpen ? 'sb--open' : 'sb--closed',
      isLight ? 'sb--light' : 'sb--dark',
    ]"
    :aria-expanded="isSidebarOpen"
    :style="tickerPadStyle"
  >
    <div class="sb__glow" aria-hidden="true" />

    <!-- Header : logo + collapse -->
    <header class="sb__head">
      <Link href="/" class="sb__brand" :title="isSidebarOpen ? undefined : 'iSpaceCoin'">
        <BrandLogo media-class="sb__logo" alt="iSpaceCoin" />
        <span v-show="isSidebarOpen" class="sb__word">
          <em>i</em>Space<span>Coin</span>
        </span>
      </Link>

      <button
        type="button"
        class="sb__toggle"
        :aria-label="isSidebarOpen ? 'Réduire le menu' : 'Ouvrir le menu'"
        @click="$emit('toggle')"
      >
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
          <path v-if="isSidebarOpen" d="M15 6l-6 6 6 6" />
          <path v-else d="M9 6l6 6-6 6" />
        </svg>
      </button>
    </header>

    <p v-if="isSidebarOpen" class="sb__section">Navigation</p>

    <!-- Nav -->
    <nav class="sb__nav" aria-label="Menu principal">
      <Link
        v-for="item in items"
        :key="item.route"
        :href="navHref(item)"
        class="sb__link"
        :class="{ 'is-active': isActive(item) }"
        :title="isSidebarOpen ? undefined : item.label"
        :aria-label="item.label"
        :aria-current="isActive(item) ? 'page' : undefined"
      >
        <span class="sb__ico" aria-hidden="true">
          <slot name="icon" :icon="item.icon" :isLight="isLight">
            <SidebarIcon :icon="item.icon" :is-light="isLight" />
          </slot>
        </span>
        <span class="sb__label">{{ item.label }}</span>
        <span v-if="isActive(item)" class="sb__active-dot" aria-hidden="true" />
      </Link>
    </nav>

    <!-- Bas : profil -->
    <div class="sb__foot">
      <Link
        :href="profileHref"
        class="sb__profile"
        :class="{ 'is-active': isProfileActive }"
        :title="isSidebarOpen ? undefined : 'Profil'"
      >
        <span class="sb__profile-avatar" aria-hidden="true">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="8" r="3.2" />
            <path d="M5.5 19c1.6-3.2 4-4.8 6.5-4.8s4.9 1.6 6.5 4.8" />
          </svg>
        </span>
        <span v-show="isSidebarOpen" class="sb__profile-meta">
          <span class="sb__profile-title">Profil</span>
          <span class="sb__profile-sub">Compte &amp; sécurité</span>
        </span>
      </Link>
    </div>
  </aside>
</template>

<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import BrandLogo from '@/Components/BrandLogo.vue'
import SidebarIcon from '@/Components/SidebarIcon.vue'

const props = defineProps({
  isSidebarOpen: { type: Boolean, default: true },
  isLight: { type: Boolean, default: false },
  items: { type: Array, default: () => [] },
  /** hauteur ticker (ex. "48px") — réserve l’espace sous le bandeau */
  tickerHeightPx: { type: [String, Number], default: '0px' },
})

defineEmits(['toggle'])

const tickerPadStyle = computed(() => {
  const raw = props.tickerHeightPx
  if (raw === undefined || raw === null || raw === '' || raw === '0' || raw === '0px') {
    return undefined
  }
  const h = typeof raw === 'number' ? `${raw}px` : String(raw)
  return { ['--sb-ticker']: h }
})

function navHref(item) {
  try {
    if (typeof route === 'function') return route(item.route)
  } catch {
    /* fallback */
  }
  return `/${item.route}`
}

const profileHref = computed(() => {
  try {
    if (typeof route === 'function') return route('profile.edit')
  } catch {
    /* fallback */
  }
  return '/profile'
})

function isActive(item) {
  try {
    if (typeof route === 'function' && route().current) {
      if (route().current(item.route)) return true
      if (item.route === 'crypto' && route().current('crypto.*')) return true
      if (item.route === 'deposit' && route().current('deposit.*')) return true
    }
  } catch {
    /* fallback */
  }
  if (typeof window === 'undefined') return false
  const path = window.location.pathname.replace(/\/$/, '') || '/'
  return path === `/${item.route}` || path.startsWith(`/${item.route}/`)
}

const isProfileActive = computed(() => {
  try {
    if (typeof route === 'function' && route().current) {
      return !!route().current('profile.*') || !!route().current('profile.edit')
    }
  } catch {
    /* fallback */
  }
  if (typeof window === 'undefined') return false
  return window.location.pathname.startsWith('/profile')
})
</script>

<style scoped>
.sb {
  --sb-w-open: 15.5rem;
  --sb-w-closed: 4.75rem;
  --sb-ticker: 0px;
  position: sticky;
  top: 0;
  z-index: 40;
  flex-shrink: 0;
  display: flex;
  flex-direction: column;
  width: var(--sb-w-open);
  height: 100vh;
  height: 100dvh;
  padding: calc(var(--sb-ticker) + 0.85rem) 0.7rem 0.85rem;
  overflow: hidden;
  isolation: isolate;
  transition:
    width 0.28s cubic-bezier(0.4, 0, 0.2, 1),
    padding 0.28s ease,
    background 0.3s ease,
    border-color 0.3s ease;
  border-right: 1px solid rgba(165, 180, 252, 0.16);
  background:
    linear-gradient(
      165deg,
      rgba(29, 78, 216, 0.22) 0%,
      rgba(79, 70, 229, 0.14) 42%,
      rgba(15, 12, 40, 0.28) 100%
    );
  backdrop-filter: blur(14px);
  -webkit-backdrop-filter: blur(14px);
  box-shadow: 4px 0 28px -12px rgba(0, 0, 0, 0.45);
}

.sb--closed {
  width: var(--sb-w-closed);
  padding-left: 0.55rem;
  padding-right: 0.55rem;
}

.sb--light {
  border-right-color: rgba(15, 23, 42, 0.1);
  background:
    linear-gradient(
      165deg,
      rgba(219, 234, 254, 0.72) 0%,
      rgba(237, 233, 254, 0.55) 50%,
      rgba(255, 255, 255, 0.45) 100%
    );
  box-shadow: 4px 0 24px -14px rgba(15, 23, 42, 0.18);
}

/* glow discret */
.sb__glow {
  pointer-events: none;
  position: absolute;
  inset: auto -20% 10% -30%;
  height: 12rem;
  border-radius: 50%;
  background: radial-gradient(circle, rgba(124, 58, 237, 0.35), transparent 68%);
  filter: blur(28px);
  z-index: 0;
  opacity: 0.7;
}
.sb--light .sb__glow {
  background: radial-gradient(circle, rgba(99, 102, 241, 0.2), transparent 70%);
}

.sb__head,
.sb__nav,
.sb__foot,
.sb__section {
  position: relative;
  z-index: 1;
}

/* ─── Header ─── */
.sb__head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 0.35rem;
  margin-bottom: 1.1rem;
  min-height: 2.75rem;
}

.sb--closed .sb__head {
  flex-direction: column;
  gap: 0.55rem;
}

.sb__brand {
  display: inline-flex;
  align-items: center;
  gap: 0.55rem;
  min-width: 0;
  text-decoration: none;
  color: inherit;
}

:deep(.sb__logo) {
  width: 2.25rem;
  height: 2.25rem;
  border-radius: 0.55rem;
  object-fit: cover;
  display: block;
  flex-shrink: 0;
  border: 1px solid rgba(255, 255, 255, 0.2);
  background: rgba(255, 255, 255, 0.06);
  box-shadow: 0 4px 14px -6px rgba(56, 189, 248, 0.45);
}
.sb--light :deep(.sb__logo) {
  border-color: rgba(15, 23, 42, 0.1);
  box-shadow: 0 4px 12px -6px rgba(37, 99, 235, 0.25);
}

.sb__word {
  font-size: 0.95rem;
  font-weight: 700;
  letter-spacing: -0.03em;
  white-space: nowrap;
  color: #f8fafc;
}
.sb__word em {
  font-style: normal;
  color: #60a5fa;
}
.sb__word span {
  color: #c4b5fd;
}
.sb--light .sb__word {
  color: #0f172a;
}
.sb--light .sb__word em {
  color: #2563eb;
}
.sb--light .sb__word span {
  color: #7c3aed;
}

.sb__toggle {
  flex-shrink: 0;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 2rem;
  height: 2rem;
  border-radius: 0.55rem;
  border: 1px solid rgba(255, 255, 255, 0.14);
  background: rgba(255, 255, 255, 0.06);
  color: rgba(226, 232, 240, 0.9);
  cursor: pointer;
  transition: background 0.15s ease, border-color 0.15s ease, transform 0.15s ease;
}
.sb__toggle:hover {
  background: rgba(255, 255, 255, 0.12);
  border-color: rgba(167, 139, 250, 0.45);
}
.sb__toggle svg {
  width: 1.05rem;
  height: 1.05rem;
}
.sb--light .sb__toggle {
  border-color: rgba(15, 23, 42, 0.1);
  background: rgba(255, 255, 255, 0.55);
  color: #334155;
}
.sb--light .sb__toggle:hover {
  background: #fff;
  border-color: rgba(124, 58, 237, 0.35);
}

.sb__section {
  margin: 0 0 0.45rem 0.55rem;
  font-size: 0.65rem;
  font-weight: 700;
  letter-spacing: 0.14em;
  text-transform: uppercase;
  color: rgba(199, 210, 254, 0.55);
}
.sb--light .sb__section {
  color: rgba(71, 85, 105, 0.65);
}

/* ─── Nav ─── */
.sb__nav {
  flex: 1 1 auto;
  display: flex;
  flex-direction: column;
  gap: 0.2rem;
  overflow-y: auto;
  overflow-x: hidden;
  padding-bottom: 0.5rem;
  scrollbar-width: thin;
  scrollbar-color: rgba(148, 163, 184, 0.25) transparent;
}

.sb__link {
  position: relative;
  display: flex;
  align-items: center;
  gap: 0.7rem;
  min-height: 2.65rem;
  padding: 0.45rem 0.65rem;
  border-radius: 0.7rem;
  text-decoration: none;
  color: rgba(226, 232, 240, 0.82);
  font-size: 0.875rem;
  font-weight: 600;
  letter-spacing: -0.01em;
  transition:
    background 0.15s ease,
    color 0.15s ease,
    box-shadow 0.15s ease;
}
.sb--closed .sb__link {
  justify-content: center;
  padding-left: 0.4rem;
  padding-right: 0.4rem;
}

.sb__link:hover {
  background: rgba(255, 255, 255, 0.07);
  color: #f8fafc;
}

.sb__link.is-active {
  color: #fff;
  background: linear-gradient(
    105deg,
    rgba(37, 99, 235, 0.42) 0%,
    rgba(124, 58, 237, 0.38) 100%
  );
  box-shadow:
    inset 0 0 0 1px rgba(167, 139, 250, 0.28),
    0 6px 16px -8px rgba(124, 58, 237, 0.55);
}

.sb--light .sb__link {
  color: rgba(30, 41, 59, 0.78);
}
.sb--light .sb__link:hover {
  background: rgba(15, 23, 42, 0.05);
  color: #0f172a;
}
.sb--light .sb__link.is-active {
  color: #1e1b4b;
  background: linear-gradient(
    105deg,
    rgba(191, 219, 254, 0.85) 0%,
    rgba(221, 214, 254, 0.9) 100%
  );
  box-shadow:
    inset 0 0 0 1px rgba(124, 58, 237, 0.18),
    0 6px 14px -8px rgba(99, 102, 241, 0.35);
}

.sb__ico {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 1.85rem;
  height: 1.85rem;
  flex-shrink: 0;
  border-radius: 0.5rem;
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.08);
  color: inherit;
  transition: background 0.15s ease, border-color 0.15s ease, color 0.15s ease;
}
.sb__link.is-active .sb__ico {
  background: rgba(255, 255, 255, 0.12);
  border-color: rgba(196, 181, 253, 0.35);
  color: #c4b5fd;
}
.sb--light .sb__ico {
  background: rgba(15, 23, 42, 0.04);
  border-color: rgba(15, 23, 42, 0.08);
}
.sb--light .sb__link.is-active .sb__ico {
  background: rgba(124, 58, 237, 0.1);
  border-color: rgba(124, 58, 237, 0.22);
  color: #6d28d9;
}

/* forcer icônes slot à hériter */
.sb__ico :deep(svg) {
  width: 1.15rem;
  height: 1.15rem;
  color: inherit;
}

.sb__label {
  min-width: 0;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  transition: opacity 0.18s ease, width 0.18s ease;
}
.sb--closed .sb__label {
  position: absolute;
  width: 1px;
  height: 1px;
  opacity: 0;
  overflow: hidden;
  clip: rect(0 0 0 0);
}

.sb__active-dot {
  margin-left: auto;
  width: 0.4rem;
  height: 0.4rem;
  border-radius: 999px;
  background: linear-gradient(135deg, #38bdf8, #a78bfa);
  box-shadow: 0 0 8px rgba(167, 139, 250, 0.8);
  flex-shrink: 0;
}
.sb--closed .sb__active-dot {
  display: none;
}

/* ─── Profil bas ─── */
.sb__foot {
  margin-top: auto;
  padding-top: 0.65rem;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
}
.sb--light .sb__foot {
  border-top-color: rgba(15, 23, 42, 0.08);
}

.sb__profile {
  display: flex;
  align-items: center;
  gap: 0.65rem;
  padding: 0.5rem 0.55rem;
  border-radius: 0.75rem;
  text-decoration: none;
  color: rgba(226, 232, 240, 0.88);
  transition: background 0.15s ease;
}
.sb--closed .sb__profile {
  justify-content: center;
  padding: 0.45rem;
}
.sb__profile:hover {
  background: rgba(255, 255, 255, 0.07);
}
.sb__profile.is-active {
  background: linear-gradient(
    105deg,
    rgba(37, 99, 235, 0.35) 0%,
    rgba(124, 58, 237, 0.32) 100%
  );
  box-shadow: inset 0 0 0 1px rgba(167, 139, 250, 0.25);
}
.sb--light .sb__profile {
  color: #1e293b;
}
.sb--light .sb__profile:hover {
  background: rgba(15, 23, 42, 0.05);
}
.sb--light .sb__profile.is-active {
  background: linear-gradient(105deg, rgba(191, 219, 254, 0.9), rgba(221, 214, 254, 0.95));
}

.sb__profile-avatar {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 2.15rem;
  height: 2.15rem;
  border-radius: 0.65rem;
  flex-shrink: 0;
  background: linear-gradient(135deg, rgba(37, 99, 235, 0.55), rgba(124, 58, 237, 0.55));
  border: 1px solid rgba(196, 181, 253, 0.35);
  color: #fff;
}
.sb__profile-avatar svg {
  width: 1.15rem;
  height: 1.15rem;
}
.sb--light .sb__profile-avatar {
  background: linear-gradient(135deg, #3b82f6, #8b5cf6);
  border-color: transparent;
}

.sb__profile-meta {
  display: flex;
  flex-direction: column;
  min-width: 0;
  line-height: 1.2;
}
.sb__profile-title {
  font-size: 0.85rem;
  font-weight: 700;
}
.sb__profile-sub {
  font-size: 0.68rem;
  opacity: 0.55;
  font-weight: 500;
  margin-top: 0.12rem;
}

/* Mobile : rail compact par défaut si viewport étroit */
@media (max-width: 768px) {
  .sb {
    position: sticky;
    top: 0;
  }
}
</style>
