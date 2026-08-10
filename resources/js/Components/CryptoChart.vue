<template>
  <div class="w-full flex-grow flex flex-col bg-gray-800 rounded-xl shadow-xl overflow-hidden relative">
    <!-- Conteneur du graphique -->
    <div ref="chartContainer" class="w-full flex-grow h-[600px]"></div>

    <!-- Contrôles timeframes et mode -->
    <div class="flex flex-wrap gap-3 p-4 bg-gray-850">
      <div class="flex gap-2">
        <button
          v-for="t in timeframes"
          :key="t.value"
          @click="loadChart(t.value)"
          :class="[
            'px-3 py-1.5 rounded-md text-sm font-medium transition-colors',
            timeframe === t.value ? 'bg-blue-600 text-white' : 'bg-gray-700 hover:bg-gray-600 text-gray-300'
          ]">
          {{ t.label }}
        </button>
      </div>

      <button
        @click="toggleChartType"
        class="px-3 py-1.5 rounded-md bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium transition-colors">
        Mode : {{ chartType === 'candlestick' ? 'Chandeliers' : 'Ligne' }}
      </button>
    </div>
  </div>
</template>

<script setup>
import { onMounted, onUnmounted, ref, watch } from 'vue'
import { createChart } from 'lightweight-charts'

// Props + events
const props = defineProps({
  coinId: { type: String, required: true }
})
const emit = defineEmits(['update:price'])

// Réfs & état
const chartContainer = ref(null)
let chart = null
let mainSeries = null

// ⚠️ Par défaut 1 jour et chandeliers
const chartType = ref('candlestick') // 'candlestick' | 'line'
const timeframe = ref('1')           // valeurs compatibles CoinGecko OHLC: 1,7,14,30,90,180,365,max

// Caches pour limiter l’API gratuite
const cacheOHLC = {}   // { '<coin>_<days>': [ {time,open,high,low,close}, ... ] }
const cachePrices = {} // { '<coin>_<days>': [ [ts, price], ... ] }

let resizeObserver = null

// ✅ Timeframes allégés (évite 180j/365j lourds par défaut)
const timeframes = [
  { label: '1j',  value: '1' },
  { label: '7j',  value: '7' },
  { label: '30j', value: '30' },
  { label: '90j', value: '90' },
]

// -------- Utils --------
function downsamplePrices(prices, maxPoints = 400) {
  if (!prices?.length) return []
  const step = Math.max(1, Math.ceil(prices.length / maxPoints))
  const out = []
  for (let i = 0; i < prices.length; i += step) {
    const p = prices[i]
    out.push({ time: Math.floor(p[0] / 1000), value: p[1] })
  }
  return out
}

function emitPriceFromCandles(candles) {
  if (!candles?.length) return
  const last = candles[candles.length - 1]
  const info = {
    open: last.open,
    high: last.high,
    low:  last.low,
    close: last.close,
    change: ((last.close - last.open) / last.open) * 100,
  }
  emit('update:price', { price: last.close, ohlc: info })
}

function emitPriceFromLine(lineData) {
  if (!lineData?.length) return
  const values = lineData.map(d => d.value)
  const first = values[0]
  const last  = values[values.length - 1]
  const info = {
    open: first,
    high: Math.max(...values),
    low:  Math.min(...values),
    close: last,
    change: ((last - first) / first) * 100,
  }
  emit('update:price', { price: last, ohlc: info })
}

// -------- Data fetchers (avec cache) --------
async function fetchOHLC(coinId, days) {
  const key = `${coinId}_${days}`
  if (cacheOHLC[key]) return cacheOHLC[key]

  const url = `https://api.coingecko.com/api/v3/coins/${coinId}/ohlc?vs_currency=usd&days=${days}`
  const res = await fetch(url)
  if (!res.ok) throw new Error(`OHLC ${res.status}`)
  const raw = await res.json() // [[ts, open, high, low, close], ...]
  const data = raw.map(d => ({
    time: Math.floor(d[0] / 1000),
    open: d[1], high: d[2], low: d[3], close: d[4],
  }))
  cacheOHLC[key] = data
  return data
}

async function fetchPrices(coinId, days) {
  const key = `${coinId}_${days}`
  if (cachePrices[key]) return cachePrices[key]

  const url = `https://api.coingecko.com/api/v3/coins/${coinId}/market_chart?vs_currency=usd&days=${days}`
  const res = await fetch(url)
  if (!res.ok) throw new Error(`PRICES ${res.status}`)
  const data = await res.json()
  cachePrices[key] = data.prices || []
  return cachePrices[key]
}

// -------- Rendu chart --------
function drawCandles(candles) {
  if (!chart) return
  if (mainSeries) { try { chart.removeSeries(mainSeries) } catch {} }
  mainSeries = chart.addCandlestickSeries({
    upColor: '#10b981', borderUpColor: '#10b981', wickUpColor: '#10b981',
    downColor: '#ef4444', borderDownColor: '#ef4444', wickDownColor: '#ef4444',
  })
  mainSeries.setData(candles)
  chart.timeScale().fitContent()
}

function drawLine(lineData) {
  if (!chart) return
  if (mainSeries) { try { chart.removeSeries(mainSeries) } catch {} }
  mainSeries = chart.addLineSeries({ color: '#3b82f6', lineWidth: 2, priceLineVisible: true })
  mainSeries.setData(lineData)
  chart.timeScale().fitContent()
}

// -------- Actions --------
async function loadChart(days) {
  timeframe.value = days

  try {
    if (chartType.value === 'candlestick') {
      // ⚡ Vraies bougies → endpoint OHLC
      const candles = await fetchOHLC(props.coinId, days)
      drawCandles(candles)
      emitPriceFromCandles(candles)
    } else {
      // ⚡ Courbe → endpoint market_chart (ou dérivé d’OHLC si déjà en cache)
      const key = `${props.coinId}_${days}`
      let prices = cachePrices[key]
      if (!prices) {
        // Si on a déjà l’OHLC en cache, on peut dériver une courbe des "close" sans refaire un appel
        const candles = cacheOHLC[key]
        if (candles?.length) {
          const lineData = candles.map(c => ({ time: c.time, value: c.close }))
          const ds = downsample(lineData, 400)
          drawLine(ds)
          emitPriceFromLine(ds)
          return
        }
        // Sinon, on va chercher les prices
        prices = await fetchPrices(props.coinId, days)
      }
      const line = downsamplePrices(prices, 400)
      drawLine(line)
      emitPriceFromLine(line)
    }
  } catch (e) {
    console.error('loadChart error:', e?.message || e)
  }
}

function toggleChartType() {
  chartType.value = chartType.value === 'candlestick' ? 'line' : 'candlestick'
  // On réutilise le cache, pas de nouvel appel si possible
  loadChart(timeframe.value)
}

// Downsample générique pour lineData déjà formaté [{time,value}]
function downsample(arr, maxPoints = 400) {
  if (!arr?.length) return []
  const step = Math.max(1, Math.ceil(arr.length / maxPoints))
  const out = []
  for (let i = 0; i < arr.length; i += step) out.push(arr[i])
  return out
}

// -------- Lifecycle --------
onMounted(() => {
  if (!chartContainer.value) return
  chart = createChart(chartContainer.value, {
    width: chartContainer.value.clientWidth,
    height: chartContainer.value.clientHeight,
    layout: { background: { color: '#111827' }, textColor: '#f3f4f6' },
    grid: { vertLines: { color: '#1f2937' }, horzLines: { color: '#1f2937' } },
    crosshair: { mode: 1 },
    timeScale: { timeVisible: true, secondsVisible: false },
  })

  loadChart(timeframe.value)

  resizeObserver = new ResizeObserver(() => {
    if (chart && chartContainer.value) {
      chart.applyOptions({
        width: chartContainer.value.clientWidth,
        height: chartContainer.value.clientHeight
      })
    }
  })
  resizeObserver.observe(chartContainer.value)
})

onUnmounted(() => {
  if (chart) chart.remove()
  if (resizeObserver && chartContainer.value) {
    resizeObserver.unobserve(chartContainer.value)
    resizeObserver.disconnect()
  }
})

// Si on change de coin, on recharge (en profitant du cache si dispo)
watch(() => props.coinId, () => loadChart(timeframe.value))
</script>
