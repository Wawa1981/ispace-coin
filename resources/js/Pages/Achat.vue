<template>
  <div :class="[bgClass, textClass]" class="min-h-screen relative overflow-x-hidden transition-colors duration-700 flex">
    
    <Starfield />

    <Sidebar 
      :isSidebarOpen="isSidebarOpen"
      :isLight="isLight"
      :items="items"
      :tickerHeightPx="tickerHeightPx"
      @toggle="toggleSidebar"
    >
      <template #icon="{ icon, isLight }">
        <svg class="w-6 h-6 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor"
          :class="isLight ? 'text-black/80' : 'text-white/90'" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
          <template v-if="icon === 'home'"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></template>
          <template v-else-if="icon === 'hex'"><path d="M12 3l7 4v10l-7 4-7-4V7z"/></template>
          <template v-else-if="icon === 'orbit'"><circle cx="12" cy="12" r="4"/><path d="M3 12c3-6 15-6 18 0M3 12c3 6 15 6 18 0"/></template>
          <template v-else-if="icon === 'spark'"><path d="M4 16l6-8 4 5 6-9"/><circle cx="5" cy="17" r="1"/><circle cx="18" cy="5" r="1"/></template>
          <template v-else-if="icon === 'stack'"><path d="M4 10l8-4 8 4-8 4-8-4z"/><path d="M4 14l8 4 8-4"/></template>
          <template v-else-if="icon === 'flow'"><path d="M4 8c6 0 6 8 12 8h4"/><path d="M20 16l-2-2 2-2"/></template>
          <template v-else-if="icon === 'id'"><rect x="6" y="6" width="12" height="12" rx="2"/><circle cx="12" cy="11" r="2"/><path d="M9 15h6"/></template>
          <template v-else-if="icon === 'cards'"><rect x="7" y="7" width="10" height="7" rx="1.6"/><rect x="5" y="10" width="10" height="7" rx="1.6"/></template>
          <template v-else-if="icon === 'pulse'"><path d="M3 12h4l2-3 3 6 2-3h7"/><circle cx="6" cy="12" r="1"/></template>
        </svg>
      </template>
    </Sidebar>

    <main class="relative z-10 w-full pb-16 transition-all duration-300 px-8" :style="{ paddingTop: contentPadTopPx }">
      
      <Ticker />

      <ThemeSwitcher />
      
      <section class="flex flex-col items-center justify-start w-full min-h-[calc(100vh-10rem)] pt-20 px-6">
        <h1 :class="['text-3xl md:text-4xl font-extrabold mb-8 text-center', titleClass]">Choisir une cryptomonnaie</h1>

        <div class="w-full max-w-4xl grid gap-4 md:grid-cols-2 lg:grid-cols-3">
          <div v-for="crypto in markets" :key="crypto.id" :class="isLight ? 'bg-white/80' : 'bg-white/5'"
            class="flex items-center rounded-2xl p-4 shadow-lg hover:shadow-xl transition-all duration-300 cursor-pointer hover:bg-white/10"
            @click="$inertia.visit(`/deposit/confirm/${crypto.symbol}`)">
            <img :src="crypto.image" :alt="`Logo de ${crypto.name}`" class="w-12 h-12 mr-4 rounded-full"/>
            <div>
              <div class="text-lg font-bold">{{ crypto.name }}</div>
              <div :class="isLight ? 'text-gray-600' : 'text-gray-400'" class="text-sm font-medium">
                {{ crypto.symbol.toUpperCase() }} — {{ fmtCurrency(crypto.current_price) }}
              </div>
            </div>
          </div>
        </div>
        <div class="mt-6 text-sm opacity-70 text-center">Données CoinGecko — top 50 cryptos.</div>
      </section>

    </main>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

// Importation des composants
import Sidebar from '@/Components/Sidebar.vue'
import Starfield from '@/Components/Starfield.vue'
import Ticker from '@/Components/Ticker.vue'
import ThemeSwitcher from '@/Components/ThemeSwitcher.vue' 

// Importation des composables
import { useTheme } from '@/composables/useTheme'
import { useCryptoMarkets } from '@/composables/useCryptoMarkets'

// Logique de la page
const isSidebarOpen = ref(true)
function toggleSidebar() { isSidebarOpen.value = !isSidebarOpen.value }

// Utilisation des composables
const { isLight, bgClass, textClass, titleClass, toggleLight, cycleDarkTheme } = useTheme()
const { markets } = useCryptoMarkets()

// Configuration du layout
const tickerHeight = ref(48)
const gapUnderTicker = ref(32)
const contentPadTopPx = computed(() => `${tickerHeight.value + gapUnderTicker.value}px`)
const tickerHeightPx = computed(() => `${tickerHeight.value}px`)

// Liste de navigation de la sidebar
const items = [
  { route: 'dashboard', label: 'Tableau de bord', icon: 'home' },
  { route: 'achat', label: 'Achat', icon: 'hex' },
  { route: 'retrait', label: 'Retrait', icon: 'orbit' },
  { route: 'deposit', label: 'Dépôt', icon: 'spark' },
  { route: 'envoyer', label: 'Envoyer', icon: 'stack' },
  { route: 'echange', label: 'Échange', 'icon': 'flow' },
  { route: 'compte', label: 'Compte', icon: 'id' },
  { route: 'cartes', label: 'Cartes', icon: 'cards' },
  { route: 'crypto', label: 'Marché', icon: 'pulse' },
]

// Fonction de formatage (laisser ici car elle est unique à la liste d'achat)
function fmtCurrency(v) { return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'USD' }).format(v ?? 0) }
</script>

<style scoped>
/* Les styles qui ne sont pas dans les composants */
.neon-text-night {
  text-shadow: 0 0 6px #fff, 0 0 12px #0ff, 0 0 24px #0ff, 0 0 48px #0ff, 0 0 96px #0ff;
}
.title-gradient {
  background: linear-gradient(90deg, #2563eb, #a855f7);
  -webkit-background-clip: text;
  background-clip: text;
  color: transparent;
  text-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
}
</style>
