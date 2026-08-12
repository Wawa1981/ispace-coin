<template>
  <header class="w-full p-12 flex flex-col md:flex-row justify-between items-center z-10 gap-4">
    <div class="flex items-center gap-4">
      <img
        :src="iconSrc"
        :alt="coinId"
        class="w-10 h-10 rounded-full bg-slate-800 object-cover"
        @error="onImageError"
      />

      <div class="flex flex-col">
        <h1 class="text-3xl font-bold tracking-tight">
          {{ coinId.toUpperCase() }}
        </h1>
        <p class="text-xl font-semibold">
          {{ currentPrice ? fmtCurrency(currentPrice) : '...' }}
          <span :class="ohlcInfo.change >= 0 ? 'text-green-500' : 'text-red-500'">
            ({{ ohlcInfo.change >= 0 ? '+' : '' }}{{ ohlcInfo.change.toFixed(2) }}%)
          </span>
        </p>
      </div>
    </div>

    <div class="text-sm flex gap-6">
      <span class="text-blue-400">O: {{ fmtCurrency(ohlcInfo.open) }}</span>
      <span class="text-green-400">H: {{ fmtCurrency(ohlcInfo.high) }}</span>
      <span class="text-red-400">L: {{ fmtCurrency(ohlcInfo.low) }}</span>
      <span :class="ohlcInfo.close >= ohlcInfo.open ? 'text-green-500' : 'text-red-500'">
        C: {{ fmtCurrency(ohlcInfo.close) }}
      </span>
    </div>
  </header>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue'

const props = defineProps({
  coinId: { type: String, required: true },
  currentPrice: { type: Number, default: null },
  ohlcInfo: {
    type: Object,
    default: () => ({ open: 0, high: 0, low: 0, close: 0, change: 0 }),
  },
})

const NEUTRAL = '/image/coins/default.svg'
const iconSrc = ref(NEUTRAL)
const stage = ref(0) // 0=coingecko, 1=local, 2=neutral done

async function resolveIcon(id) {
  stage.value = 0
  const coinId = String(id || '').toLowerCase()

  // 1) Vraie image CoinGecko via notre API markets
  try {
    const res = await fetch(`/api/markets?vs=usd&per_page=250&page=1`, { cache: 'force-cache' })
    if (res.ok) {
      const data = await res.json()
      const list = Array.isArray(data) ? data : []
      const coin = list.find((c) => c.id === coinId || c.symbol?.toLowerCase() === coinId)
      if (coin?.image) {
        iconSrc.value = coin.image
        return
      }
    }
  } catch (_) {
    /* ignore */
  }

  // 2) Fichier local si on l’a vraiment (bitcoin, ethereum, …)
  stage.value = 1
  iconSrc.value = `/image/coins/${coinId}.png`
}

function onImageError() {
  if (stage.value === 0) {
    // CoinGecko a échoué → essai local
    stage.value = 1
    iconSrc.value = `/image/coins/${String(props.coinId).toLowerCase()}.png`
    return
  }
  if (stage.value === 1) {
    // Local absent → placeholder neutre (PAS le logo bitcoin)
    stage.value = 2
    iconSrc.value = NEUTRAL
  }
}

watch(
  () => props.coinId,
  (id) => resolveIcon(id),
  { immediate: true },
)

onMounted(() => resolveIcon(props.coinId))

function fmtCurrency(v) {
  return new Intl.NumberFormat('fr-FR', {
    style: 'currency',
    currency: 'USD',
  }).format(v ?? 0)
}
</script>
