<template>
  <div class="absolute top-0 left-0 w-full overflow-hidden z-40" :style="{ height: tickerHeightPx }">
    <div
      class="inline-block w-max h-full flex items-center px-4 animate-marquee backdrop-blur-sm ring-1 ring-white/10 pointer-events-none"
      :style="{ animationDuration: tickerDuration }">
      <template v-if="items.length">
        <span
          v-for="(c, idx) in items"
          :key="`${c.type}-${c.id || c.symbol}-${idx}`"
          class="mx-5 inline-flex items-center gap-1.5"
        >
          <span class="type-badge" :class="`type-${c.type || 'crypto'}`">{{ typeLabel(c.type) }}</span>
          <strong>{{ displayName(c) }}</strong>
          <span class="opacity-90">{{ fmtPrice(c) }}</span>
          <span
            v-if="c.price_change_percentage_24h != null"
            :class="(c.price_change_percentage_24h ?? 0) < 0 ? 'text-red-400' : 'text-emerald-400'"
          >
            {{ fmtChange(c.price_change_percentage_24h) }}
          </span>
        </span>
        <span v-if="stale" class="mx-4 text-xs opacity-50">· cours en cache</span>
      </template>
      <template v-else-if="tickerError">
        <span class="opacity-80 text-red-400">{{ tickerError }}</span>
      </template>
      <template v-else>
        <span class="opacity-80">Chargement des cours…</span>
      </template>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'

const STORAGE_KEY = 'ispace:ticker:last_good:v2'
const items = ref(readLocal())
const tickerError = ref(null)
const stale = ref(false)
const secondsPerItem = ref(3.2)
const tickerHeight = ref(48)

const tickerHeightPx = computed(() => `${tickerHeight.value}px`)
const tickerDuration = computed(
  () => `${Math.max(12, items.value.length) * secondsPerItem.value}s`
)

let tickerTimer = null

function typeLabel(type) {
  switch (type) {
    case 'fiat':
      return 'FIAT'
    case 'stock':
      return 'ACT' // actions
    case 'crypto':
    default:
      return 'CRY' // crypto
  }
}

function displayName(c) {
  if (c.type === 'fiat') return c.name || c.symbol
  if (c.type === 'stock') {
    const sym = (c.symbol || '').replace('.PA', '')
    return `${c.name || sym} (${sym})`
  }
  // crypto
  const sym = c.symbol ? String(c.symbol).toUpperCase() : ''
  return sym ? `${c.name} (${sym})` : c.name || '—'
}

function fmtPrice(c) {
  const v = c?.current_price
  if (v == null || Number.isNaN(Number(v))) return '—'

  // Devises: 4 décimales (JPY: 2)
  if (c.type === 'fiat') {
    const isJpy = String(c.symbol || c.name || '').includes('JPY')
    return new Intl.NumberFormat('fr-FR', {
      minimumFractionDigits: isJpy ? 2 : 4,
      maximumFractionDigits: isJpy ? 2 : 4,
    }).format(Number(v))
  }

  const currency = c.currency || 'USD'
  const abs = Math.abs(Number(v))
  const digits = abs >= 1000 ? 2 : abs >= 1 ? 2 : 4

  try {
    return new Intl.NumberFormat('fr-FR', {
      style: 'currency',
      currency,
      minimumFractionDigits: digits,
      maximumFractionDigits: digits,
    }).format(Number(v))
  } catch {
    return `${Number(v).toFixed(digits)} ${currency}`
  }
}

function fmtChange(v) {
  if (v == null || Number.isNaN(Number(v))) return ''
  const n = Number(v)
  const sign = n > 0 ? '+' : ''
  return `${sign}${n.toFixed(2)}%`
}

function readLocal() {
  try {
    const raw = localStorage.getItem(STORAGE_KEY)
    if (!raw) return []
    const parsed = JSON.parse(raw)
    return Array.isArray(parsed) ? parsed : []
  } catch {
    return []
  }
}

function persistLocal(list) {
  try {
    if (Array.isArray(list) && list.length > 0) {
      localStorage.setItem(STORAGE_KEY, JSON.stringify(list))
    }
  } catch {
    /* ignore */
  }
}

async function loadTicker() {
  try {
    const res = await fetch('/api/crypto-prices', { cache: 'no-store' })
    if (!res.ok) throw new Error('Erreur chargement')
    const json = await res.json()
    const list = Array.isArray(json?.items)
      ? json.items
      : Array.isArray(json?.prices)
        ? json.prices
        : Array.isArray(json)
          ? json
          : []

    if (list.length > 0) {
      items.value = list
      persistLocal(list)
      stale.value = !!json?.stale
      tickerError.value = null
    } else if (items.value.length === 0) {
      tickerError.value = 'Impossible de charger les cours.'
    } else {
      stale.value = true
    }
  } catch (e) {
    console.error('Erreur ticker:', e)
    if (items.value.length === 0) {
      tickerError.value = 'Impossible de charger les cours.'
    } else {
      stale.value = true
    }
  }
}

onMounted(() => {
  loadTicker()
  tickerTimer = setInterval(loadTicker, 90000)
})
onUnmounted(() => {
  if (tickerTimer) clearInterval(tickerTimer)
})
</script>

<style scoped>
@keyframes marquee {
  0% {
    transform: translateX(100%);
  }
  100% {
    transform: translateX(-100%);
  }
}
.animate-marquee {
  display: inline-block;
  white-space: nowrap;
  animation: marquee linear infinite;
}
.type-badge {
  font-size: 0.62rem;
  font-weight: 700;
  letter-spacing: 0.04em;
  line-height: 1;
  padding: 0.2rem 0.35rem;
  border-radius: 0.3rem;
  opacity: 0.9;
}
/* CRY = crypto · FIAT = devises · ACT = actions */
.type-crypto {
  background: rgba(56, 189, 248, 0.18);
  color: #7dd3fc;
  border: 1px solid rgba(56, 189, 248, 0.35);
}
.type-fiat {
  background: rgba(251, 191, 36, 0.16);
  color: #fcd34d;
  border: 1px solid rgba(251, 191, 36, 0.35);
}
.type-stock {
  background: rgba(167, 139, 250, 0.16);
  color: #c4b5fd;
  border: 1px solid rgba(167, 139, 250, 0.35);
}
</style>
