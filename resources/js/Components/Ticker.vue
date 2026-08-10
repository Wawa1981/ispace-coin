<template>
  <div class="absolute top-0 left-0 w-full overflow-hidden z-40" :style="{ height: tickerHeightPx }">
    <div
      class="inline-block w-max h-full flex items-center px-4 animate-marquee backdrop-blur-sm ring-1 ring-white/10 pointer-events-none"
      :style="{ animationDuration: tickerDuration }">
      <template v-if="coins.length && !tickerError">
        <span v-for="c in coins" :key="c.id" class="mx-6">
          <strong>{{ c.name }} ({{ c.symbol?.toUpperCase() }})</strong> :
          {{ fmtPrice(c.current_price) }}
          <span :class="(c.price_change_percentage_24h ?? 0) < 0 ? 'text-red-400' : 'text-emerald-400'">
            {{ fmtChange(c.price_change_percentage_24h) }}
          </span>
        </span>
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

const coins = ref([])
const tickerError = ref(null)
const secondsPerItem = ref(5)
const tickerHeight = ref(48)

const tickerHeightPx = computed(() => `${tickerHeight.value}px`)
const tickerDuration = computed(() => `${Math.max(1, coins.value.length) * secondsPerItem.value}s`)

let tickerTimer = null
function fmtPrice(v) { return new Intl.NumberFormat('fr-FR',{style:'currency',currency:'USD'}).format(v ?? 0) }
function fmtChange(v) { if(v==null||Number.isNaN(v)) return 'N/A'; return `${Number(v).toFixed(2)}%` }

async function loadTicker() {
  try {
    const res = await fetch('/api/crypto-prices', { cache: 'no-store' })
    if (!res.ok) throw new Error('Erreur chargement')
    const json = await res.json()
    coins.value = Array.isArray(json) ? json : Array.isArray(json?.prices) ? json.prices : []
    tickerError.value = null
  } catch(e) {
    console.error('Erreur ticker:', e)
    tickerError.value = 'Impossible de charger les cours.'
  }
}

onMounted(() => { loadTicker(); tickerTimer=setInterval(loadTicker,60000) })
onUnmounted(() => { if(tickerTimer) clearInterval(tickerTimer) })
</script>

<style scoped>
@keyframes marquee {0%{transform:translateX(100%)}100%{transform:translateX(-100%)}}
.animate-marquee { display:inline-block; white-space:nowrap; animation:marquee linear infinite; }
</style>
