<template>
  <header class="w-full p-12 flex flex-col md:flex-row justify-between items-center z-10 gap-4">
    <div class="flex items-center gap-4">
      <!-- Logo local -->
      <img
        :src="`/image/coins/${coinId.toLowerCase()}.png`"
        :alt="coinId"
        class="w-10 h-10"
        @error="onImageError"
      />

      <div class="flex flex-col">
        <h1 class="text-3xl font-bold tracking-tight">
          {{ coinId.toUpperCase() }}
        </h1>
        <p class="text-xl font-semibold">
          {{ currentPrice ? fmtCurrency(currentPrice) : '...' }}
          <span :class="ohlcInfo.change >= 0 ? 'text-green-500' : 'text-red-500'">
            ({{ ohlcInfo.change >= 0 ? '+' : ''}}{{ ohlcInfo.change.toFixed(2) }}%)
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

const props = defineProps({
  coinId: { type: String, required: true },
  currentPrice: { type: Number, default: null },
  ohlcInfo: {
    type: Object,
    default: () => ({ open: 0, high: 0, low: 0, close: 0, change: 0 })
  }
})

// Fallback vers un logo générique si l’image n’existe pas
function onImageError(e) {
  e.target.src = '/image/coins/default.png' // crée un default.png pour éviter les erreurs
}

function fmtCurrency(v) {
  return new Intl.NumberFormat('fr-FR', {
    style: 'currency',
    currency: 'USD'
  }).format(v ?? 0)
}
</script>
