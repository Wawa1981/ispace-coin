<template>
  <div :class="[bgClass, textClass]" class="min-h-screen flex flex-col relative overflow-hidden font-sans antialiased">
    <Starfield />
    <ThemeSwitcher />
    <Ticker />

    <button @click="closeChart"
      class="absolute top-8 left-4 px-3 py-2 bg-gray-800 hover:bg-gray-700 rounded-md text-sm font-medium z-50 transition-colors">
      ✕
    </button>

    <div class="flex flex-col flex-grow items-center w-full max-w-7xl mx-auto px-6 z-10">
      <CryptoHeader :coin-id="coinId" :current-price="currentPrice" :ohlc-info="ohlcInfo" />

      <div class="flex justify-center gap-8 mb-6">
        <CryptoTabs :tabs="tabs" :current-tab="currentTab" @update:tab="currentTab = $event" />
      </div>

      <main class="flex-grow w-full flex flex-col items-center justify-center gap-6">
        <CryptoChart
          v-if="currentTab === 'chart'"
          :coin-id="coinId"
          @update:price="updatePriceInfo"
        />
        
        <OrderBook v-if="currentTab === 'orderbook'" :current-price="currentPrice" />
        <PreventionSection v-if="currentTab === 'prevention'" />
        <ContactForm v-if="currentTab === 'contact'" />
      </main>
    </div>

    <footer class="w-full p-6 text-center text-sm opacity-70 mt-8 border-t border-gray-800">
      © 2015 CryptoBank — Tous droits réservés. | 
      <a href="#" class="hover:underline">Conditions d'utilisation</a> | 
      <a href="#" class="hover:underline">Politique de confidentialité</a>
    </footer>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { usePage } from '@inertiajs/vue3'
import { useTheme } from '@/composables/useTheme'

import Starfield from '@/Components/Starfield.vue'
import ThemeSwitcher from '@/Components/ThemeSwitcher.vue'
import Ticker from '@/Components/Ticker.vue'
import CryptoHeader from '@/Components/CryptoHeader.vue'
import CryptoTabs from '@/Components/CryptoTabs.vue'
import CryptoChart from '@/Components/CryptoChart.vue'
import OrderBook from '@/Components/OrderBook.vue'
import PreventionSection from '@/Components/PreventionSection.vue'
import ContactForm from '@/Components/ContactForm.vue'

const { props } = usePage()
const coinId = props.coinId
const currentTab = ref('chart')
const currentPrice = ref(null)
const ohlcInfo = ref({ open: 0, high: 0, low: 0, close: 0, change: 0 })

const { isLight, bgClass, textClass, titleClass, toggleLight, cycleDarkTheme } = useTheme()

const tabs = [
  { label: 'Graphique', value: 'chart' },
  { label: 'Carnet d\'ordres', value: 'orderbook' },
  { label: 'Prévention', value: 'prevention' },
  { label: 'Contact', value: 'contact' },
]

function closeChart() {
  window.history.back()
}

function updatePriceInfo(data) {
  currentPrice.value = data.price
  ohlcInfo.value = data.ohlc
}
</script>
