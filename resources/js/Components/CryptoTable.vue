<template>
  <section class="py-6">
    <div class="max-w-5xl mx-auto px-4">
      <h1 :class="['text-3xl md:text-4xl font-extrabold mb-6 text-center', titleClass]">
      </h1>
      <div :class="isLight ? 'bg-white/80' : 'bg-white/5'"
        class="backdrop-blur-lg rounded-2xl shadow-xl ring-1 ring-white/10 overflow-hidden">

        <div class="flex items-center justify-between px-6 py-4">
          <h2 class="text-lg md:text-xl font-bold">Cours des cryptos</h2>
          <div class="flex items-center gap-3 md:gap-4">
            <input v-model="query" type="text" placeholder="Rechercher une crypto…"
              class="hidden md:block rounded-lg px-3 py-2 bg-transparent ring-1 ring-white/10 focus:outline-none"
              :class="isLight ? 'text-gray-800 placeholder:text-gray-500' : 'text-white placeholder:text-gray-300'" />
            <button @click="toggleTableCollapse"
              class="text-sm font-medium px-3 py-2 rounded-lg bg-transparent ring-1 ring-white/10 hover:bg-white/10 transition"
              :class="isLight ? 'text-gray-800' : 'text-white'">
              {{ isTableCollapsed ? 'Voir tous' : 'Réduire' }}
            </button>
          </div>
        </div>

        <div class="overflow-x-auto overflow-y-auto max-h-[48vh] scrollbox">
          <table class="min-w-full table-auto">
            <thead :class="isLight ? 'bg-gray-100' : 'bg-white/10'" class="sticky top-0 z-10">
              <tr>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide">#</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide">Pièces</th>
                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide">Prix</th>
                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide">Changement 24h</th>
                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide">Capitalisation</th>
              </tr>
            </thead>
            <tbody :class="isLight ? 'divide-y divide-gray-200' : 'divide-y divide-white/10'">
              <tr v-for="(c, i) in filteredMarkets" :key="c.id" @click="goCoin(c.id)"
                class="hover:bg-white/5 cursor-pointer transition hover:scale-[1.01]">
                <td class="px-4 py-3 text-sm opacity-80">{{ i + 1 }}</td>
                <td class="px-4 py-3">
                  <div class="flex items-center gap-3">
                    <img
                      :src="c.image"
                      alt=""
                      class="w-6 h-6 rounded-full"
                      loading="lazy"
                      @error="onImgError"
                    />
                    <div class="flex flex-col">
                      <span class="text-sm font-medium">{{ c.name }}</span>
                      <span class="text-xs opacity-70">{{ c.symbol?.toUpperCase() }}</span>
                    </div>
                  </div>
                </td>
                <td class="px-4 py-3 text-right text-sm">{{ fmtCurrency(c.current_price) }}</td>
                <td class="px-4 py-3 text-right text-sm"
                  :class="(c.price_change_percentage_24h ?? 0) < 0 ? 'text-red-400' : 'text-emerald-400'">
                  {{ fmtPercent(c.price_change_percentage_24h) }}
                </td>
                <td class="px-4 py-3 text-right text-sm">{{ fmtCompact(c.market_cap) }}</td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="px-6 py-4 text-xs opacity-70">
          Cours multi-source (CoinGecko / CryptoCompare / Binance…) — actualisés périodiquement.
          <span v-if="!markets?.length" class="ml-1 opacity-80">Chargement…</span>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'

// On passe les props nécessaires pour la réutilisation
const props = defineProps({
  markets: { type: Array, required: true },
  isLight: { type: Boolean, required: true },
  titleClass: { type: String, required: true },
})

const query = ref('')
const isTableCollapsed = ref(true)

const filteredMarkets = computed(() => {
  const q = query.value.trim().toLowerCase()
  let list = props.markets
  if (q) {
    list = list.filter(c => (c.name || '').toLowerCase().includes(q) || (c.symbol || '').toLowerCase().includes(q))
  }
  return isTableCollapsed.value ? list.slice(0, 10) : list
})

function toggleTableCollapse() { isTableCollapsed.value = !isTableCollapsed.value }
function goCoin(id) { router.visit(`/crypto/${id}`) }

function onImgError(e) {
  // Une seule fois → placeholder neutre (jamais une autre crypto)
  if (e.target.dataset.fb === '1') return
  e.target.dataset.fb = '1'
  e.target.src = '/image/coins/default.svg'
}

// Les fonctions de formatage
function fmtCurrency(v) { return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'USD' }).format(v ?? 0) }
function fmtPercent(v) { if (v == null || Number.isNaN(v)) return '—'; return `${Number(v).toFixed(2)}%` }
function fmtCompact(v) { if (v == null) return '—'; return new Intl.NumberFormat('fr-FR', { notation: 'compact', maximumFractionDigits: 1 }).format(v) }
</script>

<style scoped>
/* Scrollbar invisible dans le tableau */
:deep(.scrollbox) {
  scrollbar-width: none;
  -ms-overflow-style: none;
  scrollbar-gutter: auto;
}

:deep(.scrollbox::-webkit-scrollbar) {
  width: 0;
  height: 0;
  display: none;
}
</style>
